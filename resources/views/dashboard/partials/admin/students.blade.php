<div class="space-y-6 relative animate-float-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Student Directory</h1>
            <p class="text-gray-500 text-sm">Manage enrolled learners across the Zamboanga Division.</p>
        </div>

        <div class="flex-shrink-0 flex flex-wrap items-center gap-2">
            
            <button id="bulkDeleteBtn" onclick="confirmDeleteStudent()" class="hidden flex items-center justify-center gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-600 font-bold rounded-xl shadow-sm hover:bg-red-100 transition-all text-sm">
                <i class="fas fa-trash-alt"></i>
                <span id="bulkDeleteCount">Delete (0)</span>
            </button>

            <a href="{{ route('students.import.template') }}" download
                class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 border border-gray-200 text-gray-600 font-bold rounded-xl shadow-sm hover:bg-gray-200 transition-all text-sm"
                title="Download Excel/CSV Template">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">Template</span>
            </a>

            <input type="file" id="studentImportInput" class="hidden" accept=".xlsx,.xls,.csv" onchange="handleStudentImport(this)">
            
            <button id="importStudentBtn" onclick="document.getElementById('studentImportInput').click()"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl shadow-sm hover:bg-gray-50 transition-all text-sm">
                <i class="fas fa-file-import"></i>
                <span>Import</span>
            </button>

            <button onclick="loadPartial('{{ route('students.create') }}', document.getElementById('nav-students-btn'))"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all">
                <i class="fas fa-plus-circle"></i>
                <span>Add New Student</span>
            </button>
        </div>
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
                        <th class="px-4 py-3 text-center w-12">
                            <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-[#a52a2a] focus:ring-[#a52a2a] cursor-pointer">
                        </th>
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
                            <td class="px-4 py-2.5 text-center">
                                <input type="checkbox" value="{{ $student->id }}" class="student-checkbox rounded border-gray-300 text-[#a52a2a] focus:ring-[#a52a2a] cursor-pointer">
                            </td>
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
                                            LRN: {{ $student->lrn }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate" title="{{ $student->email }}">
                                        <i class="fas fa-envelope text-[10px] mr-1"></i>
                                        {{ $student->email ?? 'No email' }}
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
                                    $currentStyle = $statusStyles[strtolower($student->status ?? 'pending')] ?? 'bg-gray-50 text-gray-700 border-gray-200';
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
                            <td colspan="7" class="px-6 py-16 text-center">
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

<div id="deleteStudentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity" onclick="closeDeleteStudentModal()"></div>
    <div id="deleteStudentModalBox" class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-user-minus text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Remove Student?</h3>
        <p class="text-gray-500 mb-8 text-sm">This action cannot be undone. Are you sure you want to permanently remove <span id="deleteStudentCountText" class="font-bold">this student's account</span>?</p>
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

<div id="importMessageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity" onclick="closeImportModal()"></div>
    <div id="importMessageModalBox" class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">
        
        <div id="importModalIconBox" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner bg-gray-50 text-gray-500">
            <i id="importModalIcon" class="fas fa-info-circle text-4xl"></i>
        </div>
        
        <h3 id="importModalTitle" class="text-2xl font-black text-gray-900 mb-2">Notice</h3>
        <p id="importModalMessage" class="text-gray-500 mb-8 text-sm">Message content goes here.</p>
        
        <button type="button" onclick="closeImportModal()" id="importModalBtn" class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-md hover:bg-red-800 transition">
            Okay
        </button>
    </div>
</div>

<script>
    // --- MULTI-SELECT CHECKBOX LOGIC ---
    // FIXED: Changed const to var to prevent SPA redeclaration crashes
    var selectAllCheckbox = document.getElementById('selectAllCheckbox');
    var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    var bulkDeleteCount = document.getElementById('bulkDeleteCount');

    function updateBulkActionUI() {
        var checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        var count = checkedBoxes.length;

        if (count > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteCount.innerText = `Delete (${count})`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
        }

        var visibleRows = Array.from(document.querySelectorAll('.student-row')).filter(row => row.style.display !== 'none');
        if(selectAllCheckbox) {
            selectAllCheckbox.checked = (count > 0 && count === visibleRows.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function(e) {
            var isChecked = e.target.checked;
            document.querySelectorAll('.student-row').forEach(row => {
                if (row.style.display !== 'none') { 
                    row.querySelector('.student-checkbox').checked = isChecked;
                }
            });
            updateBulkActionUI();
        });
    }

    document.getElementById('studentsTable').addEventListener('change', function(e) {
        if (e.target.classList.contains('student-checkbox')) {
            updateBulkActionUI();
        }
    });

    // --- ANIMATED DELETE LOGIC ---
    // FIXED: Changed let to var
    var deleteStudentIds = []; 

    function confirmDeleteStudent(id = null) {
        if (id) {
            deleteStudentIds = [id]; 
        } else {
            var checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            deleteStudentIds = Array.from(checkedBoxes).map(cb => cb.value);
        }

        if (deleteStudentIds.length === 0) return;

        document.getElementById('deleteStudentCountText').innerText = deleteStudentIds.length > 1 
            ? `these ${deleteStudentIds.length} students' accounts` 
            : "this student's account";

        var modal = document.getElementById('deleteStudentModal');
        var box = document.getElementById('deleteStudentModalBox');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeDeleteStudentModal() {
        var modal = document.getElementById('deleteStudentModal');
        var box = document.getElementById('deleteStudentModalBox');
        
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            deleteStudentIds = []; 
        }, 300);
    }

    // FIXED: Changed const to var
    var confirmStudentBtn = document.getElementById('confirmDeleteStudentBtn');
    if (confirmStudentBtn) {
        var newConfirmStudentBtn = confirmStudentBtn.cloneNode(true);
        confirmStudentBtn.parentNode.replaceChild(newConfirmStudentBtn, confirmStudentBtn);

        newConfirmStudentBtn.addEventListener('click', function() {
            if (deleteStudentIds.length === 0) return;

            var btnText = this.querySelector('span');
            var originalText = btnText.textContent;
            
            this.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/dashboard/students/bulk-delete`, {
                method: 'POST', 
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    ids: deleteStudentIds,
                    _method: 'DELETE' 
                })
            })
            .then(async response => {
                var data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Server error occurred.'); 
                }
                return data;
            })
            .then(data => {
                closeDeleteStudentModal();
                showImportModal('Deleted Successfully!', data.message, 'success', () => {
                    // FIXED: Used double quotes outside to prevent Laravel single-quote conflict
                    loadPartial("{{ route('dashboard.students') }}", document.getElementById('nav-students-btn'));
                });
            })
            .catch(error => {
                console.error("Deletion error:", error);
                closeDeleteStudentModal();
                
                setTimeout(() => {
                    showImportModal('Deletion Blocked', error.message, 'error');
                }, 350); 
            })
            .finally(() => {
                this.disabled = false;
                btnText.textContent = originalText;
            });
        });
    }

    // --- PAGINATION, FILTER, SEARCH & SORT LOGIC ---
    var currentPage = 1;
    var pageSize = 20; 
    var allStudentRows = [];
    var currentFilteredRows = [];
    var currentStatusFilter = 'all';
    var currentSearchFilter = '';

    setTimeout(function() {
        allStudentRows = Array.from(document.querySelectorAll('.student-row'));
        currentFilteredRows = [...allStudentRows];
        applyFilters(); 
    }, 50);

    function applyFilters() {
        currentFilteredRows = allStudentRows.filter(function(row) {
            var text = row.textContent.toLowerCase();
            var rowStatus = row.getAttribute('data-status');
            var matchesSearch = text.includes(currentSearchFilter);
            var matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
            return matchesSearch && matchesStatus;
        });

        var counterElement = document.getElementById('total-students-count');
        if (counterElement) counterElement.textContent = currentFilteredRows.length;

        currentPage = 1; 
        applyPagination();
        
        if(selectAllCheckbox) selectAllCheckbox.checked = false;
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
        updateBulkActionUI();
    }

    function applyPagination() {
        var tbody = document.querySelector('#studentsTable tbody');
        var emptyState = document.getElementById('emptyStateRow');
        var paginationWrapper = document.getElementById('pagination-wrapper');

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
                    if(selectAllCheckbox) selectAllCheckbox.checked = false;
                    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
                    updateBulkActionUI();
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

    var studentSearchInput = document.getElementById('studentSearchInput');
    if (studentSearchInput) {
        var newSearchInput = studentSearchInput.cloneNode(true);
        studentSearchInput.parentNode.replaceChild(newSearchInput, studentSearchInput);

        newSearchInput.addEventListener('input', function() {
            currentSearchFilter = this.value.toLowerCase();
            applyFilters();
        });
    }

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

    var studentSortableHeaders = document.querySelectorAll('#studentsTable .sortable-col');
    studentSortableHeaders.forEach(function(header) {
        var newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', function() {
            var colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
            var isAsc = newHeader.classList.contains('asc');

            document.querySelectorAll('#studentsTable .sortable-col i').forEach(function(icon) {
                icon.className = 'fas fa-sort ml-1 text-gray-300';
            });
            document.querySelectorAll('#studentsTable .sortable-col').forEach(function(h) {
                h.classList.remove('asc', 'desc');
            });

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

            currentFilteredRows.sort(function(a, b) {
                var aText = a.children[colIndex].textContent.trim().toLowerCase();
                var bText = b.children[colIndex].textContent.trim().toLowerCase();

                if (aText < bText) return -1 * multiplier;
                if (aText > bText) return 1 * multiplier;
                return 0;
            });

            currentPage = 1;
            applyPagination();
            
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
            updateBulkActionUI();
        });
    });

    // --- IMPORT MODAL LOGIC ---
    // FIXED: Changed let to var
    var importSuccessCallback = null;

    function showImportModal(title, message, type, callback = null) {
        importSuccessCallback = callback;
        
        var modal = document.getElementById('importMessageModal');
        var box = document.getElementById('importMessageModalBox');
        var iconBox = document.getElementById('importModalIconBox');
        var icon = document.getElementById('importModalIcon');

        document.getElementById('importModalTitle').innerText = title;
        document.getElementById('importModalMessage').innerText = message;

        if (type === 'success') {
            iconBox.className = 'w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner';
            icon.className = 'fas fa-check text-4xl';
        } else {
            iconBox.className = 'w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner';
            icon.className = 'fas fa-exclamation-triangle text-4xl';
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeImportModal() {
        var modal = document.getElementById('importMessageModal');
        var box = document.getElementById('importMessageModalBox');
        
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            if (importSuccessCallback) {
                importSuccessCallback(); 
                importSuccessCallback = null;
            }
        }, 300); 
    }

    function handleStudentImport(input) {
        if (!input.files || !input.files[0]) return;

        var formData = new FormData();
        formData.append('file', input.files[0]);

        var importBtn = document.getElementById('importStudentBtn');
        var originalContent = importBtn.innerHTML;
        
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Importing...</span>';

        fetch('{{ route("students.import") }}', { 
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            var data = await response.json();
            if (response.ok) {
                showImportModal('Import Successful!', data.message || 'Students imported successfully!', 'success', () => {
                    // FIXED: Used double quotes outside
                    loadPartial("{{ route('dashboard.students') }}", document.getElementById('nav-students-btn'));
                });
            } else {
                throw data;
            }
        })
        .catch(error => {
            console.error("Import error:", error);
            showImportModal('Import Failed', error.message || "An error occurred during import. Please check your file format.", 'error');
        })
        .finally(() => {
            importBtn.disabled = false;
            importBtn.innerHTML = originalContent;
            input.value = '';
        });
    }
</script>