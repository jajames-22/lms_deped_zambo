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
                <button id="edit-content-btn" onclick="loadPartial('{{ route('dashboard.assessments.builder', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                    class="w-full py-3 px-4 bg-white border-2 border-[#a52a2a] text-[#a52a2a] font-bold rounded-xl hover:bg-[#a52a2a] hover:text-white transition-all flex items-center justify-center gap-2 group shadow-sm">
                    <i id="edit-content-icon" class="fas {{ $isLive ? 'fa-eye' : 'fa-tools' }} group-hover:rotate-12 transition-transform"></i>
                    <span id="edit-content-text">{{ $isLive ? 'View Preview' : 'Edit Content' }}</span>
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

                <button type="button" onclick="openLrnImportModal()"
                    class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-file-import"></i> Import List
                </button>
            </form>
        </div>

        <div class="flex items-center justify-between mb-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
            <div class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-72">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="search-student" onkeyup="filterStudents()" placeholder="Search LRN or Name..." 
                        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                </div>
                <button type="button" onclick="exportStudentResultsList()" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 hover:text-[#a52a2a] transition-all shadow-sm flex items-center gap-2 whitespace-nowrap" title="Export List">
                    <i class="fas fa-file-export text-[#a52a2a]"></i> Export List
                </button>
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
                        
                        {{-- FIXED: Always show the name if the account exists in the database --}}
                        <td class="px-6 py-4 font-semibold text-gray-800 name-cell" data-value="{{ $access->student ? ($access->student->first_name . ' ' . $access->student->last_name) : 'ZZZ' }}">
                            @if($access->student)
                                {{ $access->student->first_name ?? '' }} {{ $access->student->last_name ?? '' }}
                            @else
                                <span class="italic text-gray-400 text-xs" title="This LRN is not registered in the system yet.">No account registered</span>
                            @endif
                        </td>
                        
                        {{-- FIXED: Always show the school if the account exists --}}
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
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if(in_array($access->status, ['finished', 'completed']) && $access->student)
                                <a href="{{ route('dashboard.assessments.students.results', [$assessment->id, $access->student->id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] hover:bg-[#a52a2a] hover:text-white rounded-lg text-xs font-bold transition mr-1" 
                                   title="View Assessment Results">
                                    <i class="fas fa-file-alt"></i> View Results
                                </a>
                            @endif
                            <button type="button" onclick="window.openDeleteModal('{{ $access->id }}')" class="text-gray-400 hover:text-red-500 transition inline-block px-1" title="Remove Access">
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

    <div id="draft-warning-modal" class="fixed inset-0 z-[100] hidden h-full">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="window.closeDraftWarningModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
            <div class="h-16 w-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Revert to Draft?</h3>
            <p class="text-gray-500 text-sm mb-6">This assessment has already been published. Reverting it to a draft will close the exam and prevent students from accessing it until you publish it again. Are you sure?</p>
            <div class="flex gap-3 mt-2">
                <button type="button" onclick="window.closeDraftWarningModal()" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">
                    Cancel
                </button>
                <button type="button" id="confirm-draft-btn" class="w-full py-3 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 transition active:scale-95 shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-undo"></i> Revert
                </button>
            </div>
        </div>
    </div>
</div>

    <div id="custom-snackbar" class="fixed bg-[#a52a2a] bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white">
        <div id="assessment-snackbar-icon" class="text-xl"></div>
        <span id="assessment-snackbar-message"></span>
        <button onclick="closeSnackbar()" class="ml-4 text-white/70 hover:text-white transition"><i class="fas fa-times"></i></button>
    </div>
</div>

{{-- Custom Alert Modal (replaces window.alert for errors and confirmations) --}}
<div id="customAlertModal"
    class="fixed inset-0 z-[10000] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeCustomAlert()"></div>
    <div id="customAlertBox"
        class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10">
        <div id="customAlertIconContainer"
            class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl">
            <i id="customAlertIcon" class="fas fa-info"></i>
        </div>
        <h3 id="customAlertTitle" class="text-xl font-black text-gray-900 mb-2">Notice</h3>
        <p id="customAlertMessage" class="text-sm text-gray-500 mb-6"></p>
        <button type="button" id="customAlertBtn" onclick="closeCustomAlert()"
            class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
            Okay
        </button>
    </div>
</div>

<div id="lrnConflictModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeLrnConflictModal()"></div>
    <div id="lrnConflictModalBox"
        class="relative bg-white rounded-3xl shadow-2xl w-full max-w-3xl transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10 flex flex-col max-h-[92vh] overflow-hidden">

        <div class="px-8 pt-7 pb-5 border-b border-gray-100 flex items-start gap-4 shrink-0">
            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3 class="text-2xl font-black text-gray-900">Duplicate LRNs Detected</h3>
                <p class="text-sm text-gray-500 mt-1">We found students in your file who are already on the access list. What would you like to do with them?</p>
            </div>
        </div>

        <div class="flex flex-1 min-h-0">
            <div class="w-3/5 border-r border-gray-100 flex flex-col min-h-0">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 shrink-0">
                    <p class="text-xs font-bold uppercase text-gray-500 tracking-wide">
                        Conflicting LRNs (<span id="lrnDuplicateCountLabel">0</span>)
                    </p>
                </div>
                <div id="lrnDuplicateList" class="flex-1 min-h-0 overflow-y-auto px-6 py-4 space-y-4"></div>
            </div>

            <div class="w-2/5 flex flex-col px-6 py-6 overflow-y-auto">
                <p class="text-sm font-semibold text-gray-700 mb-4 shrink-0">Choose Action</p>
                <div class="space-y-4 shrink-0">
                    <label class="flex gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="lrn_conflict_strategy" value="skip" checked class="mt-1 w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-600 shrink-0">
                        <div>
                            <span class="font-bold text-gray-900 block">Skip Duplicates</span>
                            <span class="text-xs text-gray-500">Ignore these LRNs and only add brand-new students.</span>
                        </div>
                    </label>
                    <label class="flex gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="lrn_conflict_strategy" value="update" class="mt-1 w-5 h-5 text-amber-600 border-gray-300 focus:ring-amber-600 shrink-0">
                        <div>
                            <span class="font-bold text-gray-900 block">Update Access</span>
                            <span class="text-xs text-gray-500">Update these students to offline status so they can retake the exam.</span>
                        </div>
                    </label>
                </div>
                <div class="mt-auto pt-6 shrink-0">
                    <div class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <strong>Tip:</strong> Updating is useful if a student needs to retake the exam.
                    </div>
                </div>
            </div>
        </div>

        <div class="px-8 py-5 border-t border-gray-100 bg-white flex justify-end gap-3 shrink-0">
            <button type="button" onclick="closeLrnConflictModal()"
                class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition">Cancel</button>
            <button type="button" id="confirmLrnImportBtn" onclick="executeLrnImport()"
                class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl shadow hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-upload"></i> Continue Import
            </button>
        </div>
    </div>
</div>
{{-- Import LRN Modal --}}
<div id="importLrnModal"
    class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeLrnImportModal()"></div>
    <div id="importLrnBox"
        class="bg-white rounded-3xl max-w-sm w-full p-8 shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-900">Import LRN List</h3>
            <button onclick="closeLrnImportModal()"
                class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
        </div>

        <div class="mb-5 p-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-gray-600 leading-relaxed space-y-2">
            <p class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-info-circle text-[#a52a2a]"></i> File Requirements</p>
            <ul class="list-disc list-inside space-y-1 text-xs text-gray-500">
                <li>Accepted formats: <strong>.csv</strong>, <strong>.xlsx</strong>, <strong>.xls</strong></li>
                <li>File must contain a column with the header <strong class="font-mono text-gray-700">"lrn"</strong></li>
                <li>LRN values must be numeric</li>
                <li>Other columns in the file will be ignored</li>
            </ul>
        </div>

        <div class="mb-6">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select File</label>
            <input type="file" id="lrn-file-input" accept=".csv, .xlsx, .xls"
                class="block w-full text-sm text-gray-500
                       file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                       file:text-sm file:font-bold file:bg-[#a52a2a]/10 file:text-[#a52a2a]
                       hover:file:bg-[#a52a2a]/20 transition cursor-pointer">
        </div>

        <button id="submitLrnImportBtn" onclick="submitLrnImport()"
            class="w-full py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-black transition flex justify-center items-center gap-2 shadow-lg shadow-gray-900/20">
            <i class="fas fa-upload"></i> Upload & Import
        </button>
    </div>
</div>

<script>
    // ==========================================
    // DOM PREPARATION (Fixes Modal & Snackbar Trapping)
    // ==========================================
    setTimeout(() => {
        ['assessment-delete-modal', 'student-delete-modal', 'custom-snackbar', 'customAlertModal', 'lrnConflictModal', 'importLrnModal'].forEach(id => {
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
    window.closeDraftWarningModal = function() {
        document.getElementById('draft-warning-modal').classList.add('hidden');
        window.toggleBodyScroll(false);
    };

    window.toggleAssessmentStatus = function(checkbox) {
        const isGoingLive = checkbox.checked;
        
        // If unchecking (Published -> Draft), intercept and show warning
        if (!isGoingLive) {
            checkbox.checked = true; // Temporarily revert visual state
            document.getElementById('draft-warning-modal').classList.remove('hidden');
            window.toggleBodyScroll(true);
            
            document.getElementById('confirm-draft-btn').onclick = function() {
                window.closeDraftWarningModal();
                executeToggle(checkbox, false);
            };
            return;
        }

        // If checking (Draft -> Published), proceed immediately
        executeToggle(checkbox, true);
    };

    async function executeToggle(checkbox, targetState) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        
        checkbox.disabled = true;
        // Set the actual check state to what we are attempting to execute
        checkbox.checked = targetState; 

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
                
                const isLive = data.new_status === 'published';
                const badge = document.getElementById('status-badge');
                const text = document.getElementById('status-text');
                const dot = document.getElementById('status-dot');
                const ping = document.getElementById('status-ping');
                const analyticsBtn = document.getElementById('analytics-btn');
                
                // Edit / Preview Button Elements
                const editBtn = document.getElementById('edit-content-btn');
                const editIcon = document.getElementById('edit-content-icon');
                const editText = document.getElementById('edit-content-text');

                if (isLive) {
                    badge.className = "px-3 py-1.5 bg-green-100 text-green-700 text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors";
                    text.innerText = "Published";
                    dot.className = "relative inline-flex rounded-full h-2 w-2 bg-green-500";
                    ping.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75";
                    if(analyticsBtn) analyticsBtn.classList.remove('hidden');
                    
                    // Switch to View Preview
                    editIcon.className = "fas fa-eye group-hover:rotate-12 transition-transform";
                    editText.innerText = "View Preview";
                } else {
                    badge.className = "px-3 py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors";
                    text.innerText = "Draft Mode";
                    dot.className = "relative inline-flex rounded-full h-2 w-2 bg-amber-500";
                    ping.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 hidden";
                    if(analyticsBtn) analyticsBtn.classList.add('hidden');
                    
                    // Switch to Edit Content
                    editIcon.className = "fas fa-tools group-hover:rotate-12 transition-transform";
                    editText.innerText = "Edit Content";
                }
                
            } else {
                checkbox.checked = !targetState;
                throw new Error(data.message || 'Failed to update status.');
            }
        } catch (error) {
            console.error(error);
            showSnackbar(error.message || 'A network error occurred.', 'error');
            checkbox.checked = !targetState;
        } finally {
            checkbox.disabled = false;
        }
    }

    
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
        const msgEl = document.getElementById('assessment-snackbar-message');
        const iconEl = document.getElementById('assessment-snackbar-icon');

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

    // --- Custom Alert Modal (replaces window.alert) ---
    window.showCustomAlert = function(title, message, type = 'error', callback = null) {
        const modal = document.getElementById('customAlertModal');
        const box = document.getElementById('customAlertBox');
        const iconContainer = document.getElementById('customAlertIconContainer');
        const icon = document.getElementById('customAlertIcon');

        document.getElementById('customAlertTitle').innerText = title;
        document.getElementById('customAlertMessage').innerText = message;
        document.getElementById('customAlertBtn').innerText = 'Okay';

        window.customAlertCallback = callback;

        if (type === 'success') {
            iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-green-100 text-green-500';
            icon.className = 'fas fa-check-circle';
        } else if (type === 'warning') {
            iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-amber-100 text-amber-500';
            icon.className = 'fas fa-exclamation-triangle';
        } else {
            iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-red-100 text-red-500';
            icon.className = 'fas fa-exclamation-circle';
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    };

    window.closeCustomAlert = function() {
        const modal = document.getElementById('customAlertModal');
        const box = document.getElementById('customAlertBox');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            if (window.customAlertCallback) window.customAlertCallback();
            window.customAlertCallback = null;
        }, 300);
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
        const lrnValue = lrnInput.value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        if (!lrnValue || isNaN(lrnValue)) {
            showCustomAlert('Invalid LRN', 'Please enter a valid numeric LRN.', 'error');
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

            const data = await response.json().catch(() => null);

            if (!response.ok) throw data || { message: 'Server error occurred.' };

            if (data.success) {
                lrnInput.value = '';
                showSnackbar('Student added successfully!', 'success');
                setTimeout(refreshTableOnly, 200);
            } else {
                throw { message: data.message || 'Failed to add LRN.' };
            }
        } catch (error) {
            let errorMsg = 'An error occurred while adding the student.';
            if (error && error.errors) {
                errorMsg = Object.values(error.errors)[0][0];
            } else if (error && error.message) {
                errorMsg = error.message;
            }
            showCustomAlert('Add Failed', errorMsg, 'error');
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
            let firstError = null;

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

                const data = await response.json().catch(() => null);

                if (response.ok && data && data.success) {
                    successCount++;
                } else if (!firstError) {
                    firstError = (data && data.message) ? data.message : 'An unknown error occurred.';
                }
            }

            window.closeDeleteModal();

            if (successCount > 0) {
                showSnackbar(`Successfully removed ${successCount} student(s).`, 'success');
                setTimeout(refreshTableOnly, 200);
            }

            if (firstError) {
                showCustomAlert('Delete Error', firstError, 'error');
            }

        } catch (error) {
            showCustomAlert('Network Error', 'A network error occurred while deleting. Please try again.', 'error');
            console.error(error);
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    };
let pendingLrnImportFile = null;

    // --- LRN Import Modal Helpers ---
    window.openLrnImportModal = function() {
        const modal = document.getElementById('importLrnModal');
        const box   = document.getElementById('importLrnBox');
        document.getElementById('lrn-file-input').value = '';
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    };

    window.closeLrnImportModal = function() {
        const modal = document.getElementById('importLrnModal');
        const box   = document.getElementById('importLrnBox');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    };

    // Called by the Upload & Import button inside the modal
    window.submitLrnImport = function() {
        const fileInput = document.getElementById('lrn-file-input');
        if (!fileInput.files || fileInput.files.length === 0) {
            showCustomAlert('No File Selected', 'Please select a CSV or Excel file to import.', 'error');
            return;
        }

        pendingLrnImportFile = fileInput.files[0];
        fileInput.value = '';

        const btn = document.getElementById('submitLrnImportBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scanning...';

        showSnackbar('Scanning file...', 'info');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        const formData = new FormData();
        formData.append('file', pendingLrnImportFile);
        formData.append('check_only', 1);

        fetch('{{ route("dashboard.assessments.access.import", $assessment->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json().catch(() => null);
            if (!response.ok) throw data || { message: 'Server error occurred while scanning.' };
            return data;
        })
        .then(data => {
            if (data.has_duplicates) {
                closeLrnImportModal();
                renderLrnDuplicates(data.duplicates);
                const modal = document.getElementById('lrnConflictModal');
                const box   = document.getElementById('lrnConflictModalBox');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                closeLrnImportModal();
                executeLrnImport(true);
            }
        })
        .catch(error => {
            let errorMsg = 'Failed to scan the file. Please check your document and try again.';
            if (error && error.errors) {
                errorMsg = Object.values(error.errors)[0][0];
            } else if (error && error.message) {
                errorMsg = error.message;
            }
            showCustomAlert('Import Failed', errorMsg, 'error');
            pendingLrnImportFile = null;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> Upload & Import';
        });
    };

window.importLrnList = function(input) {
    if (!input.files || input.files.length === 0) return;
    pendingLrnImportFile = input.files[0];
    input.value = '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const formData = new FormData();
    formData.append('file', pendingLrnImportFile);
    formData.append('check_only', 1);

    showSnackbar('Scanning file...', 'info');

    fetch('{{ route("dashboard.assessments.access.import", $assessment->id) }}', {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': csrfToken,
            // CRITICAL: This tells Laravel to return the specific validation text instead of an HTML page
            'Accept': 'application/json' 
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json().catch(() => null);
        
        // If it's a 422 Validation Error or 500 Server Error, throw the data down to the catch block
        if (!response.ok) throw data || { message: 'Server error occurred while scanning.' };

        if (data.has_duplicates) {
            renderLrnDuplicates(data.duplicates);
            const modal = document.getElementById('lrnConflictModal');
            const box   = document.getElementById('lrnConflictModalBox');
            modal.classList.remove('hidden');
            setTimeout(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }, 10);
        } else {
            executeLrnImport(true);
        }
    })
    .catch(error => {
        let errorMsg = 'Failed to scan the file. Please check your document and try again.';
        
        // Extract exact Laravel Validation Errors (e.g., "The file must be a file of type: xlsx, csv")
        if (error && error.errors) {
            // This grabs the very first validation error string Laravel generated
            errorMsg = Object.values(error.errors)[0][0]; 
        } else if (error && error.message) {
            // This catches custom backend error messages (e.g., "No 'lrn' column found")
            errorMsg = error.message; 
        }
        
        // Trigger your custom big modal instead of the tiny snackbar
        showCustomAlert('Import Failed', errorMsg, 'error');
        pendingLrnImportFile = null;
    });
};

window.executeLrnImport = function(autoRun = false) {
    if (!pendingLrnImportFile) return;

    const strategy = autoRun
        ? 'skip'
        : document.querySelector('input[name="lrn_conflict_strategy"]:checked').value;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const formData  = new FormData();
    formData.append('file', pendingLrnImportFile);
    formData.append('strategy', strategy);
    formData.append('check_only', 0);

    const confirmBtn = document.getElementById('confirmLrnImportBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    fetch('{{ route("dashboard.assessments.access.import", $assessment->id) }}', {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json' 
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json().catch(() => null);
        
        if (!response.ok) throw data || { message: 'Server error occurred while importing.' };
        
        if (!autoRun) closeLrnConflictModal();
        showSnackbar(data.message || 'Import successful!', 'success');
        setTimeout(refreshTableOnly, 200);
    })
    .catch(error => {
        if (!autoRun) closeLrnConflictModal();
        
        let errorMsg = 'An error occurred while saving the students.';
        
        // Same robust error extraction here just in case it fails during execution
        if (error && error.errors) {
            errorMsg = Object.values(error.errors)[0][0];
        } else if (error && error.message) {
            errorMsg = error.message;
        }
        
        showCustomAlert('Import Failed', errorMsg, 'error');
    })
    .finally(() => {
        pendingLrnImportFile = null;
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-upload"></i> Continue Import';
        }
    });
};

window.closeLrnConflictModal = function() {
    const modal = document.getElementById('lrnConflictModal');
    const box   = document.getElementById('lrnConflictModalBox');
    box.classList.remove('scale-100', 'opacity-100');
    box.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        pendingLrnImportFile = null;
    }, 300);
};

