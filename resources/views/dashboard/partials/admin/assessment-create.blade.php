<div id="assessment-wrapper" 
     data-save-url="{{ route('dashboard.assessments.store_questions', $assessment->id) }}"
     data-delete-url="{{ route('dashboard.assessments.destroy', $assessment->id) }}"
     data-csrf="{{ csrf_token() }}"
     data-redirect-url="{{ route('dashboard.assessment') }}"
     class="space-y-6 pb-20 w-full max-w-5xl mx-auto">
    
    <input type="hidden" id="existing-data" value="{{ json_encode($categories ?? []) }}">
    
    <img src onerror="if(typeof window.initBuilder === 'function') window.initBuilder()" style="display:none;">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-100 p-6 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-4">
                <button type="button" onclick="loadPartial('{{ route('dashboard.assessment') }}', this)" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-[#a52a2a] transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-900">Assessment Settings</h2>
            </div>
            <div class="flex items-center gap-3">
                <div id="autosave-indicator" class="text-xs text-gray-400 italic font-medium px-3 py-1 bg-white border border-gray-100 rounded-lg">Ready</div>
                <button type="button" onclick="window.deleteAssessmentFromBuilder()" class="h-10 px-4 flex items-center gap-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition text-sm font-bold">
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
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Year / Grade Level</label>
                            <input type="text" id="setup-year" value="{{ $assessment->year_level }}" placeholder="ex. Grade 7"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Instructions</label>
                        <textarea id="setup-desc" rows="2" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition font-medium">{{ $assessment->description }}</textarea>
                    </div>
                </div>

                <div class="bg-[#a52a2a] rounded-2xl p-6 text-white relative overflow-hidden flex flex-col justify-center items-center shadow-lg shadow-[#a52a2a]/20">
                    <i class="fas fa-key absolute -right-2 -bottom-2 text-6xl text-white/10 rotate-12"></i>
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80 mb-1">Student Access Key</span>
                    <div class="text-4xl font-mono font-black tracking-widest">{{ $assessment->access_key }}</div>
                    <p class="text-[10px] mt-3 opacity-70 text-center">Share this key for students to begin the test.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="builder-container" class="space-y-4"></div>

    <button type="button" onclick="window.addCategory()" class="w-full mt-4 px-4 py-6 border-2 border-dashed border-gray-200 text-gray-400 font-bold rounded-2xl hover:bg-gray-50 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] transition flex items-center justify-center gap-3 group">
        <div class="h-8 w-8 rounded-full bg-gray-100 group-hover:bg-[#a52a2a]/10 flex items-center justify-center transition">
            <i class="fas fa-plus"></i>
        </div>
        Add New Section / Category
    </button>

    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end gap-4">
        <button type="button" onclick="window.saveCompleteExam(this, 'draft')" class="px-8 py-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition shadow-sm active:scale-95">
            Save as Draft
        </button>
        <button type="button" onclick="window.saveCompleteExam(this, 'published')" class="px-10 py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition shadow-lg shadow-green-600/20 flex items-center gap-2 active:scale-95">
            <span>Publish & Open Exam</span>
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>