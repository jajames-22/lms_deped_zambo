<div id="material-wrapper" data-material-id="{{ $material->id ?? '' }}"
    data-is-new="{{ isset($isNew) && $isNew ? 'true' : 'false' }}"
    data-autosave-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.autosave', $material->id) : '#' }}"
    data-save-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.store', $material->id) : '#' }}"
    data-builder-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.edit', $material->id) : '#' }}"
    data-manage-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.manage', $material->id) : '#' }}"
    data-delete-url="{{ isset($material) && isset($material->id) ? route('dashboard.materials.destroy', $material->id) : '#' }}"
    data-redirect-url="{{ route('dashboard.materials.index') }}" data-csrf="{{ csrf_token() }}"
    data-upload-url="{{ route('dashboard.materials.upload_media') }}" class="space-y-6 pb-20 w-full max-w-5xl mx-auto">

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <input type="hidden" id="existing-data" value="{{ json_encode($lessons ?? []) }}">
    <input type="hidden" id="server-draft-data" value="{{ $material->draft_json ?? '' }}">

    <img src
        onerror="if(typeof MaterialBuilder !== 'undefined' && typeof MaterialBuilder.initBuilder === 'function') MaterialBuilder.initBuilder()"
        style="display:none;">

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
                <div id="total-time-display"
                    class="text-xs text-blue-600 font-bold px-3 py-1 bg-blue-50 border border-blue-100 rounded-lg flex items-center">
                    <i class="far fa-clock mr-1"></i> 0 mins
                </div>

                <div id="autosave-indicator"
                    class="text-xs text-gray-400 italic font-medium px-3 py-1 bg-white border border-gray-100 rounded-lg">
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
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Course / Module
                            Title</label>
                        <input type="text" id="setup-title" value="{{ $material->title ?? '' }}"
                            placeholder="e.g. Complete Web Development Bootcamp"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Course Description &
                            Overview</label>
                        <textarea id="setup-desc" rows="3"
                            placeholder="Provide an overview of what students will learn..."
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">{{ $material->description ?? '' }}</textarea>
                    </div>
                </div>

                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-0 relative overflow-hidden flex flex-col justify-center items-center bg-gray-50 hover:bg-gray-100 transition group cursor-pointer min-h-[160px] h-full"
                    onclick="document.getElementById('thumbnail-upload').click()">
                    <input type="file" id="thumbnail-upload" class="hidden" accept="image/*"
                        onchange="previewThumbnail(this)">

                    <img id="thumbnail-preview"
                        src="{{ isset($material) && $material->thumbnail ? asset('storage/' . $material->thumbnail) : '' }}"
                        class="absolute inset-0 w-full h-full object-cover {{ isset($material) && $material->thumbnail ? '' : 'hidden' }} z-0"
                        alt="Thumbnail Preview">

                    <button type="button" id="remove-thumbnail-btn"
                        onclick="removeThumbnail(event, {{ $material->id ?? 'null' }})"
                        class="absolute top-3 right-3 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-700 transition z-20 {{ isset($material) && $material->thumbnail ? '' : 'hidden' }}"
                        title="Remove Thumbnail">
                        <i class="fas fa-times"></i>
                    </button>

                    <div id="thumbnail-placeholder"
                        class="flex flex-col items-center justify-center relative z-10 w-full h-full rounded-xl transition {{ isset($material) && $material->thumbnail ? 'bg-black/40 opacity-0 hover:opacity-100' : 'bg-white/50 backdrop-blur-[2px] group-hover:bg-white/80' }}">
                        <i
                            class="fas fa-image text-4xl {{ isset($material) && $material->thumbnail ? 'text-white' : 'text-gray-400 group-hover:text-[#a52a2a]' }} transition mb-3"></i>
                        <span
                            class="text-sm font-bold {{ isset($material) && $material->thumbnail ? 'text-white' : 'text-gray-700' }} mb-1">{{ isset($material) && $material->thumbnail ? 'Change Thumbnail' : 'Course Thumbnail' }}</span>
                        <p
                            class="text-[10px] text-center {{ isset($material) && $material->thumbnail ? 'text-gray-200' : 'text-gray-500' }} px-4">
                            Upload a cover image (JPG, PNG).</p>
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
            <button type="button" onclick="MaterialBuilder.addSection('lesson')"
                class="px-4 py-8 border-2 border-dashed border-blue-200 text-blue-600 font-bold rounded-2xl hover:bg-blue-50 transition flex flex-col items-center justify-center gap-3 group bg-white shadow-sm">
                <div
                    class="h-12 w-12 rounded-full bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center transition text-xl">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="text-center">
                    <span class="block text-lg">Add Lesson</span>
                    <span class="text-xs font-medium text-blue-400 mt-1 block">Includes reading content and practice
                        quizzes</span>
                </div>
            </button>

            <button type="button" onclick="handleAddExam()"
                class="px-4 py-8 border-2 border-dashed border-red-200 text-red-600 font-bold rounded-2xl hover:bg-red-50 transition flex flex-col items-center justify-center gap-3 group bg-white shadow-sm">
                <div
                    class="h-12 w-12 rounded-full bg-red-100 group-hover:bg-red-200 flex items-center justify-center transition text-xl">
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
                class="cursor-pointer flex-1 py-3 border-2 border-dashed border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-center gap-2 text-sm bg-white">
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
    </div>
