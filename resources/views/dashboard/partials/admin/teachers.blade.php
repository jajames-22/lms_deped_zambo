<div class="space-y-6 relative animate-float-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff & CID Directory</h1>
            <p class="text-gray-500 text-sm">Manage registered educators and CID personnel across the Zamboanga Division.</p>
        </div>

        <div class="flex-shrink-0 flex flex-wrap items-center gap-2 relative">
            <button id="bulkDeleteBtn" onclick="confirmDeleteTeacher()"
                class="hidden flex items-center justify-center gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-600 font-bold rounded-xl shadow-sm hover:bg-red-100 transition-all text-sm">
                <i class="fas fa-trash-alt"></i>
                <span id="bulkDeleteCount">Delete (0)</span>
            </button>

            <a href="{{ route('teachers.import.template') }}" download
                class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 border border-gray-200 text-gray-600 font-bold rounded-xl shadow-sm hover:bg-gray-200 transition-all text-sm"
                title="Download Template">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">Template</span>
            </a>
            
            <button id="importTeacherBtn" onclick="openTeacherImportModal()"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl shadow-sm hover:bg-gray-50 transition-all text-sm">
                <i class="fas fa-file-import"></i>
                <span>Import</span>
            </button>

            <button onclick="loadPartial('{{ route('teachers.create') }}', document.getElementById('nav-teachers-btn'))"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all">
                <i class="fas fa-plus-circle"></i>
                <span>Add Personnel</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div
            class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between md:col-span-1">
            <div>
                @php
                    $label = match($filter) {
                        'verified' => 'Verified Teachers',
                        'pending' => 'Pending Teachers',
                        'rejected' => 'Rejected Teachers',
                        default => 'Teachers'
                    };
                @endphp

            <p id="total-teachers-label" class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total {{ $label }}</p>
