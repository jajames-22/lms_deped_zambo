<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $material->title }} - Public Overview</title>

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

        // 2. GET GRADING RULES
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

        // 3. EXTRACT MEDIA & RESOURCES
        $resources = collect();
        foreach($timeline as $section) {
            foreach($section->items as $item) {
                if(!empty($item->media_url)) {
                    $mediaUrl = str_starts_with($item->media_url, 'http') ? $item->media_url : asset('storage/' . $item->media_url);
                    $pathForExt = parse_url($mediaUrl, PHP_URL_PATH) ?? $mediaUrl;
                    $ext = strtolower(pathinfo($pathForExt, PATHINFO_EXTENSION));
                    
                    // Use the media_name if it exists. If not, extract the raw filename from the URL as a fallback.
                    $name = !empty($item->media_name) ? $item->media_name : basename($pathForExt);

                    // Attempt to get file size safely
                    $size = 'Unknown Size';
                    try {
                        if (!str_starts_with($item->media_url, 'http')) {
                            $filePath = public_path('storage/' . $item->media_url);
                            if (file_exists($filePath)) {
                                $size = number_format(filesize($filePath) / 1048576, 2) . ' MB';
                            }
                        }
                    } catch(\Exception $e) {}

                    $resources->push((object)[
                        'url' => $mediaUrl,
                        'ext' => $ext,
                        'name' => $name,
                        'size' => $size
                    ]);
                }
            }
        }
        $resources = $resources->unique('url')->values();
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

                {{-- Public Auth Actions --}}
                <div class="flex items-center gap-3 relative z-10">
                    <a href="{{ route('login') }}" class="text-sm font-bold text-gray-600 hover:text-[#a52a2a] transition-colors hidden sm:block">Sign In</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-[#a52a2a] text-white text-xs font-bold rounded-xl shadow-sm hover:bg-red-800 transition-colors">Create Account</a>
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
                            <span class="px-3 py-1 bg-white/90 backdrop-blur text-green-700 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm">Public Module</span>
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

                    {{-- GRADING BLOCK (Shown to public to display course requirements) --}}
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
                                    <p class="text-xs text-gray-500 mt-1">There are no quizzes and exams in this material.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- DYNAMIC ACTION BUTTONS --}}
                    <div id="action-buttons-container" class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('register') }}" class="flex w-full sm:w-auto px-8 py-3.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 items-center justify-center gap-2">
                            <i class="fas fa-user-plus text-lg"></i> Create Free Account to Enroll
                        </a>
                        <a href="{{ route('login') }}" class="flex w-full sm:w-auto px-6 py-3.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-xl hover:bg-gray-100 transition items-center justify-center gap-2 cursor-pointer shadow-sm">
                            <i class="fas fa-sign-in-alt text-lg"></i> Log In
                        </a>
                    </div>
                </div>
            </div>

            {{-- Course Content / Lessons List --}}
            <h3 class="text-xl font-black text-gray-900 mb-4 px-2">Course Content</h3>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-2 md:p-4 mb-10">

                @forelse($timeline as $index => $section)
                    <div class="lesson-item p-4 rounded-2xl transition-colors border border-transparent flex items-start gap-4 group opacity-80 cursor-not-allowed">
                        <div class="lesson-number h-10 w-10 shrink-0 rounded-xl {{ $section->is_exam ? 'bg-red-50 text-red-500' : 'bg-gray-100 text-gray-400' }} font-black flex items-center justify-center transition-colors">
                            @if($section->is_exam) <i class="fas fa-star"></i> @else {{ $index + 1 }} @endif
                        </div>
                        <div class="flex-1 min-w-0 pt-1.5">
                            <h4 class="font-bold text-gray-900 transition-colors text-lg">
                                {{ $section->title }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-4 mt-1">
                                <p class="text-xs text-gray-500 font-medium flex items-center gap-1.5 uppercase tracking-wider">
                                    <i class="fas {{ $section->is_exam ? 'fa-pen-alt' : 'fa-book-open' }} text-gray-400"></i> {{ $section->items->count() }} {{ $section->is_exam ? 'Questions' : 'Items' }}
                                </p>
                            </div>
                        </div>
                        <div class="pt-3 pl-4">
                            <i class="lesson-status-icon fas fa-lock text-gray-300 tooltip" title="Create an account to unlock"></i>
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

            {{-- DOWNLOADABLE RESOURCES --}}
            @if($resources->count() > 0)
                <h3 class="text-xl font-black text-gray-900 mb-4 px-2 mt-12">Media & Resources</h3>
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-2 md:p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($resources as $res)
                        @php
                            $isPdf = $res->ext === 'pdf';
                            $isVideo = in_array($res->ext, ['mp4', 'webm', 'ogg']);
                            $isImage = in_array($res->ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            
                            $bgClass = 'text-gray-500 bg-gray-100';
                            $iconName = 'fa-file-alt';

                            if($isPdf) { $bgClass = 'text-red-500 bg-red-50'; $iconName = 'fa-file-pdf'; }
                            elseif($isVideo) { $bgClass = 'text-blue-500 bg-blue-50'; $iconName = 'fa-file-video'; }
                            elseif($isImage) { $bgClass = 'text-green-500 bg-green-50'; $iconName = 'fa-file-image'; }
                            elseif(in_array($res->ext, ['zip', 'rar'])) { $bgClass = 'text-yellow-600 bg-yellow-50'; $iconName = 'fa-file-archive'; }
                        @endphp

                        {{-- Changed to a direct download link --}}
                        <a href="{{ $res->url }}" download target="_blank" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 border border-transparent hover:border-gray-100 transition group cursor-pointer">
                            
                            <div class="h-12 w-12 shrink-0 rounded-xl flex items-center justify-center overflow-hidden {{ $bgClass }}">
                                <i class="fas {{ $iconName }} text-xl"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 text-sm truncate group-hover:text-[#a52a2a] transition">{{ $res->name }}</h4>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-0.5">{{ strtoupper($res->ext) }} • {{ $res->size }}</p>
                            </div>

                            <div class="h-10 w-10 shrink-0 rounded-full flex items-center justify-center text-gray-400 group-hover:text-[#a52a2a] group-hover:bg-red-50 transition" title="Download Resource">
                                <i class="fas fa-download"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </main>

    </div>

    <script>
        function navigateBack() {
            const wrapper = document.getElementById('page-wrapper');

            wrapper.classList.remove('animate-slide-in');
            wrapper.classList.add('animate-slide-out');

            setTimeout(() => {
                window.location.href = "{{ route('explore.public') }}";
            }, 300);
        }
    </script>
</body>

</html>