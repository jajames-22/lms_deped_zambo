<head>
    <style>
        /* Styling for the visual track when the checkbox is UNCHECKED */
        .toggle-container .toggle-track {
            background-color: #d1d5db; /* Default gray-300 */
        }

        /* Styling for the knob/handle when the checkbox is UNCHECKED */
        .toggle-container .toggle-handle {
            transform: translateX(1px); /* Default left position */
        }

        /* * THE MAGIC: When the hidden checkbox (.toggle-input) is :checked, 
        * change the styling of the TRACK element that immediately follows it (+)
        */
        .toggle-container .toggle-input:checked + .toggle-track {
            background-color: #26da65; /* The precise teal color from the image! */
        }

        /* * When the hidden checkbox is :checked, shift the knob/handle 
        * to the right (calculated based on track width minus handle width and padding)
        */
        .toggle-container .toggle-input:checked ~ .toggle-handle {
            transform: translateX(2rem); /* Shifts the knob to the right side */
        }
        
        /* Optional: Ensure focus state is visible for accessibility */
        .toggle-container .toggle-input:focus-visible + .toggle-track {
            box-shadow: 0 0 0 4px rgba(165, 42, 42, 0.4); /* subtle maroon glow */
        }
    </style>  
</head>

<div class="space-y-6 pb-20 max-w-6xl mx-auto relative">
    @php 
        $isLive = ($material->status === 'published'); 
    @endphp

    <div class="flex items-center justify-between">
        <button onclick="loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn'))"
            class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Materials
        </button>

        <div class="flex items-center gap-4">
            <span id="status-badge" class="px-3 py-1.5 {{ $isLive ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }} text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors">
                <span class="relative flex h-2 w-2">
                    @if($isLive)
                        <span id="status-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span id="status-dot" class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    @else
                        <span id="status-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 hidden"></span>
                        <span id="status-dot" class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    @endif
                </span>
                <span id="status-text">{{ $isLive ? 'Published' : 'Draft Mode' }}</span>
            </span>

            <label class="toggle-container relative inline-block w-16 h-8 cursor-pointer" title="Toggle Material Status">
                <input type="checkbox" id="material-status-toggle" class="sr-only toggle-input" onchange="window.toggleMaterialStatus(this)" {{ $isLive ? 'checked' : '' }}>
                <span class="toggle-track absolute inset-0 bg-gray-300 rounded-full transition-colors duration-300 peer-focus-visible:ring-2 peer-focus-visible:ring-[#a52a2a]/40 shadow-inner"></span>
                <span class="toggle-handle absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform duration-300 transform shadow-md shadow-black/20"></span>
            </label>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#a52a2a]/5 to-transparent rounded-bl-full pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest">
                        Module Setup
                    </span>
                    <span class="text-gray-400 text-sm font-medium">
                        Last Updated {{ $material->updated_at->format('M d, Y') }}
                    </span>
                </div>
                
                <h1 class="text-3xl font-black text-gray-900 mb-4">{{ $material->title }}</h1>
                <p class="text-gray-600 max-w-3xl leading-relaxed">
                    {{ $material->description ?: 'No description provided for this module.' }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0 md:w-48">
                <button onclick="loadPartial('{{ route('dashboard.materials.edit', $material->id) }}', document.getElementById('nav-materials-btn'))"
                    class="w-full py-3 px-4 bg-white border-2 border-[#a52a2a] text-[#a52a2a] font-bold rounded-xl hover:bg-[#a52a2a] hover:text-white transition-all flex items-center justify-center gap-2 group shadow-sm">
                    <i class="fas fa-pen group-hover:rotate-12 transition-transform"></i>
                    Edit Content
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8">
        
        <div class="w-full lg:flex-1">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Module Tags</h3>
            
            <div id="active-tags-container" class="flex flex-wrap gap-2 mb-3">
                </div>

            <div class="relative w-full md:w-80">
                <div class="flex items-center bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 focus-within:ring-2 focus-within:ring-[#a52a2a]/20 focus-within:border-[#a52a2a] transition-all">
                    <i class="fas fa-tags text-gray-400 mr-2 text-sm"></i>
                    <input type="text" id="tag-input" placeholder="Add a tag (e.g. Science, Grade 4)..." 
                    class="bg-transparent border-none outline-none w-full text-sm font-medium text-gray-700 placeholder-gray-400" 
                    onkeydown="handleTagKeydown(event)" oninput="handleTagInput(event)" autocomplete="off">
                </div>
                
                <div id="tag-suggestions" class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-lg hidden max-h-48 overflow-y-auto">
                    </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Press <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded text-gray-500 font-mono">Enter</kbd> to add a custom tag.</p>
        </div>

        <div class="flex items-center justify-center gap-8 lg:shrink-0 w-full lg:w-auto border-t lg:border-t-0 lg:border-l border-gray-100 pt-6 lg:pt-0 lg:pl-8">
            
            <div class="text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-xl shadow-sm mb-2">
                    <i class="fas fa-eye"></i>
                </div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($material->views ?? 0) }}</p>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Views</p>
            </div>

            <div class="w-px h-16 bg-gray-200"></div>

            <div class="text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm mb-2">
                    <i class="fas fa-layer-group"></i>
                </div>
                <p class="text-2xl font-black text-gray-900">{{ $material->lessons_count ?? 0 }}</p>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Lessons</p>
            </div>
            
            <div class="w-px h-16 bg-gray-200"></div>
            
            <div class="text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-sm mb-2">
                    <i class="fas fa-cubes"></i>
                </div>
                <p class="text-2xl font-black text-gray-900">{{ $material->items_count ?? 0 }}</p>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Items</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-8 relative transition-all duration-300" id="access-management-container">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl mb-6 border transition-colors duration-300 {{ $material->is_public ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}" id="visibility-banner">
            <div>
                <h4 class="font-bold text-gray-900 flex items-center gap-2">
                    <i id="visibility-icon" class="fas {{ $material->is_public ? 'fa-globe-asia text-green-600' : 'fa-lock text-gray-500' }}"></i>
                    <span id="visibility-title">{{ $material->is_public ? 'Public Material' : 'Private Material' }}</span>
                </h4>
                <p id="visibility-desc" class="text-sm mt-1 {{ $material->is_public ? 'text-green-700' : 'text-gray-500' }}">
                    {{ $material->is_public ? 'Anyone on the platform can view this module. The access list below is currently ignored.' : 'Only the specific LRNs listed below can view this module.' }}
                </p>
            </div>
            
            <label class="toggle-container relative inline-block w-16 h-8 cursor-pointer shrink-0" title="Toggle Privacy">
                <input type="checkbox" id="visibility-toggle" class="sr-only toggle-input" onchange="window.toggleVisibility(this)" {{ $material->is_public ? 'checked' : '' }}>
                <span class="toggle-track absolute inset-0 bg-gray-300 rounded-full transition-colors duration-300 peer-focus-visible:ring-2 peer-focus-visible:ring-[#a52a2a]/40 shadow-inner"></span>
                <span class="toggle-handle absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform duration-300 transform shadow-md shadow-black/20"></span>
            </label>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 transition-opacity duration-300" id="access-controls" style="opacity: {{ $material->is_public ? '0.5' : '1' }}; pointer-events: {{ $material->is_public ? 'none' : 'auto' }};">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Access Management</h3>
                <p class="text-sm text-gray-500 mt-1">Manage who can see this private module.</p>
                
                <button type="button" onclick="notifyStudents(this)" class="mt-3 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-sm font-bold rounded-lg transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Send Email Invites
                </button>
            </div>
            
            <form id="add-email-form" class="flex flex-wrap gap-2 w-full md:w-auto">
                <input type="email" id="student-email-input" name="email" placeholder="Enter Student Email" required
                    class="w-full md:w-64 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm font-medium">
                
                <button type="button" onclick="submitEmail(this)" 
                    class="px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Add
                </button>

                <input type="file" id="email-file-input" class="hidden" accept=".csv, .xlsx, .xls" onchange="importEmailList(this)">
                <button type="button" onclick="document.getElementById('email-file-input').click()" 
                    class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-file-import"></i> Import List
                </button>
            </form>
        </div>

        <div id="access-table-container" class="transition-opacity duration-300" style="opacity: {{ $material->is_public ? '0.5' : '1' }}; pointer-events: {{ $material->is_public ? 'none' : 'auto' }};">
            <div class="flex items-center justify-between mb-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="relative w-full md:w-72">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="search-student" onkeyup="filterStudents()" placeholder="Search Email or Name..." 
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
                            <th class="px-6 py-4 cursor-pointer hover:text-gray-900 select-none group" onclick="sortTable(1, 'alpha')">
                                Email Address <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i>
                            </th>
                            <th class="px-6 py-4 cursor-pointer hover:text-gray-900 select-none group" onclick="sortTable(2, 'alpha')">
                                Student Name <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i>
                            </th>
                            <th class="px-6 py-4 text-center cursor-pointer hover:text-gray-900 select-none group" onclick="sortTable(3, 'alpha')">
                                Status <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i>
                            </th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="students-tbody">
                    @forelse($whitelistedStudents ?? [] as $access)
                        <tr class="student-row hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" class="email-checkbox w-4 h-4 text-[#a52a2a] bg-white border-gray-300 rounded focus:ring-[#a52a2a]" value="{{ $access->id }}" onclick="updateBulkDeleteBtn()">
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-gray-900 email-cell">{{ $access->email }}</td>
                            
                            <td class="px-6 py-4 font-semibold text-gray-800 name-cell" data-value="{{ $access->student ? ($access->student->first_name . ' ' . $access->student->last_name) : 'ZZZ' }}">
                                @if($access->user_id && $access->student)
                                    {{ $access->student->first_name }} {{ $access->student->last_name }}
                                @else
                                    <span class="italic text-gray-400 text-xs">Pending Registration</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 text-center status-cell" data-value="{{ $access->status }}">
                                @if($access->status === 'enrolled')
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-md text-[10px] font-bold uppercase tracking-wider border border-green-200">
                                        Enrolled
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-md text-[10px] font-bold uppercase tracking-wider border border-amber-200">
                                        pending
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
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-users-slash text-3xl mb-3 text-gray-300"></i>
                                    <p class="text-sm font-medium">No students added yet.</p>
                                    <p class="text-xs mt-1">Enter an email address above to grant access.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div id="pagination-wrapper" class="rounded-xl mt-1 hidden flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                    Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span id="page-end-info" class="font-bold text-gray-900">0</span> of <span id="page-total-info" class="font-bold text-gray-900">0</span> results
                </div>
                <div class="flex items-center gap-1" id="pagination-controls"></div>
            </div>
        </div>
    </div>

    <div class="bg-red-50 rounded-3xl p-6 border border-red-100 mt-8">
        <h3 class="text-red-800 font-bold mb-2">Danger Zone</h3>
        <p class="text-sm text-red-600 mb-4">Deleting this module will permanently remove it and all associated content. This action cannot be undone.</p>
        <button onclick="window.openMaterialDeleteModal()" 
            class="px-6 py-2.5 bg-white border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
            Delete Module
        </button>
    </div>

    <div id="material-delete-modal" class="fixed inset-0 z-[100] hidden h-full">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="window.closeMaterialDeleteModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
                <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Delete Module?</h3>
                <p class="text-gray-500 text-sm mb-6">Are you sure you want to permanently delete this material? All associated content will be lost.</p>
                <div class="flex gap-3 mt-2">
                    <button type="button" onclick="window.closeMaterialDeleteModal()" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">Cancel</button>
                    <button type="button" id="confirm-material-delete-btn" onclick="window.executeMaterialDelete()" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition active:scale-95 shadow-md flex items-center justify-center gap-2">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="student-delete-modal" class="fixed inset-0 z-[100] hidden h-full">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="window.closeDeleteModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
                <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Revoke Access?</h3>
                <p id="delete-modal-text" class="text-gray-500 text-sm mb-6">Are you sure you want to remove access for the selected student(s)? They will not be able to view this module.</p>
                <div class="flex gap-3 mt-2">
                    <button type="button" onclick="window.closeDeleteModal()" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">Cancel</button>
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
    // --- Visibility Toggle Logic ---
    window.toggleVisibility = async function(checkbox) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        checkbox.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.materials.toggle-visibility", $material->id ?? 0) }}', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSnackbar(data.message, 'success');

                const isPublic = data.is_public;
                
                // Update UI visually
                const banner = document.getElementById('visibility-banner');
                const icon = document.getElementById('visibility-icon');
                const title = document.getElementById('visibility-title');
                const desc = document.getElementById('visibility-desc');
                const controls = document.getElementById('access-controls');
                const tableContainer = document.getElementById('access-table-container');

                if (isPublic) {
                    banner.className = "flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl mb-6 border transition-colors duration-300 bg-green-50 border-green-200";
                    icon.className = "fas fa-globe-asia text-green-600";
                    title.innerText = "Public Material";
                    desc.innerText = "Anyone on the platform can view this module. The access list below is currently ignored.";
                    desc.className = "text-sm mt-1 text-green-700";
                    
                    // Dim the access controls to indicate they are inactive
                    controls.style.opacity = '0.5';
                    controls.style.pointerEvents = 'none';
                    tableContainer.style.opacity = '0.5';
                    tableContainer.style.pointerEvents = 'none';
                } else {
                    banner.className = "flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl mb-6 border transition-colors duration-300 bg-gray-50 border-gray-200";
                    icon.className = "fas fa-lock text-gray-500";
                    title.innerText = "Private Material";
                    desc.innerText = "Only the specific LRNs listed below can view this module.";
                    desc.className = "text-sm mt-1 text-gray-500";
                    
                    // Restore access controls
                    controls.style.opacity = '1';
                    controls.style.pointerEvents = 'auto';
                    tableContainer.style.opacity = '1';
                    tableContainer.style.pointerEvents = 'auto';
                }
            } else {
                checkbox.checked = !checkbox.checked;
                throw new Error(data.message || 'Failed to update visibility.');
            }
        } catch (error) {
            console.error(error);
            showSnackbar(error.message || 'A network error occurred.', 'error');
        } finally {
            checkbox.disabled = false;
        }
    };

    // --- Send Emails Logic ---
    window.notifyStudents = async function(btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        btn.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        try {
            const response = await fetch('{{ route("dashboard.materials.notify-students", $material->id ?? 0) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();

            if (response.ok && data.success) {
                showSnackbar(data.message, 'success');
            } else {
                showSnackbar(data.message || 'Failed to send emails.', 'error');
            }
        } catch (error) {
            showSnackbar('A network error occurred.', 'error');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    };

    // --- Toggle Status Logic ---
    window.toggleMaterialStatus = async function(checkbox) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        checkbox.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.materials.toggle-status", $material->id ?? 0) }}', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSnackbar(data.message, 'success');

                const isLive = data.new_status === 'published';
                const badge = document.getElementById('status-badge');
                const text = document.getElementById('status-text');
                const dot = document.getElementById('status-dot');
                const ping = document.getElementById('status-ping');

                if (isLive) {
                    badge.className = "px-3 py-1.5 bg-green-100 text-green-700 text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors";
                    text.innerText = "Published";
                    dot.className = "relative inline-flex rounded-full h-2 w-2 bg-green-500";
                    ping.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75";
                    ping.classList.remove('hidden');
                } else {
                    badge.className = "px-3 py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors";
                    text.innerText = "Draft Mode";
                    dot.className = "relative inline-flex rounded-full h-2 w-2 bg-amber-500";
                    ping.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 hidden";
                    ping.classList.add('hidden');
                }
            } else {
                checkbox.checked = !checkbox.checked;
                throw new Error(data.message || 'Failed to update status.');
            }
        } catch (error) {
            console.error(error);
            showSnackbar(error.message || 'A network error occurred.', 'error');
        } finally {
            checkbox.disabled = false;
        }
    };

    // --- Snackbar Logic ---
    var snackbarTimeout; 
    window.showSnackbar = function(message, type = 'error') {
        const snackbar = document.getElementById('custom-snackbar');
        const msgEl = document.getElementById('snackbar-message');
        const iconEl = document.getElementById('snackbar-icon');

        snackbar.className = "fixed bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white";
        if (type === 'error') {
            snackbar.classList.add('bg-[#a52a2a]'); iconEl.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
        } else if (type === 'success') {
            snackbar.classList.add('bg-green-600'); iconEl.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
            snackbar.classList.add('bg-gray-800'); iconEl.innerHTML = '<i class="fas fa-info-circle"></i>';
        }

        msgEl.innerText = message;
        setTimeout(() => snackbar.classList.remove('translate-y-24', 'opacity-0'), 10);
        clearTimeout(snackbarTimeout);
        snackbarTimeout = setTimeout(closeSnackbar, 4000);
    };

    window.closeSnackbar = function() {
        document.getElementById('custom-snackbar').classList.add('translate-y-24', 'opacity-0');
    };

    // --- State and Pagination Management ---
    var allRows = [], currentRows = [], currentPage = 1, pageSize = 10, sortColumnIndex = null, sortIsAscending = true, sortType = 'alpha';
    
    setTimeout(() => { initializeTableData(); }, 50);

    function initializeTableData() {
        allRows = Array.from(document.querySelectorAll("#students-tbody .student-row"));
        applyFilterAndSort();
    }

