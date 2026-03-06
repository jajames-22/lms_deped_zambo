<div class="space-y-6 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">Assessment Management</h1>
            <p class="text-gray-500 text-sm">Data-driven test to measure students learning progress.</p>
        </div>
        
        <button onclick="loadPartial('{{ route('dashboard.assessments.create') }}', this)" 
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition-all active:scale-95">
            <i class="fas fa-plus"></i>
            <span>Create New Test</span>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center space-x-1 bg-gray-200/50 p-1 rounded-xl w-fit shrink-0">
            <button onclick="filterAssessments('all', this)" class="assessment-tab px-6 py-2 text-sm font-bold rounded-lg transition-all bg-white text-[#a52a2a] shadow-sm">
                All
            </button>
            <button onclick="filterAssessments('live', this)" class="assessment-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700">
                Live
            </button>
            <button onclick="filterAssessments('draft', this)" class="assessment-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700">
                Drafts
            </button>
        </div>

        <div class="relative w-full max-w-md">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="assessment-search" onkeyup="searchAssessments()" 
                placeholder="Search test by title..." 
                class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all shadow-sm">
        </div>
    </div>

    <div id="assessment-grid" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        
        @forelse($assessments as $assessment)
            @php
                // If it has categories, it's considered fully built ("live"). Otherwise "draft".
                $isLive = $assessment->categories->count() > 0;
            @endphp

            @if($isLive)
                <div class="assessment-card live bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group overflow-hidden border-t-4 border-t-green-500">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="bg-green-50 text-green-600 p-3 rounded-xl"><i class="fas fa-file-signature text-xl"></i></div>
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-md uppercase">Live</span>
                        </div>
                        <h4 class="test-title text-lg font-bold text-gray-900 mb-1 group-hover:text-green-600 transition-colors">{{ $assessment->title }}</h4>
                        <p class="text-xs text-gray-500 mb-4 line-clamp-2">{{ $assessment->description ?? 'No description provided.' }}</p>
                        
                        <div class="space-y-2 mb-6 text-xs text-gray-600">
                            <div class="flex items-center"><i class="fas fa-key w-5 text-gray-400"></i> Key: <strong class="ml-1 tracking-widest text-gray-900">{{ $assessment->access_key }}</strong></div>
                            <div class="flex items-center"><i class="fas fa-layer-group w-5 text-gray-400"></i> {{ $assessment->categories->count() }} Categories</div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button class="flex-1 py-2 bg-gray-50 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-100 transition">Edit</button>
                            <button class="flex-1 py-2 bg-[#a52a2a] text-white text-xs font-bold rounded-lg hover:bg-red-800 transition shadow-sm">Results</button>
                        </div>
                    </div>
                </div>
            @else
                <div class="assessment-card draft bg-white rounded-2xl border-2 border-dashed border-amber-200 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="bg-amber-50 text-amber-600 p-3 rounded-xl"><i class="fas fa-tools text-xl"></i></div>
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md uppercase">Draft</span>
                    </div>
                    <h4 class="test-title text-lg font-bold text-gray-900 mb-1 italic">{{ $assessment->title }}</h4>
                    <p class="text-xs text-gray-500 mb-6 line-clamp-2">Setup complete, but no questions added yet.</p>
                    <button onclick="loadPartial('{{ route('dashboard.assessments.builder', $assessment->id) }}', this)" class="w-full py-2.5 bg-gray-900 text-white text-xs font-bold rounded-lg hover:bg-black transition flex justify-center items-center gap-2">
                        <span>Continue Building</span> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            @endif
        @empty
            <div class="col-span-full py-12 text-center text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                <p>No assessments created yet.</p>
            </div>
        @endforelse

    </div>
</div>

<script>
    let currentStatus = 'all';

    function filterAssessments(status, btnElement) {
        currentStatus = status;
        
        document.querySelectorAll('.assessment-tab').forEach(tab => {
            tab.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm');
            tab.classList.add('text-gray-500');
        });
        btnElement.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm');
        btnElement.classList.remove('text-gray-500');

        applyFilters();
    }

    function searchAssessments() {
        applyFilters();
    }

    function applyFilters() {
        const query = document.getElementById('assessment-search').value.toLowerCase();
        const cards = document.querySelectorAll('.assessment-card');

        cards.forEach(card => {
            const title = card.querySelector('.test-title').innerText.toLowerCase();
            const matchesTab = (currentStatus === 'all' || card.classList.contains(currentStatus));
            const matchesSearch = title.includes(query);

            if (matchesTab && matchesSearch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>