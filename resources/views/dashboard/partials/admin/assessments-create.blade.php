<div id="assessment-wrapper" data-assessment-id="{{ $assessment->id }}"
    data-autosave-url="{{ route('dashboard.assessments.autosave', $assessment->id) }}"
    data-save-url="{{ route('dashboard.assessments.store_questions', $assessment->id) }}"
    data-delete-url="{{ route('dashboard.assessments.destroy', $assessment->id) }}"
    data-redirect-url="{{ route('dashboard.assessments.index') }}" data-csrf="{{ csrf_token() }}"
    data-upload-url="{{ route('dashboard.assessments.upload_image') }}"
    class="space-y-6 pb-20 w-full max-w-5xl mx-auto">

    <input type="hidden" id="existing-data" value="{{ json_encode($categories ?? []) }}">
    <input type="hidden" id="server-draft-data" value="{{ $assessment->draft_json ?? '' }}">

    <img src onerror="if(typeof window.initBuilder === 'function') window.initBuilder()" style="display:none;">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-100 p-6 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-4">
                <button type="button" onclick="document.getElementById('back-modal').classList.remove('hidden')"
                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-[#a52a2a] transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-900">Assessment Settings</h2>
            </div>
            <div class="flex items-center gap-3">
                <div id="autosave-indicator"
                    class="text-xs text-gray-400 italic font-medium px-3 py-1 bg-white border border-gray-100 rounded-lg">
                    Ready</div>
                <button type="button" onclick="window.deleteAssessmentFromBuilder()"
                    class="h-10 px-4 flex items-center gap-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition text-sm font-bold">
                    <i class="fas fa-trash-alt"></i> Discard
                </button>
            </div>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Test Title</label>
                            <input type="text" id="setup-title" value="{{ $assessment->title }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Year / Grade
                                Level</label>
                            <input type="text" id="setup-year" value="{{ $assessment->year_level }}"
                                placeholder="ex. Grade 7"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Instructions</label>
                        <textarea id="setup-desc" rows="2"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">{{ $assessment->description }}</textarea>
                    </div>
                </div>

                <div
                    class="bg-[#a52a2a] rounded-2xl p-6 text-white relative overflow-hidden flex flex-col justify-center items-center shadow-lg shadow-[#a52a2a]/20">
                    <i class="fas fa-key absolute -right-2 -bottom-2 text-6xl text-white/10 rotate-12"></i>
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80 mb-1">Student Access
                        Key</span>
                    <div class="text-4xl font-mono font-black tracking-widest">{{ $assessment->access_key }}</div>
                    <p class="text-[10px] mt-3 opacity-70 text-center">Share this key for students to begin the test.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="builder-container" class="space-y-4"></div>

    <button type="button" onclick="window.addCategory()"
        class="w-full mt-4 px-4 py-6 border-2 border-dashed border-gray-200 text-gray-400 font-bold rounded-2xl hover:bg-gray-50 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] transition flex items-center justify-center gap-3 group">
        <div
            class="h-8 w-8 rounded-full bg-gray-100 group-hover:bg-[#a52a2a]/10 flex items-center justify-center transition">
            <i class="fas fa-plus"></i>
        </div>
        Add New Section / Category
    </button>

    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end gap-4">
        <button type="button" onclick="window.saveCompleteExam(this, 'draft')"
            class="px-8 py-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition shadow-sm active:scale-95">
            Save as Draft
        </button>
        <button type="button" onclick="window.saveCompleteExam(this, 'published')"
            class="px-10 py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition shadow-lg shadow-green-600/20 flex items-center gap-2 active:scale-95">
            <span>Publish & Open Exam</span>
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
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
                    <button onclick="window.saveCompleteExam(this, 'published')"
                        class="w-full py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane text-sm"></i>
                        <span>Publish & Exit</span>
                    </button>

                    <button onclick="window.saveCompleteExam(this, 'draft')"
                        class="w-full py-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition flex items-center justify-center gap-2">
                        <i class="fas fa-file-alt text-sm text-gray-400"></i>
                        <span>Save as Draft & Exit</span>
                    </button>

                    <button type="button" onclick="window.discardChangesAndExit(this)"
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
            </div>

            <h3 id="status-modal-title" class="text-xl font-bold text-gray-900 mb-2">Title</h3>
            <p id="status-modal-message" class="text-gray-500 text-sm mb-6">Message goes here.</p>

            <div class="flex gap-3 mt-2">
                <button id="status-modal-cancel-btn" type="button"
                    class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95 hidden">
                    Cancel
                </button>
                <button id="status-modal-btn" type="button"
                    class="w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>