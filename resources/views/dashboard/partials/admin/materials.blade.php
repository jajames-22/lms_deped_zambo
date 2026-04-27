<div class="space-y-6 relative pb-20">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Learning Materials</h1>
            <p class="text-gray-500 text-sm">Create and manage modules, files, and lessons for your students.</p>
        </div>

        <button onclick="loadPartial('{{ route('dashboard.materials.create') }}', document.getElementById('nav-materials-btn'))"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all">
            <i class="fas fa-plus-circle"></i>
            <span>Create New Material</span>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center space-x-1 bg-gray-200/50 p-1 rounded-xl w-full md:w-fit overflow-x-auto no-scrollbar shrink-0">
    <button onclick="MaterialTableManager.filterStatus('all', this)"
        class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all bg-white text-[#a52a2a] shadow-sm whitespace-nowrap">All</button>
    
    <button onclick="MaterialTableManager.filterStatus('published', this)"
        class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">Published</button>
    
    <button onclick="MaterialTableManager.filterStatus('pending', this)"
        class="material-tab flex items-center gap-2 px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">
        <span>Pending</span>
        @php 
            // Safely calculate the number of pending materials from the loaded collection
            $pendingCount = collect($materials)->filter(function($m) {
                return strtolower($m->status ?? 'draft') === 'pending';
            })->count();
        @endphp
        
        @if($pendingCount > 0)
            <span class="flex items-center justify-center min-w-[20px] h-[20px] px-1.5 bg-red-500 text-white text-[11px] font-bold rounded-full shadow-sm animate-pulse">
                {{ $pendingCount }}
            </span>
        @endif
    </button>

    <button onclick="MaterialTableManager.filterStatus('draft', this)"
        class="material-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">Drafts</button>
</div>

        <div class="relative w-full max-w-md">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="materialSearchInput" placeholder="Search materials by title or description..."
                class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all shadow-sm">
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="materialsTable">
                <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-center w-16">Cover</th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Title">
                            Material Details <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Status">
                            Status <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Instructor">
                            Instructor <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Lessons">
                            Sections <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($materials as $material)
                        @php 
                            $statusStr = strtolower($material->status ?? 'draft'); 
                            
                            // Set Dynamic Styling based on Status
                            if ($statusStr === 'published') {
                                $statusColor = 'bg-green-50 text-green-700 border-green-200';
                                $indicatorColor = 'bg-green-500';
                                $statusLabel = 'Published';
                                $btnIcon = 'fa-desktop';
                                $btnTooltip = 'Manage Material';
                            } elseif ($statusStr === 'pending') {
                                $statusColor = 'bg-amber-50 text-amber-700 border-amber-200';
                                $indicatorColor = 'bg-amber-400';
                                $statusLabel = 'Pending Review';
                                $btnIcon = 'fa-search';
                                $btnTooltip = 'Review Submission';
                            } else {
                                $statusColor = 'bg-gray-100 text-gray-600 border-gray-200';
                                $indicatorColor = 'bg-gray-400';
                                $statusLabel = 'Draft Mode';
                                $btnIcon = 'fa-pen';
                                $btnTooltip = 'Resume Draft';
                            }
                        @endphp

                        <tr class="hover:bg-gray-50/50 transition material-row cursor-pointer" 
                        data-status="{{ $statusStr }}"
                        data-owner="{{ $material->instructor_id == auth()->id() ? 'mine' : 'other' }}"
                        onclick="loadPartial('{{ route('dashboard.materials.manage', $material->id) }}', document.getElementById('nav-materials-btn'))">

                            <td class="px-4 py-3">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shadow-sm mx-auto relative">
                                    <div class="absolute bottom-0 left-0 w-full h-1 {{ $indicatorColor }} z-10"></div>
                                    @if($material->thumbnail)
                                        <img src="{{ asset('storage/' . $material->thumbnail) }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-book-open text-gray-300 text-lg"></i>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <p class="text-sm font-bold text-gray-900 leading-tight material-title">{{ $material->title ?? 'Untitled Material' }}</p>
                                    <p class="text-xs text-gray-500 mt-1 max-w-[300px] truncate material-desc">
                                        {{ $material->description ?: 'No description provided.' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-md border uppercase tracking-tighter material-status-text {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-[10px]">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 material-instructor">{{ $material->instructor->first_name ?? 'N/A' }} {{ $material->instructor->last_name ?? '' }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-md font-bold border border-gray-200 material-count">
                                    {{ $material->lessons_count ?? 0 }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center" onclick="event.stopPropagation();">
                                <div class="flex items-center justify-center gap-2">
                                    @if($statusStr === 'published')
                                    <button onclick="loadPartial('{{ route('dashboard.materials.analytics', $material->id) }}', document.getElementById('nav-materials-btn'))"
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition shadow-none"
                                        title="View Analytics">
                                        <i class="fas fa-chart-line text-sm"></i>
                                    </button>

                                    <button onclick="window.location.href='{{ route('dashboard.materials.preview', $material->hashid) }}'"
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition shadow-none"
                                        title="{{ $btnTooltip }}">
                                        <i class="fas {{ $btnIcon }} text-sm"></i>
                                    </button>
                                    
                                    @elseif($statusStr === 'draft')
                                     <button onclick="loadPartial('{{ url('/dashboard/materials/'.$material->id.'/edit') }}')"
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition shadow-none"
                                        title="{{ $btnTooltip }}">
                                        <i class="fas {{ $btnIcon }} text-sm"></i>
                                    </button>
                                                                    
                                    @endif

                                    <button onclick="MaterialTableManager.confirmDelete({{ $material->id }}, '{{ route('dashboard.materials.destroy', $material->id) }}', this)" 
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shadow-none" title="Delete">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyStateRowInit">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-folder-open text-gray-200 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No materials found.</p>
                                    <p class="text-gray-400 text-xs mt-1">Start by creating a new learning module.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div id="dynamicEmptyState" class="hidden px-6 py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-search-minus text-gray-200 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">No materials found matching your filters.</p>
                </div>
            </div>
        </div>

        <div id="pagination-wrapper" class="hidden flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span id="page-end-info" class="font-bold text-gray-900">0</span> of <span id="page-total-info" class="font-bold text-gray-900">0</span> results
            </div>
            <div class="flex items-center gap-1" id="pagination-controls"></div>
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
    window.MaterialTableManager = {
        currentPage: 1,
        pageSize: 15,
        currentStatusFilter: 'all',
        allRows: [],
        filteredRows: [],

        init: function() {
            setTimeout(() => {
                this.allRows = Array.from(document.querySelectorAll('.material-row'));
                this.applyFilters();
                this.setupSearch();
                this.setupSorting();
            }, 50);
        },

        filterStatus: function(status, btnElement) {
            this.currentStatusFilter = status;

            // Update Tab UI
            document.querySelectorAll('.material-tab').forEach(tab => {
                tab.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm' );
                tab.classList.add('text-gray-500', 'hover:text-gray-700');
            });

            btnElement.classList.remove('text-gray-500', 'hover:text-gray-700');
            btnElement.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm');

            this.applyFilters();
        },

        setupSearch: function() {
            const searchInput = document.getElementById('materialSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    this.applyFilters();
                });
            }
        },

applyFilters: function() {
            const query = (document.getElementById('materialSearchInput')?.value || '').toLowerCase();
            
            this.filteredRows = this.allRows.filter(row => {
                const textContent = row.textContent.toLowerCase();
                const matchesSearch = textContent.includes(query);
                const matchesStatus = (this.currentStatusFilter === 'all' || row.dataset.status === this.currentStatusFilter);
                return matchesSearch && matchesStatus;
            });

            // ALWAYS group by owner by default (My materials first)
            this.filteredRows.sort((a, b) => {
                const ownerA = a.dataset.owner;
                const ownerB = b.dataset.owner;
                if (ownerA !== ownerB) return ownerA === 'mine' ? -1 : 1;
                return 0; // Preserve existing order within groups
            });

            this.currentPage = 1;
            this.renderPagination();
        },

        setupSorting: function() {
            const sortableHeaders = document.querySelectorAll('.sortable-col');
            sortableHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const colIndex = Array.from(header.parentNode.children).indexOf(header);
                    const isAsc = header.classList.contains('asc');

                    // Reset all headers
                    document.querySelectorAll('.sortable-col i').forEach(icon => {
                        icon.className = 'fas fa-sort ml-1 text-gray-300';
                    });
                    document.querySelectorAll('.sortable-col').forEach(h => h.classList.remove('asc', 'desc'));

                    // Toggle sort direction
                    let multiplier = 1;
                    if (isAsc) {
                        header.classList.add('desc');
                        header.querySelector('i').className = 'fas fa-sort-down ml-1 text-[#a52a2a]';
                        multiplier = -1;
                    } else {
                        header.classList.add('asc');
                        header.querySelector('i').className = 'fas fa-sort-up ml-1 text-[#a52a2a]';
                        multiplier = 1;
                    }

                    // Sort filtered array
                    this.filteredRows.sort((a, b) => {
                        // 1. Maintain Ownership grouping first
                        const ownerA = a.dataset.owner;
                        const ownerB = b.dataset.owner;
                        if (ownerA !== ownerB) return ownerA === 'mine' ? -1 : 1;

                        // 2. Then apply the column sorting within the groups
                        let aText = a.children[colIndex].textContent.trim().toLowerCase();
                        let bText = b.children[colIndex].textContent.trim().toLowerCase();

                        // Parse as numbers if sorting by the sections/counts column
                        if (colIndex === 4) {
                            aText = parseInt(aText) || 0;
                            bText = parseInt(bText) || 0;
                        }

                        if (aText < bText) return -1 * multiplier;
                        if (aText > bText) return 1 * multiplier;
                        return 0;
                    });

                    this.currentPage = 1;
                    this.renderPagination();
                });
            });
        },

        renderPagination: function() {
            const tbody = document.querySelector('#materialsTable tbody');
            const initEmptyState = document.getElementById('emptyStateRowInit');
            const dynamicEmptyState = document.getElementById('dynamicEmptyState');
            const paginationWrapper = document.getElementById('pagination-wrapper');

            // Clean up old dynamically inserted dividers
            document.querySelectorAll('.owner-divider').forEach(el => el.remove());

            // Hide all globally
            this.allRows.forEach(row => row.style.display = 'none');
            if (initEmptyState) initEmptyState.style.display = 'none';

            if (this.filteredRows.length === 0) {
                if (this.allRows.length === 0 && initEmptyState) {
                    initEmptyState.style.display = '';
                    dynamicEmptyState.classList.add('hidden');
                } else {
                    dynamicEmptyState.classList.remove('hidden');
                }
                paginationWrapper.classList.add('hidden');
                paginationWrapper.classList.remove('flex');
                return;
            }

            dynamicEmptyState.classList.add('hidden');
            paginationWrapper.classList.remove('hidden');
            paginationWrapper.classList.add('flex');

            const totalPages = Math.ceil(this.filteredRows.length / this.pageSize);
            if (this.currentPage > totalPages) this.currentPage = totalPages;
            if (this.currentPage < 1) this.currentPage = 1;

            const startIdx = (this.currentPage - 1) * this.pageSize;
            const endIdx = Math.min(startIdx + this.pageSize, this.filteredRows.length);

            let currentOwner = null;

            // Append sorted/filtered rows for current page
            for (let i = startIdx; i < endIdx; i++) {
                const row = this.filteredRows[i];
                row.style.display = '';

                // Insert a distinct visual divider row when transitioning between groups
                if (row.dataset.owner !== currentOwner) {
                    currentOwner = row.dataset.owner;
                    const divider = document.createElement('tr');
                    divider.className = 'owner-divider select-none pointer-events-none bg-gray-50/80';
                    divider.innerHTML = `
                        <td colspan="6" class="px-4 py-2 text-xs font-extrabold text-gray-500 uppercase tracking-widest border-y border-gray-100">
                            <div class="flex items-center gap-2 text-[#a52a2a]">
                                <i class="fas ${currentOwner === 'mine' ? 'fa-user' : 'fa-users'}"></i>
                                ${currentOwner === 'mine' ? 'My Materials' : 'Other Instructors'}
                            </div>
                        </td>
                    `;
                    tbody.appendChild(divider);
                }

                tbody.appendChild(row); // This moves the row into the correct visual order
            }

            // Update info text
            document.getElementById('page-start-info').innerText = startIdx + 1;
            document.getElementById('page-end-info').innerText = endIdx;
            document.getElementById('page-total-info').innerText = this.filteredRows.length;

            this.buildPaginationControls(totalPages);
        },
        buildPaginationControls: function(totalPages) {
            const controls = document.getElementById('pagination-controls');
            controls.innerHTML = '';

            const createBtn = (text, page, disabled, active) => {
                const btn = document.createElement('button');
                btn.innerHTML = text;
                btn.disabled = disabled;
                btn.className = `px-3 py-1 min-w-[32px] rounded-lg text-sm font-bold transition-all border ${
                    active 
                    ? 'bg-[#a52a2a] text-white border-[#a52a2a] shadow-sm' 
                    : disabled 
                        ? 'bg-transparent text-gray-300 border-transparent cursor-not-allowed' 
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a] hover:border-[#a52a2a]/30 shadow-sm'
                }`;
                
                if (!disabled && !active) {
                    btn.onclick = () => {
                        this.currentPage = page;
                        this.renderPagination();
                    };
                }
                return btn;
            };

            controls.appendChild(createBtn('<i class="fas fa-chevron-left text-xs"></i>', this.currentPage - 1, this.currentPage === 1, false));

            let startP = Math.max(1, this.currentPage - 1);
            let endP = Math.min(totalPages, this.currentPage + 1);

            if (this.currentPage === 1) endP = Math.min(3, totalPages);
            if (this.currentPage === totalPages) startP = Math.max(1, totalPages - 2);

            if (startP > 1) {
                controls.appendChild(createBtn(1, 1, false, this.currentPage === 1));
                if (startP > 2) controls.appendChild(createBtn('...', null, true, false));
            }

            for (let i = startP; i <= endP; i++) {
                controls.appendChild(createBtn(i, i, false, i === this.currentPage));
            }

            if (endP < totalPages) {
                if (endP < totalPages - 1) controls.appendChild(createBtn('...', null, true, false));
                controls.appendChild(createBtn(totalPages, totalPages, false, this.currentPage === totalPages));
            }

            controls.appendChild(createBtn('<i class="fas fa-chevron-right text-xs"></i>', this.currentPage + 1, this.currentPage === totalPages, false));
        },

        // Modal Handlers
        showModal: function(type, title, message, callback = null) {
            const modal = document.getElementById('status-modal');
            if (!modal) return alert(message);

            const iconContainer = document.getElementById('status-modal-icon');
            const titleEl = document.getElementById('status-modal-title');
            const msgEl = document.getElementById('status-modal-message');
            const btn = document.getElementById('status-modal-btn');
            const cancelBtn = document.getElementById('status-modal-cancel-btn');

            titleEl.innerText = title;
            msgEl.innerText = message;
            
            // Reset styles
            iconContainer.className = 'h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
            btn.className = 'w-full py-3 text-white font-bold rounded-xl transition active:scale-95 shadow-md';
            cancelBtn.classList.add('hidden');
            btn.innerText = 'OK';
            
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
            }

            modal.classList.remove('hidden');
            btn.onclick = () => {
                modal.classList.add('hidden');
                if (callback) callback();
            };
        },

        confirmDelete: function(id, url, btnElement) {
            this.showModal('confirm', 'Delete Material?', 'Are you sure you want to permanently delete this material? This action cannot be undone.', async () => {
                
                const originalHtml = btnElement.innerHTML;
                btnElement.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
                btnElement.disabled = true;

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
                        const row = btnElement.closest('tr');
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            // Update our tracking arrays
                            this.allRows = this.allRows.filter(r => r !== row);
                            this.applyFilters();
                        }, 300);
                    } else {
                        this.showModal('error', 'Delete Failed', 'The server returned an error while deleting.');
                        btnElement.innerHTML = originalHtml;
                        btnElement.disabled = false;
                    }
                } catch (e) {
                    this.showModal('error', 'Network Error', 'Could not delete the material. Please check your connection.');
                    btnElement.innerHTML = originalHtml;
                    btnElement.disabled = false;
                }
            });
        }
    };

    // Initialize Table Logic
    MaterialTableManager.init();
</script>