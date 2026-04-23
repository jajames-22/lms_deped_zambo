<head>
    <style>
        .toggle-container .toggle-track { background-color: #d1d5db; }
        .toggle-container .toggle-handle { transform: translateX(1px); }
        .toggle-container .toggle-input:checked+.toggle-track { background-color: #26da65; }
        .toggle-container .toggle-input:checked~.toggle-handle { transform: translateX(1.5rem); }
        .toggle-container .toggle-input:focus-visible+.toggle-track { box-shadow: 0 0 0 4px rgba(165, 42, 42, 0.4); }
        
        /* Custom Slider Styling to match the screenshot */
        input[type=range].custom-slider {
            -webkit-appearance: none; 
            width: 100%; 
            height: 12px; 
            border-radius: 999px; 
            outline: none;
            background: #e5e7eb; /* Fallback */
        }
        input[type=range].custom-slider::-webkit-slider-thumb {
            -webkit-appearance: none; 
            height: 24px; 
            width: 24px; 
            border-radius: 50%; 
            background: #ffffff; 
            border: 2px solid #9ca3af; /* Default gray border */
            cursor: pointer; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.15); 
            transition: transform 0.1s;
        }
        input[type=range].custom-slider::-moz-range-thumb {
            height: 24px; 
            width: 24px; 
            border-radius: 50%; 
            background: #ffffff; 
            border: 2px solid #9ca3af; 
            cursor: pointer; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }
        
        /* Thumb color overrides for specific sliders */
        #weight-slider::-webkit-slider-thumb { border-color: #6b7280; } /* Darker gray to pop against colors */
        #weight-slider::-moz-range-thumb { border-color: #6b7280; }
        
        #passing-slider::-webkit-slider-thumb { border-color: #22c55e; } /* Green border */
        #passing-slider::-moz-range-thumb { border-color: #22c55e; }

        /* Disabled State */
        input[type=range].custom-slider:disabled::-webkit-slider-thumb { 
            background: #f3f4f6; border-color: #d1d5db; cursor: not-allowed; box-shadow: none; 
        }
    </style>
</head>

@php
    $isOwner = auth()->id() === $material->instructor_id;
    $isAdminOrCid = in_array(auth()->user()->role, ['admin', 'cid']);

    // --- CHECK FOR ASSESSMENTS & GRADING RULES ---
    $hasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
    $hasQuizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
        ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
        ->where('lessons.material_id', $material->id)
        ->whereIn('lesson_contents.type', ['mcq', 'checkbox', 'true_false', 'text']) 
        ->exists();

    $savedExamWeight = $material->exam_weight ?? 60;
    $savedPassingPercentage = $material->passing_percentage ?? 80;
    
    // Auto-adjust weights if one type is missing
    if ($hasExams && !$hasQuizzes) { $savedExamWeight = 100; }
    elseif (!$hasExams && $hasQuizzes) { $savedExamWeight = 0; }
    elseif (!$hasExams && !$hasQuizzes) { $savedExamWeight = 0; }
    
    $quizWeight = 100 - $savedExamWeight;
    
    // Draft Lock Check
    $isLocked = $material->status !== 'draft';
@endphp

<div class="animate-float-in">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-6">
        <div class="flex items-center gap-4">
            <button onclick="loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn'))"
                class="h-10 w-10 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-full flex items-center justify-center transition border border-gray-200 shrink-0">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div>
                <h1 id="header-title-display" class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ $material->title }}</h1>
                <p class="text-sm text-gray-500 font-medium">Manage module settings, tags, and access</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            
            {{-- Preview Button --}}
            <a href="{{ url('/dashboard/materials/'.$material->id.'/preview') }}" class="px-4 py-2.5 bg-white text-gray-700 border border-gray-200 font-bold rounded-xl hover:bg-gray-50 transition shadow-sm flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-desktop text-[#a52a2a]"></i> Preview
            </a>

            @if($isOwner || $isAdminOrCid)
                {{-- Evaluation Result Button --}}
                <a href="{{ url('/dashboard/materials/'.$material->id.'/evaluation-result') }}" class="px-4 py-2.5 bg-white text-gray-700 border border-gray-200 font-bold rounded-xl hover:bg-gray-50 transition shadow-sm flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-clipboard-check text-blue-600"></i> Evaluation
                </a>
                
                {{-- View Analytics Button --}}
                @if($material->status === 'published')
                <button onclick="loadPartial('{{ url('/dashboard/materials/'.$material->id.'/analytics') }}')" class="px-4 py-2.5 bg-white text-gray-700 border border-gray-200 font-bold rounded-xl hover:bg-gray-50 transition shadow-sm flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-chart-pie text-amber-600"></i> Analytics
                </button>
                @endif
            @endif

            {{-- Edit Content Button (LOCKED IF NOT DRAFT) --}}
            @if(!$isLocked)
                <button onclick="loadPartial('{{ url('/dashboard/materials/'.$material->id.'/edit') }}')" class="px-4 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-edit"></i> Content
                </button>
            @else
                <button disabled title="Revert to draft to edit content" class="px-4 py-2.5 bg-gray-50 text-gray-400 border border-gray-200 font-bold rounded-xl flex items-center justify-center gap-2 text-sm cursor-not-allowed opacity-70">
                    <i class="fas fa-lock"></i> Content Locked
                </button>
            @endif
            
            {{-- Status & Publish/Revert Actions --}}
            @if($material->status === 'draft')
                <button onclick="attemptPublish()" class="px-5 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl shadow-md shadow-[#a52a2a]/20 hover:bg-red-800 transition flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-paper-plane"></i> Submit for Review
                </button>
            @else
                <button onclick="revertToDraft()" class="px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-700 font-bold rounded-xl hover:bg-amber-100 transition flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-undo"></i> Revert to Draft
                </button>

                @if($material->status === 'pending')
                    @if($isAdminOrCid)
                        {{-- ADMIN/CID Evaluate Button (Standard Link for Full Screen) --}}
                        <a href="{{ url('/dashboard/materials/'.$material->id.'/evaluate') }}" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-md shadow-blue-600/20 hover:bg-blue-700 transition flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-clipboard-list"></i> Evaluate to Publish
                        </a>
                    @else
                        {{-- Teacher Pending Badge --}}
                        <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 font-bold rounded-xl flex items-center justify-center gap-2 cursor-default text-sm">
                            <i class="fas fa-clock"></i> Pending Review
                        </div>
                    @endif
                @else
                    <div class="px-4 py-2.5 bg-green-50 border border-green-200 text-green-700 font-bold rounded-xl flex items-center justify-center gap-2 cursor-default text-sm">
                        <i class="fas fa-check-circle"></i> Published
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- DASHBOARD GRID LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN: Details, Tables & Analytics --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- BASIC INFORMATION EDIT --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 flex flex-col md:flex-row gap-6 relative overflow-hidden">
                
                {{-- Watermark if locked --}}
                @if($isLocked)
                    <div class="absolute -right-10 -top-10 text-gray-50 opacity-50 transform rotate-12 pointer-events-none z-0">
                        <i class="fas fa-lock text-9xl"></i>
                    </div>
                @endif

                {{-- Thumbnail Upload --}}
                <div class="w-full md:w-1/3 flex flex-col gap-3 relative z-10">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Thumbnail</label>
                    <div class="aspect-[4/3] rounded-2xl bg-gray-100 border-2 border-dashed border-gray-300 transition-colors overflow-hidden relative {{ !$isLocked ? 'hover:border-[#a52a2a] group cursor-pointer' : 'opacity-80 cursor-not-allowed' }}" 
                        @if(!$isLocked) onclick="document.getElementById('thumbnailInput').click()" @endif>
                        
                        <img id="thumbnailPreview" src="{{ $material->thumbnail ? asset('storage/'.$material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=800' }}" class="w-full h-full object-cover">
                        
                        @if(!$isLocked)
                            <div class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-camera text-white text-2xl mb-2"></i>
                                <span class="text-white font-bold text-xs uppercase tracking-wider mt-1">Change Photo</span>
                            </div>
                        @else
                            <div class="absolute inset-0 bg-gray-900/40 flex items-center justify-center">
                                <div class="bg-black/50 p-3 rounded-full"><i class="fas fa-lock text-white text-xl"></i></div>
                            </div>
                        @endif
                    </div>
                    @if(!$isLocked)
                        <input type="file" id="thumbnailInput" class="hidden" accept="image/*" onchange="previewThumbnail(this)">
                    @endif
                </div>

                {{-- Text Details --}}
                <div class="w-full md:w-2/3 flex flex-col gap-4 relative z-10">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Module Title</label>
                        <input type="text" id="materialTitle" value="{{ $material->title }}" {{ $isLocked ? 'disabled' : '' }} 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all font-bold {{ $isLocked ? 'text-gray-500 cursor-not-allowed' : 'text-gray-900' }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Description</label>
                        <textarea id="materialDescription" rows="4" {{ $isLocked ? 'disabled' : '' }} 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm resize-none leading-relaxed {{ $isLocked ? 'text-gray-500 cursor-not-allowed' : 'text-gray-700' }}">{{ $material->description }}</textarea>
                    </div>
                    <div class="flex justify-end mt-auto pt-2">
                        <button id="saveDetailsBtn" onclick="saveMaterialDetails()" 
                            class="px-6 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl transition shadow-md flex items-center gap-2 {{ $isLocked ? 'opacity-50 cursor-not-allowed grayscale' : 'hover:bg-red-800 shadow-[#a52a2a]/20' }}"
                            {{ $isLocked ? 'disabled title="Revert to draft to edit details"' : '' }}>
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>

            {{-- GRADING & CERTIFICATION SETTINGS --}}
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-award"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Grading & Certification</h3>
                        <p class="text-xs text-gray-500 mt-1">Configure evaluation standards for this module.</p>
                    </div>
                </div>

                @php
                    $isWeightDisabled = $material->status !== 'draft' || (!$hasExams || !$hasQuizzes);
                    $isPassingDisabled = $material->status !== 'draft';
                @endphp

                @if(!$hasExams && !$hasQuizzes)
                    <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-xl flex items-start gap-3">
                        <i class="fas fa-info-circle text-gray-400 text-lg mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-gray-700">No Assessments Detected</p>
                            <p class="text-xs text-gray-500 mt-1">This module currently has no quizzes or exams. Students won't receive certificates.</p>
                        </div>
                    </div>
                @else
                    {{-- UI MATCHING THE SCREENSHOT --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 mt-2 border-t border-gray-100 pt-6">
                        
                        {{-- SLIDER 1: Weight Distribution --}}
                        <div class="pr-0 md:pr-8 pb-6 md:pb-0">
                            <h4 class="text-base font-bold text-gray-800">Assessment Weights</h4>
                            <p class="text-xs text-gray-500 mb-6 mt-1">Adjust the impact of Quizzes vs. Final Exam.</p>
                            
                            <input type="range" id="weight-slider" min="0" max="100" value="{{ $quizWeight }}"
                                class="custom-slider {{ $isWeightDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $isWeightDisabled ? 'disabled' : '' }} oninput="window.updateWeightUI()">
                            
                            <div class="flex justify-between mt-4 text-sm font-bold">
                                <span id="quiz-weight-text" class="text-amber-500">Quizzes: {{ $quizWeight }}%</span>
                                <span id="exam-weight-text" class="text-red-500">Exam: {{ $savedExamWeight }}%</span>
                            </div>
                        </div>

                        {{-- SLIDER 2: Passing Percentage --}}
                        <div class="pl-0 md:pl-8 pt-6 md:pt-0">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h4 class="text-base font-bold text-gray-800">Passing Grade Required</h4>
                                    <p class="text-xs text-gray-500 mt-1">Minimum overall score to earn the certificate.</p>
                                </div>
                                <span id="passing-percentage-text" class="text-3xl font-black text-green-600 leading-none">{{ $savedPassingPercentage }}%</span>
                            </div>
                            
                            <input type="range" id="passing-slider" min="0" max="100" value="{{ $savedPassingPercentage }}"
                                class="custom-slider {{ $isPassingDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $isPassingDisabled ? 'disabled' : '' }} oninput="window.updatePassingUI()">
                            
                            <div id="zero-percent-warning" class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl items-start gap-2 transition-all duration-300 {{ $savedPassingPercentage == 0 ? 'flex' : 'hidden' }}">
                                <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 text-xs"></i>
                                <p class="text-xs text-amber-700 font-medium">At 0%, answers aren't strictly graded. Certificates are awarded for completion.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-end">
                        <button type="button" onclick="window.saveGradingSettings(this)" 
                            class="px-6 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl transition-all shadow-sm flex items-center gap-2 {{ $isLocked ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-800' }}"
                            {{ $isLocked ? 'disabled title="Revert to draft to edit grading"' : '' }}>
                            <i class="fas fa-save"></i> Save Grading
                        </button>
                    </div>
                @endif
            </div>
            
            {{-- ACCESS MANAGEMENT (Whitelist) --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-6 md:p-8 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                    <div>
                        <h2 class="text-xl font-black text-gray-900">Access Management</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage who can view and enroll in this material</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="openModal('importStudentModal', 'importStudentBox')" class="h-10 px-4 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition text-sm font-bold shadow-sm">
                            <i class="fas fa-file-import mr-1.5"></i> Import CSV
                        </button>
                        <button onclick="openModal('addStudentModal', 'addStudentBox')" class="h-10 px-4 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl hover:bg-blue-100 transition text-sm font-bold shadow-sm">
                            <i class="fas fa-user-plus mr-1.5"></i> Add Student
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/50 text-[10px] uppercase text-gray-400 font-black tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Student Email</th>
                                <th class="px-6 py-4">Current Status</th>
                                <th class="px-6 py-4">Progress / Score</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="student-list-body">
                            @forelse($whitelistedStudents as $access)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-100">
                                                {{ strtoupper(substr($access->email, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 truncate max-w-[150px] sm:max-w-xs">{{ $access->email }}</p>
                                                @if($access->student)
                                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">{{ $access->student->first_name }} {{ $access->student->last_name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($access->status === 'enrolled')
                                            <span class="px-2.5 py-1 bg-green-50 text-green-700 text-[10px] font-black uppercase tracking-wider rounded-md border border-green-100">Enrolled</span>
                                        @elseif($access->status === 'invited')
                                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-wider rounded-md border border-blue-100">Invited</span>
                                        @elseif($access->status === 'dropped')
                                            <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-black uppercase tracking-wider rounded-md border border-red-100">Dropped</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-wider rounded-md border border-amber-100">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($access->current_enrollment)
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="font-bold text-gray-700">{{ ucfirst($access->current_enrollment->status) }}</span>
                                                    <span class="text-gray-400 font-mono">{{ $access->current_enrollment->score ?? '0' }}%</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 font-medium">No activity yet</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($access->status === 'pending' || $access->status === 'invited')
                                                <button onclick="sendIndividualInvite({{ $access->id }}, this)" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-500 hover:bg-blue-50 transition" title="Send Invitation Email">
                                                    <i class="fas fa-paper-plane text-xs"></i>
                                                </button>
                                            @endif
                                            <button onclick="revokeAccess({{ $access->id }}, this)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 transition" title="Revoke Access">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                            <i class="fas fa-users-slash text-2xl text-gray-300"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">No students added yet.</p>
                                        <p class="text-xs text-gray-400 mt-1">Add students manually or import a CSV list.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ANALYTICS OVERVIEW --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                <h2 class="text-xl font-black text-gray-900 mb-6">Analytics Overview</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Total Enrolled</p>
                        <h3 class="text-2xl font-black text-gray-900">{{ $whitelistedStudents->where('status', 'enrolled')->count() }}</h3>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Total Lessons</p>
                        <h3 class="text-2xl font-black text-gray-900">{{ $material->lessons_count ?? 0 }}</h3>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Assessment Items</p>
                        <h3 class="text-2xl font-black text-gray-900">{{ $material->items_count ?? 0 }}</h3>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Pass Rate</p>
                        <h3 class="text-2xl font-black text-green-600">--%</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Configuration Sidebar --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- VISIBILITY --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2"><i class="fas fa-eye text-[#a52a2a]"></i> Visibility</h3>
                
                <div class="mb-6">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <div>
                            <span class="text-sm font-bold text-gray-900 block group-hover:text-[#a52a2a] transition-colors">Public Access</span>
                            <span class="text-[10px] text-gray-500 uppercase tracking-wider">Visible in Explore Page</span>
                        </div>
                        <div class="relative toggle-container">
                            <input type="checkbox" id="publicToggle" class="sr-only toggle-input" onchange="toggleVisibility(this)" {{ $material->is_public ? 'checked' : '' }}>
                            <div class="block w-14 h-8 rounded-full transition-colors toggle-track border border-gray-200"></div>
                            <div class="absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform toggle-handle shadow-sm"></div>
                        </div>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-2">Direct Access Code</p>
                    <div class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200">
                        <code class="flex-1 text-center font-black text-gray-700 tracking-widest text-lg">{{ $material->access_code ?? 'N/A' }}</code>
                        <button onclick="copyAccessCode('{{ $material->access_code }}')" class="h-10 w-10 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-[#a52a2a] shadow-sm flex items-center justify-center transition" title="Copy Code">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAGS & CATEGORIZATION --}}
            <div id="tags-section" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-2 flex items-center gap-2"><i class="fas fa-tags text-[#a52a2a]"></i> Categorization</h3>
                <p class="text-xs text-gray-500 mb-5 leading-relaxed">Require at least one Grade and one Subject tag before publishing.</p>
                
                {{-- Quick Add Badges --}}
                <div class="mb-5">
                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-2">Quick Add Requirements:</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="submitTag('KINDERGARTEN')" class="px-2 py-1 text-[10px] font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-100 rounded-lg transition shadow-sm">KINDERGARTEN</button>
                        <button type="button" onclick="submitTag('GRADE 1')" class="px-2 py-1 text-[10px] font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-100 rounded-lg transition shadow-sm">GRADE 1</button>
                        <button type="button" onclick="submitTag('FILIPINO')" class="px-2 py-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-100 rounded-lg transition shadow-sm">FILIPINO</button>
                        <button type="button" onclick="submitTag('ENGLISH')" class="px-2 py-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-100 rounded-lg transition shadow-sm">ENGLISH</button>
                        <button type="button" onclick="submitTag('MATH')" class="px-2 py-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-100 rounded-lg transition shadow-sm">MATH</button>
                    </div>
                </div>

                {{-- Tag Input --}}
                <div class="relative w-full mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-tag text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" id="tag-input" placeholder="Type custom tag & press Enter"
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm text-gray-700 font-medium">
                </div>

                {{-- Active Tags Container --}}
                <div id="active-tags-container" class="flex flex-wrap gap-2">
                    @foreach($material->tags as $tag)
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#a52a2a]/10 border border-[#a52a2a]/20 text-[#a52a2a] text-xs font-black uppercase tracking-wider rounded-lg shadow-sm">
                            <span>{{ $tag->name }}</span>
                            <button type="button" onclick="removeTag('{{ $tag->name }}')" class="text-[#a52a2a]/60 hover:text-[#a52a2a] hover:bg-[#a52a2a]/10 rounded-full h-4 w-4 flex items-center justify-center transition-colors">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- DANGER ZONE --}}
            <div class="bg-red-50 rounded-3xl border border-red-100 p-6 relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-red-100 opacity-50 transform rotate-12 pointer-events-none">
                    <i class="fas fa-exclamation-triangle text-8xl"></i>
                </div>
                <h3 class="text-sm font-black text-red-800 uppercase tracking-wider mb-2 relative z-10">Danger Zone</h3>
                <p class="text-xs text-red-600/80 mb-5 relative z-10 leading-relaxed">Permanently delete this module and wipe all student progress.</p>
                <button onclick="openModal('deleteConfirmModal', 'deleteConfirmBox')" class="w-full py-3 bg-white text-red-600 border border-red-200 font-bold rounded-xl shadow-sm hover:bg-red-600 hover:text-white transition relative z-10">
                    Delete Module
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODALS & OVERLAYS --}}
{{-- ========================================== --}}

{{-- Custom Alert Modal (Smooth transitions, no native alerts, NO blur) --}}
<div id="customAlertModal" class="fixed inset-0 z-[10000] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeCustomAlert()"></div>
    <div id="customAlertBox" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10">
        <div id="customAlertIconContainer" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl">
            <i id="customAlertIcon" class="fas fa-info"></i>
        </div>
        <h3 id="customAlertTitle" class="text-xl font-black text-gray-900 mb-2">Notice</h3>
        <p id="customAlertMessage" class="text-sm text-gray-500 mb-6"></p>
        <button type="button" id="customAlertBtn" onclick="closeCustomAlert()" class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
            Okay
        </button>
    </div>
</div>

{{-- Snackbar (For silent, non-blocking notifications) --}}
<div id="snackbar" class="fixed bottom-6 right-6 transform translate-y-24 opacity-0 transition-all duration-300 z-[9999] flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl font-medium text-sm border">
    <i id="snackbar-icon" class="fas fa-check-circle text-xl"></i>
    <span id="snackbar-message">Notification message</span>
</div>

{{-- 1. Publish Confirmation Modal --}}
<div id="publishConfirmModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('publishConfirmModal', 'publishConfirmBox')"></div>
    <div id="publishConfirmBox" class="bg-white rounded-3xl max-w-md w-full p-8 text-center shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="w-16 h-16 bg-[#a52a2a]/10 text-[#a52a2a] rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
            <i class="fas fa-paper-plane"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Submit for Review?</h3>
        <p class="text-gray-500 mb-6 text-sm leading-relaxed">Once submitted, this module will be reviewed by an Administrator before being published to the public explore page.</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeModal('publishConfirmModal', 'publishConfirmBox')" class="flex-1 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
            <button type="button" id="confirmPublishBtn" onclick="submitToPublish()" class="flex-1 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-[#a52a2a]/20 hover:bg-red-800 transition flex justify-center items-center">Yes, Submit</button>
        </div>
    </div>
</div>

{{-- 2. Revert to Draft Confirmation Modal --}}
<div id="revertConfirmModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('revertConfirmModal', 'revertConfirmBox')"></div>
    <div id="revertConfirmBox" class="bg-white rounded-3xl max-w-md w-full p-8 text-center shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
            <i class="fas fa-undo"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Revert to Draft?</h3>
        <p class="text-gray-500 mb-6 text-sm leading-relaxed">Are you sure you want to revert this module to draft? It will be hidden from the public explore page and students.</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeModal('revertConfirmModal', 'revertConfirmBox')" class="flex-1 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
            <button type="button" onclick="executeRevertToDraft()" id="executeRevertBtn" class="flex-1 py-3 bg-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-500/20 hover:bg-amber-600 transition flex justify-center items-center">Yes, Revert</button>
        </div>
    </div>
</div>

{{-- 3. Add Student Modal --}}
<div id="addStudentModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('addStudentModal', 'addStudentBox')"></div>
    <div id="addStudentBox" class="bg-white rounded-3xl max-w-sm w-full p-8 shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-900">Add Student</h3>
            <button onclick="closeModal('addStudentModal', 'addStudentBox')" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
        </div>
        <div class="mb-6">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Student Email Address</label>
            <div class="relative">
                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="email" id="newStudentEmail" placeholder="student@deped.gov.ph" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm font-medium">
            </div>
        </div>
        <button id="submitAddStudentBtn" onclick="submitAddStudent()" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition flex justify-center items-center shadow-lg shadow-blue-600/20">
            Grant Access
        </button>
    </div>
</div>

{{-- 4. Import Students Modal --}}
<div id="importStudentModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('importStudentModal', 'importStudentBox')"></div>
    <div id="importStudentBox" class="bg-white rounded-3xl max-w-sm w-full p-8 shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-900">Import List</h3>
            <button onclick="closeModal('importStudentModal', 'importStudentBox')" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
        </div>
        <div class="mb-6 text-center">
            <p class="text-sm text-gray-500 mb-4">Upload a CSV/Excel file containing a single column with the header <strong>"email"</strong>.</p>
            <input type="file" id="importFileInput" accept=".csv, .xlsx, .xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
        </div>
        <button id="submitImportBtn" onclick="submitImport()" class="w-full py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-black transition flex justify-center items-center shadow-lg shadow-gray-900/20">
            Upload & Import
        </button>
    </div>
</div>

{{-- 5. Delete Module Modal --}}
<div id="deleteConfirmModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('deleteConfirmModal', 'deleteConfirmBox')"></div>
    <div id="deleteConfirmBox" class="bg-white rounded-3xl max-w-md w-full p-8 text-center shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Delete Module?</h3>
        <p class="text-gray-500 mb-6 text-sm leading-relaxed">This action cannot be undone. All lessons, content, assessments, and student progress will be permanently erased.</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeModal('deleteConfirmModal', 'deleteConfirmBox')" class="flex-1 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
            <button type="button" onclick="executeDelete()" id="executeDeleteBtn" class="flex-1 py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-600/20 hover:bg-red-700 transition flex justify-center items-center">Delete Permanently</button>
        </div>
    </div>
</div>

<script>
    // Initialize Gradient Sliders on Load
    document.addEventListener('DOMContentLoaded', () => {
        if(document.getElementById('weight-slider')) {
            window.updateWeightUI();
            window.updatePassingUI();
        }
    });

    // --- GRADING UI LOGIC (Matches Image Gradients) ---
    window.updateWeightUI = function() {
        const slider = document.getElementById('weight-slider');
        if (!slider) return;
        const quizWeight = parseInt(slider.value);
        
        // Dynamic Gradient: Amber (left) to Red (right)
        slider.style.background = `linear-gradient(to right, #fbbf24 ${quizWeight}%, #ef4444 ${quizWeight}%)`;
        
        document.getElementById('quiz-weight-text').innerText = `Quizzes: ${quizWeight}%`;
        document.getElementById('exam-weight-text').innerText = `Exam: ${100 - quizWeight}%`;
    };

    window.updatePassingUI = function() {
        const slider = document.getElementById('passing-slider');
        if (!slider) return;
        const val = parseInt(slider.value);
        
        // Dynamic Gradient: Green (left) to Gray (right)
        slider.style.background = `linear-gradient(to right, #22c55e ${val}%, #e5e7eb ${val}%)`;
        
        document.getElementById('passing-percentage-text').innerText = `${val}%`;
        
        const warning = document.getElementById('zero-percent-warning');
        if (val === 0) {
            warning.classList.remove('hidden'); warning.classList.add('flex');
        } else {
            warning.classList.remove('flex'); warning.classList.add('hidden');
        }
    };

    // Initialize immediately when script runs (for AJAX loads)
    setTimeout(() => {
        if(document.getElementById('weight-slider')) {
            window.updateWeightUI();
        }
        if(document.getElementById('passing-slider')) {
            window.updatePassingUI();
        }
    }, 50);

    window.saveGradingSettings = async function(btn = null) {
        const quizWeight = document.getElementById('weight-slider') ? parseInt(document.getElementById('weight-slider').value) : 0;
        const passingScore = document.getElementById('passing-slider') ? parseInt(document.getElementById('passing-slider').value) : 0;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        let originalHtml = '';
        if (btn) {
            originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
            btn.disabled = true;
        }

        try {
            const response = await fetch(`/dashboard/materials/{{ $material->id }}/grading`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ exam_weight: 100 - quizWeight, passing_percentage: passingScore })
            });
            const data = await response.json();
            
            if(response.ok && data.success) {
                showCustomAlert('Success', 'Grading settings saved successfully!', 'success');
            } else {
                throw new Error(data.message || "Failed to save.");
            }
        } catch (e) {
            showCustomAlert('Error', e.message, 'error');
        } finally {
            if (btn) { btn.innerHTML = originalHtml; btn.disabled = false; }
        }
    };

    // --- MODAL ANIMATION HELPERS ---
    function openModal(modalId, boxId) {
        const modal = document.getElementById(modalId);
        const box = document.getElementById(boxId);
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    }

    function closeModal(modalId, boxId) {
        const modal = document.getElementById(modalId);
        const box = document.getElementById(boxId);
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // --- CUSTOM ALERT MODAL (Replaces window.alert) ---
    window.showCustomAlert = function (title, message, type = 'error', callback = null) {
        const modal = document.getElementById('customAlertModal');
        const box = document.getElementById('customAlertBox');
        const iconContainer = document.getElementById('customAlertIconContainer');
        const icon = document.getElementById('customAlertIcon');

        document.getElementById('customAlertTitle').innerText = title;
        document.getElementById('customAlertMessage').innerText = message;
        document.getElementById('customAlertBtn').innerText = 'Okay'; // Reset button text
        
        window.customAlertCallback = callback;

        if (type === 'success') {
            iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-green-100 text-green-500';
            icon.className = 'fas fa-check-circle';
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
            if(window.customAlertCallback) window.customAlertCallback();
            window.customAlertCallback = null; // Clear callback after execution
        }, 300);
    };

    // --- SNACKBAR ALERT ---
    window.showSnackbar = function (message, type = 'success') {
        const snackbar = document.getElementById('snackbar');
        const icon = document.getElementById('snackbar-icon');
        document.getElementById('snackbar-message').textContent = message;

        snackbar.className = `fixed bottom-6 right-6 transform translate-y-0 opacity-100 transition-all duration-300 z-[9999] flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl font-bold text-sm border`;

        if (type === 'success') {
            snackbar.classList.add('bg-white', 'text-gray-800', 'border-gray-100');
            icon.className = 'fas fa-check-circle text-green-500 text-xl';
        } else {
            snackbar.classList.add('bg-red-600', 'text-white', 'border-red-700');
            icon.className = 'fas fa-exclamation-circle text-white text-xl';
        }

        if (window.snackbarTimer) clearTimeout(window.snackbarTimer);
        window.snackbarTimer = setTimeout(() => {
            snackbar.classList.replace('translate-y-0', 'translate-y-24');
            snackbar.classList.replace('opacity-100', 'opacity-0');
        }, 4000);
    }

    // --- BASIC INFORMATION EDITING ---
    window.previewThumbnail = function(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('thumbnailPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    window.saveMaterialDetails = async function() {
        const btn = document.getElementById('saveDetailsBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

        const formData = new FormData();
        formData.append('_method', 'PUT'); 
        formData.append('title', document.getElementById('materialTitle').value);
        formData.append('description', document.getElementById('materialDescription').value);
        
        const thumbnailInput = document.getElementById('thumbnailInput');
        if (thumbnailInput.files.length > 0) {
            formData.append('thumbnail', thumbnailInput.files[0]);
        }

        try {
            const response = await fetch(`{{ url('/dashboard/materials/'.$material->id) }}`, {
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json' // Explicitly ask for JSON so Laravel returns proper 422 errors
                },
                body: formData
            });

            if (response.ok) {
                showSnackbar('Details updated successfully!', 'success');
                document.getElementById('header-title-display').textContent = document.getElementById('materialTitle').value;
            } else {
                // Read exact Laravel validation errors
                const data = await response.json().catch(() => null);
                let errorMsg = 'Failed to update details.';
                if (data && data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                } else if (data && data.message) {
                    errorMsg = data.message;
                }
                showCustomAlert('Update Failed', errorMsg, 'error');
            }
        } catch (error) {
            // Handle complete network failure or unexpected redirect crash
            showCustomAlert('Error', 'An unexpected error occurred while saving.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    // --- STATUS UPDATES (PUBLISH & REVERT) ---
    window.attemptPublish = function() {
        const tags = window.getActiveTags().map(t => t.toUpperCase());
        
        const gradeRegex = /^(KINDERGARTEN|GRADE\s*([1-9]|1[0-2]))$/;
        const hasGrade = tags.some(tag => gradeRegex.test(tag));
        const hasSubject = tags.some(tag => !gradeRegex.test(tag));

        if (!hasGrade || !hasSubject) {
            showCustomAlert('Missing Tags', 'You must add at least one Grade Level and one Subject tag before publishing.', 'error');
            
            const tagsSection = document.getElementById('tags-section');
            if(tagsSection) tagsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            const tagInputWrap = document.getElementById('tag-input').parentElement;
            tagInputWrap.classList.add('ring-2', 'ring-red-500');
            setTimeout(() => tagInputWrap.classList.remove('ring-2', 'ring-red-500'), 2500);
            return;
        }

        openModal('publishConfirmModal', 'publishConfirmBox');
    }

    window.submitToPublish = function () {
        const btn = document.getElementById('confirmPublishBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';

        fetch(`{{ url('/dashboard/materials/'.$material->id.'/status') }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: 'pending' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('publishConfirmModal', 'publishConfirmBox');
                showCustomAlert('Success', 'Module submitted for approval!', 'success', () => {
                    loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn'));
                });
            } else {
                showCustomAlert('Error', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = 'Yes, Submit';
            }
        })
        .catch(error => {
            showCustomAlert('Error', 'An error occurred.', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Yes, Submit';
        });
    }

    window.revertToDraft = function() {
        openModal('revertConfirmModal', 'revertConfirmBox');
    }

    window.executeRevertToDraft = function() {
        const btn = document.getElementById('executeRevertBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Reverting...';

        fetch(`{{ url('/dashboard/materials/'.$material->id.'/status') }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'draft' })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                closeModal('revertConfirmModal', 'revertConfirmBox');
                showSnackbar('Module reverted to draft.', 'success');
                // Redirects completely out of the manage page back to the materials list on success!
                setTimeout(() => loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn')), 500);
            } else {
                showCustomAlert('Error', data.message || 'Failed to revert to draft.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            showCustomAlert('Error', 'An error occurred.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    // --- TOGGLE VISIBILITY ---
    window.toggleVisibility = function (checkbox) {
        fetch(`{{ url('/dashboard/materials/'.$material->id.'/visibility') }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_public: checkbox.checked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSnackbar(data.message, 'success');
            } else {
                checkbox.checked = !checkbox.checked; 
                showCustomAlert('Error', data.message, 'error');
            }
        });
    }

    // --- ACCESS MANAGEMENT ---
    window.submitAddStudent = function () {
        const email = document.getElementById('newStudentEmail').value;
        const btn = document.getElementById('submitAddStudentBtn');

        if (!email) { showSnackbar('Please enter an email address.', 'error'); return; }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(`{{ url('/dashboard/materials/'.$material->id.'/access') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSnackbar(data.message, 'success');
                closeModal('addStudentModal', 'addStudentBox');
                setTimeout(() => loadPartial('{{ url('/dashboard/materials/'.$material->id.'/manage') }}'), 500);
            } else {
                showCustomAlert('Error', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = 'Grant Access';
            }
        });
    }

    window.submitImport = function () {
        const fileInput = document.getElementById('importFileInput');
        const btn = document.getElementById('submitImportBtn');

        if (!fileInput.files.length) { showSnackbar('Please select a file to import.', 'error'); return; }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(`{{ url('/dashboard/materials/'.$material->id.'/import-access') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSnackbar(data.message, 'success');
                closeModal('importStudentModal', 'importStudentBox');
                setTimeout(() => loadPartial('{{ url('/dashboard/materials/'.$material->id.'/manage') }}'), 500);
            } else {
                showCustomAlert('Error', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = 'Upload & Import';
            }
        });
    }

    window.revokeAccess = function (accessId, btnElement) {
        showCustomAlert('Remove Student', 'Are you sure you want to remove this student? Their progress will be permanently lost.', 'error', () => {
            const originalHtml = btnElement.innerHTML;
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
            btnElement.disabled = true;

            fetch(`{{ url('/dashboard/materials/access') }}/${accessId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    btnElement.closest('tr').remove();
                    showSnackbar('Student removed.', 'success');
                } else {
                    showCustomAlert('Error', 'Error removing student.', 'error');
                    btnElement.innerHTML = originalHtml;
                    btnElement.disabled = false;
                }
            });
        });
        document.getElementById('customAlertBtn').innerText = 'Yes, Remove';
    }

    window.sendIndividualInvite = function(accessId, btnElement) {
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
        btnElement.disabled = true;

        fetch(`{{ url('/dashboard/materials/access') }}/${accessId}/invite`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showSnackbar('Invitation sent!', 'success');
                setTimeout(() => loadPartial('{{ url('/dashboard/materials/'.$material->id.'/manage') }}'), 500);
            } else {
                showCustomAlert('Error', 'Error sending invite.', 'error');
                btnElement.innerHTML = originalHtml;
                btnElement.disabled = false;
            }
        });
    }

    // --- TAGS LOGIC ---
    window.getActiveTags = function () {
        const spans = document.querySelectorAll('#active-tags-container span');
        return Array.from(spans).map(span => span.textContent.trim());
    }

    window.submitTag = function(value) {
        window.addTagToBackend(value);
    }

    window.addTagToBackend = function(tagValue) {
        tagValue = tagValue.trim().toUpperCase();
        if (!tagValue) return;
        
        fetch(`{{ url('/dashboard/materials/'.$material->id.'/tags') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ tag: tagValue })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const currentTags = window.getActiveTags();
                if(!currentTags.includes(tagValue)) {
                    renderActiveTags([...currentTags, tagValue]);
                }
                document.getElementById('tag-input').value = '';
            } else {
                showCustomAlert('Error', data.message, 'error');
            }
        });
    }

    document.getElementById('tag-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.addTagToBackend(this.value);
        }
    });

    window.removeTag = function (tag) {
        fetch(`{{ url('/dashboard/materials/'.$material->id.'/tags') }}/${encodeURIComponent(tag)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const currentTags = window.getActiveTags();
                renderActiveTags(currentTags.filter(t => t !== tag));
            } else {
                showSnackbar('Failed to remove tag.', 'error');
            }
        });
    }

    window.renderActiveTags = function (tags) {
        const activeTagsContainer = document.getElementById('active-tags-container');
        activeTagsContainer.innerHTML = '';
        const uniqueTags = [...new Set(tags)];

        uniqueTags.forEach(tag => {
            const tagEl = document.createElement('div');
            tagEl.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#a52a2a]/10 border border-[#a52a2a]/20 text-[#a52a2a] text-xs font-black uppercase tracking-wider rounded-lg shadow-sm';

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
    }

    // --- DELETE LOGIC ---
    window.executeDelete = function() {
        const btn = document.getElementById('executeDeleteBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';
        
        fetch(`{{ url('/dashboard/materials/'.$material->id) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }).then(response => response.json())
        .then(data => {
            if(data.success) {
                closeModal('deleteConfirmModal', 'deleteConfirmBox');
                showCustomAlert('Success', 'Module deleted successfully.', 'success', () => {
                    loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn'));
                });
            } else {
                showCustomAlert('Error', data.message || 'Error deleting module.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Delete Permanently';
            }
        });
    }
</script>