function applyFilterAndSort() {
        const query = document.getElementById('search-student').value.toLowerCase();
        
        currentRows = allRows.filter(row => {
            // CHANGED: Query against email-cell instead of lrn-cell
            const email = row.querySelector('.email-cell').innerText.toLowerCase();
            const name = row.querySelector('.name-cell').innerText.toLowerCase();
            return email.includes(query) || name.includes(query);
        });

        if (sortColumnIndex !== null) {
            currentRows.sort((a, b) => {
                let valA = a.cells[sortColumnIndex].getAttribute('data-value') || a.cells[sortColumnIndex].innerText.trim();
                let valB = b.cells[sortColumnIndex].getAttribute('data-value') || b.cells[sortColumnIndex].innerText.trim();
                if (sortType === 'numeric') {
                    return sortIsAscending ? valA.localeCompare(valB, undefined, {numeric: true}) : valB.localeCompare(valA, undefined, {numeric: true});
                } else {
                    return sortIsAscending ? valA.localeCompare(valB) : valB.localeCompare(valA);
                }
            });
        }
        updateTablePagination();
    }

    function updateTablePagination() {
        const tbody = document.getElementById("students-tbody");
        const emptyState = document.getElementById("empty-state-row");
        const paginationWrapper = document.getElementById("pagination-wrapper");

        allRows.forEach(row => row.style.display = 'none');
        if (currentRows.length === 0) {
            if (emptyState) emptyState.style.display = '';
            paginationWrapper.classList.remove('flex', 'sm:flex-row'); paginationWrapper.classList.add('hidden');
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        paginationWrapper.classList.remove('hidden'); paginationWrapper.classList.add('flex', 'sm:flex-row');

        const totalPages = Math.ceil(currentRows.length / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = Math.min(startIdx + pageSize, currentRows.length);

        for (let i = startIdx; i < endIdx; i++) {
            currentRows[i].style.display = ''; tbody.appendChild(currentRows[i]);
        }

        document.getElementById('page-start-info').innerText = startIdx + 1;
        document.getElementById('page-end-info').innerText = endIdx;
        document.getElementById('page-total-info').innerText = currentRows.length;

        renderPaginationUI(totalPages);
        document.getElementById('select-all').checked = false;
        updateBulkDeleteBtn();
    }

    function renderPaginationUI(totalPages) {
        const controls = document.getElementById('pagination-controls');
        controls.innerHTML = '';

        const createPageBtn = (text, page, disabled = false, active = false) => {
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
                btn.onclick = () => { currentPage = page; updateTablePagination(); };
            }
            return btn;
        };

        controls.appendChild(createPageBtn('<i class="fas fa-chevron-left text-xs"></i>', currentPage - 1, currentPage === 1));

        let startP = Math.max(1, currentPage - 1);
        let endP = Math.min(totalPages, currentPage + 1);

        if (currentPage === 1) endP = Math.min(3, totalPages);
        if (currentPage === totalPages) startP = Math.max(1, totalPages - 2);

        if (startP > 1) {
            controls.appendChild(createPageBtn(1, 1, false, currentPage === 1));
            if (startP > 2) controls.appendChild(createPageBtn('...', null, true));
        }

        for (let i = startP; i <= endP; i++) {
            controls.appendChild(createPageBtn(i, i, false, i === currentPage));
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) controls.appendChild(createPageBtn('...', null, true));
            controls.appendChild(createPageBtn(totalPages, totalPages, false, currentPage === totalPages));
        }

        controls.appendChild(createPageBtn('<i class="fas fa-chevron-right text-xs"></i>', currentPage + 1, currentPage === totalPages));
    }

    window.filterStudents = function() { currentPage = 1; applyFilterAndSort(); };
    
    window.sortTable = function(columnIndex, type) {
        if (allRows.length === 0) return;
        if (sortColumnIndex === columnIndex) {
            sortIsAscending = !sortIsAscending;
        } else {
            sortColumnIndex = columnIndex;
            sortIsAscending = true;
            sortType = type;
        }

        const table = document.getElementById("students-table");
        const headers = table.querySelectorAll('th i.fa-sort, th i.fa-sort-up, th i.fa-sort-down');
        headers.forEach(icon => icon.className = 'fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500');
        
        const clickedHeaderIcon = table.querySelectorAll('th')[columnIndex].querySelector('i');
        if (clickedHeaderIcon) {
            clickedHeaderIcon.className = sortIsAscending ? 'fas fa-sort-up ml-1 text-[#a52a2a]' : 'fas fa-sort-down ml-1 text-[#a52a2a]';
        }

        applyFilterAndSort();
    };

    window.toggleSelectAll = function(selectAllCheckbox) {
        // CHANGED: Targets email-checkbox class
        const checkboxes = document.querySelectorAll('.email-checkbox');
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = selectAllCheckbox.checked;
            }
        });
        updateBulkDeleteBtn();
    };

    window.updateBulkDeleteBtn = function() {
        const selectedCount = document.querySelectorAll('.email-checkbox:checked').length;
        const bulkBtn = document.getElementById('bulk-delete-btn');
        const countSpan = document.getElementById('selected-count');
        
        if (selectedCount > 0) {
            bulkBtn.classList.remove('hidden');
            bulkBtn.classList.add('flex');
            countSpan.innerText = selectedCount;
        } else {
            bulkBtn.classList.add('hidden');
            bulkBtn.classList.remove('flex');
            const selectAllCheck = document.getElementById('select-all');
            if(selectAllCheck) selectAllCheck.checked = false;
        }
    };

    async function refreshTableOnly() {
        try {
            const baseUrl = '{{ route("dashboard.materials.manage", $material->id ?? 0) }}';
            const fetchUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + '_t=' + new Date().getTime();

            const response = await fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const htmlText = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            const newTbody = doc.querySelector('#students-tbody');
            
            if (newTbody) {
                document.getElementById('students-tbody').innerHTML = newTbody.innerHTML;
                initializeTableData();
            }
        } catch (error) { showSnackbar('Failed to update table visually. Please refresh.', 'error'); }
    }

    // --- API Interactions ---
    
    // CHANGED: submitLrn becomes submitEmail
    window.submitEmail = async function(btn) {
        const emailInput = document.getElementById('student-email-input');
        const emailValue = emailInput.value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        // Basic frontend email validation
        if (!emailValue || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
            showSnackbar('Please enter a valid email address.', 'error');
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.materials.access.add", $material->id ?? 0) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: emailValue }) // CHANGED payload to 'email'
            });
            const data = await response.json();

            if (response.ok && data.success) {
                emailInput.value = ''; 
                showSnackbar('Student added successfully!', 'success');
                setTimeout(refreshTableOnly, 200);
            } else if (response.status === 422) {
                showSnackbar("Validation Error: " + data.errors.email[0], 'error');
            } else {
                showSnackbar(data.message || 'Failed to add email.', 'error');
            }
        } catch (error) {
            showSnackbar('A network error occurred.', 'error');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    };


    window.targetsToDelete = []; 

    window.openDeleteModal = function(id) {
        if (id === 'bulk') {
            const checked = Array.from(document.querySelectorAll('.email-checkbox:checked'));
            window.targetsToDelete = checked.map(cb => cb.value);
            
            if (window.targetsToDelete.length === 0) {
                showSnackbar("Please select at least one student to delete.", 'error');
                return;
            }
            document.getElementById('delete-modal-text').innerText = `Are you sure you want to remove access for ${window.targetsToDelete.length} selected student(s)?`;
        } else {
            window.targetsToDelete = [id];
            document.getElementById('delete-modal-text').innerText = "Are you sure you want to remove access for this student?";
        }
        
        document.getElementById('student-delete-modal').classList.remove('hidden');
    };

    // CHANGED: importLrnList becomes importEmailList
    window.importEmailList = async function(input) {
        if (!input.files || input.files.length === 0) return;

        const file = input.files[0];
        const formData = new FormData();
        formData.append('file', file);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        showSnackbar('Importing Emails...', 'info');

        try {
            const response = await fetch('{{ route("dashboard.materials.access.import", $material->id ?? 0) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSnackbar(data.message, 'success');
                setTimeout(refreshTableOnly, 200);
            } else {
                showSnackbar(data.message || 'Import failed.', 'error');
            }
        } catch (error) {
            showSnackbar('A network error occurred during import.', 'error');
        } finally {
            input.value = ''; 
        }
    };

    window.closeDeleteModal = function() {
        document.getElementById('student-delete-modal').classList.add('hidden');
        window.targetsToDelete = []; 
    };

    window.executeDelete = async function() {
        if (!window.targetsToDelete || window.targetsToDelete.length === 0) return;

        const btn = document.getElementById('confirm-delete-btn');
        const originalHtml = btn.innerHTML;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        btn.disabled = true;

        try {
            let successCount = 0;

            for (const id of window.targetsToDelete) {
                // Using a direct path bypasses ANY Laravel URL encoding bugs!
                let deleteUrl = `/dashboard/materials/access/${id}`;

                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                // Improved error handling to catch 500 Server Errors gracefully
                if (!response.ok) {
                    let errMsg = `Server Error: ${response.status}`;
                    try {
                        const errData = await response.json();
                        errMsg = errData.message || errMsg;
                    } catch(e) {
                        console.error("Failed to parse error as JSON");
                    }
                    throw new Error(errMsg);
                }

                const data = await response.json();
                
                if (data.success) {
                    successCount++;
                } else {
                    throw new Error(data.message || "Unknown error occurred.");
                }
            }

            window.closeDeleteModal();
            
            if (successCount > 0) {
                showSnackbar(`Successfully removed ${successCount} student(s).`, 'success');
                setTimeout(refreshTableOnly, 200);
            }
            
        } catch (error) {
            console.error("Delete Exception:", error);
            // This will now show the EXACT error (e.g. "Server Error: 500") if it fails again
            showSnackbar(error.message || 'A network error occurred.', 'error');
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            window.targetsToDelete = []; // Reset after processing
        }
    };
    
    // --- Material Delete Logic ---
    window.openMaterialDeleteModal = function() {
        document.getElementById('material-delete-modal').classList.remove('hidden');
    };

    window.closeMaterialDeleteModal = function() {
        document.getElementById('material-delete-modal').classList.add('hidden');
    };

    window.executeMaterialDelete = async function() {
        const btn = document.getElementById('confirm-material-delete-btn');
        const originalHtml = btn.innerHTML;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...'; btn.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.materials.destroy", $material->id ?? 0) }}', {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' }
            });

            if (response.ok) {
                window.closeMaterialDeleteModal();
                showSnackbar('Module deleted successfully.', 'success');
                setTimeout(() => { loadPartial('{{ url("/dashboard/materials") }}', document.getElementById('nav-materials-btn')); }, 1000);
            } else {
                let data = {}; try { data = await response.json(); } catch(e) {}
                showSnackbar(data.message || 'Failed to delete module.', 'error');
            }
        } catch (error) {
            showSnackbar('A network error occurred.', 'error');
        } finally {
            if (btn) { btn.innerHTML = originalHtml; btn.disabled = false; }
        }
    };

    setTimeout(() => {
        const materialsBtn = document.getElementById('nav-materials-btn');
        
        if (materialsBtn) {
            // 1. Strip the active classes from ALL sidebar buttons and restore default styling
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
                btn.classList.add('text-gray-600', 'hover:bg-gray-100');
            });

            // 2. Apply the exact active classes to the Materials button
            materialsBtn.classList.remove('text-gray-600', 'hover:bg-gray-100');
            materialsBtn.classList.add('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
        }
    }, 50);

  // --- Tags Autocomplete & Management Logic ---
    const availableTags = [
        "Science", "Earth Science", "Computer Science", "Biology", "Chemistry", "Physics",
        "Mathematics", "Algebra", "Calculus", "Geometry",
        "English", "Literature", "Grammar", "Filipino", "Pananaliksik",
        "MAPEH", "Music", "Arts", "Physical Education", "Health",
        "History", "World History", "Philippine History", "Contemporary Issues",
        "Technology", "Programming", "Web Development", "First Aid"
    ];

    // 1. DOM Elements
    const tagInput = document.getElementById('tag-input');
    const suggestionsBox = document.getElementById('tag-suggestions');
    const activeTagsContainer = document.getElementById('active-tags-container');

    // 2. Initialize Tags from Database
    let currentTags = {!! json_encode($material->tags->pluck('name') ?? []) !!}; 

    // Render tags on page load
    renderActiveTags();

    // 3. Handle Keyboard Events (Enter Key)
    window.handleTagKeydown = function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Stop form submission
            const query = e.target.value.trim();
            if (query !== '') {
                addTag(query);
            }
        }
    };

    // 4. Handle Typing (Autocomplete Suggestions)
    window.handleTagInput = function(e) {
        const query = e.target.value.trim().toLowerCase();

        if (query === '') {
            suggestionsBox.classList.add('hidden');
            return;
        }

        const matchedTags = availableTags.filter(tag => {
            const isNotAdded = !currentTags.map(t => t.toLowerCase()).includes(tag.toLowerCase());
            const matchesQuery = tag.toLowerCase().includes(query);
            return isNotAdded && matchesQuery;
        });

        renderSuggestions(matchedTags, query);
    };

    // 5. Render the Suggestions Dropdown
    function renderSuggestions(tags, query) {
        if (tags.length === 0) {
            suggestionsBox.classList.add('hidden');
            return;
        }

        suggestionsBox.innerHTML = '';
        tags.forEach(tag => {
            const regex = new RegExp(`(${query})`, "gi");
            const highlightedText = tag.replace(regex, "<span class='text-[#a52a2a] font-bold'>$1</span>");

            const div = document.createElement('div');
            div.className = 'px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b border-gray-50 last:border-0 transition-colors';
            div.innerHTML = highlightedText;
            
            div.onclick = function() {
                addTag(tag);
            };
            suggestionsBox.appendChild(div);
        });

        suggestionsBox.classList.remove('hidden');
    }

    // 6. Add Tag to UI and Database
    async function addTag(tagValue) {
        if (currentTags.map(t => t.toLowerCase()).includes(tagValue.toLowerCase())) {
            tagInput.value = '';
            suggestionsBox.classList.add('hidden');
            return;
        }

        // Instantly update UI (Optimistic rendering)
        currentTags.push(tagValue);
        tagInput.value = '';
        suggestionsBox.classList.add('hidden');
        renderActiveTags();

        // Send to Backend
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        try {
            const response = await fetch('{{ route("dashboard.materials.tags.add", $material->id ?? 0) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tag: tagValue })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message);
        } catch (error) {
            console.error('Failed to save tag:', error);
            showSnackbar('Failed to save tag to database.', 'error');
            // If it fails on the server, remove it from the UI to stay synced
            currentTags = currentTags.filter(t => t !== tagValue);
            renderActiveTags();
        }
    }

    // 7. Remove Tag from UI and Database
    window.removeTag = async function(tagValue) {
        // Instantly update UI
        currentTags = currentTags.filter(t => t !== tagValue);
        renderActiveTags();

        // Send Delete request to Backend
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        try {
            const safeTag = encodeURIComponent(tagValue);let url = '{{ route("dashboard.materials.tags.remove", ["material" => $material->id ?? 0, "tag" => "PLACEHOLDER_TAG"]) }}';
url = url.replace('PLACEHOLDER_TAG', safeTag);

            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message);
        } catch (error) {
            console.error('Failed to remove tag:', error);
            showSnackbar('Failed to remove tag from database.', 'error');
            // Re-add to UI if server delete failed
            currentTags.push(tagValue);
            renderActiveTags();
        }
    }

    // 8. Render Active Tags in the DOM
    function renderActiveTags() {
        if (!activeTagsContainer) return;
        activeTagsContainer.innerHTML = '';

        if (currentTags.length === 0) {
            activeTagsContainer.innerHTML = '<span class="text-sm text-gray-400 italic">No tags added yet.</span>';
            return;
        }

        currentTags.forEach(tag => {
            const tagEl = document.createElement('div');
            tagEl.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] border border-[#a52a2a]/20 rounded-lg text-xs font-bold transition-all';
            
            const spanEl = document.createElement('span');
            spanEl.textContent = tag;

            const btnEl = document.createElement('button');
            btnEl.type = 'button';
            btnEl.className = 'text-[#a52a2a]/60 hover:text-[#a52a2a] hover:bg-[#a52a2a]/10 rounded-full h-4 w-4 flex items-center justify-center transition-colors';
            btnEl.innerHTML = '<i class="fas fa-times text-[10px]"></i>';
            btnEl.onclick = () => removeTag(tag);

            tagEl.appendChild(spanEl);
            tagEl.appendChild(btnEl);
            activeTagsContainer.appendChild(tagEl);
        });
    }

    // 9. Close suggestions dropdown when clicking outside
    window.onclickCloseSuggestions = function(e) {
        if (tagInput && suggestionsBox) {
            if (!tagInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('hidden');
            }
        }
    };
    
    document.removeEventListener('click', window.onclickCloseSuggestions);
    document.addEventListener('click', window.onclickCloseSuggestions);
</script>