</div>

<div id="quick-nav-widget" class="fixed bottom-8 right-8 z-[90] flex flex-col items-end gap-2 hidden">
    <div id="quick-nav-menu"
        class="hidden flex-col gap-2 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-2xl border border-gray-200 mb-2 w-72 max-h-[60vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-2 px-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Course Outline</span>
            <span id="nav-lesson-count"
                class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-bold">0</span>
        </div>
        <div id="quick-nav-list" class="flex flex-col gap-1">
        </div>
    </div>
    <button onclick="toggleQuickNav()"
        class="h-14 w-14 bg-gray-900 text-white rounded-full shadow-lg hover:bg-gray-800 hover:scale-105 transition-all flex items-center justify-center text-xl active:scale-95 group">
        <i class="fas fa-list-ul group-hover:hidden"></i>
        <i class="fas fa-chevron-up hidden group-hover:block"></i>
    </button>
</div>

<div id="back-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="p-6 text-center">
                <div
                    class="h-16 w-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-save"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Unsaved Changes</h3>
                <p class="text-gray-500 text-sm mb-6">How would you like to exit? Your progress is currently stored as a
                    temporary draft.</p>

                <div class="space-y-3">
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
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        onclick="document.getElementById('status-modal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
            <div id="status-modal-icon"
                class="h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fas fa-check-circle text-green-500"></i>
            </div>
            <h3 id="status-modal-title" class="text-xl font-bold text-gray-900 mb-2">Title</h3>
            <p id="status-modal-message" class="text-gray-500 text-sm mb-6">Message goes here.</p>
            <div class="flex gap-3 mt-2">
                <button id="status-modal-cancel-btn" type="button"
                    class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95 hidden">Cancel</button>
                <button id="status-modal-btn" type="button"
                    class="w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md">OK</button>
            </div>
        </div>
    </div>
</div>

