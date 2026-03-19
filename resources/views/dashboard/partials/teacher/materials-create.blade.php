<div id="material-wrapper" data-material-id="{{ $material->id ?? '' }}"
    data-is-new="{{ isset($isNew) && $isNew ? 'true' : 'false' }}"
    data-autosave-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.autosave', $material->id) : '#' }}"
    data-save-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.store', $material->id) : '#' }}"
    data-builder-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.edit', $material->id) : '#' }}"
    data-manage-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.manage', $material->id) : '#' }}"
    data-delete-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.destroy', $material->id) : '#' }}"
    data-redirect-url="{{ route('dashboard.materials.index') }}" 
    data-csrf="{{ csrf_token() }}"
    data-upload-url="{{ route('dashboard.materials.upload_media') }}"
    class="space-y-6 pb-20 w-full max-w-5xl mx-auto">

    <input type="hidden" id="existing-data" value="{{ json_encode($lessons ?? []) }}">
    <input type="hidden" id="server-draft-data" value="{{ $material->draft_json ?? '' }}">

    <img src onerror="if(typeof MaterialBuilder !== 'undefined' && typeof MaterialBuilder.initBuilder === 'function') MaterialBuilder.initBuilder()" style="display:none;">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-100 p-6 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-4">
                <button type="button" onclick="MaterialBuilder.handleBackButton(this)"
                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-[#a52a2a] transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-900">Module Setup</h2>
            </div>
            <div class="flex items-center gap-3">
                <div id="autosave-indicator" class="text-xs text-gray-400 italic font-medium px-3 py-1 bg-white border border-gray-100 rounded-lg">
                    Ready
                </div>
                <button type="button" onclick="MaterialBuilder.discardChangesAndExit(this)"
                    class="h-10 px-4 flex items-center gap-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition text-sm font-bold">
                    <i class="fas fa-trash-alt"></i> Discard
                </button>
            </div>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Course / Module Title</label>
                        <input type="text" id="setup-title" value="{{ $material->title ?? '' }}" placeholder="e.g. Complete Web Development Bootcamp"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Course Description & Overview</label>
                        <textarea id="setup-desc" rows="3" placeholder="Provide an overview of what students will learn..."
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">{{ $material->description ?? '' }}</textarea>
                    </div>
                </div>

                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-0 relative overflow-hidden flex flex-col justify-center items-center bg-gray-50 hover:bg-gray-100 transition group cursor-pointer min-h-[160px] h-full" onclick="document.getElementById('thumbnail-upload').click()">
                    <input type="file" id="thumbnail-upload" class="hidden" accept="image/*" onchange="previewThumbnail(this)">
                    
                    <img id="thumbnail-preview" src="{{ isset($material) && $material->thumbnail ? asset('storage/' . $material->thumbnail) : '' }}" class="absolute inset-0 w-full h-full object-cover {{ isset($material) && $material->thumbnail ? '' : 'hidden' }} z-0" alt="Thumbnail Preview">
                    
                    <button type="button" id="remove-thumbnail-btn" 
                        onclick="removeThumbnail(event, {{ $material->id ?? 'null' }})" 
                        class="absolute top-3 right-3 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-700 transition z-20 {{ isset($material) && $material->thumbnail ? '' : 'hidden' }}" 
                        title="Remove Thumbnail">
                        <i class="fas fa-times"></i>
                    </button>

                    <div id="thumbnail-placeholder" class="flex flex-col items-center justify-center relative z-10 w-full h-full rounded-xl transition {{ isset($material) && $material->thumbnail ? 'bg-black/40 opacity-0 hover:opacity-100' : 'bg-white/50 backdrop-blur-[2px] group-hover:bg-white/80' }}">
                        <i class="fas fa-image text-4xl {{ isset($material) && $material->thumbnail ? 'text-white' : 'text-gray-400 group-hover:text-[#a52a2a]' }} transition mb-3"></i>
                        <span class="text-sm font-bold {{ isset($material) && $material->thumbnail ? 'text-white' : 'text-gray-700' }} mb-1">{{ isset($material) && $material->thumbnail ? 'Change Thumbnail' : 'Course Thumbnail' }}</span>
                        <p class="text-[10px] text-center {{ isset($material) && $material->thumbnail ? 'text-gray-200' : 'text-gray-500' }} px-4">Upload a cover image (JPG, PNG).</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="builder-container" class="space-y-6"></div>

    <div class="mt-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="h-px flex-1 bg-gray-200"></div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Builder Controls</span>
            <div class="h-px flex-1 bg-gray-200"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button type="button" onclick="MaterialBuilder.addSection('lesson')" class="px-4 py-8 border-2 border-dashed border-blue-200 text-blue-600 font-bold rounded-2xl hover:bg-blue-50 transition flex flex-col items-center justify-center gap-3 group bg-white shadow-sm">
                <div class="h-12 w-12 rounded-full bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center transition text-xl">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="text-center">
                    <span class="block text-lg">Add Lesson</span>
                    <span class="text-xs font-medium text-blue-400 mt-1 block">Includes reading content and practice quizzes</span>
                </div>
            </button>

            <button type="button" onclick="MaterialBuilder.addSection('exam')" class="px-4 py-8 border-2 border-dashed border-red-200 text-red-600 font-bold rounded-2xl hover:bg-red-50 transition flex flex-col items-center justify-center gap-3 group bg-white shadow-sm">
                <div class="h-12 w-12 rounded-full bg-red-100 group-hover:bg-red-200 flex items-center justify-center transition text-xl">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div class="text-center">
                    <span class="block text-lg">Add Final Exam</span>
                    <span class="text-xs font-medium text-red-400 mt-1 block">Graded assessment questions</span>
                </div>
            </button>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mt-4">
            <button type="button" onclick="openImportModal()"
                class="flex-1 py-3 border-2 border-dashed border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-center gap-2 text-sm bg-white">
                <i class="fas fa-upload"></i> Import Content via Excel
            </button>
            <a href="{{ route('dashboard.materials.download_template') ?? '#' }}"
                class="flex-1 py-3 border border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition flex items-center justify-center gap-2 text-sm bg-white">
                <i class="fas fa-download"></i> Download Template
            </a>
        </div>
    </div>

    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end gap-4">
        <button type="button" onclick="MaterialBuilder.saveCompleteMaterial(this, 'draft')"
            class="px-8 py-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition shadow-sm active:scale-95">
            Save as Draft
        </button>
        <button type="button" onclick="MaterialBuilder.saveCompleteMaterial(this, 'published')"
            class="px-10 py-4 bg-[#a52a2a] text-white font-bold rounded-2xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 flex items-center gap-2 active:scale-95">
            <span>Publish Module</span>
            <i class="fas fa-upload"></i>
        </button>
    </div>
</div>

<div id="back-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="p-6 text-center">
                <div class="h-16 w-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-save"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Unsaved Changes</h3>
                <p class="text-gray-500 text-sm mb-6">How would you like to exit? Your progress is currently stored as a temporary draft.</p>

                <div class="space-y-3">
                    <button type="button" onclick="MaterialBuilder.saveCompleteMaterial(this, 'published')"
                        class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-2xl hover:bg-red-800 transition flex items-center justify-center gap-2">
                        <i class="fas fa-upload text-sm"></i>
                        <span>Publish & Exit</span>
                    </button>
                    <button type="button" onclick="MaterialBuilder.saveCompleteMaterial(this, 'draft')"
                        class="w-full py-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition flex items-center justify-center gap-2">
                        <i class="fas fa-file-alt text-sm text-gray-400"></i>
                        <span>Save as Draft & Exit</span>
                    </button>
                    <button type="button" onclick="MaterialBuilder.discardChangesAndExit(this)"
                        class="w-full py-3 text-gray-400 hover:text-red-500 text-sm font-bold transition">
                        Discard changes and Exit
                    </button>
                </div>
            </div>
            <button onclick="document.getElementById('back-modal').classList.add('hidden')"
                class="absolute top-6 right-6 h-8 w-8 flex items-center justify-center text-gray-400 hover:bg-gray-50 rounded-full">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<div id="status-modal" class="fixed inset-0 z-[110] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="document.getElementById('status-modal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
            <div id="status-modal-icon" class="h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"></div>
            <h3 id="status-modal-title" class="text-xl font-bold text-gray-900 mb-2">Title</h3>
            <p id="status-modal-message" class="text-gray-500 text-sm mb-6">Message goes here.</p>
            <div class="flex gap-3 mt-2">
                <button id="status-modal-cancel-btn" type="button" class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95 hidden">Cancel</button>
                <button id="status-modal-btn" type="button" class="w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md">OK</button>
            </div>
        </div>
    </div>
</div>

<div id="excel-import-modal" class="fixed inset-0 z-[120] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6">
            <div class="text-center mb-6">
                <div class="h-16 w-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-file-excel"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Import Content</h3>
                <p class="text-gray-500 text-sm mt-1">Upload your filled Excel or CSV template.</p>
            </div>
            <div class="mb-6">
                <input type="file" id="excel-file-input" accept=".xlsx, .xls, .csv" class="hidden" onchange="handleFileSelect(this)">
                <label for="excel-file-input" id="file-dropzone"
                    class="cursor-pointer flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500 font-medium">Click to choose a file</p>
                        <p class="text-xs text-gray-400 mt-1">.xlsx, .xls, or .csv</p>
                    </div>
                </label>
                <div id="selected-file-display" class="hidden mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <i class="fas fa-file-excel text-blue-500 text-lg"></i>
                        <span id="selected-file-name" class="font-mono text-sm text-blue-700 truncate font-medium">filename.xlsx</span>
                    </div>
                    <button type="button" onclick="clearSelectedFile()" class="text-blue-400 hover:text-red-500 transition px-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeImportModal()" class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">Cancel</button>
                <button type="button" id="start-upload-btn" onclick="executeExcelUpload()" disabled class="w-full py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md flex justify-center items-center gap-2">
                    <i class="fas fa-upload"></i><span>Upload</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="media-upload-modal" class="fixed inset-0 z-[120] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="MaterialBuilder.closeMediaModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6">
            <div class="text-center mb-6">
                <div class="h-16 w-16 bg-[#a52a2a]/10 text-[#a52a2a] rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-photo-video"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Upload Media or Document</h3>
                <p class="text-gray-500 text-sm mt-1">Attach an image, audio, video, PDF or ZIP.</p>
            </div>
            <div class="mb-6">
                <input type="file" id="media-file-input" class="hidden" onchange="MaterialBuilder.handleMediaFileSelect(this)">
                <label for="media-file-input" id="media-dropzone"
                    class="cursor-pointer flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-[#a52a2a]/50 transition">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500 font-medium">Click to choose a file</p>
                    </div>
                </label>
                <div id="selected-media-display" class="hidden mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <i class="fas fa-file-alt text-[#a52a2a] text-lg"></i>
                        <span id="selected-media-name" class="font-mono text-sm text-gray-700 truncate font-medium">file.mp4</span>
                    </div>
                    <button type="button" onclick="MaterialBuilder.clearSelectedMedia()" class="text-gray-400 hover:text-red-500 transition px-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="MaterialBuilder.closeMediaModal()" class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">Cancel</button>
                <button type="button" id="start-media-upload-btn" onclick="MaterialBuilder.executeMediaUpload()" disabled
                    class="w-full py-3.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-[#801f1f] transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md flex justify-center items-center gap-2">
                    <i class="fas fa-upload"></i><span>Upload Media</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/material.js') }}"></script>

<script>
    function previewThumbnail(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('thumbnail-preview');
                const placeholder = document.getElementById('thumbnail-placeholder');
                const removeBtn = document.getElementById('remove-thumbnail-btn');

                if(preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                if(removeBtn) {
                    removeBtn.classList.remove('hidden');
                }
                if(placeholder) {
                    placeholder.className = "flex flex-col items-center justify-center relative z-10 w-full h-full rounded-xl transition bg-black/40 opacity-0 hover:opacity-100";
                    placeholder.innerHTML = `
                        <i class="fas fa-image text-4xl text-white transition mb-3"></i>
                        <span class="text-sm font-bold text-white mb-1">Change Thumbnail</span>
                        <p class="text-[10px] text-center text-gray-200 px-4">Upload a cover image (JPG, PNG).</p>
                    `;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    async function removeThumbnail(event, materialId) {
        event.stopPropagation(); 

        const btn = document.getElementById('remove-thumbnail-btn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        const input = document.getElementById('thumbnail-upload');
        const preview = document.getElementById('thumbnail-preview');
        const placeholder = document.getElementById('thumbnail-placeholder');

        const resetUI = () => {
            input.value = '';
            preview.src = '';
            preview.classList.add('hidden');
            btn.classList.add('hidden');

            placeholder.className = "flex flex-col items-center justify-center relative z-10 w-full h-full rounded-xl transition bg-white/50 backdrop-blur-[2px] group-hover:bg-white/80";
            placeholder.innerHTML = `
                <i class="fas fa-image text-4xl text-gray-400 group-hover:text-[#a52a2a] transition mb-3"></i>
                <span class="text-sm font-bold text-gray-700 mb-1">Course Thumbnail</span>
                <p class="text-[10px] text-center text-gray-500 px-4">Upload a cover image (JPG, PNG).</p>
            `;
        };

        if (materialId && preview.src.includes('storage')) {
            const csrf = document.querySelector('[data-csrf]')?.dataset.csrf;

            try {
                const response = await fetch(`/dashboard/materials/${materialId}/thumbnail`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    resetUI();
                } else {
                    showStatusModal('Removal Failed', 'Failed to remove the thumbnail from the server.', 'error');
                }
            } catch (error) {
                console.error(error);
                showStatusModal('Network Error', 'A network error occurred while trying to remove the thumbnail.', 'error');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        } else {
            resetUI();
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    let selectedFile = null;

    function openImportModal() {
        clearSelectedFile();
        document.getElementById('excel-import-modal').classList.remove('hidden');
    }

    function closeImportModal() {
        document.getElementById('excel-import-modal').classList.add('hidden');
    }

    function handleFileSelect(input) {
        if (!input.files || input.files.length === 0) return;
        selectedFile = input.files[0];
        document.getElementById('selected-file-name').innerText = selectedFile.name;
        document.getElementById('file-dropzone').classList.add('hidden');
        document.getElementById('selected-file-display').classList.remove('hidden');
        document.getElementById('selected-file-display').classList.add('flex');
        document.getElementById('start-upload-btn').disabled = false;
    }

    function clearSelectedFile() {
        selectedFile = null;
        document.getElementById('excel-file-input').value = '';
        document.getElementById('file-dropzone').classList.remove('hidden');
        document.getElementById('selected-file-display').classList.add('hidden');
        document.getElementById('selected-file-display').classList.remove('flex');
        document.getElementById('start-upload-btn').disabled = true;
    }

    function executeExcelUpload() {
        if (!selectedFile) return;
        
        let btn = document.getElementById('start-upload-btn');
        let originalHtml = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
        btn.disabled = true;

        let payload = MaterialBuilder.getPayload("draft");
        let formData = new FormData();
        
        formData.append('module_file', selectedFile); 
        formData.append('_token', document.querySelector('[data-csrf]').dataset.csrf);
        formData.append('title', payload.title);
        formData.append('description', payload.description);
        formData.append('categories', JSON.stringify(payload.categories));

        let wrapper = document.getElementById('material-wrapper');
        let materialId = wrapper.dataset.materialId;

        fetch(`/dashboard/materials/${materialId}/import`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        })
        .then(async response => {
            if (!response.ok) {
                let errorData = await response.json().catch(() => ({}));
                let errorMessage = errorData.message || `Server error (${response.status})`;
                
                if (errorData.errors) {
                    const firstErrorKey = Object.keys(errorData.errors)[0];
                    errorMessage = errorData.errors[firstErrorKey][0];
                }
                
                throw new Error(errorMessage);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                closeImportModal();
                showStatusModal('Success!', 'Your module has been updated with the imported lessons.', 'success');

                MaterialBuilder.hasChanged = false;
                localStorage.removeItem("material_draft_" + materialId);

                setTimeout(() => {
                    let buildUrl = wrapper.dataset.builderUrl;
                    if (typeof loadPartial === 'function') {
                        loadPartial(buildUrl);
                    } else {
                        window.location.href = buildUrl;
                    }
                }, 2000);
            } else {
                throw new Error(data.message || 'Import failed.');
            }
        })
        .catch(error => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            showStatusModal('Import Failed', error.message, 'error');
        });
    }

    function showStatusModal(title, message, type) {
        const modal = document.getElementById('status-modal');
        const iconContainer = document.getElementById('status-modal-icon');
        const titleEl = document.getElementById('status-modal-title');
        const msgEl = document.getElementById('status-modal-message');
        const actionBtn = document.getElementById('status-modal-btn');

        iconContainer.className = 'h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
        actionBtn.className = 'w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md';

        if (type === 'success') {
            iconContainer.classList.add('bg-green-50', 'text-green-500');
            iconContainer.innerHTML = '<i class="fas fa-check-circle"></i>';
            actionBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        } else {
            iconContainer.classList.add('bg-red-50', 'text-red-500');
            iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            actionBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        }

        titleEl.innerText = title;
        msgEl.innerText = message;
        modal.classList.remove('hidden');
        actionBtn.onclick = () => modal.classList.add('hidden');
    }
</script>