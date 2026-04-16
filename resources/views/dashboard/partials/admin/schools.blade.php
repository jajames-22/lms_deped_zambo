<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">School Directory</h1>
            <p class="text-gray-500 text-sm">Manage registered institutions within the Zamboanga Division.</p>
        </div>

        <div class="flex-shrink-0 flex flex-wrap items-center gap-2 relative">
            <div class="relative group z-50">
                <button
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-gray-800 text-white font-bold rounded-xl shadow-sm hover:bg-gray-900 transition-all text-sm">
                    <i class="fas fa-file-alt"></i>
                    <span>Report</span>
                    <i class="fas fa-chevron-down text-xs ml-1 opacity-70"></i>
                </button>
                <div
                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right">
                    <a href="{{ route('schools.report', ['action' => 'print']) }}" target="_blank"
                        class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a] rounded-t-xl transition-colors"><i
                            class="fas fa-print w-5 text-gray-400"></i> Print List</a>
                    <a href="{{ route('schools.report', ['action' => 'download']) }}" target="_blank"
                        class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a] rounded-b-xl transition-colors"><i
                            class="fas fa-download w-5 text-gray-400"></i> Download PDF</a>
                </div>
            </div>

            <button onclick="loadPartial('{{ route('schools.create') }}', document.getElementById('nav-schools-btn'))"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all">
                <i class="fas fa-plus-circle"></i>
                <span>Add New School</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div
            class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between md:col-span-1">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Schools</p>
                <h3 class="text-2xl font-black text-gray-900" id="total-schools-count">{{ $schools->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-school text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center md:col-span-2">
            <div class="relative w-full">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="schoolSearchInput" placeholder="Search by name, ID, address, or district..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all text-sm text-gray-700">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="schoolsTable">
                <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-center w-16">Logo</th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                            title="Sort by Name">
                            School Details <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                            title="Sort by Level">
                            Level <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                            title="Sort by District">
                            District <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($schools as $school)
                        <tr class="hover:bg-gray-50/50 transition school-row">
                            <td class="px-4 py-2.5">
                                <div
                                    class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shadow-sm mx-auto">
                                    @if ($school->logo)
                                        <img src="{{ asset('storage/' . $school->logo) }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-image text-gray-300 text-xs"></i>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-gray-900 leading-tight school-name">
                                            {{ $school->name }}</p>
                                        <span
                                            class="bg-gray-100 text-gray-600 text-[10px] px-1.5 py-0.5 rounded font-mono border border-gray-200 school-id">
                                            {{ $school->school_id }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 max-w-[250px] truncate"
                                        title="{{ $school->address }}">
                                        <i class="fas fa-map-marker-alt text-[10px] mr-1"></i>
                                        {{ $school->address ?? 'No address provided' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-4 py-2.5">
                                @php
                                    $badgeStyles = [
                                        'elementary' => 'bg-green-50 text-green-700 border-green-200',
                                        'highschool' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'seniorhighschool' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'integrated' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    ];
                                    $displayNames = [
                                        'elementary' => 'Elementary',
                                        'highschool' => 'High School',
                                        'seniorhighschool' => 'Senior High School',
                                        'integrated' => 'Integrated',
                                    ];
                                    $style = $badgeStyles[$school->level] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                    $name = $displayNames[$school->level] ?? ucfirst($school->level);
                                @endphp
                                <span
                                    class="px-2 py-1 {{ $style }} text-[10px] font-bold rounded-md border uppercase tracking-tighter school-level">
                                    {{ $name }}
                                </span>
                            </td>

                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-gray-700 school-district">{{ $school->district->name ?? 'N/A' }}</span>
                                    <span
                                        class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $school->district->quadrant->name ?? '' }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        onclick="loadPartial('{{ route('schools.edit', $school->id) }}', document.getElementById('nav-schools-btn'))"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition shadow-none"
                                        title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button onclick="confirmDelete({{ $school->id }})"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shadow-none"
                                        title="Delete">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyStateRow">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-folder-open text-gray-200 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No schools found.</p>
                                    <p class="text-gray-400 text-xs">Start by adding a new institution to the division.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="pagination-wrapper"
            class="hidden flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span id="page-end-info"
                    class="font-bold text-gray-900">0</span> of <span id="page-total-info"
                    class="font-bold text-gray-900">0</span> results
            </div>
            <div class="flex items-center gap-1" id="pagination-controls">
            </div>
        </div>

    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-100 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/60"></div>
    <div
        class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
        <div
            class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-exclamation-triangle text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Delete School?</h3>
        <p class="text-gray-500 mb-8 text-sm">This action cannot be undone. Are you sure you want to permanently remove
            this institution?</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                Cancel
            </button>
            <button type="button" id="confirmDeleteBtn"
                class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-700 transition flex items-center justify-center">
                <span>Delete</span>
            </button>
        </div>
    </div>
</div>

<script>
    // --- DELETE LOGIC ---
    var deleteSchoolId = null;

    function confirmDelete(id) {
        deleteSchoolId = id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteSchoolId = null;
        document.getElementById('deleteModal').classList.add('hidden');
    }

    var confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        var newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', function () {
            if (!deleteSchoolId) return;

            var btnText = this.querySelector('span');
            var originalText = btnText.textContent;

            this.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/dashboard/schools/${deleteSchoolId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    closeDeleteModal();
                    loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
                })
                .catch(error => {
                    console.error("Deletion error:", error);
                    alert("An error occurred while trying to delete the school.");
                })
                .finally(() => {
                    this.disabled = false;
                    btnText.textContent = originalText;
                });
        });
    }

    // --- PAGINATION, SEARCH & SORT LOGIC ---
    var currentPage = 1;
    var pageSize = 20; // Set to 20 maximum items per page
    var allSchoolRows = [];
    var currentFilteredRows = [];

    // Initialize table data on load
    setTimeout(function () {
        allSchoolRows = Array.from(document.querySelectorAll('.school-row'));
        currentFilteredRows = [...allSchoolRows];
        applyPagination();
    }, 50);

    function applyPagination() {
        var tbody = document.querySelector('#schoolsTable tbody');
        var emptyState = document.getElementById('emptyStateRow');
        var paginationWrapper = document.getElementById('pagination-wrapper');

        // Hide all rows globally first
        allSchoolRows.forEach(row => row.style.display = 'none');

        if (currentFilteredRows.length === 0) {
            if (emptyState) emptyState.style.display = '';
            paginationWrapper.classList.add('hidden');
            paginationWrapper.classList.remove('flex');
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        paginationWrapper.classList.remove('hidden');
        paginationWrapper.classList.add('flex');

        var totalPages = Math.ceil(currentFilteredRows.length / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        var startIdx = (currentPage - 1) * pageSize;
        var endIdx = Math.min(startIdx + pageSize, currentFilteredRows.length);

        // Show and re-append current rows (to enforce sorted order automatically)
        for (var i = startIdx; i < endIdx; i++) {
            currentFilteredRows[i].style.display = '';
            tbody.appendChild(currentFilteredRows[i]);
        }

        // Update Text
        document.getElementById('page-start-info').innerText = startIdx + 1;
        document.getElementById('page-end-info').innerText = endIdx;
        document.getElementById('page-total-info').innerText = currentFilteredRows.length;

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        var controls = document.getElementById('pagination-controls');
        controls.innerHTML = '';

        var createBtn = function (text, page, disabled, active) {
            var btn = document.createElement('button');
            btn.innerHTML = text;
            btn.disabled = disabled;
            btn.className = `px-3 py-1 min-w-[32px] rounded-lg text-sm font-bold transition-all border ${active
                    ? 'bg-[#a52a2a] text-white border-[#a52a2a] shadow-sm'
                    : disabled
                        ? 'bg-transparent text-gray-300 border-transparent cursor-not-allowed'
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a] hover:border-[#a52a2a]/30 shadow-sm'
                }`;

            if (!disabled && !active) {
                btn.onclick = function () {
                    currentPage = page;
                    applyPagination();
                };
            }
            return btn;
        };

        controls.appendChild(createBtn('<i class="fas fa-chevron-left text-xs"></i>', currentPage - 1, currentPage === 1, false));

        var startP = Math.max(1, currentPage - 1);
        var endP = Math.min(totalPages, currentPage + 1);

        if (currentPage === 1) endP = Math.min(3, totalPages);
        if (currentPage === totalPages) startP = Math.max(1, totalPages - 2);

        if (startP > 1) {
            controls.appendChild(createBtn(1, 1, false, currentPage === 1));
            if (startP > 2) controls.appendChild(createBtn('...', null, true, false));
        }

        for (var i = startP; i <= endP; i++) {
            controls.appendChild(createBtn(i, i, false, i === currentPage));
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) controls.appendChild(createBtn('...', null, true, false));
            controls.appendChild(createBtn(totalPages, totalPages, false, currentPage === totalPages));
        }

        controls.appendChild(createBtn('<i class="fas fa-chevron-right text-xs"></i>', currentPage + 1, currentPage === totalPages, false));
    }

    // --- SEARCH LOGIC OVERRIDE ---
    var searchInput = document.getElementById('schoolSearchInput');
    if (searchInput) {
        var newSearchInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newSearchInput, searchInput);

        newSearchInput.addEventListener('input', function () {
            var filter = this.value.toLowerCase();

            currentFilteredRows = allSchoolRows.filter(function (row) {
                return row.textContent.toLowerCase().includes(filter);
            });

            var counterElement = document.getElementById('total-schools-count');
            if (counterElement) {
                counterElement.textContent = currentFilteredRows.length;
            }

            currentPage = 1; // Reset to page 1 on search
            applyPagination();
        });
    }

    // --- SORTING LOGIC OVERRIDE ---
    var sortableHeaders = document.querySelectorAll('.sortable-col');
    sortableHeaders.forEach(function (header) {
        var newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', function () {
            var colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
            var isAsc = newHeader.classList.contains('asc');

            // Reset UI
            document.querySelectorAll('.sortable-col i').forEach(function (icon) {
                icon.className = 'fas fa-sort ml-1 text-gray-300';
            });
            document.querySelectorAll('.sortable-col').forEach(function (h) {
                h.classList.remove('asc', 'desc');
            });

            // Toggle Sort
            var multiplier = 1;
            if (isAsc) {
                newHeader.classList.add('desc');
                newHeader.querySelector('i').className = 'fas fa-sort-down ml-1 text-[#a52a2a]';
                multiplier = -1;
            } else {
                newHeader.classList.add('asc');
                newHeader.querySelector('i').className = 'fas fa-sort-up ml-1 text-[#a52a2a]';
                multiplier = 1;
            }

            // Sort the filtered array
            currentFilteredRows.sort(function (a, b) {
                var aText = a.children[colIndex].textContent.trim().toLowerCase();
                var bText = b.children[colIndex].textContent.trim().toLowerCase();

                if (aText < bText) return -1 * multiplier;
                if (aText > bText) return 1 * multiplier;
                return 0;
            });

            currentPage = 1; // Reset to page 1 on sort
            applyPagination();
        });
    });
</script>