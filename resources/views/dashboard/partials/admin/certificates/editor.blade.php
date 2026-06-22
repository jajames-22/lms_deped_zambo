{{-- ================================================================
     ADMIN — CERTIFICATE TEMPLATE EDITOR  (Create / Edit)
     ================================================================ --}}
@php
    $isEdit = isset($template);
    $defaultElements = [
        ['id' => 'student_name',    'label' => 'Student Name',       'x' => 50,  'y' => 35,  'fontSize' => 48, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
        ['id' => 'course_name',     'label' => 'Module / Course',    'x' => 50,  'y' => 52,  'fontSize' => 34, 'fontWeight' => 'bold',   'color' => '#a52a2a', 'align' => 'center'],
        ['id' => 'duration',        'label' => 'Completion Time',    'x' => 50,  'y' => 63,  'fontSize' => 18, 'fontWeight' => 'normal', 'color' => '#555555', 'align' => 'center'],
        ['id' => 'instructor_name', 'label' => 'Instructor Name',    'x' => 25,  'y' => 80,  'fontSize' => 20, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
        ['id' => 'date',            'label' => 'Date of Completion', 'x' => 75,  'y' => 80,  'fontSize' => 20, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
        ['id' => 'certificate_id',  'label' => 'Certificate ID',     'x' => 70,  'y' => 93,  'fontSize' => 11, 'fontWeight' => 'normal', 'color' => '#888888', 'align' => 'left'],
        ['id' => 'qr_code',         'label' => 'QR Code',            'x' => 50,  'y' => 74,  'fontSize' => 0,  'fontWeight' => 'normal', 'color' => '#000000', 'align' => 'center', 'size' => 110],
    ];
    $initialElements = $isEdit && $template->elements ? $template->elements : $defaultElements;
@endphp

<style>
    #cert-canvas { position: relative; overflow: hidden; user-select: none; }
    .cert-elem   { position: absolute; cursor: grab; border: 2px dashed transparent; border-radius: 4px; padding: 4px 8px; transition: border-color .15s; white-space: nowrap; }
    .cert-elem:hover, .cert-elem.selected { border-color: #a52a2a; background: rgba(165,42,42,.06); }
    .cert-elem .elem-handle { font-size: 11px; background: #a52a2a; color: white; border-radius: 4px; padding: 1px 6px; position: absolute; top: -18px; left: 0; white-space: nowrap; pointer-events: none; display: none; }
    .cert-elem:hover .elem-handle, .cert-elem.selected .elem-handle { display: block; }
    .tool-btn { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; border: 1px solid #e5e7eb; cursor: pointer; transition: all .15s; background: white; color: #374151; }
    .tool-btn:hover { background: #f3f4f6; }
    .tool-btn.active { background: #a52a2a; color: white; border-color: #a52a2a; }
</style>

<div class="max-w-7xl mx-auto">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <button onclick="handleBackClick()"
                class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </button>
            <div>
                <h1 class="text-2xl font-black text-gray-900">{{ $isEdit ? 'Edit Template' : 'New Certificate Template' }}</h1>
                <p class="text-sm text-gray-500">Drag the field labels to position them on your certificate background.</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="saveTemplate()" id="save-btn"
                class="px-5 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-900 transition shadow-lg shadow-[#a52a2a]/30 text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> Save Template
            </button>
        </div>
    </div>

    {{-- TOAST --}}
    <div id="editor-toast" class="fixed bg-gray-900 bottom-6 right-6 z-[9999] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white">
        <i id="editor-toast-icon" class="fas fa-check-circle text-lg"></i>
        <span id="editor-toast-msg"></span>
    </div>

    {{-- UNSAVED CHANGES MODAL --}}
    <div id="unsaved-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center">
        <!-- Backdrop -->
        <div id="unsaved-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
        
        <!-- Modal Panel -->
        <div id="unsaved-panel" class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transform scale-95 opacity-0 transition-all duration-300">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-2xl text-amber-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Unsaved Changes</h3>
            <p class="text-sm text-gray-500 mb-6">You have unsaved changes. Are you sure you want to discard them?</p>
            <div class="flex gap-3 w-full">
                <button onclick="closeUnsavedModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <button onclick="goBack()" class="flex-1 px-4 py-2 bg-amber-600 text-white font-bold rounded-xl hover:bg-amber-700 transition shadow-lg shadow-amber-600/20">Discard</button>
            </div>
        </div>
    </div>

    {{-- RENDER WARNING TOAST --}}
    <div id="render-warning-toast" class="fixed bottom-6 right-6 z-[9998] bg-white border-l-4 border-amber-500 shadow-xl rounded-xl p-4 max-w-sm flex gap-3 animate-float-in transition-all duration-300 transform">
        <i class="fas fa-info-circle text-amber-500 text-xl mt-0.5"></i>
        <div>
            <h4 class="text-sm font-bold text-gray-800">Missing Elements?</h4>
            <p class="text-xs text-gray-600 mt-1">If you don't see the draggable fields, please click the reload button <i class="fas fa-redo-alt mx-1 text-gray-800"></i> on your browser or <button onclick="location.reload()" class="text-[#a52a2a] hover:underline font-bold">reload this page</button>.</p>
        </div>
        <button onclick="dismissWarningToast(this)" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="flex flex-col xl:flex-row gap-6">

        {{-- LEFT — Controls --}}
        <div class="xl:w-72 shrink-0 space-y-5">

            {{-- Template Name --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Template Name</label>
                <input id="tpl-name" type="text" value="{{ $isEdit ? $template->name : '' }}" placeholder="e.g. 2025 Design"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-[#a52a2a]/30 focus:border-[#a52a2a] outline-none"
                    oninput="markChanged()">
            </div>

            {{-- Background Image --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Background Image</label>
                <p class="text-xs text-gray-500 mb-3">Upload a full A4-landscape image (1122×794 px recommended). The image should already contain your logos, borders, and static text.</p>
                <label class="block cursor-pointer">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-[#a52a2a] transition text-sm text-gray-500">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-300 mb-2 block"></i>
                        <span id="bg-file-label">Click to choose image</span>
                    </div>
                    <input type="file" id="bg-image-input" accept="image/*" class="hidden" onchange="onBgImageChange(this)">
                </label>
                @if($isEdit && $template->background_image)
                    <p class="text-xs text-green-600 mt-2"><i class="fas fa-check-circle mr-1"></i> Current background is set. Upload to replace.</p>
                @endif
            </div>

            {{-- Selected Element Properties --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm" id="prop-panel" style="display:none">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-3">
                    <i class="fas fa-sliders-h mr-1 text-[#a52a2a]"></i> Field Properties
                </label>
                <p id="prop-field-name" class="text-sm font-bold text-[#a52a2a] mb-3"></p>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 font-bold">Font Size (px)</label>
                        <input id="prop-font-size" type="number" min="8" max="80" value="20"
                            class="w-full mt-1 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-[#a52a2a]/30 focus:border-[#a52a2a] outline-none"
                            oninput="updateSelectedProp('fontSize', +this.value)">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-bold">Color</label>
                        <input id="prop-color" type="color" value="#222222"
                            class="w-full mt-1 h-9 rounded-lg border border-gray-200 cursor-pointer"
                            oninput="updateSelectedProp('color', this.value)">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-bold">Text Align</label>
                        <select id="prop-align"
                            class="w-full mt-1 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-[#a52a2a]/30 focus:border-[#a52a2a] outline-none"
                            onchange="updateSelectedProp('align', this.value)">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-bold">Font Weight</label>
                        <select id="prop-weight"
                            class="w-full mt-1 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-[#a52a2a]/30 focus:border-[#a52a2a] outline-none"
                            onchange="updateSelectedProp('fontWeight', this.value)">
                            <option value="normal">Normal</option>
                            <option value="bold">Bold</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-bold">QR Code Size (px)</label>
                        <input id="prop-qr-size" type="number" min="60" max="200" value="110"
                            class="w-full mt-1 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-[#a52a2a]/30 focus:border-[#a52a2a] outline-none hidden"
                            oninput="updateSelectedProp('size', +this.value)">
                    </div>
                </div>
            </div>

            {{-- Field Palette --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-3">Draggable Fields</label>
                <p class="text-xs text-gray-500 mb-3">Click a field to select it on the canvas, then drag to reposition.</p>
                <div class="space-y-1.5" id="field-palette">
                    @php
                        $fields = [
                            ['id' => 'student_name',    'label' => 'Student Name',       'icon' => 'fa-user'],
                            ['id' => 'course_name',     'label' => 'Module / Course',    'icon' => 'fa-book'],
                            ['id' => 'duration',        'label' => 'Completion Time',    'icon' => 'fa-clock'],
                            ['id' => 'instructor_name', 'label' => 'Instructor Name',    'icon' => 'fa-chalkboard-user'],
                            ['id' => 'date',            'label' => 'Date of Completion', 'icon' => 'fa-calendar-check'],
                            ['id' => 'certificate_id',  'label' => 'Certificate ID',     'icon' => 'fa-hashtag'],
                            ['id' => 'qr_code',         'label' => 'QR Code',            'icon' => 'fa-qrcode'],
                        ];
                    @endphp
                    @foreach ($fields as $f)
                        <button onclick="selectElement('{{ $f['id'] }}')"
                            id="palette-{{ $f['id'] }}"
                            class="w-full text-left px-3 py-2 rounded-lg border border-gray-200 text-xs font-bold text-gray-700 hover:bg-[#a52a2a]/5 hover:border-[#a52a2a]/30 transition flex items-center gap-2">
                            <i class="fas {{ $f['icon'] }} text-[#a52a2a] w-4"></i> {{ $f['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Exclusive Modules --}}
            @if($isEdit)
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm" id="exclusive-modules-panel">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">
                    <i class="fas fa-lock text-[#a52a2a] mr-1"></i> Exclusive Modules
                </label>
                <p class="text-xs text-gray-500 mb-3">Modules added here will use <strong>this template</strong> for their certificate instead of the active global template.</p>

                <button type="button" onclick="openModuleSearchModal()" class="w-full mb-4 px-4 py-2 border border-[#a52a2a] text-[#a52a2a] hover:bg-[#a52a2a] hover:text-white rounded-xl text-xs font-bold transition">
                    <i class="fas fa-search mr-1"></i> Add Exclusive Module
                </button>

                {{-- Assigned modules list --}}
                <div id="exclusive-module-tags" class="flex flex-wrap gap-1.5">
                    @forelse($template->exclusiveMaterials as $em)
                        <span class="exclusive-tag flex items-center gap-1 px-2.5 py-1 bg-[#a52a2a]/10 border border-[#a52a2a]/30 text-[#a52a2a] rounded-full text-[11px] font-bold"
                            data-id="{{ $em->id }}">
                            {{ Str::limit($em->title, 28) }}
                            <button type="button"
                                onclick="unassignModule({{ $em->id }}, this)"
                                class="ml-1 text-[#a52a2a]/60 hover:text-[#a52a2a] transition">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        </span>
                    @empty
                        <p class="text-xs text-gray-400 italic" id="no-exclusive-msg">No modules assigned yet.</p>
                    @endforelse
                </div>
            </div>
            @else
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-700">
                <i class="fas fa-info-circle mr-1"></i> <strong>Exclusive Modules</strong> can be assigned after you save this template for the first time.
            </div>
            @endif
        </div>

        {{-- RIGHT — Canvas --}}
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 md:p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Canvas Preview <span class="text-gray-400 font-normal">(1122 × 794 px — A4 Landscape)</span></p>
                    <button onclick="resetElements()" class="tool-btn text-xs"><i class="fas fa-undo mr-1"></i> Reset Layout</button>
                </div>

                {{-- Outer scaler — makes the fixed 1122px canvas fit any screen --}}
                <div id="canvas-scaler" class="w-full overflow-hidden rounded-xl border border-gray-300 shadow-inner">
                    <div id="cert-canvas" style="width:1122px; height:794px; background:#fff; position:relative;">
                        <img id="canvas-bg" 
                             src="{{ ($isEdit && $template->background_image) ? asset('storage/' . $template->background_image) : '' }}" 
                             alt="" 
                             style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;pointer-events:none; {{ ($isEdit && $template->background_image) ? 'display:block;' : 'display:none;' }}">
                        {{-- Elements are injected by JS --}}
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2 text-center">
                    <i class="fas fa-info-circle mr-1"></i> Drag any field to reposition. Select a field to adjust its style in the left panel.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- MODULE SEARCH MODAL --}}
@if($isEdit)
<div id="module-search-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div id="module-search-backdrop" onclick="closeModuleSearchModal()" class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Panel -->
    <div id="module-search-panel" class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[80vh]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900"><i class="fas fa-search text-[#a52a2a] mr-2"></i> Find Module</h3>
            <button onclick="closeModuleSearchModal()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        
        <p class="text-sm text-gray-500 mb-4">Search for published modules to assign this certificate template to.</p>
        
        <div class="relative mb-4 shrink-0">
            <input type="text" id="modal-module-search-input" placeholder="Search by module title..."
                class="w-full border border-gray-200 rounded-xl pl-10 pr-3 py-3 text-sm focus:ring-2 focus:ring-[#a52a2a]/30 focus:border-[#a52a2a] outline-none shadow-sm"
                oninput="moduleSearchDebounce(this.value)">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        </div>
        
        <div id="modal-module-results" class="flex-1 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
            <!-- Results injected here -->
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <i class="fas fa-search text-3xl mb-2 opacity-50"></i>
                <p class="text-sm">Type to start searching...</p>
            </div>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
</style>
@endif

<script>
// ─── STATE ────────────────────────────────────────────────────────────────────
var CANVAS_W = 1122, CANVAS_H = 794;
var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var isEdit = {{ $isEdit ? 'true' : 'false' }};
var editId = {{ $isEdit ? $template->id : 'null' }};

var elements = @json($initialElements);
var selectedId = null;
var hasChanges = false;

function markChanged() { hasChanges = true; }

function openUnsavedModal() {
    const modal = document.getElementById('unsaved-modal');
    const backdrop = document.getElementById('unsaved-backdrop');
    const panel = document.getElementById('unsaved-panel');
    
    modal.classList.remove('hidden');
    // Force browser reflow to allow transition from display:none
    void modal.offsetWidth;
    
    backdrop.classList.remove('opacity-0');
    backdrop.classList.add('opacity-100');
    panel.classList.remove('scale-95', 'opacity-0');
    panel.classList.add('scale-100', 'opacity-100');
}

function closeUnsavedModal() {
    const modal = document.getElementById('unsaved-modal');
    const backdrop = document.getElementById('unsaved-backdrop');
    const panel = document.getElementById('unsaved-panel');
    
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    panel.classList.remove('scale-100', 'opacity-100');
    panel.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function dismissWarningToast(btn) {
    const toast = btn.closest('#render-warning-toast');
    if (!toast) return;
    
    // Remove the entrance animation class so it doesn't override the exit transform
    toast.classList.remove('animate-float-in');
    
    // Force a browser reflow so it registers the removed animation before transitioning
    void toast.offsetWidth;
    
    // Apply exit transitions
    toast.classList.add('opacity-0', 'translate-x-8');
    
    setTimeout(() => toast.remove(), 300);
}

function handleBackClick() {
    if (hasChanges) {
        openUnsavedModal();
    } else {
        goBack();
    }
}

function goBack() {
    loadPartial('{{ route('dashboard.cert-templates.index') }}', document.getElementById('nav-certificates-btn'));
}

// ─── CANVAS SCALE ─────────────────────────────────────────────────────────────
// Uses a polling approach to handle partial/SPA injection where clientWidth
// may still be 0 when the script first executes.
function applyScale() {
    var scaler = document.getElementById('canvas-scaler');
    var canvas = document.getElementById('cert-canvas');
    if (!scaler || !canvas) return;
    var available = scaler.clientWidth;
    if (!available) return; // will be called again by ResizeObserver / resize event
    var scale = available / CANVAS_W;
    canvas.style.transform       = `scale(${scale})`;
    canvas.style.transformOrigin = 'top left';
    scaler.style.height          = Math.round(CANVAS_H * scale) + 'px';
}

if (window.certResizeListener) {
    window.removeEventListener('resize', window.certResizeListener);
}
window.certResizeListener = applyScale;
window.addEventListener('resize', window.certResizeListener);

// ─── RENDER ───────────────────────────────────────────────────────────────────
function renderAll() {
    const canvas = document.getElementById('cert-canvas');
    if (!canvas) return;
    // Remove old elements (keep bg img)
    canvas.querySelectorAll('.cert-elem').forEach(el => el.remove());

    elements.forEach(el => {
        const div = document.createElement('div');
        div.className  = 'cert-elem' + (el.id === selectedId ? ' selected' : '');
        div.id         = 'elem-' + el.id;
        div.style.left = el.x + '%';
        div.style.top  = el.y + '%';

        const handle = document.createElement('span');
        handle.className   = 'elem-handle';
        handle.textContent = el.label;
        div.appendChild(handle);

        // Apply alignment
        if (el.align === 'center') {
            div.style.transform = 'translateX(-50%)';
        } else if (el.align === 'right') {
            div.style.transform = 'translateX(-100%)';
        } else {
            div.style.transform = 'none';
        }

        if (el.id === 'qr_code') {
            const qrImg = document.createElement('div');
            qrImg.style.cssText = `width:${el.size || 110}px;height:${el.size || 110}px;background:#f3f4f6;border:2px dashed #ccc;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:11px;font-weight:bold;`;
            qrImg.textContent = 'QR Code';
            div.appendChild(qrImg);
        } else {
            const span = document.createElement('span');
            span.style.fontSize   = (el.fontSize || 16) + 'px';
            span.style.fontWeight = el.fontWeight || 'normal';
            span.style.color      = el.color || '#222';
            span.textContent      = el.label;
            div.appendChild(span);
        }

        makeDraggable(div, el.id);
        div.addEventListener('click', (e) => { e.stopPropagation(); selectElement(el.id); });
        canvas.appendChild(div);
    });

    // update palette highlights
    document.querySelectorAll('[id^="palette-"]').forEach(btn => {
        const fid = btn.id.replace('palette-', '');
        btn.classList.toggle('bg-[#a52a2a]/10', fid === selectedId);
        btn.classList.toggle('border-[#a52a2a]/50', fid === selectedId);
    });
}

// ─── DRAG ─────────────────────────────────────────────────────────────────────
function makeDraggable(div, id) {
    let startX, startY, origLeft, origTop;

    div.addEventListener('mousedown', e => {
        if (e.button !== 0) return;
        e.preventDefault();
        selectElement(id);

        const canvas = document.getElementById('cert-canvas');
        const rect   = canvas.getBoundingClientRect();
        const scale  = rect.width / CANVAS_W;

        const el = elements.find(e => e.id === id);
        origLeft = (el.x / 100) * CANVAS_W;
        origTop  = (el.y / 100) * CANVAS_H;
        startX   = e.clientX;
        startY   = e.clientY;

        div.style.cursor = 'grabbing';

        function onMove(ev) {
            markChanged();
            const dx = (ev.clientX - startX) / scale;
            const dy = (ev.clientY - startY) / scale;
            const newLeft = Math.min(Math.max(0, origLeft + dx), CANVAS_W - 10);
            const newTop  = Math.min(Math.max(0, origTop  + dy), CANVAS_H - 10);
            el.x = parseFloat(((newLeft / CANVAS_W) * 100).toFixed(2));
            el.y = parseFloat(((newTop  / CANVAS_H) * 100).toFixed(2));
            div.style.left = el.x + '%';
            div.style.top  = el.y + '%';
        }

        function onUp() {
            div.style.cursor = 'grab';
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
        }

        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });
}

// ─── SELECT ───────────────────────────────────────────────────────────────────
function selectElement(id) {
    selectedId = id;

    // Visually update the canvas elements without recreating them
    document.querySelectorAll('.cert-elem').forEach(el => {
        el.classList.toggle('selected', el.id === 'elem-' + id);
    });

    document.querySelectorAll('[id^="palette-"]').forEach(btn => {
        const fid = btn.id.replace('palette-', '');
        if (fid === id) {
            btn.classList.add('bg-[#a52a2a]/10', 'border-[#a52a2a]/50');
        } else {
            btn.classList.remove('bg-[#a52a2a]/10', 'border-[#a52a2a]/50');
        }
    });

    const el = elements.find(e => e.id === id);
    if (!el) return;

    document.getElementById('prop-panel').style.display = 'block';
    document.getElementById('prop-field-name').textContent = el.label;
    document.getElementById('prop-font-size').value = el.fontSize || 16;
    document.getElementById('prop-color').value     = el.color || '#222222';
    document.getElementById('prop-weight').value    = el.fontWeight || 'normal';

    const pAlign = document.getElementById('prop-align');
    if (pAlign) pAlign.value = el.align || 'left';

    const qrSizeRow = document.getElementById('prop-qr-size');
    if (id === 'qr_code') {
        qrSizeRow.classList.remove('hidden');
        qrSizeRow.value = el.size || 110;
        document.getElementById('prop-font-size').closest('div').style.display = 'none';
        document.getElementById('prop-weight').closest('div').style.display    = 'none';
        if (pAlign) pAlign.closest('div').style.display = 'none';
    } else {
        qrSizeRow.classList.add('hidden');
        document.getElementById('prop-font-size').closest('div').style.display = '';
        document.getElementById('prop-weight').closest('div').style.display    = '';
        if (pAlign) pAlign.closest('div').style.display = '';
    }
}

document.addEventListener('click', e => {
    if (!e.target.closest('.cert-elem') && !e.target.closest('#prop-panel') && !e.target.closest('#field-palette')) {
        selectedId = null;
        renderAll();
        document.getElementById('prop-panel').style.display = 'none';
    }
});

// ─── UPDATE PROP ──────────────────────────────────────────────────────────────
function updateSelectedProp(prop, value) {
    if (!selectedId) return;
    markChanged();
    const el = elements.find(e => e.id === selectedId);
    if (el) { el[prop] = value; renderAll(); }
}

// ─── BACKGROUND IMAGE ─────────────────────────────────────────────────────────
function onBgImageChange(input) {
    const file = input.files[0];
    if (!file) return;
    markChanged();
    document.getElementById('bg-file-label').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        const bg = document.getElementById('canvas-bg');
        bg.src = e.target.result;
        bg.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

// ─── RESET ────────────────────────────────────────────────────────────────────
function resetElements() {
    markChanged();
    elements = @json($defaultElements);
    selectedId = null;
    renderAll();
    document.getElementById('prop-panel').style.display = 'none';
}

// ─── SAVE ─────────────────────────────────────────────────────────────────────
async function saveTemplate() {
    const name = document.getElementById('tpl-name').value.trim();
    if (!name) { showEditorToast('Please enter a template name.', false); return; }

    const btn = document.getElementById('save-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('name', name);
    formData.append('elements', JSON.stringify(elements));

    const fileInput = document.getElementById('bg-image-input');
    if (fileInput.files[0]) {
        formData.append('background_image', fileInput.files[0]);
    }

    const url    = isEdit
        ? `/dashboard/certificate-templates/${editId}`
        : '/dashboard/certificate-templates';
    // Both create and update use POST (update route in web.php is also POST)
    const method = 'POST';

    if (isEdit) {
        formData.append('_method', 'POST'); // no spoofing needed, route is POST
    }

    try {
        const resp = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData
        });
        const data = await resp.json();

        if (resp.ok && data.success) {
            hasChanges = false;
            showEditorToast(data.message, true);
            setTimeout(() => goBack(), 1200);
        } else {
            let errorMsg = data.message || 'Error saving template.';
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                errorMsg = firstError;
            }
            showEditorToast(errorMsg, false);
        }
    } catch (err) {
        showEditorToast('A network error occurred while uploading.', false);
    } finally {
        btn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Template';
        btn.disabled  = false;
    }
}

function showEditorToast(message, success = true) {
    const t = document.getElementById('editor-toast');
    document.getElementById('editor-toast-msg').textContent = message;
    document.getElementById('editor-toast-icon').className  = `fas ${success ? 'fa-check-circle' : 'fa-exclamation-circle'} text-lg`;

    t.classList.remove('bg-green-600', 'bg-[#a52a2a]', 'bg-gray-900');
    t.classList.add(success ? 'bg-green-600' : 'bg-[#a52a2a]');
    t.classList.remove('translate-y-24', 'opacity-0');

    setTimeout(() => {
        t.classList.add('translate-y-24', 'opacity-0');
    }, 3500);
}

// ─── INIT ─────────────────────────────────────────────────────────────────────
// Use setInterval polling because this script is re-injected synchronously by
// loadPartial() before the browser has had a chance to compute flex layout.
// requestAnimationFrame is not sufficient here — it fires in the same task queue.
(function() {
    // Clear any previous poll that may still be running from a prior partial load
    if (window._certInitPoll) clearInterval(window._certInitPoll);

    var attempts = 0;
    window._certInitPoll = setInterval(function() {
        attempts++;
        var scaler = document.getElementById('canvas-scaler');

        // Stop if DOM was removed (navigated away) or we've waited too long (3s)
        if (!scaler || attempts > 150) {
            clearInterval(window._certInitPoll);
            return;
        }

        // Must use clientWidth because offsetWidth includes borders and would 
        // return 2px even before layout is fully computed.
        var w = scaler.clientWidth;
        if (w > 0) {
            clearInterval(window._certInitPoll);

            applyScale();
            renderAll();

            if (window._certRObs) window._certRObs.disconnect();
            if (window.ResizeObserver) {
                window._certRObs = new ResizeObserver(function() { applyScale(); });
                window._certRObs.observe(scaler);
            }
        }
    }, 20);
})();

@if($isEdit)
// ─── EXCLUSIVE MODULES ────────────────────────────────────────────────────────
var _moduleSearchTimer = null;
var _templateId = {{ $template->id }};

function openModuleSearchModal() {
    const modal = document.getElementById('module-search-modal');
    const backdrop = document.getElementById('module-search-backdrop');
    const panel = document.getElementById('module-search-panel');
    const input = document.getElementById('modal-module-search-input');
    
    modal.classList.remove('hidden');
    void modal.offsetWidth; // Reflow
    
    backdrop.classList.remove('opacity-0');
    backdrop.classList.add('opacity-100');
    panel.classList.remove('scale-95', 'opacity-0');
    panel.classList.add('scale-100', 'opacity-100');

    input.value = '';
    document.getElementById('modal-module-results').innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 text-gray-400">
            <i class="fas fa-search text-3xl mb-2 opacity-50"></i>
            <p class="text-sm">Type to start searching...</p>
        </div>
    `;
    input.focus();
}

function closeModuleSearchModal() {
    const modal = document.getElementById('module-search-modal');
    const backdrop = document.getElementById('module-search-backdrop');
    const panel = document.getElementById('module-search-panel');
    
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    panel.classList.remove('scale-100', 'opacity-100');
    panel.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function moduleSearchDebounce(val) {
    clearTimeout(_moduleSearchTimer);
    _moduleSearchTimer = setTimeout(() => moduleSearch(val), 300);
}

async function moduleSearch(q) {
    const resultsContainer = document.getElementById('modal-module-results');
    if (!resultsContainer) return;

    if (q.trim() === '') {
        resultsContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <i class="fas fa-search text-3xl mb-2 opacity-50"></i>
                <p class="text-sm">Type to start searching...</p>
            </div>
        `;
        return;
    }

    resultsContainer.innerHTML = '<p class="text-sm text-gray-400 py-4 text-center"><i class="fas fa-spinner fa-spin mr-2"></i> Searching…</p>';

    try {
        const res  = await fetch(`/dashboard/certificate-templates/${_templateId}/search-modules?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();

        if (!data.length) {
            resultsContainer.innerHTML = '<p class="text-sm text-gray-400 py-4 text-center italic">No modules found matching your query.</p>';
            return;
        }

        const assignedIds = new Set(
            [...document.querySelectorAll('#exclusive-module-tags .exclusive-tag')].map(t => +t.dataset.id)
        );

        resultsContainer.innerHTML = '';
        data.forEach(m => {
            const isAssignedHere    = assignedIds.has(m.id);
            const isAssignedElsewhere = m.exclusive_template_id && m.exclusive_template_id !== _templateId;

            const div = document.createElement('div');
            div.className = 'flex items-center gap-3 p-3 rounded-xl border ' + (isAssignedHere ? 'bg-[#a52a2a]/5 border-[#a52a2a]/30' : 'bg-white border-gray-100 hover:border-gray-200 shadow-sm');
            
            // Thumbnail
            const thumbDiv = document.createElement('div');
            thumbDiv.className = 'w-12 h-12 rounded-lg bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center';
            if (m.thumbnail) {
                const img = document.createElement('img');
                img.src = m.thumbnail;
                img.className = 'w-full h-full object-cover';
                thumbDiv.appendChild(img);
            } else {
                thumbDiv.innerHTML = '<i class="fas fa-image text-gray-300 text-lg"></i>';
            }
            
            // Info
            const infoDiv = document.createElement('div');
            infoDiv.className = 'flex-1 min-w-0';
            
            const title = document.createElement('h4');
            title.className = 'text-sm font-bold text-gray-900 truncate';
            title.textContent = m.title;
            
            const instructor = document.createElement('p');
            instructor.className = 'text-xs text-gray-500 truncate mt-0.5';
            instructor.innerHTML = `<i class="fas fa-chalkboard-teacher mr-1"></i> ${m.instructor_name}`;
            
            infoDiv.appendChild(title);
            infoDiv.appendChild(instructor);

            // Action
            const actionDiv = document.createElement('div');
            actionDiv.className = 'shrink-0 ml-2';

            if (isAssignedHere) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-[#a52a2a] text-white flex items-center gap-1.5 hover:bg-red-900 transition';
                btn.innerHTML = '<i class="fas fa-check"></i> Added';
                btn.onclick = () => {
                    unassignModule(m.id, document.querySelector(`.exclusive-tag[data-id="${m.id}"] button`));
                    // Update search result item state after unassign
                    setTimeout(() => moduleSearch(document.getElementById('modal-module-search-input').value), 300);
                };
                actionDiv.appendChild(btn);
            } else if (isAssignedElsewhere) {
                const badge = document.createElement('span');
                badge.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 flex items-center gap-1.5';
                badge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Used Elsewhere';
                
                // Allow overriding? Sure, we can add a small override button
                const overrideBtn = document.createElement('button');
                overrideBtn.className = 'ml-2 text-xs text-amber-600 hover:text-amber-800 underline font-medium';
                overrideBtn.textContent = 'Steal';
                overrideBtn.onclick = () => {
                    assignModule(m.id, m.title);
                    setTimeout(() => moduleSearch(document.getElementById('modal-module-search-input').value), 300);
                };
                
                actionDiv.appendChild(badge);
                actionDiv.appendChild(overrideBtn);
            } else {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition flex items-center gap-1.5';
                btn.innerHTML = '<i class="fas fa-plus"></i> Add';
                btn.onclick = () => {
                    assignModule(m.id, m.title);
                    setTimeout(() => moduleSearch(document.getElementById('modal-module-search-input').value), 300);
                };
                actionDiv.appendChild(btn);
            }

            div.appendChild(thumbDiv);
            div.appendChild(infoDiv);
            div.appendChild(actionDiv);

            resultsContainer.appendChild(div);
        });
    } catch(e) {
        resultsContainer.innerHTML = '<p class="text-sm text-red-500 py-4 text-center">Error fetching modules.</p>';
    }
}

async function assignModule(materialId, materialTitle) {
    try {
        const res  = await fetch(`/dashboard/certificate-templates/${_templateId}/assign-module`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ material_id: materialId })
        });
        const data = await res.json();

        if (res.ok && data.success) {
            // Remove "no modules" placeholder if present
            const noMsg = document.getElementById('no-exclusive-msg');
            if (noMsg) noMsg.remove();

            // Add tag to the list if not already there
            const tags = document.getElementById('exclusive-module-tags');
            if (tags && !tags.querySelector(`.exclusive-tag[data-id="${materialId}"]`)) {
                const tag = document.createElement('span');
                tag.className = 'exclusive-tag flex items-center gap-1 px-2.5 py-1 bg-[#a52a2a]/10 border border-[#a52a2a]/30 text-[#a52a2a] rounded-full text-[11px] font-bold animate-[fadeIn_.2s_ease]';
                tag.dataset.id = materialId;
                const maxLen = 28;
                const displayTitle = materialTitle.length > maxLen ? materialTitle.substring(0, maxLen) + '…' : materialTitle;
                tag.innerHTML = `${displayTitle} <button type="button" onclick="unassignModule(${materialId}, this)" class="ml-1 text-[#a52a2a]/60 hover:text-[#a52a2a] transition"><i class="fas fa-times text-[10px]"></i></button>`;
                tags.appendChild(tag);
            }
            showEditorToast(data.message, true);
        } else {
            showEditorToast(data.message || 'Failed to assign module.', false);
        }
    } catch(e) {
        showEditorToast('Network error assigning module.', false);
    }
}

async function unassignModule(materialId, btnEl) {
    try {
        const res  = await fetch(`/dashboard/certificate-templates/${_templateId}/unassign-module/${materialId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();

        if (res.ok && data.success) {
            // Remove the tag
            if (btnEl) {
                const tag = btnEl.closest('.exclusive-tag');
                if (tag) tag.remove();
            }

            // If no tags remain, show placeholder
            const tags = document.getElementById('exclusive-module-tags');
            if (tags && !tags.querySelector('.exclusive-tag')) {
                const p = document.createElement('p');
                p.id = 'no-exclusive-msg';
                p.className = 'text-xs text-gray-400 italic';
                p.textContent = 'No modules assigned yet.';
                tags.appendChild(p);
            }
            showEditorToast(data.message, true);
        } else {
            showEditorToast(data.message || 'Failed to remove module.', false);
        }
    } catch(e) {
        showEditorToast('Network error removing module.', false);
    }
}
@endif
</script>
