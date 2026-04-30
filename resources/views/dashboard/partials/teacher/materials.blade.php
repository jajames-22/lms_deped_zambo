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
        <div class="flex items-center space-x-1 bg-gray-200/50 p-1 rounded-xl w-full lg:w-fit overflow-x-auto no-scrollbar shrink-0">
            <button onclick="MaterialManager.filter('all', this)"
                class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all bg-white text-[#a52a2a] shadow-sm whitespace-nowrap">All</button>
            <button onclick="MaterialManager.filter('published', this)"
                class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">Published</button>
            <button onclick="MaterialManager.filter('pending', this)"
                class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">Pending</button>
            <button onclick="MaterialManager.filter('draft', this)"
                class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">Drafts</button>
        </div>

        <div class="relative w-full max-w-md shrink-0">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="material-search" onkeyup="MaterialManager.search()"
                placeholder="Search materials by title..."
                class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all shadow-sm">
        </div>
    </div>

    <div id="material-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-5 items-stretch relative">
        @forelse($materials as $material)
            @php 
                $statusStr = strtolower($material->status ?? 'draft'); 
                
                // Set Dynamic Styling based on Status
                if ($statusStr === 'published') {
                    $statusColor = 'bg-green-500';
                    $statusText = 'text-green-800';
                    $statusBg = 'bg-green-100/95';
                    $statusLabel = 'Published';
                    $btnIcon = 'fa-layer-group';
                    $btnText = 'Manage Material';
                } elseif ($statusStr === 'pending') {
                    $statusColor = 'bg-amber-500';
                    $statusText = 'text-amber-800';
                    $statusBg = 'bg-amber-100/95';
                    $statusLabel = 'Pending Review';
                    $btnIcon = 'fa-search';
                    $btnText = 'Review Submission';
                } else {
                    $statusColor = 'bg-gray-400';
                    $statusText = 'text-gray-700';
                    $statusBg = 'bg-gray-100/95';
                    $statusLabel = 'Draft Mode';
                    $btnIcon = 'fa-pen';
                    $btnText = 'Resume Draft';
                }
            @endphp

            <div id="material-card-{{ $material->id }}"
                onclick="loadPartial('{{ route('dashboard.materials.manage', $material->id) }}', document.getElementById('nav-materials-btn'))"
                class="material-card {{ $statusStr }} flex flex-col h-full bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 group overflow-hidden relative cursor-pointer">

                <div class="w-full h-36 bg-gray-50 relative border-b border-gray-100">
                    <div class="absolute top-0 left-0 w-full h-1 {{ $statusColor }} z-10"></div>

                    @if($material->thumbnail)
                        <img src="{{ asset('storage/' . $material->thumbnail) }}" alt="{{ $material->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full min-h-full flex items-center justify-center bg-gray-50 py-12">
                            <i class="fas fa-book-open text-4xl text-gray-200"></i>
                        </div>
                    @endif
                    
                    {{-- DUPLICATE AND DELETE BUTTONS --}}
                    <div class="absolute top-3 right-3 flex gap-1.5 opacity-0 group-hover:opacity-100 transition-all z-10">
                        <button
                            onclick="event.stopPropagation(); MaterialManager.duplicate('{{ $material->id }}', '{{ route('dashboard.materials.duplicate', $material->id) }}')"
                            class="h-7 w-7 rounded-full bg-white/90 backdrop-blur-sm text-gray-500 flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 shadow-sm transition-colors"
                            title="Duplicate Material">
                            <i class="fas fa-copy text-xs"></i>
                        </button>
                        
                        <button
                            onclick="event.stopPropagation(); MaterialManager.delete('{{ $material->id }}', '{{ route('dashboard.materials.destroy', $material->id) }}')"
                            class="h-7 w-7 rounded-full bg-white/90 backdrop-blur-sm text-gray-500 flex items-center justify-center hover:bg-red-50 hover:text-red-600 shadow-sm transition-colors"
                            title="Delete Material">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>

                    <div class="absolute top-3 left-3 flex items-center">
                        <span class="px-2 py-1 {{ $statusBg }} {{ $statusText }} backdrop-blur-sm text-[9px] font-bold rounded uppercase tracking-wider shadow-sm border border-white/40">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <h4 class="material-title text-base font-bold text-gray-900 mb-1.5 line-clamp-1" title="{{ $material->title }}">{{ $material->title ?? 'Untitled Material' }}</h4>

                    <p class="text-xs text-gray-500 mb-4 line-clamp-2 flex-1 leading-relaxed">
                        {{ $material->description ?: 'No description provided for this module.' }}
                    </p>

                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2.5 mb-4 text-[11px] text-gray-600 border border-gray-100">
                        <div class="flex items-center gap-1.5 truncate pr-2" title="Instructor">
                            <i class="fas fa-chalkboard-teacher text-gray-400"></i>
                            <span class="truncate font-medium text-gray-700">{{ $material->instructor->first_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 border-l border-gray-200 pl-3" title="Sections">
                            <i class="fas fa-layer-group text-gray-400"></i>
                            <span class="font-bold text-gray-700">{{ $material->lessons_count ?? 0 }}</span>
                        </div>
                    </div>

                    <button onclick="event.stopPropagation(); loadPartial('{{ route('dashboard.materials.manage', $material->id) }}', document.getElementById('nav-materials-btn'))"
                        class="w-full py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-50 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] transition-all shadow-sm flex items-center justify-center gap-1.5">
                        <i class="fas {{ $btnIcon }}"></i>
                        {{ $btnText }}
                    </button>
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
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        onclick="document.getElementById('status-modal').classList.add('hidden')"></div>

    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">

            <div id="status-modal-icon"
                class="h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"></div>

            <h3 id="status-modal-title" class="text-xl font-bold text-gray-900 mb-2">Title</h3>
            <p id="status-modal-message" class="text-gray-500 text-sm mb-6">Message goes here.</p>

            <div class="flex gap-3 mt-2">
                <button id="status-modal-cancel-btn" type="button"
                    class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95 hidden">
                    Cancel
                </button>
                <button id="status-modal-btn" type="button"
                    class="w-full py-3 text-white font-bold rounded-xl transition active:scale-95 shadow-md">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.MaterialManager = window.MaterialManager || {};
    
    MaterialManager.currentStatus = 'all';

    MaterialManager.showModal = function (type, title, message, callback = null) {
        const modal = document.getElementById('status-modal');
        if (!modal) return alert(message);

        const iconContainer = document.getElementById('status-modal-icon');
        const titleEl = document.getElementById('status-modal-title');
        const msgEl = document.getElementById('status-modal-message');
        const btn = document.getElementById('status-modal-btn');
        const cancelBtn = document.getElementById('status-modal-cancel-btn');

        titleEl.innerText = title;
        msgEl.innerText = message;
        iconContainer.className = 'h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
        btn.className = 'w-full py-3 text-white font-bold rounded-xl transition active:scale-95 shadow-md';
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

    MaterialManager.delete = function (id, url) {
        MaterialManager.showModal('confirm', 'Delete Material?', 'Are you sure you want to delete this material? This action cannot be undone.', async () => {

            const card = document.getElementById('material-card-' + id);
            if (!card) return;

            const btn = card.querySelector('button[title="Delete Material"]');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
            btn.disabled = true;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    card.style.transform = 'scale(0.95)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        MaterialManager.applyFilters(); 
                    }, 300);
                } else {
                    const data = await response.json().catch(() => ({}));
                    MaterialManager.showModal('error', 'Delete Failed', 'The server returned an error while deleting.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (e) {
                MaterialManager.showModal('error', 'Network Error', 'Could not delete the material. Please check your connection.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }); 
    }; 

    MaterialManager.duplicate = function (id, url) {
        MaterialManager.showModal('duplicate', 'Duplicate Material?', 'Are you sure you want to duplicate this material? A new draft copy will be created.', async () => {

            const card = document.getElementById('material-card-' + id);
            if (!card) return;

            const duplicateBtn = card.querySelector('button[title="Duplicate Material"]');
            if(!duplicateBtn) return;
            
            const originalHtml = duplicateBtn.innerHTML;
            duplicateBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
            duplicateBtn.disabled = true;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    // Reload the materials list to show the new duplicate
                    loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn'));
                } else {
                    MaterialManager.showModal('error', 'Duplication Failed', 'The server returned an error while duplicating.');
                    duplicateBtn.innerHTML = originalHtml;
                    duplicateBtn.disabled = false;
                }
            } catch (e) {
                MaterialManager.showModal('error', 'Network Error', 'Could not duplicate the material. Please check your connection.');
                duplicateBtn.innerHTML = originalHtml;
                duplicateBtn.disabled = false;
            }
        });
    };

    MaterialManager.filter = function(status, btnElement) {
        MaterialManager.currentStatus = status;

        document.querySelectorAll('.material-tab').forEach(tab => {
            tab.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm');
            tab.classList.add('text-gray-500', 'hover:text-gray-700');
        });

        btnElement.classList.remove('text-gray-500', 'hover:text-gray-700');
        btnElement.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm');

        MaterialManager.applyFilters();
    };

    MaterialManager.search = function() {
        MaterialManager.applyFilters();
    };

    MaterialManager.applyFilters = function() {
        const query = document.getElementById('material-search').value.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.material-card').forEach(card => {
            const title = card.querySelector('.material-title').innerText.toLowerCase();
            const matchesTab = (MaterialManager.currentStatus === 'all' || card.classList.contains(MaterialManager.currentStatus));

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