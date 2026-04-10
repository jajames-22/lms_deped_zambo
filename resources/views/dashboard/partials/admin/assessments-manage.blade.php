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

<div class="space-y-6 pb-20 w-full mx-auto relative">
    @php 
        $isLive = ($assessment->status === 'published'); 
    @endphp

    <div class="flex items-center justify-between">
        <button onclick="loadPartial('{{ url('/dashboard/assessment') }}', document.getElementById('nav-assessment-btn'))"
            class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Assessments
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

            <label class="toggle-container relative inline-block w-16 h-8 cursor-pointer" title="Toggle Assessment Status">
                <input type="checkbox" id="assessment-status-toggle" class="sr-only toggle-input" onchange="window.toggleAssessmentStatus(this)" {{ $isLive ? 'checked' : '' }}>
                
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

                <button onclick="loadPartial('{{ route('dashboard.assessments.analytics', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                id="analytics-btn" class="w-full py-3 px-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#a52a2a]/20 {{ $isLive ? '' : 'hidden' }}">
                    <i class="fas fa-chart-pie"></i>
                    View Analytics
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-gradient-to-r from-red-700 to-red-900 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden border border-gray-700">
            <i class="fas fa-key absolute -right-4 -bottom-4 text-8xl text-white/5 rotate-12"></i>
            <h3 class="text-white font-bold uppercase tracking-widest text-xs mb-2">Student Access Key</h3>
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
                        
                        {{-- FIXED: Hide Name if Status is Offline --}}
                        <td class="px-6 py-4 font-semibold text-gray-800 name-cell" data-value="{{ ($access->status !== 'offline' && $access->student) ? ($access->student->first_name . ' ' . $access->student->last_name) : 'ZZZ' }}">
                            @if($access->status !== 'offline')
                                @if($access->student)
                                    {{ $access->student->first_name ?? '' }} {{ $access->student->last_name ?? '' }}
                                @else
                                    <span class="italic text-gray-400 text-xs">No account registered</span>
                                @endif
                            @else
                                <span class="italic text-gray-400 text-xs" title="Hidden until student joins the lobby">No account registered</span>
                            @endif
                        </td>
                        
                        {{-- FIXED: Hide School if Status is Offline --}}
                        <td class="px-6 py-4 text-gray-500">
                            @if($access->status !== 'offline')
                                @if($access->student && $access->student->school)
                                    {{ $access->student->school->name ?? '-' }}
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
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
        <div id="pagination-wrapper" class="rounded-xl mt-1 hidden flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span id="page-end-info" class="font-bold text-gray-900">0</span> of <span id="page-total-info" class="font-bold text-gray-900">0</span> results
            </div>
            <div class="flex items-center gap-1" id="pagination-controls">
            </div>
        </div>
    </div> 
    
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Exam Results Visibility</h3>
            <p class="text-sm text-gray-500 mt-1">Allow students to see their score and the correct answers immediately after submitting the exam.</p>
        </div>
        
        <div class="shrink-0">
            <label class="toggle-container relative inline-block w-16 h-8 cursor-pointer" title="Toggle Results Visibility">
                <input type="checkbox" id="show-results-toggle" class="sr-only toggle-input" onchange="window.toggleShowResults(this)" {{ ($assessment->show_results ?? false) ? 'checked' : '' }}>
                
                <span class="toggle-track absolute inset-0 bg-gray-300 rounded-full transition-colors duration-300 peer-focus-visible:ring-2 peer-focus-visible:ring-[#a52a2a]/40 shadow-inner"></span>
                
                <span class="toggle-handle absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform duration-300 transform shadow-md shadow-black/20"></span>
            </label>
        </div>
    </div>
    
    <div class="bg-red-50 rounded-3xl p-6 border border-red-100 mt-8">
        <h3 class="text-red-800 font-bold mb-2">Danger Zone</h3>
        <p class="text-sm text-red-600 mb-4">Deleting this assessment will permanently remove it and all associated student submissions. This action cannot be undone.</p>
        <button onclick="window.openAssessmentDeleteModal()" 
            class="px-6 py-2.5 bg-white border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
            Delete Assessment
        </button>
    </div>

    {{-- Modals and Snackbars --}}
    <div id="assessment-delete-modal" class="fixed inset-0 z-[100] hidden h-full">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="window.closeAssessmentDeleteModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
                <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Delete Assessment?</h3>
                <p class="text-gray-500 text-sm mb-6">Are you sure you want to permanently delete this assessment? All associated student submissions will be lost. This action cannot be undone.</p>
                <div class="flex gap-3 mt-2">
                    <button type="button" onclick="window.closeAssessmentDeleteModal()" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">
                        Cancel
                    </button>
                    <button type="button" id="confirm-assessment-delete-btn" onclick="window.executeAssessmentDelete()" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition active:scale-95 shadow-md flex items-center justify-center gap-2">
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
    // ==========================================
    // DOM PREPARATION (Fixes Modal & Snackbar Trapping)
    // ==========================================
    setTimeout(() => {
        ['assessment-delete-modal', 'student-delete-modal', 'custom-snackbar'].forEach(id => {
            const newEl = document.getElementById(id);
            if (newEl && newEl.parentElement !== document.body) {
                // Clean up any orphan elements from previous visits to prevent duplicates
                const oldEl = document.body.querySelector('body > #' + id);
                if (oldEl) oldEl.remove();
                
                // Move the element to the body so it breaks out of the transformed container
                document.body.appendChild(newEl);
            }
        });
    }, 50);

    window.toggleBodyScroll = function(disable) {
        if (disable) document.body.classList.add('overflow-hidden');
        else document.body.classList.remove('overflow-hidden');
    };

    // --- Toggle Status Logic ---
    window.toggleAssessmentStatus = async function(checkbox) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        
        // Temporarily disable to prevent spam clicking
        checkbox.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.assessments.toggle-status", $assessment->id) }}', {
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
                
                // Update the visual status badge state
                const isLive = data.new_status === 'published';
                const badge = document.getElementById('status-badge');
                const text = document.getElementById('status-text');
                const dot = document.getElementById('status-dot');
                const ping = document.getElementById('status-ping');
                const analyticsBtn = document.getElementById('analytics-btn'); // Get the button

                if (isLive) {
                    badge.className = "px-3 py-1.5 bg-green-100 text-green-700 text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors";
                    text.innerText = "Published";
                    dot.className = "relative inline-flex rounded-full h-2 w-2 bg-green-500";
                    ping.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75";
                    
                    if(analyticsBtn) analyticsBtn.classList.remove('hidden'); // Show button
                } else {
                    badge.className = "px-3 py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors";
                    text.innerText = "Draft Mode";
                    dot.className = "relative inline-flex rounded-full h-2 w-2 bg-amber-500";
                    ping.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 hidden";
                    
                    if(analyticsBtn) analyticsBtn.classList.add('hidden'); // Hide button
                }
                
            }else {
                // If API failed, revert the visual checkbox state to match reality
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

    window.toggleShowResults = async function(checkbox) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        
        checkbox.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.assessments.toggle-results", $assessment->id) }}', {
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
            } else {
                checkbox.checked = !checkbox.checked; // Revert if failed
                throw new Error(data.message || 'Failed to update setting.');
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

        setTimeout(() => snackbar.classList.remove('translate-y-24', 'opacity-0'), 10);
        clearTimeout(snackbarTimeout);
        snackbarTimeout = setTimeout(closeSnackbar, 4000);
    };

    window.closeSnackbar = function() {
        document.getElementById('custom-snackbar').classList.add('translate-y-24', 'opacity-0');
    };

    // --- State and Pagination Management ---
    var allRows = [];
    var currentRows = [];
    var currentPage = 1;
    var pageSize = 10; 
    var sortColumnIndex = null;
    var sortIsAscending = true;
    var sortType = 'alpha';

    // Initial load
    setTimeout(() => {
        initializeTableData();
    }, 50);

    function initializeTableData() {
        allRows = Array.from(document.querySelectorAll("#students-tbody .student-row"));
        applyFilterAndSort();
    }

    function applyFilterAndSort() {
        const query = document.getElementById('search-student').value.toLowerCase();
        
        currentRows = allRows.filter(row => {
            const lrn = row.querySelector('.lrn-cell').innerText.toLowerCase();
            const name = row.querySelector('.name-cell').innerText.toLowerCase();
            return lrn.includes(query) || name.includes(query);
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
            paginationWrapper.classList.remove('flex', 'sm:flex-row');
            paginationWrapper.classList.add('hidden');
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        paginationWrapper.classList.remove('hidden');
        paginationWrapper.classList.add('flex', 'sm:flex-row');

        const totalPages = Math.ceil(currentRows.length / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = Math.min(startIdx + pageSize, currentRows.length);

        for (let i = startIdx; i < endIdx; i++) {
            currentRows[i].style.display = '';
            tbody.appendChild(currentRows[i]);
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

    window.filterStudents = function() {
        currentPage = 1; 
        applyFilterAndSort();
    };

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
        const checkboxes = document.querySelectorAll('.lrn-checkbox');
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = selectAllCheckbox.checked;
            }
        });
        updateBulkDeleteBtn();
    };

    window.updateBulkDeleteBtn = function() {
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
            const selectAllCheck = document.getElementById('select-all');
            if(selectAllCheck) selectAllCheck.checked = false;
        }
    };

    // --- Access Key Logic ---
    window.copyAccessKey = function(key, btnElement) {
        navigator.clipboard.writeText(key).then(() => {
            const icon = btnElement.querySelector('i');
            icon.className = 'fas fa-check text-green-400';
            btnElement.classList.add('bg-green-500/20');
            setTimeout(() => {
                icon.className = 'fas fa-copy group-hover:scale-110 transition-transform';
                btnElement.classList.remove('bg-green-500/20');
            }, 2000);
        });
    };

    // --- Dynamic Table Refreshing ---
    async function refreshTableOnly() {
        try {
            const baseUrl = '{{ route("dashboard.assessments.manage", $assessment->id) }}';
            const fetchUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + '_t=' + new Date().getTime();

            const response = await fetch(fetchUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const htmlText = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            
            const newTbody = doc.querySelector('#students-tbody');
            if (newTbody) {
                document.getElementById('students-tbody').innerHTML = newTbody.innerHTML;
                initializeTableData();
            }
        } catch (error) {
            console.error("Failed to refresh table quietly: ", error);
            showSnackbar('Failed to update table visually. Please refresh.', 'error');
        }
    }

    // --- API Calls ---
    window.submitLrn = async function(btn) {
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
                setTimeout(refreshTableOnly, 200);
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
    };

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
        window.toggleBodyScroll(true);
    };

    window.closeDeleteModal = function() {
        document.getElementById('student-delete-modal').classList.add('hidden');
        targetsToDelete = []; 
        window.toggleBodyScroll(false);
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
                let deleteUrl = '{{ route("dashboard.assessments.access.remove", ":id") }}'.replace(':id', id);

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
                setTimeout(refreshTableOnly, 200);
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

    // --- Assessment Delete Logic ---
    window.openAssessmentDeleteModal = function() {
        document.getElementById('assessment-delete-modal').classList.remove('hidden');
        window.toggleBodyScroll(true);
    };

    window.closeAssessmentDeleteModal = function() {
        document.getElementById('assessment-delete-modal').classList.add('hidden');
        window.toggleBodyScroll(false);
    };

    window.executeAssessmentDelete = async function() {
        const btn = document.getElementById('confirm-assessment-delete-btn');
        const originalHtml = btn.innerHTML;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.assessments.destroy", $assessment->id) }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                window.closeAssessmentDeleteModal();
                showSnackbar('Assessment deleted successfully.', 'success');
                
                setTimeout(() => {
                    loadPartial('{{ url("/dashboard/assessment") }}', document.getElementById('nav-assessment-btn'));
                }, 1000);
            } else {
                let data = {};
                try { data = await response.json(); } catch(e) {}
                showSnackbar(data.message || 'Failed to delete assessment.', 'error');
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

    setTimeout(() => {
        const assessmentBtn = document.getElementById('nav-assessment-btn');
        
        if (assessmentBtn) {
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
                btn.classList.add('text-gray-600', 'hover:bg-gray-100');
            });

            assessmentBtn.classList.remove('text-gray-600', 'hover:bg-gray-100');
            assessmentBtn.classList.add('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
        }
    }, 50);
</script>