function renderLrnDuplicates(data) {
    const list  = document.getElementById('lrnDuplicateList');
    const count = document.getElementById('lrnDuplicateCountLabel');
    list.innerHTML = '';
    count.textContent = data.length;

    data.forEach(item => {
        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm';
        div.innerHTML = `
            <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-700">
                LRN: <span class="text-blue-600 font-mono">${item.lrn}</span>
            </div>
            <div class="grid grid-cols-2 text-xs">
                <div class="p-3 border-r border-gray-200 bg-blue-50/30 space-y-1.5">
                    <p class="font-black text-blue-800 mb-2 border-b border-blue-100 pb-1">Already in Whitelist</p>
                    <p class="flex justify-between"><strong>Status:</strong>
                        <span class="bg-red-100 text-red-700 px-1.5 py-0.5 rounded border border-red-200 font-bold">${item.existing.status}</span>
                    </p>
                </div>
                <div class="p-3 bg-green-50/30 space-y-1.5">
                    <p class="font-black text-green-800 mb-2 border-b border-green-100 pb-1">Incoming Action</p>
                    <p class="flex justify-between"><strong>Status:</strong> <span>${item.incoming.status}</span></p>
                </div>
            </div>`;
        list.appendChild(div);
    });
}

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

    function exportStudentResultsList() {
        const searchVal = document.getElementById('search-student') ? document.getElementById('search-student').value.trim() : '';
        const query = encodeURIComponent(searchVal);
        window.open(`{{ route('dashboard.assessments.students.export', $assessment->id) }}?search=${query}`, '_blank');
    }
</script>