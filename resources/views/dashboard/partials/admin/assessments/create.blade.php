<div class="p-6 max-w-3xl mx-auto pb-20">
    <div class="mb-6">
        <button type="button" onclick="loadPartial('{{ route('dashboard.assessment') }}', this)" class="text-gray-500 hover:text-[#a52a2a] transition flex items-center gap-2 font-semibold">
            <i class="fas fa-arrow-left"></i> Back to Assessments
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New Assessment</h2>
        <p class="text-gray-500 mb-6 text-sm">Step 1: Set up the basic details. An access key will be generated automatically.</p>

        <div id="setup-wrapper" 
             data-setup-url="{{ route('dashboard.assessments.store_setup') }}" 
             data-csrf="{{ csrf_token() }}" 
             class="space-y-4">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Assessment Title</label>
                <input type="text" id="setup-title" required placeholder="e.g., Midterm Examination"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Year / Grade Level</label>
                <input type="text" id="setup-year" required placeholder="e.g., Grade 10"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition">
            </div>

            <div class="pb-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Instructions / Description</label>
                <textarea id="setup-desc" rows="4" placeholder="General instructions for the students..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="window.submitAssessmentSetup(this)" class="px-6 py-3 bg-[#a52a2a] text-white font-semibold rounded-xl hover:opacity-90 transition shadow-lg shadow-[#a52a2a]/30 flex items-center gap-2">
                    <span>Generate Key & Proceed to Builder</span> <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>