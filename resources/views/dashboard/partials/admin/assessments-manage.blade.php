<div class="space-y-6 pb-20 max-w-6xl mx-auto relative">
    @php 
        $isLive = ($assessment->status === 'published'); 
    @endphp

    <div class="flex items-center justify-between">
        <button onclick="loadPartial('{{ url('/dashboard/assessment') }}', document.getElementById('nav-assessment-btn'))"
            class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Assessments
        </button>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 {{ $isLive ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }} text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    @if($isLive)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    @else
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    @endif
                </span>
                {{ $isLive ? 'Published' : 'Draft Mode' }}
            </span>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#a52a2a]/5 to-transparent rounded-bl-full pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest">
                        Grade {{ $assessment->year_level }}
                    </span>
                    <span class="text-gray-400 text-sm font-medium">
                        Created {{ $assessment->created_at->format('M d, Y') }}
                    </span>
                </div>
                
                <h1 class="text-3xl font-black text-gray-900 mb-4">{{ $assessment->title }}</h1>
                <p class="text-gray-600 max-w-3xl leading-relaxed">
                    {{ $assessment->description ?: 'No description provided for this assessment.' }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0 md:w-48">
                <button onclick="loadPartial('{{ route('dashboard.assessments.builder', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                    class="w-full py-3 px-4 bg-white border-2 border-[#a52a2a] text-[#a52a2a] font-bold rounded-xl hover:bg-[#a52a2a] hover:text-white transition-all flex items-center justify-center gap-2 group shadow-sm">
                    <i class="fas fa-tools group-hover:rotate-12 transition-transform"></i>
                    Edit Content
                </button>

                @if($isLive)
                    <button class="w-full py-3 px-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#a52a2a]/20">
                        <i class="fas fa-chart-pie"></i>
                        View Analytics
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden border border-gray-700">
            <i class="fas fa-key absolute -right-4 -bottom-4 text-8xl text-white/5 rotate-12"></i>
            <h3 class="text-gray-400 font-bold uppercase tracking-widest text-xs mb-2">Student Access Key</h3>
            <p class="text-sm text-gray-300 mb-6 max-w-md">Share this code with your students. They will use it to enter the exam lobby and start the assessment.</p>
            <div class="flex items-center gap-4 bg-black/30 p-2 rounded-2xl w-fit border border-white/10 backdrop-blur-sm">
                <span id="access-key-text" class="text-3xl font-mono font-bold tracking-widest pl-4 pr-2 text-white">
                    {{ $assessment->access_key }}
                </span>
                <button onclick="copyAccessKey('{{ $assessment->access_key }}', this)"
                    class="bg-white/10 hover:bg-white/20 text-white p-3 rounded-xl transition-all flex items-center justify-center group" title="Copy to clipboard">
                    <i class="fas fa-copy group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col justify-center">
            <h3 class="text-gray-500 font-bold uppercase tracking-widest text-xs mb-6">Assessment Structure</h3>
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900">{{ $assessment->categories_count ?? 0 }}</p>
                        <p class="text-xs font-bold text-gray-500 uppercase">Sections</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900">{{ $assessment->questions_count ?? 0 }}</p>
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Questions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-8 relative">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Student Access Management</h3>
                <p class="text-sm text-gray-500 mt-1">Only students with LRNs listed below will be able to take this exam.</p>
            </div>
            
            <form id="add-lrn-form" class="flex flex-wrap gap-2 w-full md:w-auto">
                <input type="text" id="student-lrn-input" name="lrn" placeholder="Enter Student LRN" required pattern="[0-9]+" title="Please enter numbers only"
                    class="w-full md:w-64 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm font-medium">
                
                <button type="button" onclick="submitLrn(this)" 
                    class="px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Add
                </button>

                <input type="file" id="lrn-file-input" class="hidden" accept=".csv, .xlsx, .xls" onchange="importLrnList(this)">
                <button type="button" onclick="document.getElementById('lrn-file-input').click()" 
                    class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-file-import"></i> Import List
                </button>
            </form>
        </div>

        <div class="flex items-center justify-between mb-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="search-student" onkeyup="filterStudents()" placeholder="Search LRN or Name..." 
                    class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
            </div>

            <button type="button" id="bulk-delete-btn" onclick="window.openDeleteModal('bulk')" 
                class="hidden px-4 py-2 bg-red-50 text-red-600 border border-red-200 text-sm font-bold rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm items-center gap-2 whitespace-nowrap">
                <i class="fas fa-trash-alt"></i> Delete Selected (<span id="selected-count">0</span>)
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-left text-sm text-gray-600" id="students-table">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 w-12 text-center">
                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" class="w-4 h-4 text-[#a52a2a] bg-white border-gray-300 rounded focus:ring-[#a52a2a]">
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:text-gray-900 select-none group" onclick="sortTable(1, 'numeric')">
                            LRN <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:text-gray-900 select-none group" onclick="sortTable(2, 'alpha')">
                            Student Name <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i>
                        </th>
                        <th class="px-6 py-4">School</th>
                        <th class="px-6 py-4 cursor-pointer hover:text-gray-900 select-none group" onclick="sortTable(4, 'alpha')">
                            Status <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i>
                        </th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="students-tbody">
                @forelse($whitelistedStudents ?? [] as $access)
                    <tr class="student-row hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" class="lrn-checkbox w-4 h-4 text-[#a52a2a] bg-white border-gray-300 rounded focus:ring-[#a52a2a]" value="{{ $access->id }}" onclick="updateBulkDeleteBtn()">
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-gray-900 lrn-cell">{{ $access->lrn }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-800 name-cell" data-value="{{ $access->student ? ($access->student->first_name . ' ' . $access->student->last_name) : 'ZZZ' }}">
                            @if($access->student)
                                {{ $access->student->first_name ?? '' }} {{ $access->student->last_name ?? '' }}
                            @else
                                <span class="italic text-gray-400 text-xs">No account registered</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            @if($access->student && $access->student->school)
                                {{ $access->student->school->name ?? '-' }}
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 status-cell" data-value="{{ $access->status }}">
                            @if($access->status === 'taking_exam')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-100 text-blue-700 text-xs font-bold">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span> Taking Exam
                                </span>
                            @elseif($access->status === 'finished')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-green-100 text-green-700 text-xs font-bold">
                                    <i class="fas fa-check text-[10px]"></i> Finished
                                </span>
                            @elseif($access->status === 'lobby')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-100 text-amber-700 text-xs font-bold">
                                    <i class="fas fa-clock text-[10px]"></i> In Lobby
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-gray-600 text-xs font-bold">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Offline
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" onclick="window.openDeleteModal('{{ $access->id }}')" class="text-gray-400 hover:text-red-500 transition" title="Remove Access">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr id="empty-state-row">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-id-card text-3xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium">No students added yet.</p>
                                <p class="text-xs mt-1">Enter an LRN above to grant access.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-red-50 rounded-3xl p-6 border border-red-100 mt-8">
        <h3 class="text-red-800 font-bold mb-2">Danger Zone</h3>
        <p class="text-sm text-red-600 mb-4">Deleting this assessment will permanently remove it and all associated student submissions. This action cannot be undone.</p>
        <button onclick="window.deleteAssessmentFromList('{{ $assessment->id }}', '{{ route('dashboard.assessments.destroy', $assessment->id) }}')" 
            class="px-6 py-2.5 bg-white border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
            Delete Assessment
        </button>
    </div>

    <div id="student-delete-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="window.closeDeleteModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
                <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Revoke Access?</h3>
                <p id="delete-modal-text" class="text-gray-500 text-sm mb-6">Are you sure you want to remove access for the selected student(s)? They will not be able to take the exam.</p>
                <div class="flex gap-3 mt-2">
                    <button type="button" onclick="window.closeDeleteModal()" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">
                        Cancel
                    </button>
                    <button type="button" id="confirm-delete-btn" onclick="window.executeDelete()" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition active:scale-95 shadow-md flex items-center justify-center gap-2">
                        <i class="fas fa-trash-alt"></i> Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="custom-snackbar" class="fixed bg-[#a52a2a] bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white">
        <div id="snackbar-icon" class="text-xl"></div>
        <span id="snackbar-message"></span>
        <button onclick="closeSnackbar()" class="ml-4 text-white/70 hover:text-white transition"><i class="fas fa-times"></i></button>
    </div>
</div>

<script>
    // --- Snackbar Logic ---
    let snackbarTimeout;
    
    window.showSnackbar = function(message, type = 'error') {
        const snackbar = document.getElementById('custom-snackbar');
        const msgEl = document.getElementById('snackbar-message');
        const iconEl = document.getElementById('snackbar-icon');

        // Reset classes
        snackbar.className = "fixed bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white";
        
        if (type === 'error') {
            snackbar.classList.add('bg-[#a52a2a]');
            iconEl.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
        } else if (type === 'success') {
            snackbar.classList.add('bg-green-600');
            iconEl.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
            snackbar.classList.add('bg-gray-800');
            iconEl.innerHTML = '<i class="fas fa-info-circle"></i>';
        }

        msgEl.innerText = message;

        // Animate In
        setTimeout(() => {
            snackbar.classList.remove('translate-y-24', 'opacity-0');
        }, 10);

        // Auto close after 4 seconds
        clearTimeout(snackbarTimeout);
        snackbarTimeout = setTimeout(closeSnackbar, 4000);
    };

    window.closeSnackbar = function() {
        const snackbar = document.getElementById('custom-snackbar');
        snackbar.classList.add('translate-y-24', 'opacity-0');
    };

    // --- Table Sorting Logic ---
    let sortDirections = { 1: true, 2: true, 4: true }; // true = ascending

    window.sortTable = function(columnIndex, type) {
        const table = document.getElementById("students-table");
        const tbody = document.getElementById("students-tbody");
        const rows = Array.from(tbody.querySelectorAll("tr.student-row"));
        
        if (rows.length === 0) return;

        const isAscending = sortDirections[columnIndex];
        sortDirections[columnIndex] = !isAscending; // Flip direction for next click

        rows.sort((a, b) => {
            let valA, valB;

            if (columnIndex === 2 || columnIndex === 4) {
                // Read from data-value attribute for names and statuses to ignore HTML tags
                valA = a.cells[columnIndex].getAttribute('data-value') || a.cells[columnIndex].innerText.trim();
                valB = b.cells[columnIndex].getAttribute('data-value') || b.cells[columnIndex].innerText.trim();
            } else {
                valA = a.cells[columnIndex].innerText.trim();
                valB = b.cells[columnIndex].innerText.trim();
            }

            if (type === 'numeric') {
                return isAscending ? valA.localeCompare(valB, undefined, {numeric: true}) : valB.localeCompare(valA, undefined, {numeric: true});
            } else {
                return isAscending ? valA.localeCompare(valB) : valB.localeCompare(valA);
            }
        });

        // Re-append rows in new order
        rows.forEach(row => tbody.appendChild(row));
        
        // Update header icons purely for visual feedback
        const headers = table.querySelectorAll('th i.fa-sort, th i.fa-sort-up, th i.fa-sort-down');
        headers.forEach(icon => icon.className = 'fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500'); // reset all
        
        const clickedHeaderIcon = table.querySelectorAll('th')[columnIndex].querySelector('i');
        if (clickedHeaderIcon) {
            clickedHeaderIcon.className = isAscending ? 'fas fa-sort-up ml-1 text-[#a52a2a]' : 'fas fa-sort-down ml-1 text-[#a52a2a]';
        }
    };


    // --- Access Key Logic ---
    function copyAccessKey(key, btnElement) {
        navigator.clipboard.writeText(key).then(() => {
            const icon = btnElement.querySelector('i');
            icon.className = 'fas fa-check text-green-400';
            btnElement.classList.add('bg-green-500/20');
            setTimeout(() => {
                icon.className = 'fas fa-copy group-hover:scale-110 transition-transform';
                btnElement.classList.remove('bg-green-500/20');
            }, 2000);
        });
    }

    // --- Add LRN logic ---
    async function submitLrn(btn) {
        const lrnInput = document.getElementById('student-lrn-input');
        const lrnValue = lrnInput.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        if (!lrnValue || isNaN(lrnValue)) {
            showSnackbar('Please enter a valid numeric LRN.', 'error');
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.assessments.access.add", $assessment->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ lrn: lrnValue })
            });
            const data = await response.json();

            if (response.ok && data.success) {
                lrnInput.value = ''; 
                showSnackbar('Student added successfully!', 'success');
                setTimeout(() => {
                    loadPartial('{{ route("dashboard.assessments.manage", $assessment->id) }}', document.getElementById('nav-assessment-btn'));
                }, 500); // Slight delay so they see the success message before reload
            } else if (response.status === 422) {
                showSnackbar("Validation Error: " + data.errors.lrn[0], 'error');
            } else {
                showSnackbar(data.message || 'Failed to add LRN.', 'error');
            }
        } catch (error) {
            showSnackbar('A network error occurred.', 'error');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    // --- Search Logic ---
    function filterStudents() {
        const query = document.getElementById('search-student').value.toLowerCase();
        const rows = document.querySelectorAll('.student-row');

        rows.forEach(row => {
            const lrn = row.querySelector('.lrn-cell').innerText.toLowerCase();
            const name = row.querySelector('.name-cell').innerText.toLowerCase();
            
            if (lrn.includes(query) || name.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // --- Checkbox & Bulk Logic ---
    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.lrn-checkbox');
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = selectAllCheckbox.checked;
            }
        });
        updateBulkDeleteBtn();
    }

    function updateBulkDeleteBtn() {
        const selectedCount = document.querySelectorAll('.lrn-checkbox:checked').length;
        const bulkBtn = document.getElementById('bulk-delete-btn');
        const countSpan = document.getElementById('selected-count');
        
        if (selectedCount > 0) {
            bulkBtn.classList.remove('hidden');
            bulkBtn.classList.add('flex');
            countSpan.innerText = selectedCount;
        } else {
            bulkBtn.classList.add('hidden');
            bulkBtn.classList.remove('flex');
            document.getElementById('select-all').checked = false;
        }
    }

    // --- Modal & Delete Logic ---
    var targetsToDelete = [];

    window.openDeleteModal = function(id) {
        if (id === 'bulk') {
            const checked = Array.from(document.querySelectorAll('.lrn-checkbox:checked'));
            targetsToDelete = checked.map(cb => cb.value);
            
            if (targetsToDelete.length === 0) {
                showSnackbar("Please select at least one student to delete.", 'error');
                return;
            }
            document.getElementById('delete-modal-text').innerText = `Are you sure you want to remove access for ${targetsToDelete.length} selected student(s)?`;
        } else {
            targetsToDelete = [id];
            document.getElementById('delete-modal-text').innerText = "Are you sure you want to remove access for this student?";
        }
        
        document.getElementById('student-delete-modal').classList.remove('hidden');
    };

    window.closeDeleteModal = function() {
        document.getElementById('student-delete-modal').classList.add('hidden');
        targetsToDelete = []; 
    };

    window.executeDelete = async function() {
        if (!targetsToDelete || targetsToDelete.length === 0) return;

        const btn = document.getElementById('confirm-delete-btn');
        const originalHtml = btn.innerHTML;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        btn.disabled = true;

        try {
            let successCount = 0;

            for (const id of targetsToDelete) {
                let deleteUrl = '{{ route("dashboard.assessments.access.remove", ":id") }}';
                deleteUrl = deleteUrl.replace(':id', id);

                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (response.ok && data.success) {
                    successCount++;
                } else {
                    showSnackbar("Error: " + (data.message || "Unknown error"), 'error');
                }
            }

            window.closeDeleteModal();
            
            if (successCount > 0) {
                showSnackbar(`Successfully removed ${successCount} student(s).`, 'success');
                setTimeout(() => {
                    loadPartial('{{ route("dashboard.assessments.manage", $assessment->id) }}', document.getElementById('nav-assessment-btn'));
                }, 800);
            }
            
        } catch (error) {
            showSnackbar('A network error occurred.', 'error');
            console.error(error);
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    };

    window.importLrnList = async function(input) {
        if (!input.files || input.files.length === 0) return;

        const file = input.files[0];
        const formData = new FormData();
        formData.append('file', file);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        showSnackbar('Importing LRNs...', 'info');

        try {
            const response = await fetch('{{ route("dashboard.assessments.access.import", $assessment->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSnackbar(data.message, 'success');
                setTimeout(() => {
                    loadPartial('{{ route("dashboard.assessments.manage", $assessment->id) }}', document.getElementById('nav-assessment-btn'));
                }, 1000);
            } else {
                showSnackbar(data.message || 'Import failed.', 'error');
            }
        } catch (error) {
            showSnackbar('A network error occurred during import.', 'error');
        } finally {
            input.value = ''; // Reset file input
        }
    };
</script>