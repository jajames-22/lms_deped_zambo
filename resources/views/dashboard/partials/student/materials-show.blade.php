<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $material->title }} - LMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- PAGE TRANSITION ANIMATIONS --}}
    <style>
        /* Slide in from right to left */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Slide out from left to right */
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        .animate-slide-in {
            animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-slide-out {
            animation: slideOutRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 min-h-screen overflow-x-hidden selection:bg-[#a52a2a] selection:text-white">

    @php
        // 1. BUILD UNIFIED TIMELINE (Lessons + Exams)
        $timeline = collect();
        if(isset($material->lessons)) {
            foreach($material->lessons as $lesson) {
                $timeline->push((object)[
                    'is_exam' => false,
                    'id' => 'lesson_'.$lesson->id,
                    'title' => $lesson->title,
                    'items' => $lesson->contents,
                    'timestamp' => $lesson->created_at ? \Carbon\Carbon::parse($lesson->created_at)->timestamp : 0
                ]);
            }
        }

        if(isset($material->exams) && $material->exams->count() > 0) {
            $groupedExams = $material->exams->groupBy(function($e) { 
                return $e->created_at ? \Carbon\Carbon::parse($e->created_at)->format('Y-m-d H:i:s') : '0'; 
            });
            $examCounter = 1;
            foreach($groupedExams as $time => $questions) {
                $timeline->push((object)[
                    'is_exam' => true,
                    'id' => 'exam_group_'.$examCounter,
                    'title' => 'Final Examination',
                    'items' => $questions,
                    'timestamp' => \Carbon\Carbon::parse($time)->timestamp
                ]);
                $examCounter++;
            }
        }
        $timeline = $timeline->sortBy('timestamp')->values();
        $timelineCount = $timeline->count();

        // 2. CALCULATE PROGRESS
        $sectionsCompleted = 0;
        $progressPct = 0;
        
        // Count the absolute total number of individual items/questions in the entire module
        $totalContents = $timeline->sum(function($section) {
            return $section->items->count();
        });

        if ($isEnrolled) {
            $enrollment = \App\Models\Enrollment::where('material_id', $material->id)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($enrollment) {
                if ($enrollment->status === 'completed' || !is_null($enrollment->completed_at)) {
                    $sectionsCompleted = $timelineCount;
                    $progressPct = 100;
                } elseif ($enrollment->progress_data) {
                    $pData = json_decode($enrollment->progress_data);
                    
                    $highestUnlocked = isset($pData->highest_unlocked) ? (int)$pData->highest_unlocked : 0;
                    $currentContent = isset($pData->content) ? (int)$pData->content : 0;
                    $currentLesson = isset($pData->lesson) ? (int)$pData->lesson : 0;
                    
                    $sectionsCompleted = $highestUnlocked;
                    
                    // Calculate exact items passed for a smooth, granular percentage
                    $contentsPassed = 0;
                    for ($i = 0; $i < $highestUnlocked; $i++) {
                        if (isset($timeline[$i])) {
                            $contentsPassed += $timeline[$i]->items->count();
                        }
                    }
                    
                    if ($currentLesson === $highestUnlocked) {
                        $contentsPassed += $currentContent;
                    }
                    
                    $progressPct = $totalContents > 0 ? min(100, round(($contentsPassed / $totalContents) * 100)) : 0;
                }
            }
        }

        // 3. GET GRADING RULES DIRECTLY FROM DATABASE
        $dbHasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
        $dbHasQuizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
            ->where('lessons.material_id', $material->id)
            ->whereIn('lesson_contents.type', ['mcq', 'checkbox', 'true_false', 'text']) 
            ->exists();

        $examWeight = $material->exam_weight ?? 60;
        $passingScore = $material->passing_percentage ?? 80;
        
        if ($dbHasExams && !$dbHasQuizzes) { $examWeight = 100; }
        elseif (!$dbHasExams && $dbHasQuizzes) { $examWeight = 0; }
        elseif (!$dbHasExams && !$dbHasQuizzes) { $examWeight = 0; }
        
        $quizWeight = 100 - $examWeight;
    @endphp

    {{-- WRAPPER FOR ANIMATION --}}
    <div id="page-wrapper" class="min-h-screen flex flex-col animate-slide-in">

        {{-- CLEAN HEADER --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
            <div class="max-w-6xl mx-auto px-4 md:px-8 h-16 flex items-center justify-between relative">

                {{-- NATIVE BROWSER BACK BUTTON --}}
                <button onclick="navigateBack()"
                    class="flex items-center text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group px-2 py-1 rounded-lg hover:bg-red-50 relative z-10">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="hidden sm:inline">Back</span>
                </button>

                {{-- Center Title (Perfectly centered) --}}
                <div class="absolute left-1/2 -translate-x-1/2 font-black text-gray-900 text-lg truncate px-4 hidden md:block max-w-lg lg:max-w-2xl text-center">
                    {{ $material->title }}
                </div>

                {{-- User Profile Snippet --}}
                <div class="flex items-center gap-3 relative z-10">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-gray-900 leading-tight">{{ auth()->user()->first_name }}
                            {{ auth()->user()->last_name }}</p>
                        <p class="text-[9px] text-[#a52a2a] uppercase font-black tracking-widest">
                            {{ auth()->user()->role ?? 'Student' }}
                        </p>
                    </div>
                    <img class="h-8 w-8 rounded-full border-2 border-[#a52a2a]/20"
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->first_name . '+' . auth()->user()->last_name) }}&background=a52a2a&color=fff"
                        alt="Profile">
                </div>
            </div>
        </header>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 max-w-5xl mx-auto w-full py-8 px-4 sm:px-6 lg:px-8 pb-24">

            {{-- Hero Section: Side-by-Side Layout --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-10 flex flex-col md:flex-row gap-8 lg:gap-12">

                {{-- LEFT: Strict 4:3 Thumbnail --}}
                <div class="w-full md:w-1/2 lg:w-5/12 shrink-0">
                    <div class="w-full aspect-[4/3] rounded-2xl overflow-hidden shadow-md bg-gray-100 relative border border-gray-200">
                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=800' }}"
                            class="w-full h-full object-cover">

                        <div class="absolute top-4 left-4">
                            @if($material->is_public)
                                <span class="px-3 py-1 bg-white/90 backdrop-blur text-green-700 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm">Public Module</span>
                            @else
                                <span class="px-3 py-1 bg-gray-900/90 backdrop-blur text-white text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm"><i class="fas fa-lock mr-1"></i> Private</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Course Details & Action --}}
                <div class="w-full md:w-1/2 lg:w-7/12 flex flex-col">

                    <div class="flex flex-wrap gap-2 mb-4">
                        @forelse($material->tags as $tag)
                            <span class="px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] border border-[#a52a2a]/20 text-[10px] font-black uppercase tracking-wider rounded-md">
                                {{ $tag->name }}
                            </span>
                        @empty
                            <span class="px-3 py-1 bg-gray-100 text-gray-500 border border-gray-200 text-[10px] font-black uppercase tracking-wider rounded-md">
                                General
                            </span>
                        @endforelse
                    </div>

                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 mb-4 leading-tight">
                        {{ $material->title }}
                    </h1>
                    <p class="text-gray-600 text-sm md:text-base leading-relaxed mb-6 line-clamp-4">
                        {{ $material->description }}
                    </p>

                    {{-- STATS ROW --}}
                    <div class="flex flex-wrap items-center gap-6 py-4 border-t border-gray-100 mt-auto">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 text-lg border border-gray-200 shadow-sm shrink-0">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Instructor</p>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $material->instructor->first_name ?? 'Unknown' }}
                                    {{ $material->instructor->last_name ?? '' }}
                                </p>
                            </div>
                        </div>

                        <div class="hidden sm:block w-px h-8 bg-gray-200"></div>

                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Total Views</p>
                            <p class="text-sm font-bold text-gray-900"><i class="fas fa-eye text-[#a52a2a] mr-1.5"></i>{{ number_format($material->views ?? 0) }}</p>
                        </div>
                        
                        <div class="hidden sm:block w-px h-8 bg-gray-200"></div>

                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Downloads</p>
                            <p class="text-sm font-bold text-gray-900"><i class="fas fa-download text-amber-500 mr-1.5"></i>{{ number_format($material->downloads ?? 0) }}</p>
                        </div>
                    </div>

                    {{-- GRADING BLOCK --}}
                    <div class="mt-4 mb-6">
                        @if($dbHasExams || $dbHasQuizzes)
                            <div class="p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3 flex items-center gap-2"><i class="fas fa-award text-blue-500"></i> Grading & Certification</h4>
                                <div class="flex flex-wrap gap-8">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Passing Score</p>
                                        <p class="text-lg font-black {{ $passingScore == 0 ? 'text-amber-500' : 'text-green-600' }}">{{ $passingScore }}%</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Assessment Weight</p>
                                        <div class="text-xs font-bold mt-1.5 flex flex-wrap gap-3">
                                            @if($dbHasQuizzes)
                                                <span class="flex items-center gap-1.5 text-yellow-600 bg-yellow-50 px-2 py-1 rounded-md border border-yellow-100"><i class="fas fa-list-ul"></i> Quizzes: {{ $quizWeight }}%</span>
                                            @endif
                                            @if($dbHasExams)
                                                <span class="flex items-center gap-1.5 text-red-600 bg-red-50 px-2 py-1 rounded-md border border-red-100"><i class="fas fa-star"></i> Exam: {{ $examWeight }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($passingScore == 0)
                                    <p class="text-[10px] text-amber-600 mt-3 font-medium leading-tight"><i class="fas fa-exclamation-triangle mr-1"></i> No grading enforced. Complete all items to receive certificate.</p>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl flex items-start gap-3">
                                <i class="fas fa-info-circle text-gray-400 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-700">No Assessments</p>
                                    <p class="text-xs text-gray-500 mt-1">Read all materials to automatically earn a certificate.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- DYNAMIC ACTION BUTTONS --}}
                    <div id="action-buttons-container" class="flex flex-wrap items-center gap-3">
                        
                        {{-- Enroll Button (Hidden if already enrolled) --}}
                        <button id="enroll-btn" onclick="enrollInMaterial({{ $material->id }}, this)" class="{{ $isEnrolled ? 'hidden' : 'flex' }} w-full sm:w-auto px-8 py-3.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 items-center justify-center gap-2">
                            <i class="fas fa-user-plus text-lg"></i> Enroll Now
                        </button>

                        {{-- Unenroll Button (Hidden if NOT enrolled) --}}
                        <button id="unenroll-btn" onclick="openDropModal({{ $material->id }})" class="{{ $isEnrolled ? 'flex' : 'hidden' }} w-full sm:w-auto px-6 py-3.5 bg-green-50 text-green-700 border border-green-200 font-bold rounded-xl hover:bg-green-100 transition items-center justify-center gap-2 cursor-pointer shadow-sm">
                            <i class="fas fa-check-circle text-lg"></i>
                            <span>Enrolled</span>
                        </button>

                        {{-- Study Now Button (Hidden if NOT enrolled) --}}
                        <button id="study-now-btn" onclick="startStudying()" class="{{ $isEnrolled ? 'flex' : 'hidden' }} flex-1 sm:flex-none px-8 py-3.5 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-600/20 items-center justify-center gap-2">
                            <i class="fas fa-play-circle text-lg"></i> Study Now
                        </button>
                    </div>
                </div>
            </div>

            {{-- PROGRESS BAR (Hidden if not enrolled) --}}
            <div id="progress-container" class="{{ $isEnrolled ? 'block' : 'hidden' }} mb-8 bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
                <div class="flex items-end justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-lg">Your Progress</h4>
                        <p class="text-xs text-gray-500">Complete all sections to finish the module</p>
                    </div>
                    <span id="progress-percentage-text" class="text-xl font-black text-green-600">{{ $progressPct }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                    <div id="progress-bar-fill" class="bg-green-500 h-3 rounded-full transition-all duration-1000 ease-out" style="width: {{ $progressPct }}%"></div>
                </div>
                <p id="progress-count-text" class="text-xs text-gray-400 mt-3 font-bold uppercase tracking-wider text-right">{{ $sectionsCompleted }} of {{ $timelineCount }} sections completed</p>
            </div>

            {{-- Course Content / Lessons List --}}
            <h3 class="text-xl font-black text-gray-900 mb-4 px-2">Course Content</h3>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-2 md:p-4">

                @forelse($timeline as $index => $section)
                    <div class="lesson-item p-4 rounded-2xl transition-colors border border-transparent flex items-start gap-4 group {{ $isEnrolled ? 'hover:bg-gray-50 hover:border-gray-100 cursor-pointer' : 'opacity-70 cursor-not-allowed' }}">
                        <div class="lesson-number h-10 w-10 shrink-0 rounded-xl {{ $section->is_exam ? 'bg-red-50 text-red-500' : 'bg-gray-100 text-gray-400' }} font-black flex items-center justify-center {{ $isEnrolled && !$section->is_exam ? 'group-hover:bg-[#a52a2a]/10 group-hover:text-[#a52a2a]' : '' }} transition-colors">
                            @if($section->is_exam) <i class="fas fa-star"></i> @else {{ $index + 1 }} @endif
                        </div>
                        <div class="flex-1 min-w-0 pt-1.5">
                            <h4 class="font-bold text-gray-900 {{ $isEnrolled && !$section->is_exam ? 'group-hover:text-[#a52a2a]' : '' }} transition-colors text-lg">
                                {{ $section->title }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-4 mt-1">
                                <p class="text-xs text-gray-500 font-medium flex items-center gap-1.5 uppercase tracking-wider">
                                    <i class="fas {{ $section->is_exam ? 'fa-pen-alt' : 'fa-book-open' }} text-gray-400"></i> {{ $section->items->count() }} {{ $section->is_exam ? 'Questions' : 'Items' }}
                                </p>
                            </div>
                        </div>
                        <div class="pt-3 pl-4">
                            @if($isEnrolled)
                                @if($index < $sectionsCompleted)
                                    <i class="lesson-status-icon fas fa-check-circle text-green-500 tooltip" title="Completed"></i>
                                @elseif($index == $sectionsCompleted)
                                    <i class="lesson-status-icon fas fa-play-circle text-[#a52a2a] text-xl tooltip" title="Current Section"></i>
                                @else
                                    <i class="lesson-status-icon fas fa-lock text-gray-300 tooltip" title="Locked"></i>
                                @endif
                            @else
                                <i class="lesson-status-icon fas fa-lock text-gray-300 tooltip" title="Enroll to unlock"></i>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                            <i class="fas fa-folder-open text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-medium">The instructor hasn't added any lessons to this module yet.</p>
                    </div>
                @endforelse

            </div>
        </main>

    </div>

    {{-- Drop Course Modal --}}
    <div id="dropCourseModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeDropModal()"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10" id="dropCourseBox">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Drop Course?</h3>
            <p class="text-sm text-gray-500 mb-6">Are you sure you want to drop this course? All your progress and completed lessons will be permanently lost.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeDropModal()" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="button" id="confirm-drop-btn" onclick="executeDrop()" disabled class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Drop (<span id="drop-timer">5</span>s)
                </button>
            </div>
        </div>
    </div>

    {{-- Standalone Alert Modal --}}
    <div id="standaloneAlertModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeStandaloneAlert()"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10" id="standaloneAlertBox">
            <div id="standaloneAlertIconContainer" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl">
                <i id="standaloneAlertIcon" class="fas fa-info"></i>
            </div>
            <h3 id="standaloneAlertTitle" class="text-xl font-black text-gray-900 mb-2">Notice</h3>
            <p id="standaloneAlertMessage" class="text-sm text-gray-500 mb-6"></p>
            <button type="button" onclick="closeStandaloneAlert()" class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                Okay
            </button>
        </div>
    </div>

    <script>
        // PAGE TRANSITION LOGIC
        function navigateBack() {
            const wrapper = document.getElementById('page-wrapper');

            // 1. Trigger the slide-out animation
            wrapper.classList.remove('animate-slide-in');
            wrapper.classList.add('animate-slide-out');

            // 2. Explicitly redirect back to the main dashboard layout
            setTimeout(() => {
                window.location.href = "{{ url('/dashboard') }}";
            }, 300); // 300ms matches the CSS animation duration
        }

        // Study Now Logic
        function startStudying() {
            const btn = event.currentTarget || document.getElementById('study-now-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg mr-2"></i> Loading...';
            }
            window.location.href = '{{ route("dashboard.materials.study", $material->id) }}';
        }

        // Alert Modal Logic
        function showStandaloneAlert(message, type) {
            const modal = document.getElementById('standaloneAlertModal');
            const box = document.getElementById('standaloneAlertBox');
            const iconContainer = document.getElementById('standaloneAlertIconContainer');
            const icon = document.getElementById('standaloneAlertIcon');
            const title = document.getElementById('standaloneAlertTitle');
            const msg = document.getElementById('standaloneAlertMessage');

            msg.innerText = message;

            if (type === 'success') {
                title.innerText = 'Success!';
                iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-green-100 text-green-500';
                icon.className = 'fas fa-check-circle';
            } else {
                title.innerText = 'Error!';
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
        }

        function closeStandaloneAlert() {
            const modal = document.getElementById('standaloneAlertModal');
            const box = document.getElementById('standaloneAlertBox');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        // Drop Modal Logic
        let dropTimerInterval;
        let materialToDrop = null;

        function openDropModal(materialId) {
            materialToDrop = materialId;
            const modal = document.getElementById('dropCourseModal');
            const box = document.getElementById('dropCourseBox');
            const confirmBtn = document.getElementById('confirm-drop-btn');
            
            // Reset button and timer state
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = 'Drop (<span id="drop-timer">5</span>s)';
            let timeLeft = 5;

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
            }, 10);

            clearInterval(dropTimerInterval);
            dropTimerInterval = setInterval(() => {
                timeLeft--;
                const span = document.getElementById('drop-timer');
                if (span) span.innerText = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(dropTimerInterval);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Yes, Drop Course';
                }
            }, 1000);
        }

        function closeDropModal() {
            clearInterval(dropTimerInterval);
            materialToDrop = null;
            const modal = document.getElementById('dropCourseModal');
            const box = document.getElementById('dropCourseBox');
            
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        async function executeDrop() {
            if (!materialToDrop) return;
            
            const confirmBtn = document.getElementById('confirm-drop-btn');
            const originalHtml = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Dropping...';

            try {
                const response = await fetch(`{{ url('/dashboard/materials') }}/${materialToDrop}/unenroll`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closeDropModal();
                    showStandaloneAlert('Course dropped successfully.', 'success');
                    
                    // --- DOM Reversion: Re-lock everything instantly instead of refreshing ---
                    
                    const enrollBtn = document.getElementById('enroll-btn');
                    const unenrollBtn = document.getElementById('unenroll-btn');
                    const studyBtn = document.getElementById('study-now-btn');
                    
                    // Button Swap
                    enrollBtn.classList.replace('hidden', 'flex');
                    unenrollBtn.classList.replace('flex', 'hidden');
                    studyBtn.classList.replace('flex', 'hidden');
                    
                    // Hide Progress Bar & Reset values visually
                    document.getElementById('progress-container')?.classList.replace('block', 'hidden');
                    document.getElementById('progress-percentage-text').innerText = '0%';
                    document.getElementById('progress-bar-fill').style.width = '0%';
                    document.getElementById('progress-count-text').innerText = `0 of {{ $timelineCount }} sections completed`;

                    // Visually Lock Lessons
                    document.querySelectorAll('.lesson-item').forEach(item => {
                        item.classList.add('opacity-70', 'cursor-not-allowed');
                        item.classList.remove('hover:bg-gray-50', 'hover:border-gray-100', 'cursor-pointer');
                        
                        const icon = item.querySelector('.lesson-status-icon');
                        if (icon) {
                            icon.className = 'fas fa-lock text-gray-300 tooltip lesson-status-icon';
                            icon.title = "Enroll to unlock";
                        }
                        
                        const numberBadge = item.querySelector('.lesson-number');
                        if(numberBadge) numberBadge.classList.remove('group-hover:bg-[#a52a2a]/10', 'group-hover:text-[#a52a2a]');
                        
                        const titleText = item.querySelector('.lesson-title');
                        if(titleText) titleText.classList.remove('group-hover:text-[#a52a2a]');
                    });

                } else {
                    showStandaloneAlert(data.message || 'Failed to drop course.', 'error');
                    confirmBtn.innerHTML = originalHtml;
                    confirmBtn.disabled = false;
                }
            } catch (error) {
                showStandaloneAlert('A network error occurred.', 'error');
                confirmBtn.innerHTML = originalHtml;
                confirmBtn.disabled = false;
            }
        }

        // Enrollment Logic
        async function enrollInMaterial(materialId, btn) {
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg mr-2"></i> Enrolling...';

            try {
                const response = await fetch(`{{ url('/dashboard/materials') }}/${materialId}/enroll`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Instantly swap buttons
                    const unenrollBtn = document.getElementById('unenroll-btn');
                    const studyBtn = document.getElementById('study-now-btn');
                    
                    btn.classList.replace('flex', 'hidden');
                    unenrollBtn.classList.replace('hidden', 'flex');
                    studyBtn.classList.replace('hidden', 'flex');

                    // Reveal Progress Bar
                    document.getElementById('progress-container')?.classList.replace('hidden', 'block');

                    // Unlock lessons visually
                    document.querySelectorAll('.lesson-item').forEach((item, index) => {
                        item.classList.remove('opacity-70', 'cursor-not-allowed');
                        item.classList.add('hover:bg-gray-50', 'hover:border-gray-100', 'cursor-pointer');
                        
                        const icon = item.querySelector('.lesson-status-icon');
                        if (icon) {
                            if (index === 0) {
                                icon.className = 'fas fa-play-circle text-[#a52a2a] text-xl tooltip lesson-status-icon';
                                icon.title = "Current Section";
                            } else {
                                icon.className = 'fas fa-lock text-gray-300 tooltip lesson-status-icon';
                                icon.title = "Locked";
                            }
                        }
                        
                        const numberBadge = item.querySelector('.lesson-number');
                        if(numberBadge && !numberBadge.classList.contains('text-red-500')) {
                            numberBadge.classList.add('group-hover:bg-[#a52a2a]/10', 'group-hover:text-[#a52a2a]');
                        }
                        
                        const titleText = item.querySelector('h4');
                        if(titleText) titleText.classList.add('group-hover:text-[#a52a2a]');
                    });

                    // Reset enroll button back to normal
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;

                    showStandaloneAlert('Successfully enrolled!', 'success');
                } else {
                    showStandaloneAlert(data.message || 'Failed to enroll.', 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                showStandaloneAlert('A network error occurred.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        async function completeModule(materialId, btn) {
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i> Processing...';

            try {
                const response = await fetch(`{{ url('/dashboard/student/materials') }}/${materialId}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    sessionStorage.setItem('lastActiveTab', '{{ route('student.enrolled.index') }}');
                    sessionStorage.setItem('lastActiveBtn', 'nav-enrolled-btn');
                    window.location.href = data.redirect_url;
                } else {
                    showStandaloneAlert(data.message || 'Failed to mark as complete.', 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                showStandaloneAlert('A network error occurred. Please try again.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    </script>
</body>

</html>