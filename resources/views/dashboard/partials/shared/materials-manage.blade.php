<head>
    <style>
        .toggle-container .toggle-track {
            background-color: #d1d5db;
        }

        .toggle-container .toggle-handle {
            transform: translateX(1px);
        }

        .toggle-container .toggle-input:checked+.toggle-track {
            background-color: #26da65;
        }

        .toggle-container .toggle-input:checked~.toggle-handle {
            transform: translateX(2rem);
        }

        .toggle-container .toggle-input:focus-visible+.toggle-track {
            box-shadow: 0 0 0 4px rgba(165, 42, 42, 0.4);
        }
        
        /* Custom Slider Styling */
        input[type=range] {
            -webkit-appearance: none;
            width: 100%;
            height: 10px;
            border-radius: 5px;
            background: #e5e7eb;
            outline: none;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid currentColor;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: transform 0.1s;
        }
        input[type=range]:disabled::-webkit-slider-thumb {
            background: #f3f4f6;
            border-color: #9ca3af;
            cursor: not-allowed;
            box-shadow: none;
        }
        input[type=range]:not(:disabled)::-webkit-slider-thumb:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<div class="space-y-6 pb-20 max-w-6xl mx-auto relative">

    <img src="x" onerror="
        let navBtn = document.getElementById('nav-materials-btn');
        if (navBtn) {
            document.querySelectorAll('.nav-btn').forEach(b => {
                b.classList.remove('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
                b.classList.add('text-gray-600', 'hover:bg-gray-100');
            });
            navBtn.classList.remove('text-gray-600', 'hover:bg-gray-100');
            navBtn.classList.add('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
        }
    " style="display:none;">

    @php 
        $isLive = ($material->status === 'published'); 
        
        // --- GRADING CONFIGURATION LOGIC & AUTO-HEALING ---
        $hasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
        $hasQuizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
            ->where('lessons.material_id', $material->id)
            ->whereIn('lesson_contents.type', ['mcq', 'checkbox', 'true_false']) 
            ->exists();

        // Get raw DB values
        $rawExamWeight = $material->exam_weight;
        $savedPassingPercentage = $material->passing_percentage ?? 80;
        
        // Calculate what the weight MUST be based on available content
        if ($hasExams && $hasQuizzes) {
            $savedExamWeight = $rawExamWeight ?? 60; // Both exist, use DB or default to 60
        } elseif ($hasExams && !$hasQuizzes) {
            $savedExamWeight = 100; // Only exams exist
        } elseif (!$hasExams && $hasQuizzes) {
            $savedExamWeight = 0;   // Only quizzes exist
        } else {
            $savedExamWeight = 0;   // Neither exist
        }

        // Flag to trigger JS auto-correction if DB holds an invalid weight due to content deletion in builder
        $needsWeightSync = ($rawExamWeight !== null && $rawExamWeight !== $savedExamWeight);
        
        // Inverting for the UI: Slider value represents Quizzes (Left Side)
        $quizWeight = 100 - $savedExamWeight;
    @endphp

    <div class="flex items-center justify-between">
        <button onclick="loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn'))"
            class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Materials
        </button>

        <div class="flex items-center gap-4">
            <span id="status-badge"
                class="px-3 py-1.5 {{ $isLive ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }} text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2 transition-colors">
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

            <label class="relative inline-flex items-center cursor-pointer" title="Toggle Material Status">
                <input type="checkbox" id="material-status-toggle" class="sr-only peer" onchange="window.toggleMaterialStatus(this)" {{ $isLive ? 'checked' : '' }}>
                <div class="w-16 h-8 bg-gray-300 rounded-full peer peer-focus:ring-2 peer-focus:ring-[#a52a2a]/40 peer-checked:after:translate-x-8 after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all after:shadow-md peer-checked:bg-[#26da65] shadow-inner transition-colors"></div>
            </label>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#a52a2a]/5 to-transparent rounded-bl-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest">Module Setup</span>
                    <span class="text-gray-400 text-sm font-medium">Last Updated {{ $material->updated_at->format('M d, Y') }}</span>
                </div>
                <h1 class="text-3xl font-black text-gray-900 mb-4">{{ $material->title }}</h1>
                <p class="text-gray-600 max-w-3xl leading-relaxed">{{ $material->description ?: 'No description provided for this module.' }}</p>
            </div>
            <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0 md:w-48">
                <button onclick="loadPartial('{{ route('dashboard.materials.edit', $material->id) }}', document.getElementById('nav-materials-btn'))"
                    class="w-full py-3 px-4 bg-white border-2 border-[#a52a2a] text-[#a52a2a] font-bold rounded-xl hover:bg-[#a52a2a] hover:text-white transition-all flex items-center justify-center gap-2 group shadow-sm">
                    <i class="fas fa-pen group-hover:rotate-12 transition-transform"></i> Edit Content
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8">
        <div class="w-full lg:flex-1">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Module Tags</h3>
            <div id="active-tags-container" class="flex flex-wrap gap-2 mb-3"></div>
            <div class="relative w-full md:w-80">
                <div class="flex items-center bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 focus-within:ring-2 focus-within:ring-[#a52a2a]/20 focus-within:border-[#a52a2a] transition-all">
                    <i class="fas fa-tags text-gray-400 mr-2 text-sm"></i>
                    <input type="text" id="tag-input" placeholder="Add a tag (e.g. Science, Grade 4)..."
                        class="bg-transparent border-none outline-none w-full text-sm font-medium text-gray-700 placeholder-gray-400"
                        onkeydown="handleTagKeydown(event)" oninput="handleTagInput(event)" autocomplete="off">
                </div>
                <div id="tag-suggestions" class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-lg hidden max-h-48 overflow-y-auto"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Press <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded text-gray-500 font-mono">Enter</kbd> to add a custom tag.</p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-6 lg:gap-8 lg:shrink-0 w-full lg:w-auto border-t lg:border-t-0 lg:border-l border-gray-100 pt-6 lg:pt-0 lg:pl-8">
            <div class="text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-xl shadow-sm mb-2"><i class="fas fa-eye"></i></div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($material->views ?? 0) }}</p>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Views</p>
            </div>
            
            <div class="w-px h-16 bg-gray-200 hidden sm:block"></div>
            
            <div class="text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm mb-2"><i class="fas fa-layer-group"></i></div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($material->lessons_count ?? 0) }}</p>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Lessons</p>
            </div>
            
            <div class="w-px h-16 bg-gray-200 hidden md:block"></div>
            
            <div class="text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-sm mb-2"><i class="fas fa-cubes"></i></div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($material->items_count ?? 0) }}</p>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Items</p>
            </div>

            <div class="w-px h-16 bg-gray-200 hidden sm:block"></div>
            
            <div class="text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-sm mb-2"><i class="fas fa-download"></i></div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($material->downloads ?? 0) }}</p>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Downloads</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-8 relative transition-all duration-300" id="access-management-container">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border transition-colors duration-300 {{ $material->is_public ? 'bg-green-50 border-green-200 mb-0' : 'bg-gray-50 border-gray-200 mb-6' }}" id="visibility-banner">
            <div>
                <h4 class="font-bold text-gray-900 flex items-center gap-2">
                    <i id="visibility-icon" class="fas {{ $material->is_public ? 'fa-globe-asia text-green-600' : 'fa-lock text-gray-500' }}"></i>
                    <span id="visibility-title">{{ $material->is_public ? 'Public Material' : 'Private Material' }}</span>
                </h4>
                <p id="visibility-desc" class="text-sm mt-1 {{ $material->is_public ? 'text-green-700' : 'text-gray-500' }}">
                    {{ $material->is_public ? 'Anyone on the platform can view this module. The access list below is currently ignored.' : 'Only the specific Emails listed below can view this module.' }}
                </p>
            </div>

            <div class="flex items-center gap-4 mt-2 sm:mt-0">
                <div id="access-code-display" class="inline-flex items-center gap-3 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm transition-all duration-300 {{ $material->is_public ? 'opacity-50 pointer-events-none' : '' }}">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Material Code:</span>
                    <span class="font-mono text-lg font-black text-[#a52a2a] tracking-widest">{{ $material->access_code ?? 'N/A' }}</span>
                    <button onclick="copyAccessCode('{{ $material->access_code }}')" class="text-gray-400 hover:text-[#a52a2a] transition-colors" title="Copy Code"><i class="fas fa-copy"></i></button>
                </div>

                <label class="relative inline-flex items-center cursor-pointer shrink-0" title="Toggle Privacy">
                    <input type="checkbox" id="visibility-toggle" class="sr-only peer" onchange="window.toggleVisibility(this)" {{ $material->is_public ? 'checked' : '' }}>
                    <div class="w-16 h-8 bg-gray-300 rounded-full peer peer-focus:ring-2 peer-focus:ring-[#a52a2a]/40 peer-checked:after:translate-x-8 after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all after:shadow-md peer-checked:bg-[#26da65] shadow-inner transition-colors"></div>
                </label>
            </div>
        </div>

        <div id="access-management-content" class="transition-all duration-500 overflow-hidden {{ $material->is_public ? 'max-h-0 opacity-0' : 'max-h-[2000px] opacity-100' }}">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6" id="access-controls">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Access Management</h3>
                    <p class="text-sm text-gray-500 mt-1">Manage who can see this private module.</p>
                    <button type="button" onclick="notifyStudents(this)" class="mt-3 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-sm font-bold rounded-lg transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Invite All Pending
                    </button>
                </div>

                <form id="add-email-form" class="flex flex-wrap gap-2 w-full md:w-auto">
                    <input type="email" id="student-email-input" name="email" placeholder="Enter Student Email" required
                        class="w-full md:w-64 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm font-medium">

                    <button type="button" onclick="submitEmail(this)" class="px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                        <i class="fas fa-plus"></i> Add
                    </button>

                    <input type="file" id="email-file-input" class="hidden" accept=".csv, .xlsx, .xls" onchange="importEmailList(this)">
                    <button type="button" onclick="document.getElementById('email-file-input').click()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                        <i class="fas fa-file-import"></i> Import List
                    </button>
                </form>
            </div>

            <div id="access-table-container">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50/50 p-4 rounded-xl border border-gray-100 mb-4">
                    <div class="relative w-full sm:w-72">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" id="search-student" placeholder="Search Email or Name..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg outline-none focus:border-[#a52a2a] focus:ring-1 focus:ring-[#a52a2a] transition text-sm bg-white" onkeyup="filterStudents()">
                    </div>

                    <button type="button" id="bulk-delete-btn" onclick="window.openDeleteModal('bulk')" class="hidden w-full sm:w-auto items-center justify-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 text-sm font-bold rounded-lg transition-all shadow-sm">
                        <i class="fas fa-trash-alt"></i> Remove Selected (<span id="selected-count">0</span>)
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 mt-4">
                    <table class="w-full text-left text-sm text-gray-600" id="students-table">
                        <thead class="bg-gray-50/50 text-[10px] uppercase text-gray-500 font-bold tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 w-12 text-center"><input type="checkbox" id="select-all" class="rounded border-gray-300 text-[#a52a2a] focus:ring-[#a52a2a] h-4 w-4 cursor-pointer transition-all" onclick="window.toggleSelectAll(this)"></th>
                                <th class="px-6 py-4 cursor-pointer group hover:bg-gray-100 transition" onclick="window.sortTable(1, 'alpha')">Email Address <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i></th>
                                <th class="px-6 py-4 cursor-pointer group hover:bg-gray-100 transition" onclick="window.sortTable(2, 'alpha')">Student Name <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i></th>
                                <th class="px-6 py-4 text-center cursor-pointer group hover:bg-gray-100 transition" onclick="window.sortTable(3, 'alpha')">Status <i class="fas fa-sort ml-1 text-gray-300 group-hover:text-gray-500"></i></th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="students-tbody">
                            @forelse($whitelistedStudents ?? [] as $access)
                                <tr class="student-row hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-center"><input type="checkbox" class="email-checkbox rounded border-gray-300 text-[#a52a2a] focus:ring-[#a52a2a] h-4 w-4 cursor-pointer transition-all" value="{{ $access->id }}" onchange="window.updateBulkDeleteBtn()"></td>
                                    <td class="px-6 py-4 font-medium text-gray-900 email-cell">{{ $access->email }}</td>
                                    <td class="px-6 py-4 name-cell {{ $access->student ? 'text-gray-800' : '' }}">{{ $access->student ? $access->student->first_name . ' ' . $access->student->last_name : 'Unregistered' }}</td>
                                    <td class="px-6 py-4 text-center status-cell">
                                        @if($access->status === 'enrolled')
                                            <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-md text-[10px] font-bold uppercase tracking-wider border border-green-200">Enrolled</span>
                                        @else
                                            <div class="flex flex-col items-center justify-center gap-1.5">
                                                <span class="status-badge px-2.5 py-1 {{ $access->status === 'invited' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-amber-100 text-amber-700 border-amber-200' }} rounded-md text-[10px] font-bold uppercase tracking-wider border">{{ strtoupper($access->status) }}</span>
                                                <button type="button" onclick="sendIndividualInvite(this, '{{ $access->id }}')" class="invite-btn text-blue-600 hover:text-blue-800 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1 transition-colors group"><i class="fas fa-paper-plane group-hover:-translate-y-0.5 transition-transform"></i><span class="btn-text">{{ $access->status === 'invited' ? 'Send Again' : 'Send Invite' }}</span></button>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center"><button type="button" onclick="window.openDeleteModal('{{ $access->id }}')" class="text-gray-400 hover:text-red-500 transition" title="Remove Access"><i class="fas fa-times-circle"></i></button></td>
                                </tr>
                            @empty
                                <tr id="empty-state-row">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl border border-gray-100"><i class="fas fa-users-slash"></i></div>
                                        <p class="font-medium text-gray-500">No students found.</p><p class="text-xs mt-1">Add emails above to grant access.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="pagination-wrapper" class="rounded-xl mt-1 hidden flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <div class="text-sm text-gray-500 mb-3 sm:mb-0">Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span id="page-end-info" class="font-bold text-gray-900">0</span> of <span id="page-total-info" class="font-bold text-gray-900">0</span> results</div>
                    <div class="flex items-center gap-1" id="pagination-controls"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRADING & CERTIFICATION SETTINGS --}}
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-8">
        
        @if($isLive)
            <div class="mb-6 p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-200 flex items-start gap-3">
                <i class="fas fa-lock mt-0.5"></i>
                <div class="text-sm">
                    <strong class="font-bold">Grading Configuration Locked</strong>
                    <p class="mt-1">This module is currently Published. To protect student progress, you must switch it back to Draft Mode at the top of the page to adjust grading settings.</p>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-4 mb-2">
            <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fas fa-award"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Grading & Certification Configuration</h3>
        </div>
        <p class="text-sm text-gray-500 mb-8 ml-14">Configure how students are evaluated to receive their completion certificate.</p>

        @php
            $isWeightDisabled = $isLive || (!$hasExams || !$hasQuizzes);
            $isPassingDisabled = $isLive;
        @endphp

        @if(!$hasExams && !$hasQuizzes)
             <div class="p-5 bg-gray-50 border border-gray-200 rounded-xl flex items-start gap-4 ml-14">
                 <i class="fas fa-info-circle text-gray-400 text-xl mt-0.5"></i>
                 <div>
                    <p class="text-sm font-bold text-gray-700">No Assessments Detected</p>
                    <p class="text-sm text-gray-500 mt-1">This module currently has no quizzes or exams. Students won't receive any certificates from this material.</p>
                 </div>
             </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 ml-14">
                
                {{-- SLIDER 1: Weight Distribution --}}
                <div class="relative">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Assessment Weights</h4>
                            <p class="text-xs text-gray-500 mt-1">Adjust the impact of Quizzes vs. Final Exam.</p>
                        </div>
                        @if(!$hasExams || !$hasQuizzes)
                            <span class="text-[10px] font-bold uppercase tracking-widest bg-gray-100 text-gray-400 px-2 py-1 rounded">Locked</span>
                        @endif
                    </div>

                    <div class="relative w-full mt-2">
                        <input type="range" id="weight-slider" min="0" max="100" value="{{ $quizWeight }}"
                               class="{{ $isWeightDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                               {{ $isWeightDisabled ? 'disabled' : '' }}
                               oninput="window.updateWeightUI()"
                               style="color: #6b7280;">
                               
                        <div class="flex justify-between mt-4 text-sm font-bold">
                            <span id="quiz-weight-text" class="{{ $hasQuizzes ? 'text-yellow-600' : 'text-gray-300' }}">Quizzes: {{ $quizWeight }}%</span>
                            <span id="exam-weight-text" class="{{ $hasExams ? 'text-red-600' : 'text-gray-300' }}">Exam: {{ $savedExamWeight }}%</span>
                        </div>
                    </div>
                </div>

                {{-- SLIDER 2: Passing Percentage --}}
                <div class="relative border-t md:border-t-0 md:border-l border-gray-100 pt-8 md:pt-0 md:pl-12">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Passing Grade Required</h4>
                            <p class="text-xs text-gray-500 mt-1">Minimum overall score to earn the certificate.</p>
                        </div>
                        <span id="passing-percentage-text" class="text-2xl font-black text-green-600">{{ $savedPassingPercentage }}%</span>
                    </div>

                    <input type="range" id="passing-slider" min="0" max="100" value="{{ $savedPassingPercentage }}"
                           class="{{ $isPassingDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                           {{ $isPassingDisabled ? 'disabled' : '' }}
                           oninput="window.updatePassingUI()"
                           style="color: #16a34a;">

                    {{-- Zero Percent Warning --}}
                    <div id="zero-percent-warning" class="mt-5 p-4 bg-amber-50 border border-amber-200 rounded-xl items-start gap-3 transition-all duration-300 {{ $savedPassingPercentage == 0 ? 'flex' : 'hidden' }}">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-amber-800">No Grading Enforced</p>
                            <p class="text-xs text-amber-700 mt-1 leading-relaxed">At 0%, student answers will not be strictly graded. They can claim the certificate simply by traversing all materials and completing the assessments regardless of their final score.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                 <button type="button" onclick="window.saveGradingSettings(this)" 
                    class="px-6 py-3 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $isLive ? 'disabled' : '' }}>
                     <i class="fas fa-save"></i> <span>Save Grading Settings</span>
                 </button>
            </div>
        @endif
    </div>

    <div class="bg-red-50 rounded-3xl p-6 border border-red-100 mt-8">
        <h3 class="text-red-800 font-bold mb-2">Danger Zone</h3>
        <p class="text-sm text-red-600 mb-4">Deleting this module will permanently remove it and all associated content. This action cannot be undone.</p>
        <button onclick="window.openMaterialDeleteModal()" class="px-6 py-2.5 bg-white border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
            Delete Module
        </button>
    </div>

    {{-- Modals remain unchanged --}}
    <div id="material-delete-modal" class="fixed inset-0 z-[100] hidden h-full">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="window.closeMaterialDeleteModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">
                <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"><i class="fas fa-trash-alt"></i></div>
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
                <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"><i class="fas fa-exclamation-triangle"></i></div>
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
    // --- Grading Logic ---
    window.updateWeightUI = function() {
        const slider = document.getElementById('weight-slider');
        if (!slider) return;
        
        const quizWeight = parseInt(slider.value);
        const examWeight = 100 - quizWeight;
        
        document.getElementById('quiz-weight-text').innerText = `Quizzes: ${quizWeight}%`;
        document.getElementById('exam-weight-text').innerText = `Exam: ${examWeight}%`;
        
        // Dynamic Gradient: Yellow (#eab308) for Quizzes on Left, Red (#ef4444) for Exam on Right
        slider.style.background = `linear-gradient(to right, #eab308 ${quizWeight}%, #ef4444 ${quizWeight}%)`;
    };

    window.updatePassingUI = function() {
        const slider = document.getElementById('passing-slider');
        if (!slider) return;
        
        const val = parseInt(slider.value);
        document.getElementById('passing-percentage-text').innerText = `${val}%`;

        // Dynamic Gradient: Green (#16a34a) on Left, Gray (#e5e7eb) on Right
        slider.style.background = `linear-gradient(to right, #16a34a ${val}%, #e5e7eb ${val}%)`;

        const warning = document.getElementById('zero-percent-warning');
        if (val === 0) {
            warning.classList.remove('hidden');
            warning.classList.add('flex');
        } else {
            warning.classList.remove('flex');
            warning.classList.add('hidden');
        }
    };

    // Initialize CSS Gradients on Page Load
    setTimeout(() => {
        if(document.getElementById('weight-slider')) window.updateWeightUI();
        if(document.getElementById('passing-slider')) window.updatePassingUI();

        // Auto-sync if an anomaly was detected (e.g. Exam deleted in builder)
        @if($needsWeightSync)
            console.log("Anomaly detected: Syncing grading weights based on available content...");
            window.saveGradingSettings(null, true);
        @endif
    }, 50);

    window.saveGradingSettings = async function(btn = null, isSilent = false) {
        // We get quiz weight from the slider, so examWeight is 100 - quizWeight
        const quizWeight = document.getElementById('weight-slider') ? parseInt(document.getElementById('weight-slider').value) : 0;
        const examWeight = 100 - quizWeight; 
        
        const passingScore = document.getElementById('passing-slider') ? parseInt(document.getElementById('passing-slider').value) : 0;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        let originalHtml = '';
        if (btn) {
            originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Saving...</span>';
            btn.disabled = true;
        }

        try {
            const response = await fetch(`/dashboard/materials/{{ $material->id }}/grading`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ exam_weight: examWeight, passing_percentage: passingScore })
            });
            const data = await response.json();
            if(!response.ok) throw new Error(data.message || "Server Error");
            
            if (!isSilent) {
                showSnackbar('Grading settings saved successfully!', 'success');
            }

        } catch (e) {
            if (!isSilent) {
                showSnackbar(e.message || 'Failed to save grading settings.', 'error');
            }
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    };


    // --- Individual Invite Logic ---
    window.sendIndividualInvite = async function (btn, accessId) {
        const btnTextEl = btn.querySelector('.btn-text');
        const originalHtml = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        btn.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        try {
            const response = await fetch(`/dashboard/materials/access/${accessId}/invite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSnackbar('Invitation sent successfully!', 'success');

                btn.innerHTML = '<i class="fas fa-paper-plane"></i> <span class="btn-text">Send Again</span>';

                const container = btn.closest('div');
                const badge = container.querySelector('.status-badge');
                if (badge) {
                    badge.innerText = 'INVITED';
                    badge.className = 'status-badge px-2.5 py-1 bg-blue-100 text-blue-700 border-blue-200 rounded-md text-[10px] font-bold uppercase tracking-wider border';
                }
            } else {
                showSnackbar(data.message || 'Failed to send invite.', 'error');
                btn.innerHTML = originalHtml;
            }
        } catch (error) {
            showSnackbar('A network error occurred.', 'error');
            btn.innerHTML = originalHtml;
        } finally {
            btn.disabled = false;
        }
    };

    // --- Visibility Toggle Logic ---
    window.toggleVisibility = async function (checkbox) {
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

                const banner = document.getElementById('visibility-banner');
                const icon = document.getElementById('visibility-icon');
                const title = document.getElementById('visibility-title');
                const desc = document.getElementById('visibility-desc');
                const contentWrapper = document.getElementById('access-management-content');
                const accessCodeDisplay = document.getElementById('access-code-display');

                if (isPublic) {
                    banner.className = "flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border transition-colors duration-300 bg-green-50 border-green-200 mb-0";
                    icon.className = "fas fa-globe-asia text-green-600";
                    title.innerText = "Public Material";
                    desc.innerText = "Anyone on the platform can view this module. The access list below is currently ignored.";
                    desc.className = "text-sm mt-1 text-green-700";

                    contentWrapper.classList.remove('max-h-[2000px]', 'opacity-100');
                    contentWrapper.classList.add('max-h-0', 'opacity-0');

                    if (accessCodeDisplay) accessCodeDisplay.classList.add('opacity-50', 'pointer-events-none');
                } else {
                    banner.className = "flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border transition-colors duration-300 bg-gray-50 border-gray-200 mb-6";
                    icon.className = "fas fa-lock text-gray-500";
                    title.innerText = "Private Material";
                    desc.innerText = "Only the specific Emails listed below can view this module.";
                    desc.className = "text-sm mt-1 text-gray-500";

                    contentWrapper.classList.remove('max-h-0', 'opacity-0');
                    contentWrapper.classList.add('max-h-[2000px]', 'opacity-100');

                    if (accessCodeDisplay) accessCodeDisplay.classList.remove('opacity-50', 'pointer-events-none');
                }
            } else {
                checkbox.checked = !checkbox.checked;
                throw new Error(data.message || 'Failed to update visibility.');
            }
        } catch (error) {
            console.error(error);
            showSnackbar(error.message || 'A network error occurred.', 'error');
            checkbox.checked = !checkbox.checked;
        } finally {
            checkbox.disabled = false;
        }
    };

    // --- Bulk Send Emails Logic ---
    window.notifyStudents = async function (btn) {
    const rows = document.querySelectorAll('#students-tbody .student-row');
    const pendingAccesses = [];

    // 1. Gather only the rows that are explicitly 'PENDING'
    rows.forEach(row => {
        const badge = row.querySelector('.status-badge');
        if (badge && badge.innerText.trim().toUpperCase() === 'PENDING') {
            const checkbox = row.querySelector('.email-checkbox');
            if (checkbox && checkbox.value) {
                pendingAccesses.push({ id: checkbox.value, row: row });
            }
        }
    });

    if (pendingAccesses.length === 0) {
        showSnackbar('There are no pending students to invite.', 'info');
        return;
    }

    const originalHtml = btn.innerHTML;
    btn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    let successCount = 0;

    try {
        // 2. Loop through only the pending students and hit the individual invite route
        for (let i = 0; i < pendingAccesses.length; i++) {
            const access = pendingAccesses[i];
            
            // Update button text to show progress
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Sending ${i + 1}/${pendingAccesses.length}...`;
            
            const response = await fetch(`/dashboard/materials/access/${access.id}/invite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successCount++;
                
                // 3. Instantly update the row UI for this specific student
                const badge = access.row.querySelector('.status-badge');
                if (badge) {
                    badge.innerText = 'INVITED';
                    badge.className = 'status-badge px-2.5 py-1 bg-blue-100 text-blue-700 border-blue-200 rounded-md text-[10px] font-bold uppercase tracking-wider border';
                }
                const btnText = access.row.querySelector('.invite-btn .btn-text');
                if (btnText) {
                    btnText.innerText = 'Send Again';
                }
            }
        }

        if (successCount > 0) {
            showSnackbar(`Successfully sent invites to ${successCount} pending student(s).`, 'success');
        } else {
            showSnackbar('Failed to send emails. Please try again.', 'error');
        }
    } catch (error) {
        showSnackbar('A network error occurred during the sending process.', 'error');
    } finally {
        // Restore the original button state
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
};

    // --- Toggle Material Status ---
    window.toggleMaterialStatus = async function (checkbox) {
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
                
                setTimeout(() => window.location.reload(), 1000);
            } else {
                checkbox.checked = !checkbox.checked;
                throw new Error(data.message || 'Failed to update status.');
            }
        } catch (error) {
            console.error(error);
            showSnackbar(error.message || 'A network error occurred.', 'error');
            checkbox.checked = !checkbox.checked;
        } finally {
            checkbox.disabled = false;
        }
    };

    // --- Snackbar Logic ---
    var snackbarTimeout;
    window.showSnackbar = function (message, type = 'error') {
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

    window.closeSnackbar = function () {
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
            const email = row.querySelector('.email-cell').innerText.toLowerCase();
            const name = row.querySelector('.name-cell').innerText.toLowerCase();
            return email.includes(query) || name.includes(query);
        });

        if (sortColumnIndex !== null) {
            currentRows.sort((a, b) => {
                let valA = a.cells[sortColumnIndex].getAttribute('data-value') || a.cells[sortColumnIndex].innerText.trim();
                let valB = b.cells[sortColumnIndex].getAttribute('data-value') || b.cells[sortColumnIndex].innerText.trim();
                if (sortType === 'numeric') {
                    return sortIsAscending ? valA.localeCompare(valB, undefined, { numeric: true }) : valB.localeCompare(valA, undefined, { numeric: true });
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
            btn.className = `px-3 py-1 min-w-[32px] rounded-lg text-sm font-bold transition-all border ${active
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

    window.filterStudents = function () { currentPage = 1; applyFilterAndSort(); };

    window.sortTable = function (columnIndex, type) {
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

    window.toggleSelectAll = function (selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.email-checkbox');
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = selectAllCheckbox.checked;
            }
        });
        updateBulkDeleteBtn();
    };

    window.updateBulkDeleteBtn = function () {
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
            if (selectAllCheck) selectAllCheck.checked = false;
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

    // --- Add Email Access Logic ---
    window.submitEmail = async function (btn) {
        const emailInput = document.getElementById('student-email-input');
        const emailValue = emailInput.value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

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
                body: JSON.stringify({ email: emailValue })
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

    window.openDeleteModal = function (id) {
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

    window.importEmailList = async function (input) {
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

    window.closeDeleteModal = function () {
        document.getElementById('student-delete-modal').classList.add('hidden');
        window.targetsToDelete = [];
    };

    window.executeDelete = async function () {
        if (!window.targetsToDelete || window.targetsToDelete.length === 0) return;

        const btn = document.getElementById('confirm-delete-btn');
        const originalHtml = btn.innerHTML;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        btn.disabled = true;

        try {
            let successCount = 0;

            for (const id of window.targetsToDelete) {
                let deleteUrl = `/dashboard/materials/access/${id}`;

                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    let errMsg = `Server Error: ${response.status}`;
                    try {
                        const errData = await response.json();
                        errMsg = errData.message || errMsg;
                    } catch (e) {
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
            showSnackbar(error.message || 'A network error occurred.', 'error');
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            window.targetsToDelete = [];
        }
    };

    // --- Material Delete Logic ---
    window.openMaterialDeleteModal = function () {
        document.getElementById('material-delete-modal').classList.remove('hidden');
    };

    window.closeMaterialDeleteModal = function () {
        document.getElementById('material-delete-modal').classList.add('hidden');
    };

    window.executeMaterialDelete = async function () {
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
                let data = {}; try { data = await response.json(); } catch (e) { }
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
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
                btn.classList.add('text-gray-600', 'hover:bg-gray-100');
            });

            materialsBtn.classList.remove('text-gray-600', 'hover:bg-gray-100');
            materialsBtn.classList.add('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
        }
    }, 50);

    // --- Tags Autocomplete & Management Logic ---
    window.availableTags = [
        "Science", "Earth Science", "Computer Science", "Biology", "Chemistry", "Physics",
        "Mathematics", "Algebra", "Calculus", "Geometry",
        "English", "Literature", "Grammar", "Filipino", "Pananaliksik",
        "MAPEH", "Music", "Arts", "Physical Education", "Health",
        "History", "World History", "Philippine History", "Contemporary Issues",
        "Technology", "Programming", "Web Development", "First Aid"
    ];

    window.currentTags = {!! json_encode($material->tags ? $material->tags->pluck('name') : []) !!};

    setTimeout(() => { window.renderActiveTags(); }, 50);

    window.handleTagKeydown = function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = e.target.value.trim();
            if (query !== '') {
                window.addTag(query);
            }
        }
    };

    window.handleTagInput = function (e) {
        const query = e.target.value.trim().toLowerCase();
        const suggestionsBox = document.getElementById('tag-suggestions');

        if (query === '') {
            if (suggestionsBox) suggestionsBox.classList.add('hidden');
            return;
        }

        const matchedTags = window.availableTags.filter(tag => {
            const isNotAdded = !window.currentTags.map(t => t.toLowerCase()).includes(tag.toLowerCase());
            const matchesQuery = tag.toLowerCase().includes(query);
            return isNotAdded && matchesQuery;
        });

        window.renderSuggestions(matchedTags, query);
    };

    window.renderSuggestions = function (tags, query) {
        const suggestionsBox = document.getElementById('tag-suggestions');
        if (!suggestionsBox) return;

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

            div.onclick = function () {
                window.addTag(tag);
            };
            suggestionsBox.appendChild(div);
        });

        suggestionsBox.classList.remove('hidden');
    }

    window.addTag = async function (tagValue) {
        const tagInput = document.getElementById('tag-input');
        const suggestionsBox = document.getElementById('tag-suggestions');

        if (window.currentTags.map(t => t.toLowerCase()).includes(tagValue.toLowerCase())) {
            if (tagInput) tagInput.value = '';
            if (suggestionsBox) suggestionsBox.classList.add('hidden');
            return;
        }

        window.currentTags.push(tagValue);
        if (tagInput) tagInput.value = '';
        if (suggestionsBox) suggestionsBox.classList.add('hidden');
        window.renderActiveTags();

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
            window.currentTags = window.currentTags.filter(t => t !== tagValue);
            window.renderActiveTags();
        }
    }

    window.removeTag = async function (tagValue) {
        window.currentTags = window.currentTags.filter(t => t !== tagValue);
        window.renderActiveTags();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        try {
            const safeTag = encodeURIComponent(tagValue);
            let url = '{{ route("dashboard.materials.tags.remove", ["material" => $material->id ?? 0, "tag" => "PLACEHOLDER_TAG"]) }}';
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
            window.currentTags.push(tagValue);
            window.renderActiveTags();
        }
    }

    window.renderActiveTags = function () {
        const activeTagsContainer = document.getElementById('active-tags-container');
        if (!activeTagsContainer) return;

        activeTagsContainer.innerHTML = '';

        if (window.currentTags.length === 0) {
            activeTagsContainer.innerHTML = '<span class="text-sm text-gray-400 italic">No tags added yet.</span>';
            return;
        }

        window.currentTags.forEach(tag => {
            const tagEl = document.createElement('div');
            tagEl.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] border border-[#a52a2a]/20 rounded-lg text-xs font-bold transition-all';

            const spanEl = document.createElement('span');
            spanEl.textContent = tag;

            const btnEl = document.createElement('button');
            btnEl.type = 'button';
            btnEl.className = 'text-[#a52a2a]/60 hover:text-[#a52a2a] hover:bg-[#a52a2a]/10 rounded-full h-4 w-4 flex items-center justify-center transition-colors';
            btnEl.innerHTML = '<i class="fas fa-times text-[10px]"></i>';
            btnEl.onclick = () => window.removeTag(tag);

            tagEl.appendChild(spanEl);
            tagEl.appendChild(btnEl);
            activeTagsContainer.appendChild(tagEl);
        });
    }

    window.onclickCloseSuggestions = function (e) {
        const tagInput = document.getElementById('tag-input');
        const suggestionsBox = document.getElementById('tag-suggestions');

        if (tagInput && suggestionsBox) {
            if (!tagInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('hidden');
            }
        }
    };

    document.removeEventListener('click', window.onclickCloseSuggestions);
    document.addEventListener('click', window.onclickCloseSuggestions);

    window.copyAccessCode = function (code) {
        if (!code || code === 'N/A') return;
        navigator.clipboard.writeText(code).then(() => {
            showSnackbar('Access code copied to clipboard!', 'success');
        }).catch(err => {
            showSnackbar('Failed to copy code.', 'error');
        });
    };

</script>