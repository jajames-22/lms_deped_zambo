<div class="space-y-6 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">Learning Materials</h1>
            <p class="text-gray-500 text-sm">Create and manage modules, files, and lessons for your students.</p>
        </div>

        <button onclick="loadPartial('{{ route('dashboard.materials.create') }}', document.getElementById('nav-materials-btn'))"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-[#a52a2a]/20 hover:bg-red-800 transition-all active:scale-95">
            <i class="fas fa-plus"></i>
            <span>Create New Material</span>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center space-x-1 bg-gray-200/50 p-1 rounded-xl w-fit shrink-0">
            <button onclick="window.filterMaterials('all', this)"
                class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all bg-white text-[#a52a2a] shadow-sm">All</button>
            <button onclick="window.filterMaterials('published', this)"
                class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700">Published</button>
            <button onclick="window.filterMaterials('draft', this)"
                class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700">Drafts</button>
        </div>

        <div class="relative w-full max-w-md">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="material-search" onkeyup="window.searchMaterials()"
                placeholder="Search materials by title..."
                class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all shadow-sm">
        </div>
    </div>

    <div id="material-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-stretch relative">
        @forelse($materials as $material)
            @php $isLive = ($material->status === 'published'); @endphp

            <div id="material-card-{{ $material->id }}"
                onclick="loadPartial('{{ route('dashboard.materials.show', $material->id) }}', document.getElementById('nav-materials-btn'))"
                class="material-card {{ $isLive ? 'published' : 'draft' }} flex flex-col h-full bg-white rounded-2xl border border-gray-200 {{ $isLive ? 'border-t-[#a52a2a] border-t-4' : 'border-t-amber-400 border-t-4' }} shadow-sm hover:shadow-lg transition-all duration-300 group overflow-hidden relative cursor-pointer">

                <button
                    onclick="event.stopPropagation(); window.deleteMaterialFromList('{{ $material->id }}', '{{ route('dashboard.materials.destroy', $material->id) }}')"
                    class="absolute top-4 right-4 h-8 w-8 rounded-full bg-gray-50 text-gray-400 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center hover:bg-red-100 hover:text-red-600 z-10"
                    title="Delete Material">
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>

                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-4 pr-8">
                        <div class="flex items-center gap-3">
                            <div class="{{ $isLive ? 'bg-[#a52a2a]/10 text-[#a52a2a]' : 'bg-amber-50 text-amber-600' }} p-3 rounded-xl flex items-center justify-center">
                                <i class="fas {{ $isLive ? 'fa-book-open' : 'fa-tools' }} text-xl"></i>
                            </div>
                            <span class="px-2.5 py-1 {{ $isLive ? 'bg-[#a52a2a]/10 text-[#a52a2a]' : 'bg-amber-100 text-amber-700' }} text-[10px] font-bold rounded-md uppercase tracking-wide">
                                {{ $isLive ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>

                    <h4 class="material-title text-lg font-bold text-gray-900 mb-2 line-clamp-1" title="{{ $material->title }}">{{ $material->title ?? 'Untitled Material' }}</h4>

                    <p class="text-sm text-gray-500 mb-5 line-clamp-2 flex-1">
                        {{ $material->description ?? 'No description provided for this module.' }}
                    </p>

                    <div class="bg-gray-50 rounded-xl p-4 mb-2 space-y-2 text-xs text-gray-600 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-gray-500"><i class="fas fa-chalkboard-teacher"></i> Instructor:</span>
                            <b class="text-gray-900">{{ $material->instructor->first_name ?? 'N/A' }}</b>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-gray-500"><i class="fas fa-book"></i> Lessons:</span>
                            <b class="text-gray-900">{{ $material->lessons_count ?? 0 }}</b>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-4 pt-4 border-t border-gray-100">
                        <button onclick="event.stopPropagation(); loadPartial('{{ route('dashboard.materials.edit', $material->id) }}', document.getElementById('nav-materials-btn'))"
                            class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] transition-all shadow-sm">
                            <i class="fas {{ $isLive ? 'fa-edit' : 'fa-play' }} mr-1"></i>
                            {{ $isLive ? 'Edit' : 'Resume' }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="h-16 w-16 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-4">
                    <i class="fas fa-folder-open text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No materials found, start building one!</h3>
            </div>
        @endforelse

        <div id="dynamic-empty-state" style="display: none;" class="col-span-full py-16 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-300">
            <div class="h-16 w-16 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-4">
                <i class="fas fa-search-minus text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No materials found matching your search.</h3>
        </div>
    </div>
</div>

<div id="status-modal" class="fixed inset-0 z-[110] hidden">
    </div>

<script>
    // Filtering Logic Similar to Assessments
    window.currentMaterialStatus = 'all';

    window.filterMaterials = function(status, btnElement) {
        window.currentMaterialStatus = status;

        document.querySelectorAll('.material-tab').forEach(tab => {
            tab.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm');
            tab.classList.add('text-gray-500', 'hover:text-gray-700');
        });

        btnElement.classList.remove('text-gray-500', 'hover:text-gray-700');
        btnElement.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm');

        window.applyMaterialFilters();
    };

    window.searchMaterials = function() {
        window.applyMaterialFilters();
    };

    window.applyMaterialFilters = function() {
        const query = document.getElementById('material-search').value.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.material-card').forEach(card => {
            const title = card.querySelector('.material-title').innerText.toLowerCase();
            const matchesTab = (window.currentMaterialStatus === 'all' || card.classList.contains(window.currentMaterialStatus));

            if (matchesTab && title.includes(query)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const dynamicEmptyState = document.getElementById('dynamic-empty-state');
        if (dynamicEmptyState) {
            const totalCards = document.querySelectorAll('.material-card').length;
            if (totalCards > 0 && visibleCount === 0) {
                dynamicEmptyState.style.display = 'flex';
            } else {
                dynamicEmptyState.style.display = 'none';
            }
        }
    };
</script>