<div id="excel-import-modal" class="fixed inset-0 z-[120] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6">
            <div class="text-center mb-6">
                <div
                    class="h-16 w-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-file-excel"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Import Content</h3>
                <p class="text-gray-500 text-sm mt-1">Upload your filled Excel or CSV template.</p>
            </div>
            <div class="mb-6">
                <input type="file" id="excel-file-input" accept=".xlsx, .xls, .csv" class="hidden"
                    onchange="handleFileSelect(this)">
                <label for="excel-file-input" id="file-dropzone"
                    class="cursor-pointer flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500 font-medium">Click to choose a file</p>
                        <p class="text-xs text-gray-400 mt-1">.xlsx, .xls, or .csv</p>
                    </div>
                </label>
                <div id="selected-file-display"
                    class="hidden mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <i class="fas fa-file-excel text-blue-500 text-lg"></i>
                        <span id="selected-file-name"
                            class="font-mono text-sm text-blue-700 truncate font-medium">filename.xlsx</span>
                    </div>
                    <button type="button" onclick="clearSelectedFile()"
                        class="text-blue-400 hover:text-red-500 transition px-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div id="excel-upload-progress-container"
                class="hidden my-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex justify-between text-xs mb-2">
                    <span class="text-blue-700 font-bold flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin text-blue-600"></i> Uploading & Processing...
                    </span>
                    <span id="excel-upload-progress-text" class="text-blue-700 font-black">0%</span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-2.5 overflow-hidden">
                    <div id="excel-upload-progress-bar"
                        class="bg-blue-600 h-2.5 rounded-full transition-all duration-150" style="width: 0%"></div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeImportModal()"
                    class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">Cancel</button>
                <button type="button" id="start-upload-btn" onclick="executeExcelUpload()" disabled
                    class="w-full py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md flex justify-center items-center gap-2">
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
                <div
                    class="h-16 w-16 bg-[#a52a2a]/10 text-[#a52a2a] rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-photo-video"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Upload Media</h3>
                <p class="text-gray-500 text-sm mt-1">Attach an image, audio, video, PDF or ZIP.</p>
            </div>

            <div class="mb-6">
                <input type="file" id="media-file-input" class="hidden"
                    onchange="MaterialBuilder.handleMediaFileSelect(this)">

                <label for="media-file-input" id="media-dropzone"
                    class="cursor-pointer flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-[#a52a2a]/50 transition">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500 font-medium">Click to choose a file</p>
                    </div>
                </label>

                <div id="selected-media-display"
                    class="hidden mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <i class="fas fa-file-alt text-[#a52a2a] text-lg"></i>
                        <span id="selected-media-name"
                            class="font-mono text-sm text-gray-700 truncate font-medium">file.mp4</span>
                    </div>
                    <button type="button" onclick="MaterialBuilder.clearSelectedMedia()"
                        class="text-gray-400 hover:text-red-500 transition px-2" title="Remove File">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="upload-progress-container"
                    class="hidden mt-4 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex justify-between text-xs mb-2">
                        <span class="text-gray-600 font-bold flex items-center gap-2"><i
                                class="fas fa-spinner fa-spin text-[#a52a2a]"></i> Uploading...</span>
                        <span id="upload-progress-text" class="text-[#a52a2a] font-black">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                        <div id="upload-progress-bar"
                            class="bg-[#a52a2a] h-2.5 rounded-full transition-all duration-150" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="MaterialBuilder.closeMediaModal()"
                    class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">
                    Cancel
                </button>
                <button type="button" id="start-media-upload-btn" onclick="MaterialBuilder.executeMediaUpload()"
                    disabled
                    class="w-full py-3.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-[#801f1f] transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md flex justify-center items-center gap-2">
                    <i class="fas fa-upload"></i>
                    <span>Upload Media</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/material.js') }}"></script>


<script>
    /* Original scripts remain exactly the same */
    function previewThumbnail(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('thumbnail-preview');
                const placeholder = document.getElementById('thumbnail-placeholder');
                const removeBtn = document.getElementById('remove-thumbnail-btn');

                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                if (removeBtn) {
                    removeBtn.classList.remove('hidden');
                }
                if (placeholder) {
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

        // Reset progress UI
        const progressContainer = document.getElementById('excel-upload-progress-container');
        if (progressContainer) {
            progressContainer.classList.add('hidden');
            document.getElementById('excel-upload-progress-bar').style.width = '0%';
            document.getElementById('excel-upload-progress-text').innerText = '0%';
        }
    }

    let materialImportXhr = null; // Changed from AbortController to XHR reference

    function closeImportModal() {
        if (materialImportXhr) {
            materialImportXhr.abort(); // Cancel the upload if modal is closed
            materialImportXhr = null;
            console.log("Material import upload aborted.");
        }
        document.getElementById('excel-import-modal').classList.add('hidden');
    }

    function executeExcelUpload() {
        if (!selectedFile) return;

        let btn = document.getElementById('start-upload-btn');
        let originalHtml = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
        btn.disabled = true;

        // Reveal Progress UI
        const progressContainer = document.getElementById('excel-upload-progress-container');
        const progressBar = document.getElementById('excel-upload-progress-bar');
        const progressText = document.getElementById('excel-upload-progress-text');

        progressContainer.classList.remove('hidden');
        progressBar.style.width = '0%';
        progressText.innerText = '0%';

        let payload = MaterialBuilder.getPayload("draft");
        let formData = new FormData();

        formData.append('module_file', selectedFile);
        formData.append('_token', document.querySelector('[data-csrf]').dataset.csrf);
        formData.append('title', payload.title);
        formData.append('description', payload.description);
        formData.append('categories', JSON.stringify(payload.categories));

        let wrapper = document.getElementById('material-wrapper');
        let materialId = wrapper.dataset.materialId;

        // Use XMLHttpRequest instead of fetch to track upload progress
        materialImportXhr = new XMLHttpRequest();

        // 1. Listen to Upload Progress
        materialImportXhr.upload.addEventListener("progress", function (evt) {
            if (evt.lengthComputable) {
                let percentComplete = Math.round((evt.loaded / evt.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressText.innerText = percentComplete + '%';
            }
        });

        // 2. Listen to Upload Complete
        materialImportXhr.addEventListener("load", function () {
            materialImportXhr = null;
            btn.innerHTML = originalHtml;
            btn.disabled = false;

            if (this.status >= 200 && this.status < 300) {
                try {
                    let data = JSON.parse(this.responseText);
                    if (data.success) {
                        closeImportModal();
                        MaterialBuilder.showModal('success', 'Import Successful!', 'Your module content has been imported successfully.', () => {
                            MaterialBuilder.goToUrl(wrapper.dataset.builderUrl);
                        });
                    } else {
                        MaterialBuilder.showModal('error', 'Import Failed', data.message || 'Unknown error occurred.');
                    }
                } catch (e) {
                    MaterialBuilder.showModal('error', 'Import Failed', 'Invalid server response.');
                }
            } else {
                let errorMsg = `Server error (${this.status})`;
                try {
                    let errData = JSON.parse(this.responseText);
                    if (errData.message) errorMsg = errData.message;
                } catch (e) { }
                MaterialBuilder.showModal('error', 'Import Failed', errorMsg);
            }
        });

        // 3. Listen to Network Errors
        materialImportXhr.addEventListener("error", function () {
            materialImportXhr = null;
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            MaterialBuilder.showModal('error', 'Import Failed', 'A network error occurred.');
        });

        // 4. Open and Send
        materialImportXhr.open("POST", `/dashboard/materials/${materialId}/import`);
        materialImportXhr.setRequestHeader('Accept', 'application/json');
        materialImportXhr.send(formData);
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

    // FIXED EXAM FUNCTION
    function handleAddExam() {
        const existingExams = document.querySelectorAll('.category-block[data-section-type="exam"]');

        if (existingExams.length >= 1) {
            showStatusModal('Not Allowed', 'You can only add one (1) Final Exam per module.', 'error');
            return;
        }

        MaterialBuilder.addSection('exam');
    }
</script>

<script>
    (function initializeQuickNav() {
        const container = document.getElementById('builder-container');
        const contentArea = document.getElementById('content-area');

        if (!container) {
            setTimeout(initializeQuickNav, 100);
            return;
        }

        function updateNavigationAndStickyHeaders() {
            const list = document.getElementById('quick-nav-list');
            const widget = document.getElementById('quick-nav-widget');

            // Handle both assessment and material count IDs
            const counter = document.getElementById('nav-lesson-count') || document.getElementById('nav-category-count');

            if (!list || !widget || !counter) return;

            list.innerHTML = '';

            const sections = container.children;

            if (sections.length > 0) {
                widget.classList.remove('hidden');
                counter.innerText = sections.length;
            } else {
                widget.classList.add('hidden');
                document.getElementById('quick-nav-menu').classList.add('hidden');
            }

            const gapOffset = contentArea ? window.getComputedStyle(contentArea).paddingTop : '32px';

            Array.from(sections).forEach((section, index) => {
                if (!section.id) section.id = 'builder-section-' + index;

                section.classList.remove('overflow-hidden');
                section.style.overflow = 'visible';

                let header = section.firstElementChild;
                if (header && header.tagName === 'INPUT') {
                    header = header.nextElementSibling;
                }

                if (header) {
                    header.style.position = 'sticky';
                    header.style.top = '-' + gapOffset;
                    header.style.zIndex = '10';

                    // 1. Remove old flat backgrounds
                    header.classList.remove('bg-gray-50/50', 'bg-white');

                    // 2. Apply Premium Glassmorphism (frosted glass blur effect)
                    header.style.backgroundColor = 'rgba(255, 255, 255, 0.85)';
                    header.style.backdropFilter = 'blur(12px)';
                    header.style.WebkitBackdropFilter = 'blur(12px)'; // Safari support

                    // 3. Add a distinct bottom border to separate from scrolling content
                    header.style.borderBottom = '1px solid #e5e7eb';

                    // 4. Match the rounded corners of the parent card
                    header.style.borderTopLeftRadius = '1rem';
                    header.style.borderTopRightRadius = '1rem';

                    // 5. Softer, more elevated floating shadow
                    header.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025)';
                }

                const titleInput = section.querySelector('input[type="text"]');
                let titleText = `Section ${index + 1}`;

                if (titleInput && titleInput.value) {
                    titleText = titleInput.value;
                } else if (section.innerHTML.includes('Final Exam') || section.innerHTML.includes('section_type="exam"')) {
                    titleText = 'Final Exam';
                }

                if (titleInput) {
                    titleInput.addEventListener('input', (e) => {
                        const btn = document.getElementById('jump-btn-' + index);
                        if (btn) btn.innerHTML = `<i class="fas fa-circle text-[8px] text-gray-300"></i> <span class="truncate">${e.target.value || `Section ${index + 1}`}</span>`;
                    });
                }

                const btn = document.createElement('button');
                btn.id = 'jump-btn-' + index;
                btn.className = 'w-full text-left px-3 py-2.5 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition flex items-center gap-3 font-medium border border-transparent hover:border-gray-200';
                btn.innerHTML = `<i class="fas fa-circle text-[8px] text-gray-300"></i> <span class="truncate">${titleText}</span>`;

                btn.onclick = () => {
                    const yOffset = -80;
                    const y = section.getBoundingClientRect().top + window.pageYOffset + yOffset;

                    if (contentArea) {
                        contentArea.scrollTo({ top: section.offsetTop - 20, behavior: 'smooth' });
                    } else {
                        window.scrollTo({ top: y, behavior: 'smooth' });
                    }

                    section.classList.add('ring-4', 'ring-[#a52a2a]/30', 'transition-all', 'duration-500');
                    setTimeout(() => section.classList.remove('ring-4', 'ring-[#a52a2a]/30'), 1500);
                    window.toggleQuickNav();
                };
                list.appendChild(btn);
            });
        }

        const observer = new MutationObserver(() => {
            updateNavigationAndStickyHeaders();
        });

        observer.observe(container, { childList: true, subtree: false });

        setTimeout(updateNavigationAndStickyHeaders, 500);
    })();

    window.toggleQuickNav = function () {
        const menu = document.getElementById('quick-nav-menu');
        if (!menu) return;

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menu.classList.add('flex');
        } else {
            menu.classList.add('hidden');
            menu.classList.remove('flex');
        }
    };
</script>