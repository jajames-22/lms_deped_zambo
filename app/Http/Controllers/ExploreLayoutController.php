<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExplorePageSection;
use App\Models\Material;

class ExploreLayoutController extends Controller
{
    public function index()
    {
        $sections = ExplorePageSection::orderBy('order', 'asc')->get();
        
        // Fetch currently featured materials
        $featuredMaterials = Material::with('instructor')
            ->where('is_featured', true)
            ->get();
        
        $availableTags = [
            "Science", "Earth Science", "Computer Science", "Biology", "Chemistry", "Physics",
            "Mathematics", "Algebra", "Calculus", "Geometry",
            "English", "Literature", "Grammar", "Filipino", "Pananaliksik",
            "MAPEH", "Music", "Arts", "Physical Education", "Health",
            "History", "World History", "Philippine History", "Contemporary Issues",
            "Technology", "Programming", "Web Development", "First Aid"
        ];

        return view('dashboard.partials.admin.explore-layout', compact('sections', 'availableTags', 'featuredMaterials'));
    }

    // NEW: Live search for the featured materials dropdown
    public function searchMaterials(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) return response()->json([]);

        $materials = Material::with('instructor')
            ->where('status', 'published')
            ->where('is_public', true)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhereHas('instructor', function($q2) use ($query) {
                      $q2->where('first_name', 'like', "%{$query}%")
                         ->orWhere('last_name', 'like', "%{$query}%");
                  });
            })
            ->take(6)
            ->get(['id', 'title', 'thumbnail', 'instructor_id']); // Keep payload small

        return response()->json($materials);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'tag_name' => 'required|string',
        ]);

        $maxOrder = ExplorePageSection::max('order') ?? 0;

        ExplorePageSection::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'tag_name' => $request->tag_name,
            'order' => $maxOrder + 1,
            'is_active' => true
        ]);

        return response()->json(['success' => true, 'message' => 'Section added successfully!']);
    }

    public function update(Request $request, ExplorePageSection $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'tag_name' => 'required|string',
        ]);

        $section->update($request->only(['title', 'subtitle', 'tag_name']));

        return response()->json(['success' => true, 'message' => 'Section updated!']);
    }

    public function destroy(ExplorePageSection $section)
    {
        $section->delete();
        return response()->json(['success' => true, 'message' => 'Section deleted!']);
    }

    public function toggleActive(ExplorePageSection $section)
    {
        $section->update(['is_active' => !$section->is_active]);
        return response()->json([
            'success' => true, 
            'is_active' => $section->is_active,
            'message' => 'Visibility updated!'
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:explore_page_sections,id'
        ]);

        foreach ($request->ordered_ids as $index => $id) {
            ExplorePageSection::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}