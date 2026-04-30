<div class="space-y-6 pb-20 animate-float-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">Evaluation Criteria</h1>
            <p class="text-gray-500 text-sm">Manage standard evaluation rubrics used for approving materials.</p>
        </div>

        <button
            onclick="loadPartial('{{ route('dashboard.criteria.create') }}', document.getElementById('nav-criteria-btn'))"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-[#a52a2a]/20 hover:bg-red-800 transition-all active:scale-95">
            <i class="fas fa-plus"></i>
            <span>Create New Criteria</span>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-end gap-4">
        <div class="relative w-full max-w-md">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="criteria-search" onkeyup="CriteriaManager.search()"
                placeholder="Search criteria by title..."
                class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all shadow-sm">
        </div>
    </div>

    <div id="criteria-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 items-stretch relative">
        @forelse($criterias ?? [] as $criteria)
            @php 
                $rubricData = is_string($criteria->rubric) ? json_decode($criteria->rubric, true) : ($criteria->rubric ?? []);
                // Handle both new {rubric: []} format and legacy format
                $categories = isset($rubricData['rubric']) ? $rubricData['rubric'] : $rubricData;
                
                $catCount = count($categories);
                $itemCount = 0;
                foreach($categories as $cat) {
                    if(isset($cat['items'])) $itemCount += count($cat['items']);
                }
            @endphp

            <div id="criteria-card-{{ $criteria->id }}"
                onclick="loadPartial('{{ route('dashboard.criteria.create', ['id' => $criteria->id]) }}', document.getElementById('nav-criteria-btn'))"
                class="criteria-card flex flex-col h-full bg-white rounded-2xl border border-gray-200 border-t-[#a52a2a] border-t-4 shadow-sm hover:shadow-lg transition-all duration-300 group overflow-hidden relative cursor-pointer">

                <div class="absolute top-3 right-3 flex gap-1.5 opacity-0 group-hover:opacity-100 transition-all z-10">
                    <button
                        onclick="event.stopPropagation(); CriteriaManager.duplicate('{{ $criteria->id }}', '{{ route('dashboard.criteria.duplicate', $criteria->id) }}')"
                        class="h-7 w-7 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-blue-100 hover:text-blue-600 shadow-sm transition-colors"
                        title="Duplicate Criteria">
                        <i class="fas fa-copy text-[11px]"></i>
                    </button>
                    
                    <button
                        onclick="event.stopPropagation(); CriteriaManager.delete('{{ $criteria->id }}', '{{ route('dashboard.criteria.destroy', $criteria->id) }}')"
                        class="h-7 w-7 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-red-100 hover:text-red-600 shadow-sm transition-colors"
                        title="Delete Criteria">
                        <i class="fas fa-trash-alt text-[11px]"></i>
                    </button>
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="bg-red-50 text-red-600 p-2 rounded-lg flex items-center justify-center">
                            <i class="fas fa-list-check text-lg"></i>
                        </div>
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded uppercase tracking-wide">
                            Passing: {{ $criteria->passing_rate ?? 75 }}%
                        </span>
                    </div>

                    <h4 class="criteria-title text-base font-bold text-gray-900 mb-1 line-clamp-1"
                        title="{{ $criteria->title }}">{{ $criteria->title }}</h4>

                    <p class="text-xs text-gray-500 mb-4 line-clamp-2 flex-1 leading-relaxed">
                        {{ $criteria->description ?? 'No description provided.' }}
                    </p>

                    {{-- STATS BLOCK --}}
                    <div class="bg-gray-50 rounded-xl p-3 mt-auto space-y-1.5 text-[11px] text-gray-600 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-500"><i class="fas fa-folder w-3.5 text-center"></i> Categories:</span>
                            <b class="text-gray-900 text-xs">{{ $catCount }}</b>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-500"><i class="fas fa-tasks w-3.5 text-center"></i> Total Items:</span>
                            <b class="text-gray-900 text-xs">{{ $itemCount }}</b>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="h-14 w-14 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-3">
                    <i class="fas fa-clipboard-list text-xl"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">No criteria found, try adding one!</h3>
            </div>
        @endforelse

        <div id="dynamic-empty-state" style="display: none;"
            class="col-span-full py-12 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-300">
            <div class="h-14 w-14 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-3">
                <i class="fas fa-search-minus text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">No matching criteria found.</h3>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div id="criteria-status-modal" class="fixed inset-0 z-[110] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        onclick="document.getElementById('criteria-status-modal').classList.add('hidden')"></div>

    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">

            <div id="criteria-modal-icon"
                class="h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"></div>

            <h3 id="criteria-modal-title" class="text-xl font-bold text-gray-900 mb-2">Title</h3>
            <p id="criteria-modal-message" class="text-gray-500 text-sm mb-6">Message goes here.</p>

            <div class="flex gap-3 mt-2">
                <button id="criteria-modal-cancel-btn" type="button"
                    class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95 hidden">
                    Cancel
                </button>
                <button id="criteria-modal-btn" type="button"
                    class="w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.CriteriaManager = window.CriteriaManager || {};

    CriteriaManager.search = function () {
        const query = document.getElementById('criteria-search').value.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.criteria-card').forEach(card => {
            const title = card.querySelector('.criteria-title').innerText.toLowerCase();
            if (title.includes(query)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const dynamicEmptyState = document.getElementById('dynamic-empty-state');
        if (dynamicEmptyState) {
            const totalCards = document.querySelectorAll('.criteria-card').length;
            if (totalCards > 0 && visibleCount === 0) {
                dynamicEmptyState.style.display = 'flex';
            } else {
                dynamicEmptyState.style.display = 'none';
            }
        }
    };

    CriteriaManager.showModal = function (type, title, message, callback = null) {
        const modal = document.getElementById('criteria-status-modal');
        if (!modal) return alert(message);

        const iconContainer = document.getElementById('criteria-modal-icon');
        const titleEl = document.getElementById('criteria-modal-title');
        const msgEl = document.getElementById('criteria-modal-message');
        const btn = document.getElementById('criteria-modal-btn');
        const cancelBtn = document.getElementById('criteria-modal-cancel-btn');

        titleEl.innerText = title;
        msgEl.innerText = message;
        iconContainer.className = 'h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
        btn.className = 'w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md';
        cancelBtn.classList.add('hidden');
        btn.innerText = 'OK';
        cancelBtn.onclick = null;
        btn.onclick = null;

        if (type === 'error') {
            iconContainer.classList.add('bg-red-50', 'text-red-500');
            iconContainer.innerHTML = '<i class="fas fa-times-circle"></i>';
            btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-600/20');
        } else if (type === 'confirm') {
            iconContainer.classList.add('bg-red-50', 'text-red-500');
            iconContainer.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-600/20');
            btn.innerText = 'Yes, Delete';
            cancelBtn.classList.remove('hidden');
            cancelBtn.onclick = () => modal.classList.add('hidden');
        } else if (type === 'duplicate') {
            iconContainer.classList.add('bg-blue-50', 'text-blue-500');
            iconContainer.innerHTML = '<i class="fas fa-copy"></i>';
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'shadow-blue-600/20');
            btn.innerText = 'Yes, Duplicate';
            cancelBtn.classList.remove('hidden');
            cancelBtn.onclick = () => modal.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        btn.onclick = () => {
            modal.classList.add('hidden');
            if (callback) callback();
        };
    };

    CriteriaManager.delete = function(id, url) {
        CriteriaManager.showModal('confirm', 'Delete Criteria?', 'Are you sure you want to delete this criteria? This action cannot be undone.', async () => {
            
            const card = document.getElementById('criteria-card-' + id);
            if(card) {
                card.style.opacity = '0.5';
                card.style.pointerEvents = 'none';
            }

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    }
                });
                if(response.ok) {
                    if(card) {
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();
                            CriteriaManager.search(); // Trigger search logic to show empty state if needed
                        }, 300);
                    }
                } else {
                    CriteriaManager.showModal('error', 'Delete Failed', 'Failed to delete the criteria. It might be in use.');
                    if(card) {
                        card.style.opacity = '1';
                        card.style.pointerEvents = 'auto';
                    }
                }
            } catch (e) {
                CriteriaManager.showModal('error', 'Network Error', 'Could not process the deletion due to a network error.');
                if(card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            }
        });
    };

    CriteriaManager.duplicate = function(id, url) {
        CriteriaManager.showModal('duplicate', 'Duplicate Criteria?', 'Are you sure you want to duplicate this evaluation rubric?', async () => {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    }
                });
                
                if(response.ok) {
                    loadPartial('{{ route('dashboard.criteria') }}', document.getElementById('nav-criteria-btn'));
                } else {
                    CriteriaManager.showModal('error', 'Duplication Failed', 'Failed to duplicate the criteria.');
                }
            } catch (e) {
                CriteriaManager.showModal('error', 'Network Error', 'Could not process the duplication due to a network error.');
            }
        });
    };
</script>