<h3 class="text-2xl font-black text-gray-900" id="total-teachers-count">{{ $teachers->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-users-cog text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center md:col-span-2">
            <div class="relative w-full">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="teacherSearchInput"
                    placeholder="Search by name, Employee ID, email, or school..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all text-sm text-gray-700">
            </div>
        </div>
    </div>

   <div class="flex flex-wrap items-center justify-between gap-4">

    <div class="flex flex-wrap items-center gap-3">

        <div class="flex items-center bg-gray-200/50 p-1 rounded-xl">
            <button class="role-tab px-4 py-2 text-sm font-bold rounded-lg bg-white text-[#a52a2a] shadow-sm pointer-events-none" data-role="all">
                All Roles
            </button>
            <button class="role-tab px-4 py-2 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700" data-role="teacher">
                Teachers
            </button>
            <button class="role-tab px-4 py-2 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700" data-role="cid">
                CID
            </button>
        </div>

        <div class="flex items-center bg-gray-200/50 p-1 rounded-xl">
            <button class="status-tab px-4 py-2 text-sm font-bold rounded-lg bg-white text-[#a52a2a] shadow-sm pointer-events-none" data-status="all">
                All Status
            </button>
            <button class="status-tab px-4 py-2 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700" data-status="verified">
                Verified
            </button>

            <button class="status-tab flex items-center gap-1 px-4 py-2 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700" data-status="pending">
                <span>Pending</span>

                @php 
                    $pendingCount = collect($teachers)->filter(fn($t) => strtolower($t->status ?? 'pending') === 'pending')->count();
                @endphp

                @if($pendingCount > 0)
                    <span class="flex items-center justify-center min-w-[20px] h-[20px] px-1.5 bg-red-500 text-white text-[11px] font-bold rounded-full">
                        {{ $pendingCount }}
                    </span>
                @endif
            </button>

            <button class="status-tab px-4 py-2 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700" data-status="suspended">
                Suspended
            </button>
        </div>

    </div>

    <button onclick="toggleExportModal()"
        class="flex items-center gap-2 px-5 py-2.5 bg-gray-800 text-white font-bold rounded-xl shadow-sm hover:bg-gray-900 transition text-sm shrink-0">
        <i class="fas fa-file-export"></i>
        <span>Generate Report</span>
    </button>

</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="teachersTable">
                <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">
                            <input type="checkbox" id="selectAllCheckbox"
                                class="rounded border-gray-300 text-[#a52a2a] focus:ring-[#a52a2a] cursor-pointer">
                        </th>
                        <th class="px-4 py-3 text-center w-16">Photo</th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none" title="Sort by Name">
                            Personnel Details <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                            title="Sort by School">
                            Assigned School <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                            title="Sort by Status">
                            Status <i class="fas fa-sort ml-1 text-gray-300"></i>
                        </th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-gray-50/50 transition teacher-row" data-status="{{ strtolower($teacher->status ?? 'pending') }}" data-role="{{ strtolower($teacher->role ?? 'teacher') }}">
                            <td class="px-4 py-2.5 text-center">
                                <input type="checkbox" value="{{ $teacher->id }}"
                                    class="teacher-checkbox rounded border-gray-300 text-[#a52a2a] focus:ring-[#a52a2a] cursor-pointer">
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="w-10 h-10 mx-auto rounded-full {{ $teacher->role === 'cid' ? 'bg-purple-50 border-purple-100 text-purple-600' : 'bg-orange-50 border-orange-100 text-orange-600' }} border overflow-hidden flex items-center justify-center shadow-sm font-bold text-xs">
                                    @if(isset($teacher->avatar) && $teacher->avatar)
                                        <img src="{{ asset('storage/' . $teacher->avatar) }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-2.5">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-gray-900 leading-tight">
                                            {{ $teacher->first_name }}
                                            {{ $teacher->middle_name ? substr($teacher->middle_name, 0, 1) . '.' : '' }}
                                            {{ $teacher->last_name }} {{ $teacher->suffix }}
                                        </p>
                                        
                                        @if($teacher->role === 'cid')
                                            <span class="bg-purple-100 text-purple-700 text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-widest border border-purple-200">CID</span>
                                        @else
                                            <span class="bg-orange-100 text-orange-700 text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-widest border border-orange-200">Teacher</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="bg-gray-100 text-gray-700 text-[10px] px-1.5 py-0.5 rounded font-mono border border-gray-200">
                                            EMP: {{ $teacher->employee_id ?? 'N/A' }}
                                        </span>
                                        <p class="text-xs text-gray-500 truncate" title="{{ $teacher->email }}">
                                            <i class="fas fa-envelope text-[10px] mr-1"></i>
                                            {{ $teacher->email ?? 'No email' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-2.5">
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

                            <td class="px-4 py-2.5">
                                @php
                                    $statusStyles = [
                                        'verified' => 'bg-green-50 text-green-700 border-green-200',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'suspended' => 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                    $currentStyle = $statusStyles[strtolower($teacher->status ?? 'pending')] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                @endphp
                                <span
                                    class="px-2 py-1 {{ $currentStyle }} text-[10px] font-bold rounded-md border uppercase tracking-tighter">
                                    {{ ucfirst($teacher->status ?? 'pending') }}
                                </span>
                            </td>

                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        onclick="loadPartial('{{ route('teachers.edit', $teacher->id) }}', document.getElementById('nav-teachers-btn'))"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition shadow-none"
                                        title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button onclick="confirmDeleteTeacher({{ $teacher->id }})"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shadow-none"
                                        title="Delete">
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
                                        <i class="fas fa-users-cog text-gray-200 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No personnel found.</p>
                                    <p class="text-gray-400 text-xs">Start by adding a new educator or CID staff to the system.</p>
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

{{-- IMPORT TEACHER MODAL --}}
<div id="importTeacherModal"
    class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeTeacherImportModal()"></div>
    <div id="importTeacherBox"
        class="bg-white rounded-3xl max-w-sm w-full p-8 shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-900">Import Personnel List</h3>
            <button onclick="closeTeacherImportModal()"
                class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
        </div>

        <div class="mb-5 p-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-gray-600 leading-relaxed space-y-2">
            <p class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-info-circle text-[#a52a2a]"></i> File Requirements</p>
            <ul class="list-disc list-inside space-y-1 text-xs text-gray-500">
                <li>Accepted formats: <strong>.csv</strong>, <strong>.xlsx</strong>, <strong>.xls</strong></li>
                <li>Please use the official template</li>
                <li>Ensure Employee IDs are correct</li>
            </ul>
        </div>

        <div class="mb-6">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select File</label>
            <input type="file" id="teacher-file-input" accept=".csv, .xlsx, .xls"
                class="block w-full text-sm text-gray-500
                       file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                       file:text-sm file:font-bold file:bg-[#a52a2a]/10 file:text-[#a52a2a]
                       hover:file:bg-[#a52a2a]/20 transition cursor-pointer">
        </div>

        <button id="submitTeacherImportBtn" onclick="submitTeacherImport()"
            class="w-full py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-black transition flex justify-center items-center gap-2 shadow-lg shadow-gray-900/20">
            <i class="fas fa-upload"></i> Upload & Import
        </button>
    </div>
</div>

<div id="teacherImportConflictModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeTeacherConflictModal()"></div>
    <div id="teacherImportConflictModalBox"
        class="relative bg-white rounded-3xl shadow-2xl w-full max-w-3xl transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10 flex flex-col max-h-[92vh] overflow-hidden">

        <div class="px-8 pt-7 pb-5 border-b border-gray-100 flex items-start gap-4">
            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3 class="text-2xl font-black text-gray-900">Duplicate Records Detected</h3>
                <p class="text-sm text-gray-500 mt-1">Compare existing and incoming personnel data, then choose how to handle duplicates.</p>
            </div>
        </div>

        <div class="flex flex-1 min-h-0">
            <div class="w-2/3 border-r border-gray-100 flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <p class="text-xs font-bold uppercase text-gray-500 tracking-wide">
                        Conflicting Records (<span id="teacherDuplicateCountLabel">0</span>)
                    </p>
                </div>
                <div id="teacherDuplicateList" class="flex-1 min-h-0 overflow-y-auto px-6 py-4 space-y-4"></div>
            </div>

            <div class="w-1/3 flex flex-col px-6 py-6">
                <p class="text-sm font-semibold text-gray-700 mb-4">Choose Action</p>
                <div class="space-y-4">
                    <label class="flex gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                        <input type="radio" name="teacher_conflict_strategy" value="skip" checked class="mt-1 w-5 h-5 text-orange-600 border-gray-300 focus:ring-orange-600 shrink-0">
                        <div>
                            <span class="font-bold text-gray-900 block">Skip Duplicates</span>
                            <span class="text-xs text-gray-500">Only import new personnel.</span>
                        </div>
                    </label>
                    <label class="flex gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                        <input type="radio" name="teacher_conflict_strategy" value="update" class="mt-1 w-5 h-5 text-red-600 border-gray-300 focus:ring-red-600 shrink-0">
                        <div>
                            <span class="font-bold text-gray-900 block">Update Existing</span>
                            <span class="text-xs text-gray-500">Overwrite existing data.</span>
                        </div>
                    </label>
                </div>
                <div class="mt-auto pt-6">
                    <div class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg p-3">
                        Tip: Updating will permanently replace existing personnel information.
                    </div>
                </div>
            </div>
        </div>

        <div class="px-8 py-5 border-t border-gray-100 bg-white flex justify-end gap-3">
            <button type="button" onclick="closeTeacherConflictModal()"
                class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition">Cancel</button>
            <button type="button" id="confirmTeacherImportBtn" onclick="executeTeacherImport()"
                class="px-5 py-2.5 bg-orange-600 text-white font-semibold rounded-xl shadow hover:bg-orange-700 transition flex items-center gap-2">
                <i class="fas fa-upload"></i> Continue Import
            </button>
        </div>
    </div>
</div>

<div id="deleteTeacherModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity duration-300" onclick="closeDeleteTeacherModal()">
    </div>
    <div id="deleteTeacherModalBox"
        class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">
        <div
            class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-user-minus text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Remove Personnel?</h3>
        <p class="text-gray-500 mb-8 text-sm">This action cannot be undone. Are you sure you want to permanently remove <span id="deleteTeacherCountText" class="font-bold">this account</span>?</p>
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

<div id="importMessageModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity duration-300" onclick="closeImportModal()"></div>
    <div id="importMessageModalBox"
        class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">

        <div id="importModalIconBox"
            class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner bg-gray-50 text-gray-500">
            <i id="importModalIcon" class="fas fa-info-circle text-4xl"></i>
        </div>

        <h3 id="importModalTitle" class="text-2xl font-black text-gray-900 mb-2">Notice</h3>
        <p id="importModalMessage" class="text-gray-500 mb-8 text-sm">Message content goes here.</p>

        <button type="button" onclick="closeImportModal()" id="importModalBtn"
            class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-md hover:bg-red-800 transition">
            Okay
        </button>
    </div>
</div>

{{-- IMPROVED EXPORT MODAL (ROLES AND STATUSES) --}}
<div id="exportModal"
    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[110] hidden flex items-center justify-center opacity-0 transition-opacity duration-300 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 md:p-8 transform scale-95 transition-transform duration-300 border border-gray-100"
        id="exportModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-900">Export Directory Report</h3>
            <button type="button" onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i
                    class="fas fa-times text-lg"></i></button>
        </div>

        <form action="{{ route('teachers.report') }}" method="GET" target="_blank">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                {{-- ROLE FILTERS --}}
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Filter by Role</p>
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" id="export_role_all" name="role_type" value="all" checked
                                class="w-4 h-4 text-[#a52a2a] border-gray-300 focus:ring-[#a52a2a]">
                            <span class="text-gray-700 text-sm font-bold">All Roles</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-orange-50 transition-colors">
                            <input type="checkbox" name="roles[]" value="teacher"
                                class="export-role-cb w-4 h-4 text-orange-600 rounded border-gray-300 focus:ring-orange-600">
                            <span class="text-orange-700 text-sm font-bold">Teachers</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-purple-50 transition-colors">
                            <input type="checkbox" name="roles[]" value="cid"
                                class="export-role-cb w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-600">
                            <span class="text-purple-700 text-sm font-bold">CID Personnel</span>
                        </label>
                    </div>
                </div>

                {{-- STATUS FILTERS --}}
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Filter by Status</p>
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" id="export_status_all" name="status_type" value="all" checked
                                class="w-4 h-4 text-[#a52a2a] border-gray-300 focus:ring-[#a52a2a]">
                            <span class="text-gray-700 text-sm font-bold">All Statuses</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 transition-colors">
                            <input type="checkbox" name="statuses[]" value="verified"
                                class="export-status-cb w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-600">
                            <span class="text-green-700 text-sm font-bold">Verified</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-amber-50 transition-colors">
                            <input type="checkbox" name="statuses[]" value="pending"
                                class="export-status-cb w-4 h-4 text-amber-600 rounded border-gray-300 focus:ring-amber-600">
                            <span class="text-amber-700 text-sm font-bold">Pending</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-red-50 transition-colors">
                            <input type="checkbox" name="statuses[]" value="suspended"
                                class="export-status-cb w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-600">
                            <span class="text-red-700 text-sm font-bold">Suspended</span>
                        </label>
                    </div>
                </div>

            </div>

            <div class="flex gap-3">
                <button type="button" onclick="toggleExportModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 rounded-xl font-bold border-0 transition-colors">Cancel</button>
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleExportModal, 500)"
                    class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-3 rounded-xl font-bold border-0 transition-colors flex items-center justify-center gap-2"><i
                        class="fas fa-print"></i> Print</button>
                <button type="submit" name="action" value="download" onclick="setTimeout(toggleExportModal, 500)"
                    class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-xl font-bold border-0 transition-colors flex items-center justify-center gap-2"><i
                        class="fas fa-file-pdf"></i> PDF</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- EXPORT MODAL CHECKBOX LOGIC ---
    (function () {
        function setupCheckboxGroup(radioId, cbClass, maxCount) {
            var allRadio = document.getElementById(radioId);
            var checkboxes = document.querySelectorAll(cbClass);

            if (allRadio && checkboxes.length > 0) {
                // If "All" is clicked, uncheck all individual boxes
                allRadio.addEventListener('change', function () {
                    if (this.checked) {
                        checkboxes.forEach(cb => cb.checked = false);
                    }
                });

                // If a specific box is clicked, check the total count
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function () {
                        var checkedCount = document.querySelectorAll(cbClass + ':checked').length;

                        if (checkedCount === maxCount) {
                            // All checked -> switch to "All" radio automatically
                            allRadio.checked = true;
                            checkboxes.forEach(c => c.checked = false);
                        } else if (checkedCount > 0) {
                            // Some checked -> uncheck the "All" radio
                            allRadio.checked = false;
                        } else {
                            // None checked -> default back to "All" radio
                            allRadio.checked = true;
                        }
                    });
                });
            }
        }

        setupCheckboxGroup('export_role_all', '.export-role-cb', 2);
        setupCheckboxGroup('export_status_all', '.export-status-cb', 3);
    })();

    function toggleExportModal() {
        var modal = document.getElementById('exportModal');
        var content = document.getElementById('exportModalContent');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => { 
                modal.classList.remove('opacity-0'); 
                content.classList.remove('scale-95'); 
            }, 10);
        } else {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    }

    // --- MULTI-SELECT CHECKBOX LOGIC ---
    var selectAllCheckbox = document.getElementById('selectAllCheckbox');
    var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    var bulkDeleteCount = document.getElementById('bulkDeleteCount');

    function updateBulkActionUI() {
        var checkedBoxes = document.querySelectorAll('.teacher-checkbox:checked');
        var count = checkedBoxes.length;

        if (count > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteCount.innerText = `Delete (${count})`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
        }

        var visibleRows = Array.from(document.querySelectorAll('.teacher-row')).filter(row => row.style.display !== 'none');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = (count > 0 && count === visibleRows.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function (e) {
            var isChecked = e.target.checked;
            document.querySelectorAll('.teacher-row').forEach(row => {
                if (row.style.display !== 'none') {
                    row.querySelector('.teacher-checkbox').checked = isChecked;
                }
            });
            updateBulkActionUI();
        });
    }

    document.getElementById('teachersTable').addEventListener('change', function (e) {
        if (e.target.classList.contains('teacher-checkbox')) {
            updateBulkActionUI();
        }
    });

    // --- ANIMATED DELETE LOGIC ---
    var deleteTeacherIds = [];

    function confirmDeleteTeacher(id = null) {
        if (id) {
            deleteTeacherIds = [id];
        } else {
            var checkedBoxes = document.querySelectorAll('.teacher-checkbox:checked');
            deleteTeacherIds = Array.from(checkedBoxes).map(cb => cb.value);
        }

        if (deleteTeacherIds.length === 0) return;

        document.getElementById('deleteTeacherCountText').innerText = deleteTeacherIds.length > 1 
            ? `these ${deleteTeacherIds.length} accounts` 
            : "this account";

        var modal = document.getElementById('deleteTeacherModal');
        var box = document.getElementById('deleteTeacherModalBox');

        modal.classList.remove('hidden');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeDeleteTeacherModal() {
        var modal = document.getElementById('deleteTeacherModal');
        var box = document.getElementById('deleteTeacherModalBox');

        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            deleteTeacherIds = [];
        }, 300);
    }

    var confirmTeacherBtn = document.getElementById('confirmDeleteTeacherBtn');
    if (confirmTeacherBtn) {
        var newConfirmTeacherBtn = confirmTeacherBtn.cloneNode(true);
        confirmTeacherBtn.parentNode.replaceChild(newConfirmTeacherBtn, confirmTeacherBtn);

        newConfirmTeacherBtn.addEventListener('click', function () {
            if (deleteTeacherIds.length === 0) return;

            var btnText = this.querySelector('span');
            var originalText = btnText.textContent;

            this.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/dashboard/teachers/bulk-delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: deleteTeacherIds,
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
                    closeDeleteTeacherModal();
                    showImportModal('Deleted Successfully!', data.message, 'success', () => {
                        loadPartial("{{ route('dashboard.teachers') }}", document.getElementById('nav-teachers-btn'));
                    });
                })
                .catch(error => {
                    console.error("Deletion error:", error);
                    closeDeleteTeacherModal();

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
    var allTeacherRows = [];
    var currentFilteredRows = [];
    
    // Manage both Role and Status filters
    var currentRoleFilter = 'all';
    var currentStatusFilter = 'all';
    var currentSearchFilter = '';

    setTimeout(function () {
        allTeacherRows = Array.from(document.querySelectorAll('.teacher-row'));
        currentFilteredRows = [...allTeacherRows];
        applyFilters();
    }, 50);

    function applyFilters() {
        currentFilteredRows = allTeacherRows.filter(function (row) {
            var text = row.textContent.toLowerCase();
            var rowStatus = row.getAttribute('data-status');
            var rowRole = row.getAttribute('data-role');
            
            var matchesSearch = text.includes(currentSearchFilter);
            var matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
            var matchesRole = (currentRoleFilter === 'all') || (rowRole === currentRoleFilter);
            
            return matchesSearch && matchesStatus && matchesRole;
        });

        // Update the count number
        var counterElement = document.getElementById('total-teachers-count');
        if (counterElement) counterElement.textContent = currentFilteredRows.length;

        // Update the label text dynamically
        var labelElement = document.getElementById('total-teachers-label');
        if (labelElement) {
            var statusText = currentStatusFilter !== 'all' 
                ? currentStatusFilter.charAt(0).toUpperCase() + currentStatusFilter.slice(1) + " " 
                : "";
                
            var roleText = currentRoleFilter === 'teacher' 
                ? 'Teachers' 
                : (currentRoleFilter === 'cid' ? 'CID Personnel' : 'Personnel');
                
            labelElement.textContent = "Total " + statusText + roleText;
        }

        currentPage = 1;
        applyPagination();

        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        document.querySelectorAll('.teacher-checkbox').forEach(cb => cb.checked = false);
        updateBulkActionUI();
    }

    function applyPagination() {
        var tbody = document.querySelector('#teachersTable tbody');
        var emptyState = document.getElementById('emptyStateRow');
        var paginationWrapper = document.getElementById('pagination-wrapper');

        allTeacherRows.forEach(row => row.style.display = 'none');

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
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                    document.querySelectorAll('.teacher-checkbox').forEach(cb => cb.checked = false);
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

    var teacherSearchInput = document.getElementById('teacherSearchInput');
    if (teacherSearchInput) {
        var newSearchInput = teacherSearchInput.cloneNode(true);
        teacherSearchInput.parentNode.replaceChild(newSearchInput, teacherSearchInput);

        newSearchInput.addEventListener('input', function () {
            currentSearchFilter = this.value.toLowerCase();
            applyFilters();
        });
    }

    // ROLE TAB LISTENER - UPDATED STYLE
    var roleTabs = document.querySelectorAll('.role-tab');
    roleTabs.forEach(function(tab) {
        var newTab = tab.cloneNode(true);
        tab.parentNode.replaceChild(newTab, tab);

        newTab.addEventListener('click', function() {
            document.querySelectorAll('.role-tab').forEach(t => {
                t.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm', 'pointer-events-none');
                t.classList.add('text-gray-500', 'hover:text-gray-700');
            });
            
            this.classList.remove('text-gray-500', 'hover:text-gray-700');
            this.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm', 'pointer-events-none');
            
            currentRoleFilter = this.getAttribute('data-role');
            applyFilters();
        });
    });

    // STATUS TAB LISTENER - UPDATED STYLE
    var statusTabs = document.querySelectorAll('.status-tab');
    statusTabs.forEach(function(tab) {
        var newTab = tab.cloneNode(true);
        tab.parentNode.replaceChild(newTab, tab);

        newTab.addEventListener('click', function () {
            document.querySelectorAll('.status-tab').forEach(t => {
                t.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm', 'pointer-events-none');
                t.classList.add('text-gray-500', 'hover:text-gray-700');
            });

            this.classList.remove('text-gray-500', 'hover:text-gray-700');
            this.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm', 'pointer-events-none');

            currentStatusFilter = this.getAttribute('data-status');
            applyFilters();
        });
    });

    var teacherSortableHeaders = document.querySelectorAll('#teachersTable .sortable-col');
    teacherSortableHeaders.forEach(function (header) {
        var newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', function () {
            var colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
            var isAsc = newHeader.classList.contains('asc');

            document.querySelectorAll('#teachersTable .sortable-col i').forEach(function (icon) {
                icon.className = 'fas fa-sort ml-1 text-gray-300';
            });
            document.querySelectorAll('#teachersTable .sortable-col').forEach(function (h) {
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

            currentFilteredRows.sort(function (a, b) {
                var aText = a.children[colIndex].textContent.trim().toLowerCase();
                var bText = b.children[colIndex].textContent.trim().toLowerCase();

                if (aText < bText) return -1 * multiplier;
                if (aText > bText) return 1 * multiplier;
                return 0;
            });

            currentPage = 1;
            applyPagination();

            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            document.querySelectorAll('.teacher-checkbox').forEach(cb => cb.checked = false);
            updateBulkActionUI();
        });
    });

    // --- IMPORT MODAL LOGIC ---
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

    let pendingTeacherImportFile = null;

    window.openTeacherImportModal = function() {
        const modal = document.getElementById('importTeacherModal');
        const box   = document.getElementById('importTeacherBox');
        document.getElementById('teacher-file-input').value = '';
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    };

    window.closeTeacherImportModal = function() {
        const modal = document.getElementById('importTeacherModal');
        const box   = document.getElementById('importTeacherBox');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    };

    window.submitTeacherImport = function() {
        const fileInput = document.getElementById('teacher-file-input');
        if (!fileInput.files || fileInput.files.length === 0) {
            showImportModal('No File Selected', 'Please select a CSV or Excel file to import.', 'error');
            return;
        }

        pendingTeacherImportFile = fileInput.files[0];
        fileInput.value = '';

        const btn = document.getElementById('submitTeacherImportBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scanning...';

        const formData = new FormData();
        formData.append('file', pendingTeacherImportFile);
        formData.append('check_only', 1);

        fetch('{{ route("teachers.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json().catch(() => null);
            if (!response.ok) throw data || { message: 'Server error occurred while scanning.' };

            if (data.has_duplicates) {
                closeTeacherImportModal();
                renderTeacherDuplicates(data.duplicates);
                // Show the conflict modal
                const modal = document.getElementById('teacherImportConflictModal');
                const box   = document.getElementById('teacherImportConflictModalBox');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                closeTeacherImportModal();
                executeTeacherImport(true);
            }
        })
        .catch(error => {
            console.error("Check error:", error);
            
            let errorMsg = 'Failed to scan the file. Please check your document and try again.';
            
            if (error && error.errors) {
                errorMsg = Object.values(error.errors)[0][0]; 
            } else if (error && error.message) {
                errorMsg = error.message; 
            }
            
            showImportModal('Import Failed', errorMsg, 'error');
            pendingTeacherImportFile = null;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> Upload & Import';
        });
    };

    window.executeTeacherImport = function(autoRun = false) {
        if (!pendingTeacherImportFile) return;

        const strategy = autoRun
            ? 'skip'
            : document.querySelector('input[name="teacher_conflict_strategy"]:checked').value;

        const formData = new FormData();
        formData.append('file', pendingTeacherImportFile);
        formData.append('strategy', strategy);
        formData.append('check_only', 0);

        const confirmBtn = document.getElementById('confirmTeacherImportBtn');
        const importBtn = document.getElementById('importTeacherBtn');
        
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        } else if (autoRun && importBtn) {
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Importing...</span>';
        }

        fetch('{{ route("teachers.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok) {
                if (!autoRun) closeTeacherConflictModal();
                showImportModal('Import Complete!', data.message, 'success', () => {
                    loadPartial("{{ route('dashboard.teachers') }}", document.getElementById('nav-teachers-btn'));
                });
            } else {
                throw data;
            }
        })
        .catch(error => {
            console.error("Import error:", error);
            if (!autoRun) closeTeacherConflictModal();
            
            let errorMsg = 'An error occurred during import.';
            if (error && error.errors) {
                errorMsg = Object.values(error.errors)[0][0];
            } else if (error && error.message) {
                errorMsg = error.message;
            }

            setTimeout(() => showImportModal('Import Failed', errorMsg, 'error'), 350);
        })
        .finally(() => {
            pendingTeacherImportFile = null;
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-upload"></i> Continue Import';
            }
            if (autoRun && importBtn) {
                importBtn.disabled = false;
                importBtn.innerHTML = '<i class="fas fa-file-import"></i> <span>Import</span>';
            }
        });
    };

    window.closeTeacherConflictModal = function() {
        const modal = document.getElementById('teacherImportConflictModal');
        const box   = document.getElementById('teacherImportConflictModalBox');
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            pendingTeacherImportFile = null;
        }, 300);
    };

    window.renderTeacherDuplicates = function(data) {
        const list  = document.getElementById('teacherDuplicateList');
        const count = document.getElementById('teacherDuplicateCountLabel');
        list.innerHTML = '';
        count.textContent = data.length;

        data.forEach(item => {
            const checkDiff = (key, type) => {
                const existVal = String(item.existing[key] || 'N/A').trim();
                const incVal   = String(item.incoming[key] || 'N/A').trim();
                const isDiff   = existVal.toLowerCase() !== incVal.toLowerCase();
                const val      = type === 'existing' ? existVal : incVal;
                return isDiff
                    ? `<span class="bg-red-100 text-red-700 px-1.5 py-0.5 rounded border border-red-200 font-bold">${val}</span>`
                    : val;
            };

            const div = document.createElement('div');
            div.className = 'border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm';
            div.innerHTML = `
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-700">
                    Employee ID: <span class="text-orange-600">${item.employee_id}</span>
                </div>
                <div class="grid grid-cols-2 text-xs">
                    <div class="p-3 border-r border-gray-200 bg-orange-50/30 space-y-1.5">
                        <p class="font-black text-orange-800 mb-2 border-b border-orange-100 pb-1">Existing in System</p>
                        <p class="flex justify-between"><strong>Name:</strong> <span>${checkDiff('name', 'existing')}</span></p>
                        <p class="flex justify-between"><strong>Role:</strong> <span>${checkDiff('role', 'existing')}</span></p>
                        <p class="flex justify-between"><strong>School:</strong> <span>${checkDiff('school', 'existing')}</span></p>
                        <p class="flex justify-between"><strong>Gender:</strong> <span>${checkDiff('gender', 'existing')}</span></p>
                    </div>
                    <div class="p-3 bg-green-50/30 space-y-1.5">
                        <p class="font-black text-green-800 mb-2 border-b border-green-100 pb-1">Incoming Data</p>
                        <p class="flex justify-between"><strong>Name:</strong> <span>${checkDiff('name', 'incoming')}</span></p>
                        <p class="flex justify-between"><strong>Role:</strong> <span>${checkDiff('role', 'incoming')}</span></p>
                        <p class="flex justify-between"><strong>School:</strong> <span>${checkDiff('school', 'incoming')}</span></p>
                        <p class="flex justify-between"><strong>Gender:</strong> <span>${checkDiff('gender', 'incoming')}</span></p>
                    </div>
                </div>`;
            list.appendChild(div);
        });
    };
</script>