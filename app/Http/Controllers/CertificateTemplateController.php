<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateController extends Controller
{
    // ─── INDEX ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $templates = CertificateTemplate::orderBy('is_default', 'desc')->latest()->get();

        // Seed the built-in default if the table is empty
        if ($templates->isEmpty()) {
            CertificateTemplate::create([
                'name'       => 'Default Template',
                'is_active'  => true,
                'is_default' => true,
                'elements'   => $this->defaultElements(),
            ]);
            $templates = CertificateTemplate::latest()->get();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return view('dashboard.partials.admin.certificates.index', compact('templates'));
        }

        return view('dashboard.partials.admin.certificates.index', compact('templates'));
    }

    // ─── CREATE (editor page) ──────────────────────────────────────────────────
    public function create()
    {
        return view('dashboard.partials.admin.certificates.editor');
    }

    // ─── STORE ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:120',
            'elements'         => 'required|string',    // JSON from the canvas editor
            'background_image' => 'nullable|image|max:10240',
        ]);

        $bgPath = null;
        if ($request->hasFile('background_image')) {
            $bgPath = $request->file('background_image')
                ->store('certificate-backgrounds', 'public');
        }

        CertificateTemplate::create([
            'name'             => $request->name,
            'background_image' => $bgPath,
            'elements'         => json_decode($request->elements, true),
            'is_active'        => false,
            'is_default'       => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Template saved successfully.']);
    }

    // ─── EDIT (editor pre-filled) ──────────────────────────────────────────────
    public function edit(CertificateTemplate $template)
    {
        $template->load('exclusiveMaterials');
        return view('dashboard.partials.admin.certificates.editor', compact('template'));
    }

    // ─── UPDATE ────────────────────────────────────────────────────────────────
    public function update(Request $request, CertificateTemplate $template)
    {
        $request->validate([
            'name'             => 'required|string|max:120',
            'elements'         => 'required|string',
            'background_image' => 'nullable|image|max:10240',
        ]);

        $bgPath = $template->background_image;
        if ($request->hasFile('background_image')) {
            // Remove old background
            if ($bgPath) Storage::disk('public')->delete($bgPath);
            $bgPath = $request->file('background_image')
                ->store('certificate-backgrounds', 'public');
        }

        $template->update([
            'name'             => $request->name,
            'background_image' => $bgPath,
            'elements'         => json_decode($request->elements, true),
        ]);

        return response()->json(['success' => true, 'message' => 'Template updated successfully.']);
    }

    // ─── SET ACTIVE ────────────────────────────────────────────────────────────
    public function setActive(CertificateTemplate $template)
    {
        // Deactivate all others first
        CertificateTemplate::where('id', '!=', $template->id)
            ->update(['is_active' => false]);

        $template->update(['is_active' => true]);

        return response()->json(['success' => true, 'message' => '"' . $template->name . '" is now the active template.']);
    }

    // ─── DESTROY ───────────────────────────────────────────────────────────────
    public function destroy(CertificateTemplate $template)
    {
        if ($template->is_default) {
            return response()->json(['success' => false, 'message' => 'The default template cannot be deleted.'], 422);
        }

        // If deleting the active one, fall back to default
        if ($template->is_active) {
            CertificateTemplate::where('is_default', true)->update(['is_active' => true]);
        }

        if ($template->background_image) {
            Storage::disk('public')->delete($template->background_image);
        }

        $template->delete();

        return response()->json(['success' => true, 'message' => 'Template deleted.']);
    }

    // ─── MODULE SEARCH ─────────────────────────────────────────────────────────
    /**
     * Typeahead search: returns published modules whose title contains the query.
     */
    public function searchModules(Request $request)
    {
        $q = trim($request->get('q', ''));

        $modules = Material::with('instructor:id,first_name,last_name')
            ->where('status', 'published')
            ->when($q !== '', fn($query) => $query->where('title', 'like', "%{$q}%"))
            ->select('id', 'title', 'thumbnail', 'instructor_id', 'exclusive_template_id')
            ->orderBy('title')
            ->limit(15)
            ->get()
            ->map(fn($m) => [
                'id'                    => $m->id,
                'title'                 => $m->title,
                'thumbnail'             => $m->thumbnail ? asset('storage/' . $m->thumbnail) : null,
                'instructor_name'       => $m->instructor ? $m->instructor->first_name . ' ' . $m->instructor->last_name : 'Unknown',
                'exclusive_template_id' => $m->exclusive_template_id,
            ]);

        return response()->json($modules);
    }

    // ─── ASSIGN MODULE ─────────────────────────────────────────────────────────
    /**
     * Set a module's exclusive template to this template.
     */
    public function assignModule(Request $request, CertificateTemplate $template)
    {
        $request->validate(['material_id' => 'required|integer|exists:materials,id']);

        $material = Material::findOrFail($request->material_id);
        $material->update(['exclusive_template_id' => $template->id]);

        return response()->json([
            'success' => true,
            'message' => "'{$material->title}' is now exclusively using this template.",
            'module'  => ['id' => $material->id, 'title' => $material->title],
        ]);
    }

    // ─── UNASSIGN MODULE ───────────────────────────────────────────────────────
    /**
     * Remove the exclusive template assignment from a module.
     */
    public function unassignModule(CertificateTemplate $template, Material $material)
    {
        if ((int)$material->exclusive_template_id !== (int)$template->id) {
            return response()->json(['success' => false, 'message' => 'This module is not assigned to this template.'], 422);
        }

        $material->update(['exclusive_template_id' => null]);

        return response()->json(['success' => true, 'message' => "'{$material->title}' removed from exclusive list."]);
    }

    // ─── DEFAULT ELEMENT LAYOUT ────────────────────────────────────────────────
    /**
     * Positions match the built-in Blade certificate (1122×794 canvas space).
     * All values are percentages of canvas width/height for responsive scaling.
     */
    private function defaultElements(): array
    {
        return [
            ['id' => 'student_name',    'label' => 'Student Name',      'x' => 50, 'y' => 35, 'fontSize' => 48, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
            ['id' => 'course_name',     'label' => 'Module / Course',   'x' => 50, 'y' => 52, 'fontSize' => 34, 'fontWeight' => 'bold',   'color' => '#a52a2a', 'align' => 'center'],
            ['id' => 'duration',        'label' => 'Completion Time',   'x' => 50, 'y' => 63, 'fontSize' => 18, 'fontWeight' => 'normal', 'color' => '#555555', 'align' => 'center'],
            ['id' => 'instructor_name', 'label' => 'Instructor Name',   'x' => 25, 'y' => 80, 'fontSize' => 20, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
            ['id' => 'date',            'label' => 'Date of Completion', 'x' => 75, 'y' => 80, 'fontSize' => 20, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
            ['id' => 'certificate_id',  'label' => 'Certificate ID',    'x' => 70, 'y' => 93, 'fontSize' => 11, 'fontWeight' => 'normal', 'color' => '#888888', 'align' => 'left'],
            ['id' => 'qr_code',         'label' => 'QR Code',           'x' => 50, 'y' => 74, 'fontSize' => 0,  'fontWeight' => 'normal', 'color' => '#000000', 'align' => 'center', 'size' => 110],
        ];
    }
}
