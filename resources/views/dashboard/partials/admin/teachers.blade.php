<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Teacher Directory</h1>
            <p class="text-gray-500 text-sm">Manage registered educators across the Zamboanga Division.</p>
        </div>

        <button onclick="loadPartial('{{ route('teachers.create') }}', document.getElementById('nav-teachers-btn'))"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all">
            <i class="fas fa-plus-circle"></i>
            <span>Add New Teacher</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between md:col-span-1">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Teachers</p>
                <h3 class="text-2xl font-black text-gray-900" id="total-teachers-count">{{ $teachers->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center md:col-span-2">
            <div class="relative w-full">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="teacherSearchInput" placeholder="Search by name, ID, email, or school..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all text-sm text-gray-700">
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button class="status-tab px-5 py-2 rounded-xl text-sm font-bold bg-[#a52a2a] text-white shadow-sm transition-all pointer-events-none" data-status="all">
            All Teachers
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
            <table class="w-full text-left border-collapse" id="teachersTable">
                <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-center w-20">Photo</th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Name">
                            Teacher Details <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Status">
                            Status <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by School">
                            Assigned School <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-gray-50/50 transition teacher-row" data-status="{{ strtolower($teacher->status ?? 'pending') }}">
                            <td class="px-6 py-4">
                                <div class="w-12 h-12 rounded-full bg-blue-50 border border-blue-100 overflow-hidden flex items-center justify-center shadow-sm text-blue-600 font-bold">
                                    @if(isset($teacher->avatar) && $teacher->avatar)
                                        <img src="{{ asset('storage/' . $teacher->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-gray-900 leading-tight">
                                            {{ $teacher->first_name }} {{ $teacher->middle_name ? substr($teacher->middle_name, 0, 1) . '.' : '' }} {{ $teacher->last_name }} {{ $teacher->suffix }}
                                        </p>
                                        <span class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-0.5 rounded font-mono border border-blue-200">
                                            ID: {{ $teacher->user_id }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 truncate" title="{{ $teacher->email }}">
                                        <i class="fas fa-envelope text-[10px] mr-1"></i>
                                        {{ $teacher->email }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @php
                                    $statusStyles = [
                                        'verified' => 'bg-green-50 text-green-700 border-green-200',
                                        'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'suspended'=> 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                    $currentStyle = $statusStyles[strtolower($teacher->status)] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                @endphp
                                <span class="px-2 py-1 {{ $currentStyle }} text-[10px] font-bold rounded-md border uppercase tracking-tighter">
                                    {{ ucfirst($teacher->status ?? 'pending') }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-700">
                                        {{ $teacher->school->name ?? 'Unassigned' }}
                                    </span>
                                    @if($teacher->school)
                                        <span class="text-[10px] text-gray-400 uppercase tracking-tighter">
                                            {{ $teacher->school->district->name ?? 'No District' }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="loadPartial('{{ route('teachers.edit', $teacher->id) }}', document.getElementById('nav-teachers-btn'))"
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition shadow-none"
                                        title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button onclick="confirmDeleteTeacher({{ $teacher->id }})" 
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shadow-none" title="Delete">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyStateRow">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-users-slash text-gray-200 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No teachers found.</p>
                                    <p class="text-gray-400 text-xs">Start by adding a new educator to the system.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="deleteTeacherModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-user-minus text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Remove Teacher?</h3>
        <p class="text-gray-500 mb-8 text-sm">This action cannot be undone. Are you sure you want to permanently remove this educator's account?</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteTeacherModal()" 
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                Cancel
            </button>
            <button type="button" id="confirmDeleteTeacherBtn" 
                class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-700 transition flex items-center justify-center">
                <span>Remove</span>
            </button>
        </div>
    </div>
</div>

<script>
    // --- DELETE LOGIC ---
    var deleteTeacherId = null;

    function confirmDeleteTeacher(id) {
        deleteTeacherId = id;
        document.getElementById('deleteTeacherModal').classList.remove('hidden');
    }

    function closeDeleteTeacherModal() {
        deleteTeacherId = null;
        document.getElementById('deleteTeacherModal').classList.add('hidden');
    }

    var confirmTeacherBtn = document.getElementById('confirmDeleteTeacherBtn');
    if (confirmTeacherBtn) {
        var newConfirmTeacherBtn = confirmTeacherBtn.cloneNode(true);
        confirmTeacherBtn.parentNode.replaceChild(newConfirmTeacherBtn, confirmTeacherBtn);

        newConfirmTeacherBtn.addEventListener('click', function() {
            if (!deleteTeacherId) return;

            var btnText = this.querySelector('span');
            var originalText = btnText.textContent;
            
            this.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/dashboard/teachers/${deleteTeacherId}`, {
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
                closeDeleteTeacherModal();
                loadPartial('{{ route('dashboard.teachers') }}', document.getElementById('nav-teachers-btn'));
            })
            .catch(error => {
                console.error("Deletion error:", error);
                alert("An error occurred while trying to remove the teacher.");
            })
            .finally(() => {
                this.disabled = false;
                btnText.textContent = originalText;
            });
        });
    }

    // --- COMBINED FILTER LOGIC (SEARCH + TABS) ---
    var currentStatusFilter = 'all';
    var currentSearchFilter = '';

    function applyFilters() {
        var rows = document.querySelectorAll('.teacher-row');
        var visibleCount = 0;

        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            var rowStatus = row.getAttribute('data-status');
            
            var matchesSearch = text.includes(currentSearchFilter);
            var matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var counterElement = document.getElementById('total-teachers-count');
        if (counterElement) {
            counterElement.textContent = visibleCount;
        }
    }

    // 1. Search Bar Binding
    var teacherSearchInput = document.getElementById('teacherSearchInput');
    if (teacherSearchInput) {
        var newTeacherSearchInput = teacherSearchInput.cloneNode(true);
        teacherSearchInput.parentNode.replaceChild(newTeacherSearchInput, teacherSearchInput);

        newTeacherSearchInput.addEventListener('input', function() {
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
            // 1. Reset all tabs to inactive state
            document.querySelectorAll('.status-tab').forEach(t => {
                t.classList.remove('bg-[#a52a2a]', 'text-white', 'shadow-sm', 'pointer-events-none');
                t.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-200');
            });
            
            // 2. Set current tab to active state
            this.classList.remove('bg-white', 'text-gray-600', 'border', 'border-gray-200');
            this.classList.add('bg-[#a52a2a]', 'text-white', 'shadow-sm', 'pointer-events-none');
            
            // 3. Update filter state and apply
            currentStatusFilter = this.getAttribute('data-status');
            applyFilters();
        });
    });

    // --- SORTING LOGIC ---
    var teacherSortableHeaders = document.querySelectorAll('#teachersTable .sortable-col');
    teacherSortableHeaders.forEach(function(header) {
        var newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', function() {
            var table = document.getElementById('teachersTable');
            var tbody = table.querySelector('tbody');
            var rows = Array.from(tbody.querySelectorAll('.teacher-row'));
            var colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
            var isAsc = newHeader.classList.contains('asc');

            table.querySelectorAll('.sortable-col i').forEach(function(icon) {
                icon.className = 'fas fa-sort ml-1 text-gray-300';
            });
            table.querySelectorAll('.sortable-col').forEach(function(h) {
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

            rows.sort(function(a, b) {
                var aText = a.children[colIndex].textContent.trim().toLowerCase();
                var bText = b.children[colIndex].textContent.trim().toLowerCase();

                if (aText < bText) return -1 * multiplier;
                if (aText > bText) return 1 * multiplier;
                return 0;
            });

            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        });
    });
</script>