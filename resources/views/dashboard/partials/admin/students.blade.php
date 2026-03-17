<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Student Directory</h1>
            <p class="text-gray-500 text-sm">Manage enrolled learners across the Zamboanga Division.</p>
        </div>

        <button onclick="loadPartial('{{ route('students.create') }}', document.getElementById('nav-students-btn'))"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all">
            <i class="fas fa-plus-circle"></i>
            <span>Add New Student</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between md:col-span-1">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Students</p>
                <h3 class="text-2xl font-black text-gray-900" id="total-students-count">{{ $students->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-green-50 text-green-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-graduate text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center md:col-span-2">
            <div class="relative w-full">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="studentSearchInput" placeholder="Search by name, LRN/ID, email, or school..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all text-sm text-gray-700">
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button class="status-tab px-5 py-2 rounded-xl text-sm font-bold bg-[#a52a2a] text-white shadow-sm transition-all pointer-events-none" data-status="all">
            All Students
        </button>
        <button class="status-tab px-5 py-2 rounded-xl text-sm font-bold bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 transition-all" data-status="verified">
            Verified
        </button>
        <button class="status-tab px-5 py-2 rounded-xl text-sm font-bold bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 transition-all" data-status="pending">
            Pending
        </button>
        <button class="status-tab px-5 py-2 rounded-xl text-sm font-bold bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 transition-all" data-status="suspended">
            Suspended
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="studentsTable">
                <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-center w-16">Photo</th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Name">
                            Student Details <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Grade Level">
                            Grade Level <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by School">
                            Assigned School <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Status">
                            Status <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($students as $student)
                        <tr class="hover:bg-gray-50/50 transition student-row" data-status="{{ strtolower($student->status ?? 'pending') }}">
                            <td class="px-4 py-2.5">
                                <div class="w-10 h-10 mx-auto rounded-full bg-green-50 border border-green-100 overflow-hidden flex items-center justify-center shadow-sm text-green-600 font-bold text-xs">
                                    @if(isset($student->avatar) && $student->avatar)
                                        <img src="{{ asset('storage/' . $student->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-gray-900 leading-tight">
                                            {{ $student->first_name }} {{ $student->middle_name ? substr($student->middle_name, 0, 1) . '.' : '' }} {{ $student->last_name }} {{ $student->suffix }}
                                        </p>
                                        <span class="bg-gray-100 text-gray-700 text-[10px] px-1.5 py-0.5 rounded font-mono border border-gray-200">
                                            LRN: {{ $student->user_id }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate" title="{{ $student->email }}">
                                        <i class="fas fa-envelope text-[10px] mr-1"></i>
                                        {{ $student->email }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-4 py-2.5">
                                @if($student->grade_level)
                                    <span class="px-2 py-1 bg-purple-50 text-purple-700 border-purple-200 text-[10px] font-bold rounded-md border uppercase tracking-tighter">
                                        {{ $student->grade_level }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Not Assigned</span>
                                @endif
                            </td>

                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-700">
                                        {{ $student->school->name ?? 'Unassigned' }}
                                    </span>
                                    @if($student->school)
                                        <span class="text-[10px] text-gray-400 uppercase tracking-tighter">
                                            {{ $student->school->district->name ?? 'No District' }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-2.5">
                                @php
                                    $statusStyles = [
                                        'verified' => 'bg-green-50 text-green-700 border-green-200',
                                        'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'suspended'=> 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                    $currentStyle = $statusStyles[strtolower($student->status)] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                @endphp
                                <span class="px-2 py-1 {{ $currentStyle }} text-[10px] font-bold rounded-md border uppercase tracking-tighter">
                                    {{ ucfirst($student->status ?? 'pending') }}
                                </span>
                            </td>

                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="loadPartial('{{ route('students.edit', $student->id) }}', document.getElementById('nav-students-btn'))"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition shadow-none"
                                        title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button onclick="confirmDeleteStudent({{ $student->id }})" 
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shadow-none" title="Delete">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyStateRow">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-user-graduate text-gray-200 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No students found.</p>
                                    <p class="text-gray-400 text-xs">Start by adding a new learner to the system.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="pagination-wrapper" class="hidden flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span id="page-end-info" class="font-bold text-gray-900">0</span> of <span id="page-total-info" class="font-bold text-gray-900">0</span> results
            </div>
            <div class="flex items-center gap-1" id="pagination-controls">
                </div>
        </div>

    </div>
</div>

<div id="deleteStudentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-user-minus text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Remove Student?</h3>
        <p class="text-gray-500 mb-8 text-sm">This action cannot be undone. Are you sure you want to permanently remove this student's account?</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteStudentModal()" 
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                Cancel
            </button>
            <button type="button" id="confirmDeleteStudentBtn" 
                class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-700 transition flex items-center justify-center">
                <span>Remove</span>
            </button>
        </div>
    </div>
</div>

<script>
    // --- DELETE LOGIC ---
    var deleteStudentId = null;

    function confirmDeleteStudent(id) {
        deleteStudentId = id;
        document.getElementById('deleteStudentModal').classList.remove('hidden');
    }

    function closeDeleteStudentModal() {
        deleteStudentId = null;
        document.getElementById('deleteStudentModal').classList.add('hidden');
    }

    var confirmStudentBtn = document.getElementById('confirmDeleteStudentBtn');
    if (confirmStudentBtn) {
        var newConfirmStudentBtn = confirmStudentBtn.cloneNode(true);
        confirmStudentBtn.parentNode.replaceChild(newConfirmStudentBtn, confirmStudentBtn);

        newConfirmStudentBtn.addEventListener('click', function() {
            if (!deleteStudentId) return;

            var btnText = this.querySelector('span');
            var originalText = btnText.textContent;
            
            this.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/dashboard/students/${deleteStudentId}`, {
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
                closeDeleteStudentModal();
                loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'));
            })
            .catch(error => {
                console.error("Deletion error:", error);
                alert("An error occurred while trying to remove the student.");
            })
            .finally(() => {
                this.disabled = false;
                btnText.textContent = originalText;
            });
        });
    }

    // --- PAGINATION, FILTER, SEARCH & SORT LOGIC ---
    var currentPage = 1;
    var pageSize = 20; // 20 items per page limit
    var allStudentRows = [];
    var currentFilteredRows = [];
    var currentStatusFilter = 'all';
    var currentSearchFilter = '';

    // Initialize data on load
    setTimeout(function() {
        allStudentRows = Array.from(document.querySelectorAll('.student-row'));
        currentFilteredRows = [...allStudentRows];
        applyFilters(); 
    }, 50);

    // Master filter function handles Search + Tabs + Pagination array slicing
    function applyFilters() {
        currentFilteredRows = allStudentRows.filter(function(row) {
            var text = row.textContent.toLowerCase();
            var rowStatus = row.getAttribute('data-status');
            
            var matchesSearch = text.includes(currentSearchFilter);
            var matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);

            return matchesSearch && matchesStatus;
        });

        var counterElement = document.getElementById('total-students-count');
        if (counterElement) {
            counterElement.textContent = currentFilteredRows.length;
        }

        currentPage = 1; // Reset to page 1 whenever filters change
        applyPagination();
    }

    function applyPagination() {
        var tbody = document.querySelector('#studentsTable tbody');
        var emptyState = document.getElementById('emptyStateRow');
        var paginationWrapper = document.getElementById('pagination-wrapper');

        // Hide all rows initially
        allStudentRows.forEach(row => row.style.display = 'none');

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

        // Show and re-append rows for current page
        for (var i = startIdx; i < endIdx; i++) {
            currentFilteredRows[i].style.display = '';
            tbody.appendChild(currentFilteredRows[i]);
        }

        document.getElementById('page-start-info').innerText = startIdx + 1;
        document.getElementById('page-end-info').innerText = endIdx;
        document.getElementById('page-total-info').innerText = currentFilteredRows.length;

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        var controls = document.getElementById('pagination-controls');
        controls.innerHTML = '';

        var createBtn = function(text, page, disabled, active) {
            var btn = document.createElement('button');
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
                btn.onclick = function() {
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

    // 1. Search Bar Binding
    var studentSearchInput = document.getElementById('studentSearchInput');
    if (studentSearchInput) {
        var newSearchInput = studentSearchInput.cloneNode(true);
        studentSearchInput.parentNode.replaceChild(newSearchInput, studentSearchInput);

        newSearchInput.addEventListener('input', function() {
            currentSearchFilter = this.value.toLowerCase();
            applyFilters();
        });
    }

    // 2. Tab Clicking Binding
    var tabs = document.querySelectorAll('.status-tab');
    tabs.forEach(function(tab) {
        var newTab = tab.cloneNode(true);
        tab.parentNode.replaceChild(newTab, tab);

        newTab.addEventListener('click', function() {
            document.querySelectorAll('.status-tab').forEach(t => {
                t.classList.remove('bg-[#a52a2a]', 'text-white', 'shadow-sm', 'pointer-events-none');
                t.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-200');
            });
            
            this.classList.remove('bg-white', 'text-gray-600', 'border', 'border-gray-200');
            this.classList.add('bg-[#a52a2a]', 'text-white', 'shadow-sm', 'pointer-events-none');
            
            currentStatusFilter = this.getAttribute('data-status');
            applyFilters();
        });
    });

    // --- SORTING LOGIC ---
    var studentSortableHeaders = document.querySelectorAll('#studentsTable .sortable-col');
    studentSortableHeaders.forEach(function(header) {
        var newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', function() {
            var colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
            var isAsc = newHeader.classList.contains('asc');

            // Reset Icons
            document.querySelectorAll('#studentsTable .sortable-col i').forEach(function(icon) {
                icon.className = 'fas fa-sort ml-1 text-gray-300';
            });
            document.querySelectorAll('#studentsTable .sortable-col').forEach(function(h) {
                h.classList.remove('asc', 'desc');
            });

            // Set Direction
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

            // Sort filtered array directly
            currentFilteredRows.sort(function(a, b) {
                var aText = a.children[colIndex].textContent.trim().toLowerCase();
                var bText = b.children[colIndex].textContent.trim().toLowerCase();

                if (aText < bText) return -1 * multiplier;
                if (aText > bText) return 1 * multiplier;
                return 0;
            });

            currentPage = 1;
            applyPagination();
        });
    });
</script>