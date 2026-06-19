{{-- ================================================================
     ADMIN — CERTIFICATE TEMPLATE EDITOR  (Create / Edit)
     ================================================================ --}}
@php
    $isEdit = isset($template);
    $defaultElements = [
        ['id' => 'student_name',    'label' => 'Student Name',       'x' => 10,  'y' => 35,  'fontSize' => 48, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
        ['id' => 'course_name',     'label' => 'Module / Course',    'x' => 10,  'y' => 52,  'fontSize' => 34, 'fontWeight' => 'bold',   'color' => '#a52a2a', 'align' => 'center'],
        ['id' => 'duration',        'label' => 'Completion Time',    'x' => 10,  'y' => 63,  'fontSize' => 18, 'fontWeight' => 'normal', 'color' => '#555555', 'align' => 'center'],
        ['id' => 'instructor_name', 'label' => 'Instructor Name',    'x' => 5,   'y' => 80,  'fontSize' => 20, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
        ['id' => 'date',            'label' => 'Date of Completion', 'x' => 65,  'y' => 80,  'fontSize' => 20, 'fontWeight' => 'bold',   'color' => '#222222', 'align' => 'center'],
        ['id' => 'certificate_id',  'label' => 'Certificate ID',     'x' => 70,  'y' => 93,  'fontSize' => 11, 'fontWeight' => 'normal', 'color' => '#888888', 'align' => 'left'],
        ['id' => 'qr_code',         'label' => 'QR Code',            'x' => 44,  'y' => 74,  'fontSize' => 0,  'fontWeight' => 'normal', 'color' => '#000000', 'align' => 'center', 'size' => 110],
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
            <button onclick="loadPartial('{{ route('dashboard.cert-templates.index') }}', document.getElementById('nav-certificates-btn'))"
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

    <div class="flex flex-col xl:flex-row gap-6">

        {{-- LEFT — Controls --}}
        <div class="xl:w-72 shrink-0 space-y-5">

            {{-- Template Name --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Template Name</label>
                <input id="tpl-name" type="text" value="{{ $isEdit ? $template->name : '' }}" placeholder="e.g. 2025 Design"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-[#a52a2a]/30 focus:border-[#a52a2a] outline-none">
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
                    <div id="cert-canvas" style="width:1122px; height:794px; background:#fff;">
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

<script>
// ─── STATE ────────────────────────────────────────────────────────────────────
const CANVAS_W = 1122, CANVAS_H = 794;
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const isEdit = {{ $isEdit ? 'true' : 'false' }};
const editId  = {{ $isEdit ? $template->id : 'null' }};

let elements = @json($initialElements);
let selectedId = null;

// ─── CANVAS SCALE ─────────────────────────────────────────────────────────────
function applyScale() {
    const scaler  = document.getElementById('canvas-scaler');
    const canvas  = document.getElementById('cert-canvas');
    const available = scaler.clientWidth;
    const scale   = available / CANVAS_W;
    canvas.style.transform       = `scale(${scale})`;
    canvas.style.transformOrigin = 'top left';
    scaler.style.height          = Math.round(CANVAS_H * scale) + 'px';
}
window.addEventListener('resize', applyScale);
applyScale();
setTimeout(applyScale, 200);

// ─── RENDER ───────────────────────────────────────────────────────────────────
function renderAll() {
    const canvas = document.getElementById('cert-canvas');
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
    const el = elements.find(e => e.id === selectedId);
    if (el) { el[prop] = value; renderAll(); }
}

// ─── BACKGROUND IMAGE ─────────────────────────────────────────────────────────
function onBgImageChange(input) {
    const file = input.files[0];
    if (!file) return;
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

    const url    = isEdit ? `/dashboard/certificate-templates/${editId}` : '/dashboard/certificate-templates';
    const method = isEdit ? 'POST' : 'POST';   // Both use POST; controller differentiates by route

    try {
        const resp = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData
        });
        const data = await resp.json();
        
        if (resp.ok && data.success) {
            showEditorToast(data.message, true);
            setTimeout(() => loadPartial('{{ route('dashboard.cert-templates.index') }}', document.getElementById('nav-certificates-btn')), 1200);
        } else {
            let errorMsg = data.message || 'Error saving template.';
            if (data.errors) {
                // Get the first validation error
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
    
    // Clear previous color classes
    t.classList.remove('bg-green-600', 'bg-[#a52a2a]', 'bg-gray-900');
    t.classList.add(success ? 'bg-green-600' : 'bg-[#a52a2a]');
    
    t.classList.remove('translate-y-24', 'opacity-0');
    
    setTimeout(() => {
        t.classList.add('translate-y-24', 'opacity-0');
    }, 3500);
}

// ─── INIT ─────────────────────────────────────────────────────────────────────
renderAll();
</script>
