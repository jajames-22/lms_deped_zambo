<div id="assessment-wrapper" 
     data-save-url="{{ route('dashboard.assessments.store_questions', $assessment->id) }}"
     data-csrf="{{ csrf_token() }}"
     data-redirect-url="{{ route('dashboard.assessment') }}"
     class="space-y-6 pb-20 w-full max-w-5xl mx-auto">
    
    <img src onerror="if(typeof window.initBuilder === 'function') window.initBuilder()" style="display:none;">

    <div class="flex items-center justify-between mb-2">
        <button type="button" onclick="loadPartial(document.getElementById('assessment-wrapper').dataset.redirectUrl, this)" class="text-gray-500 hover:text-[#a52a2a] transition flex items-center gap-2 font-semibold">
            <i class="fas fa-arrow-left"></i> Back to Assessments
        </button>
        <h2 class="text-xl font-bold text-gray-800">Assessment Builder</h2>
    </div>

    <div class="bg-[#a52a2a]/10 border-2 border-[#a52a2a]/30 rounded-2xl p-6 text-center relative overflow-hidden shadow-sm">
        <i class="fas fa-key absolute -right-4 -bottom-4 text-8xl text-[#a52a2a]/10"></i>
        <h3 class="text-[#a52a2a] font-bold uppercase tracking-wider text-sm mb-1">Student Access Key</h3>
        <h1 class="text-5xl font-mono font-black text-gray-900 tracking-[0.2em]">{{ $assessment->access_key }}</h1>
        <p class="text-gray-600 mt-2 text-sm">Share this 6-digit key with your students to access: <b>{{ $assessment->title }}</b></p>
    </div>

    <div id="builder-container" class="space-y-6">
        </div>

    <button type="button" onclick="window.addCategory()" class="w-full mt-6 px-4 py-4 border-2 border-dashed border-gray-300 text-gray-500 font-semibold rounded-xl hover:bg-[#a52a2a]/5 hover:border-[#a52a2a] hover:text-[#a52a2a] transition flex items-center justify-center gap-2 group">
        <i class="fas fa-plus-circle text-lg group-hover:scale-110 transition-transform"></i> Add Exam Category (e.g., English, Math, Logic)
    </button>

    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end gap-4">
        <button type="button" onclick="window.saveCompleteExam(this, 'draft')" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition flex items-center gap-2">
            <span>Save as Draft</span>
            <i class="fas fa-save"></i>
        </button>
        
        <button type="button" onclick="window.saveCompleteExam(this, 'published')" class="px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-600/30 flex items-center gap-2 active:scale-95">
            <span>Publish & Open Exam</span>
            <i class="fas fa-lock-open"></i>
        </button>
    </div>
</div>