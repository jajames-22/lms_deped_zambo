<div id="material-wrapper" data-material-id="{{ $material->id ?? 'new' }}"
    data-autosave-url="{{ route('dashboard.materials.autosave', $material->id ?? 0) }}"
    data-save-url="{{ route('dashboard.materials.store', $material->id ?? 0) }}"
    data-redirect-url="{{ route('dashboard.materials.index') }}" data-csrf="{{ csrf_token() }}"
    data-upload-url="{{ route('dashboard.materials.upload_media') }}"
    class="space-y-6 pb-20 w-full max-w-5xl mx-auto">

    <input type="hidden" id="existing-data" value="{{ json_encode($lessons ?? []) }}">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-100 p-6 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-4">
                <button type="button" onclick="loadPartial('{{ route('dashboard.materials.index') }}', document.getElementById('nav-materials-btn'))"
                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-[#a52a2a] transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-900">Module Setup</h2>
            </div>
            <div class="flex items-center gap-3">
                <div id="autosave-indicator" class="text-xs text-gray-400 italic font-medium px-3 py-1 bg-white border border-gray-100 rounded-lg">
                    Ready
                </div>
                <button type="button" onclick="window.discardChangesAndExit(this)"
                    class="h-10 px-4 flex items-center gap-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition text-sm font-bold">
                    <i class="fas fa-times"></i> Discard
                </button>
            </div>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Module / Material Title</label>
                        <input type="text" id="setup-title" value="{{ $material->title ?? '' }}" placeholder="e.g. Chapter 1: Introduction to Web Dev"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Module Description & Context</label>
                        <textarea id="setup-desc" rows="3" placeholder="Provide an overview of what students will learn..."
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">{{ $material->description ?? '' }}</textarea>
                    </div>
                </div>

                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 relative overflow-hidden flex flex-col justify-center items-center bg-gray-50 hover:bg-gray-100 transition group cursor-pointer" onclick="document.getElementById('master-file-upload').click()">
                    <input type="file" id="master-file-upload" class="hidden" accept=".pdf,.ppt,.pptx,.mp4,.zip">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 group-hover:text-[#a52a2a] transition mb-3"></i>
                    <span class="text-sm font-bold text-gray-600 mb-1">Upload Main File</span>
                    <p class="text-[10px] text-center text-gray-400 px-4">Attach a master PPT, PDF, or Video for this entire module.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="builder-container" class="space-y-4">
        </div>

    <div class="mt-6">
        <button type="button" onclick="window.addCategory()" class="w-full px-4 py-5 border-2 border-dashed border-gray-200 text-gray-500 font-bold rounded-2xl hover:bg-gray-50 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] transition flex items-center justify-center gap-3 group">
            <div class="h-8 w-8 rounded-full bg-gray-100 group-hover:bg-[#a52a2a]/10 flex items-center justify-center transition">
                <i class="fas fa-book-open"></i>
            </div>
            Add New Lesson / Sub-topic
        </button>
    </div>

    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end gap-4">
        <button type="button" onclick="window.saveCompleteMaterial(this, 'draft')"
            class="px-8 py-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition shadow-sm active:scale-95">
            Save as Draft
        </button>
        <button type="button" onclick="window.saveCompleteMaterial(this, 'published')"
            class="px-10 py-4 bg-[#a52a2a] text-white font-bold rounded-2xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 flex items-center gap-2 active:scale-95">
            <span>Publish Module</span>
            <i class="fas fa-upload"></i>
        </button>
    </div>
</div>