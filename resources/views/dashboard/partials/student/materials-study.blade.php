<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studying: {{ $material->title }} - LMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- PDF.js for rendering PDFs natively --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <style>
        html { scroll-behavior: smooth; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        
        .lesson-container, .content-block { display: none; }
        .lesson-container.active, .content-block.active { 
            display: block; 
            animation: fadeIn 0.4s ease-out forwards; 
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .video-wrapper { position: relative; width: 100%; border-radius: 1rem; overflow: hidden; background: #000; }
        .video-controls {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 1.5rem 1rem 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;
            opacity: 0; transition: opacity 0.3s;
        }
        .video-wrapper:hover .video-controls { opacity: 1; }
        
        .video-progress-slider {
            -webkit-appearance: none;
            width: 100%;
            background: rgba(255,255,255,0.3);
            height: 6px;
            border-radius: 3px;
            outline: none;
        }
        .video-progress-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #a52a2a;
            cursor: pointer;
        }

        /* --- IDLE MEDIA CONTROLS HIDER --- */
        body.media-idle .video-wrapper { cursor: none !important; }
        body.media-idle .video-wrapper .video-controls { 
            opacity: 0 !important; 
            pointer-events: none; 
        }
        body.media-idle .media-fullscreen { cursor: none !important; }
        body.media-idle .media-fullscreen .pdf-toolbar,
        body.media-idle .media-fullscreen .fs-toggle-btn { 
            opacity: 0 !important; 
            pointer-events: none; 
            transition: opacity 0.4s; 
        }

        .controls-row { display: flex; align-items: center; justify-content: space-between; color: white; }
        
        .pdf-container { background: #e5e7eb; border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column; height: 60vh; lg:height: 75vh; width: 100%; }
        .pdf-toolbar { background: #1f2937; color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; }
        .pdf-render-area { 
            overflow: auto; 
            padding: 1rem; 
            position: relative; 
            flex-grow: 1; 
            text-align: center; /* Centers canvas horizontally safely */
        }
        .pdf-render-area canvas { 
            display: inline-block; /* Allows scrolling without clipping top/left */
            vertical-align: middle;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
            border-radius: 4px; 
            transition: transform 0.2s;
        }

        /* CUSTOM FULLSCREEN OVERRIDE */
        .media-fullscreen {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 99990 !important; 
            background: rgba(15, 15, 15, 0.95) !important;
            backdrop-filter: blur(8px);
            border-radius: 0 !important;
            border: none !important;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0 !important;
            padding: 0 !important;
        }
        .media-fullscreen > img, .media-fullscreen > video {
            max-height: 100vh !important;
            max-width: 100vw !important;
            object-fit: contain;
            margin: auto;
        }
        .media-fullscreen .pdf-toolbar {
            width: 100%;
            background: rgba(20, 25, 30, 0.95) !important;
        }
        .media-fullscreen .pdf-render-area {
            width: 100%;
            flex-grow: 1;
            background: transparent !important;
        }
        .media-fullscreen .video-controls {
            padding-bottom: 2rem;
            background: linear-gradient(to top, rgba(0,0,0,0.95), transparent);
        }
        .media-fullscreen .fs-toggle-btn { display: none !important; }

        body.fs-active .z-10, 
        body.fs-active .z-20, 
        body.fs-active .z-30, 
        body.fs-active .z-40, 
        body.fs-active .z-50 {
            z-index: 0 !important;
        }
        body.fs-active .lesson-container, 
        body.fs-active .content-block,
        body.fs-active #content-area {
            animation: none !important;
            transform: none !important;
            will-change: auto !important;
        }
    </style>
</head>

{{-- LOAD ANSWERS DIRECTLY FROM DB FOR FAST RENDER --}}
@php
    $userId = auth()->id();
    $quizAnswers = \Illuminate\Support\Facades\DB::table('quiz_answers')->where('user_id', $userId)->get()->keyBy('lesson_content_id');
    $examAnswers = \Illuminate\Support\Facades\DB::table('exam_answers')->where('user_id', $userId)->get()->keyBy('exam_id');

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
                'title' => 'Examination',
                'items' => $questions,
                'timestamp' => \Carbon\Carbon::parse($time)->timestamp
            ]);
            $examCounter++;
        }
    }
    $timeline = $timeline->sortBy('timestamp')->values();
@endphp

<body class="bg-gray-50 font-sans text-gray-900 h-screen overflow-hidden flex flex-col selection:bg-[#a52a2a] selection:text-white">

    <header class="bg-white border-b border-gray-200 h-16 shrink-0 flex items-center justify-between px-4 lg:px-6 z-50 shadow-sm relative">
        <div class="flex items-center gap-4 w-1/4 lg:w-1/3 shrink-0">
            <a href="{{ route('dashboard.materials.show', $material->id) }}" 
               class="flex items-center text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group px-2 lg:px-3 py-2 rounded-xl hover:bg-red-50">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                <span class="hidden sm:inline">Exit</span>
            </a>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center cursor-pointer lg:cursor-default mx-2 select-none" onclick="toggleMobileTOC()">
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest truncate max-w-[120px] sm:max-w-xs">{{ $material->title }}</span>
                <i class="fas fa-chevron-down text-[10px] text-gray-400 lg:hidden"></i>
            </div>
            <h1 id="top-lesson-title" class="font-black text-gray-900 text-sm sm:text-lg truncate max-w-[150px] sm:max-w-md">Loading...</h1>
        </div>

        <div class="w-1/4 lg:w-1/3 flex items-center justify-end gap-4 shrink-0">
            <div class="hidden sm:flex flex-col items-end">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Overall</span>
                <div class="w-24 lg:w-32 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                    <div id="top-progress-bar" class="bg-green-500 h-full rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>
            <span id="top-progress-text" class="font-black text-green-600 text-sm">0%</span>
        </div>
    </header>

    <div id="mobile-toc-overlay" class="fixed inset-0 bg-gray-900/60 z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden" onclick="toggleMobileTOC()"></div>
    
    <div id="mobile-toc-dropdown" class="fixed top-16 left-0 right-0 bg-white z-40 shadow-2xl border-b border-gray-200 transform -translate-y-full transition-transform duration-300 flex flex-col max-h-[calc(100vh-4rem)] rounded-b-3xl pointer-events-none lg:hidden">
        <div class="p-5 bg-gray-50 border-b border-gray-100 flex justify-between items-center shrink-0 rounded-b-3xl">
            <div>
                <h3 class="font-black text-gray-900 text-sm leading-tight">{{ $material->title }}</h3>
                <p class="text-xs text-[#a52a2a] font-bold mt-1 uppercase tracking-wider" id="mobile-dropdown-counter">Loading...</p>
            </div>
            <button onclick="toggleMobileTOC()" class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-600 transition"><i class="fas fa-times"></i></button>
        </div>
        
        <nav class="p-3 space-y-1 overflow-y-auto" id="mobile-sidebar-nav">
            @foreach($timeline as $index => $section)
                <button onclick="attemptGoToLesson({{ $index }}); toggleMobileTOC();" id="mobile-toc-btn-{{ $index }}" 
                    class="w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600 {{ $section->is_exam ? 'bg-red-50/40 mt-2' : '' }}">
                    <div class="mobile-toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500">
                        @if($section->is_exam) <i class="fas fa-star"></i> @else {{ $index + 1 }} @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm leading-tight truncate mobile-toc-title text-gray-700">{{ $section->title }}</p>
                        <p class="text-[10px] uppercase tracking-wider font-bold mt-1 mobile-toc-meta text-gray-400">{{ $section->items->count() }} {{ $section->is_exam ? 'Questions' : 'Items' }}</p>
                    </div>
                    <div class="shrink-0 mobile-toc-lock text-gray-300 mt-1"><i class="fas fa-lock"></i></div>
                    <div class="shrink-0 mobile-toc-status hidden text-green-500 mt-1"><i class="fas fa-check-circle"></i></div>
                </button>
            @endforeach
        </nav>
    </div>

    <div class="flex flex-1 overflow-hidden relative z-10">

        <aside class="w-80 bg-white border-r border-gray-200 flex flex-col z-20 shrink-0 hidden lg:flex h-full shadow-sm relative">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 shrink-0">
                <h2 class="font-black text-gray-900 text-lg">Course Content</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $timeline->count() }} Total Sections</p>
            </div>
            
            <nav class="flex-1 overflow-y-auto sidebar-scroll p-3 space-y-1" id="sidebar-nav">
                @foreach($timeline as $index => $section)
                    <button onclick="attemptGoToLesson({{ $index }})" id="toc-btn-{{ $index }}" 
                        class="w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600 {{ $section->is_exam ? 'bg-red-50/40 mt-4' : '' }}">
                        <div class="toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500">
                            @if($section->is_exam) <i class="fas fa-star"></i> @else {{ $index + 1 }} @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm leading-tight truncate toc-title text-gray-700">{{ $section->title }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-bold mt-1 toc-meta text-gray-400">{{ $section->items->count() }} {{ $section->is_exam ? 'Questions' : 'Items' }}</p>
                        </div>
                        <div class="shrink-0 toc-lock text-gray-300 mt-1"><i class="fas fa-lock"></i></div>
                        <div class="shrink-0 toc-status hidden text-green-500 mt-1"><i class="fas fa-check-circle"></i></div>
                    </button>
                @endforeach
            </nav>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 bg-gray-50 h-full relative z-10">
            <div id="main-scroll-area" class="flex-1 overflow-y-auto w-full relative">
                <div class="w-full max-w-7xl mx-auto px-4 py-8 sm:px-8 flex flex-col min-h-full">
                    
                    @foreach($timeline as $lessonIndex => $section)
                        <div id="lesson-{{ $lessonIndex }}" class="lesson-container w-full">
                            @forelse($section->items as $contentIndex => $block)
                                <div id="content-{{ $lessonIndex }}-{{ $contentIndex }}" class="content-block w-full">
                                    
                                    @if($section->is_exam)
                                        <div class="text-center mb-6 w-full">
                                            <span class="inline-block px-4 py-1.5 bg-[#a52a2a] text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-sm">Examination Section</span>
                                        </div>
                                    @endif

                                    @php
                                        $hasMedia = !empty($block->media_url);
                                        $hasText = false;
                                        if ($block->type === 'content' && !empty($block->question_text)) $hasText = true;
                                        if (in_array($block->type, ['mcq', 'true_false', 'checkbox', 'text'])) $hasText = true;

                                        $isQuiz = !$section->is_exam && in_array($block->type, ['mcq', 'true_false', 'checkbox', 'text']);
                                        $isExamItem = $section->is_exam;
                                        
                                        $existingAnswer = null;
                                        $isLocked = false;
                                        $isCorrect = false;
                                        $feedbackType = 'incorrect';

                                        if ($isQuiz && isset($quizAnswers[$block->id])) {
                                            $existingAnswer = $quizAnswers[$block->id];
                                            $isLocked = true; 
                                            $isCorrect = $existingAnswer->is_correct;
                                            
                                            $hasCorrectOptions = collect($block->options)->where('is_correct', 1)->count() > 0;
                                            if ($block->type === 'text' && !$hasCorrectOptions) {
                                                $feedbackType = 'recorded_as_is';
                                            } elseif ($isCorrect) {
                                                $feedbackType = 'correct';
                                            }
                                        } elseif ($isExamItem && isset($examAnswers[$block->id])) {
                                            $existingAnswer = $examAnswers[$block->id];
                                        }

                                        $inputName = $section->is_exam ? "exam_answer_{$block->id}" : "answer_{$block->id}";
                                    @endphp

                                    <div class="flex flex-col lg:flex-row gap-2 lg:gap-4 w-full">
                                        
                                        {{-- MEDIA PARSER --}}
                                        @if($hasMedia)
                                            <div class="w-full {{ $hasText ? 'lg:w-1/2 xl:w-7/12' : '' }}">
                                                @php
                                                    $mediaUrl = str_starts_with($block->media_url, 'http') ? $block->media_url : asset('storage/' . $block->media_url);
                                                    $pathForExt = parse_url($mediaUrl, PHP_URL_PATH) ?? $mediaUrl;
                                                    $ext = strtolower(pathinfo($pathForExt, PATHINFO_EXTENSION));
                                                    
                                                    $isPdf = $ext === 'pdf';
                                                    $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
                                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                @endphp

                                                @if($isPdf)
                                                    <div class="pdf-container media-container shadow-sm border border-gray-200" data-pdf-url="{{ $mediaUrl }}" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                        <div class="pdf-toolbar shrink-0">
                                                            <div class="flex items-center gap-4">
                                                                <span class="text-sm font-bold"><i class="fas fa-file-pdf mr-2"></i> Page <span class="pdf-page-num text-[#a52a2a]">1</span> of <span class="pdf-page-count">?</span></span>
                                                            </div>
                                                            <div class="flex items-center gap-3">
                                                                <button onclick="pdfZoomOut('media-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-[#a52a2a] transition"><i class="fas fa-search-minus"></i></button>
                                                                <span class="pdf-scale text-xs font-bold w-12 text-center">100%</span>
                                                                <button onclick="pdfZoomIn('media-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-[#a52a2a] transition"><i class="fas fa-search-plus"></i></button>
                                                                
                                                                {{-- Fullscreen Button --}}
                                                                <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'pdf')" class="fs-toggle-btn hover:text-[#a52a2a] transition ml-3 border-l border-gray-600 pl-3" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                                
                                                                <a href="{{ $mediaUrl }}" target="_blank" class="ml-1 pl-3 border-l border-gray-600 hover:text-[#a52a2a] transition" title="Open in new tab"><i class="fas fa-external-link-alt"></i></a>
                                                            </div>
                                                        </div>
                                                        <div class="pdf-render-area bg-gray-200 relative">
                                                            <div class="pdf-loading absolute inset-0 flex flex-col items-center justify-center bg-gray-100 z-10">
                                                                <i class="fas fa-circle-notch fa-spin text-3xl text-[#a52a2a] mb-3"></i>
                                                                <span class="text-sm font-bold text-gray-500 tracking-widest uppercase">Loading Document...</span>
                                                            </div>
                                                            <canvas class="pdf-canvas mx-auto transition-transform duration-200"></canvas>
                                                        </div>
                                                    </div>
                                                @elseif($isVideo)
                                                    <div class="video-wrapper media-container shadow-xl group border border-gray-800" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                        <video class="w-full max-h-[70vh] object-contain custom-video">
                                                            <source src="{{ $mediaUrl }}" type="video/{{ $ext === 'webm' ? 'webm' : 'mp4' }}">
                                                        </video>
                                                        <div class="video-controls">
                                                            <input type="range" min="0" max="100" step="0.1" value="0" class="video-progress-slider">
                                                            
                                                            <div class="controls-row mt-2">
                                                                <div class="flex items-center gap-4">
                                                                    <button onclick="togglePlay('media-{{ $lessonIndex }}-{{ $contentIndex }}')" class="play-btn text-xl hover:text-[#a52a2a] transition w-6"><i class="fas fa-play"></i></button>
                                                                    <div class="text-xs font-mono font-bold"><span class="current-time">0:00</span> / <span class="duration">0:00</span></div>
                                                                </div>
                                                                <div class="flex items-center gap-4">
                                                                    <select onchange="changeSpeed('media-{{ $lessonIndex }}-{{ $contentIndex }}', this.value)" class="bg-transparent text-xs font-bold outline-none cursor-pointer hover:text-[#a52a2a] transition hidden sm:block">
                                                                        <option class="text-black" value="1" selected>1.0x</option>
                                                                        <option class="text-black" value="1.5">1.5x</option>
                                                                        <option class="text-black" value="2">2.0x</option>
                                                                    </select>
                                                                    <button onclick="toggleMute('media-{{ $lessonIndex }}-{{ $contentIndex }}', event)" class="mute-btn hover:text-[#a52a2a] transition"><i class="fas fa-volume-up"></i></button>
                                                                    
                                                                    {{-- Fullscreen Button --}}
                                                                    <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'video')" class="fs-toggle-btn hover:text-[#a52a2a] transition" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($isImage)
                                                    <div class="media-container rounded-2xl overflow-hidden bg-gray-200 border border-gray-200 flex bg-white justify-center relative group w-full" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                        <img src="{{ $mediaUrl }}" class="object-contain max-h-[70vh] w-full transition-transform duration-300">
                                                        
                                                        {{-- Fullscreen Button --}}
                                                        <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'image')" class="fs-toggle-btn absolute top-4 right-4 bg-black/60 text-white w-10 h-10 flex items-center justify-center rounded-xl opacity-0 group-hover:opacity-100 transition hover:bg-[#a52a2a] shadow-lg" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- TEXT/QUESTIONS PARSER --}}
                                        @if($hasText)
                                            <div class="w-full {{ $hasMedia ? 'lg:w-1/2 xl:w-5/12' : 'max-w-4xl mx-auto' }}">
                                                
                                                @if($block->type === 'content' && $block->question_text)
                                                    <div class="bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-gray-100 prose prose-gray max-w-none text-gray-800 text-base sm:text-lg leading-relaxed">
                                                        {!! nl2br(e($block->question_text)) !!}
                                                    </div>

                                                @elseif(in_array($block->type, ['mcq', 'true_false']))
                                                    <div class="bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                                        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                                        <h3 class="text-xl font-bold text-gray-900 mb-6">{{ $block->question_text }}</h3>
                                                        <div class="space-y-3">
                                                            @foreach($block->options as $option)
                                                                @php
                                                                    $checked = false;
                                                                    if ($existingAnswer) {
                                                                        $checked = $section->is_exam ? ($existingAnswer->exam_option_id == $option->id) : ($existingAnswer->quiz_option_id == $option->id);
                                                                    }
                                                                @endphp
                                                                <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:border-[#a52a2a] has-[:checked]:bg-[#a52a2a]/5">
                                                                    <input type="radio" name="{{ $inputName }}" value="{{ $option->id }}" {{ $checked ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }} class="w-5 h-5 accent-[#a52a2a] text-[#a52a2a] bg-gray-100 border-gray-300 focus:ring-[#a52a2a]">
                                                                    <span class="text-gray-800 font-medium text-base sm:text-lg">{{ $option->option_text }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                @elseif($block->type === 'checkbox')
                                                    <div class="bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                                        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                                        <div class="flex items-start justify-between gap-4 mb-6">
                                                            <h3 class="text-xl font-bold text-gray-900">{{ $block->question_text }}</h3>
                                                            <span class="shrink-0 text-[10px] uppercase font-black tracking-wider text-gray-400 bg-gray-100 px-2 py-1 rounded hidden sm:inline-block">Select Multiple</span>
                                                        </div>
                                                        <div class="space-y-3">
                                                            @foreach($block->options as $option)
                                                                @php
                                                                    $checked = false;
                                                                    if ($existingAnswer && $existingAnswer->text_answer) {
                                                                        $selectedIds = explode(',', $existingAnswer->text_answer);
                                                                        $checked = in_array($option->id, $selectedIds);
                                                                    }
                                                                @endphp
                                                                <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:border-[#a52a2a] has-[:checked]:bg-[#a52a2a]/5">
                                                                    <input type="checkbox" name="{{ $inputName }}[]" value="{{ $option->id }}" {{ $checked ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }} class="w-5 h-5 accent-[#a52a2a] text-[#a52a2a] bg-gray-100 border-gray-300 rounded focus:ring-[#a52a2a]">
                                                                    <span class="text-gray-800 font-medium text-base sm:text-lg">{{ $option->option_text }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                @elseif($block->type === 'text')
                                                    <div class="bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                                        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                                        <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $block->question_text }}</h3>
                                                        <textarea name="{{ $inputName }}" rows="5" placeholder="Type your answer here..." {{ $isLocked ? 'disabled' : '' }}
                                                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a] outline-none transition-all resize-none text-base sm:text-lg">{{ $existingAnswer->text_answer ?? '' }}</textarea>
                                                    </div>
                                                @endif
                                                
                                                {{-- FEEDBACK BADGE (Only for Quizzes) --}}
                                                @if($isQuiz)
                                                    <div id="quiz-feedback-{{ $block->id }}" class="mt-6" style="display: {{ $isLocked ? 'block' : 'none' }};">
                                                        @if($isLocked)
                                                            @if($feedbackType === 'recorded_as_is')
                                                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3 text-blue-700 font-bold"><i class="fas fa-info-circle text-xl"></i> Answer recorded as is.</div>
                                                            @elseif($feedbackType === 'correct')
                                                                <div class="p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-700 font-bold"><i class="fas fa-check-circle text-xl"></i> Your answer is correct!</div>
                                                            @else
                                                                <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-700 font-bold"><i class="fas fa-times-circle text-xl"></i> Your answer is incorrect.</div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        
                                    </div>
                                </div>
                            @empty
                                <div class="content-block w-full active bg-white rounded-3xl p-12 shadow-sm border border-gray-100 text-center">
                                    <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">No Content</h3>
                                </div>
                            @endforelse
                        </div>
                    @endforeach

                </div>
            </div>

            {{-- FIXED BOTTOM NAVIGATION --}}
            <div class="bg-white border-t border-gray-200 p-4 lg:px-8 flex justify-between items-center shrink-0 z-30 w-full shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] relative">
                <button type="button" id="btn-prev" onclick="navigateContent(-1)" class="px-5 sm:px-6 py-3 sm:py-3.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Previous</span>
                </button>
                
                <div class="flex-1 flex flex-col items-center px-2 sm:px-4">
                    <span id="bottom-content-counter" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Content 1 of ?</span>
                    <div class="w-full max-w-md mx-auto bg-gray-200 rounded-full h-1.5">
                        <div id="lesson-progress-bar" class="bg-[#a52a2a] h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <button type="button" id="btn-next" onclick="navigateContent(1)" class="px-6 sm:px-8 py-3 sm:py-3.5 text-white font-bold rounded-xl transition bg-[#a52a2a] shadow-lg flex items-center gap-2">
                    <span id="btn-next-text" class="hidden sm:inline">Next</span> 
                    <span id="btn-next-text-mobile" class="sm:hidden">Next</span>
                    <i id="btn-next-icon" class="fas fa-arrow-right"></i>
                </button>
            </div>
            
        </main>
    </div>

    {{-- NEW GLOBAL FULLSCREEN OVERLAY CONTROLS --}}
    {{-- NEW GLOBAL FULLSCREEN OVERLAY CONTROLS --}}
    <div id="fs-global-controls" class="fixed inset-0 pointer-events-none hidden z-[999999] transition-opacity duration-300 opacity-100">
        <button onclick="closeMediaFullscreen()" class="absolute top-4 right-4 sm:top-15 sm:right-8 pointer-events-auto bg-black/60 hover:bg-red-600 text-white rounded-full w-12 h-12 flex items-center justify-center backdrop-blur transition-colors shadow-2xl border border-white/10" title="Exit Full Screen">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-6 pointer-events-auto">
            <button onclick="mediaFsNavigate(-1)" class="bg-black/60 hover:bg-[#a52a2a] text-white rounded-full h-14 w-14 flex items-center justify-center backdrop-blur transition-colors shadow-2xl border border-white/10" id="fs-btn-prev">
                <i class="fas fa-chevron-left text-xl pr-1"></i>
            </button>
            
            <button onclick="mediaFsNavigate(1)" class="bg-black/60 hover:bg-[#a52a2a] text-white rounded-full h-14 w-14 flex items-center justify-center backdrop-blur transition-colors shadow-2xl border border-white/10" id="fs-btn-next">
                <i class="fas fa-chevron-right text-xl pl-1"></i>
            </button>
        </div>
    </div>

    {{-- EXAM CONFIRMATION MODAL --}}
    <div id="exam-confirm-modal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeExamConfirm()"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10" id="exam-confirm-modal-box">
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-red-50 text-[#a52a2a]">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Start Examination?</h3>
            <p class="text-sm text-gray-500 mb-6">Once you begin the examination, you will not be able to return to the study lessons. Are you ready to proceed?</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeExamConfirm()" class="w-1/2 px-4 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <button type="button" onclick="confirmStartExam()" class="w-1/2 px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-md">Start Exam</button>
            </div>
        </div>
    </div>

    {{-- CUSTOM ALERT MODAL --}}
    <div id="custom-modal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeCustomModal()"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10" id="custom-modal-box">
            <div id="custom-modal-icon" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl"></div>
            <h3 id="custom-modal-title" class="text-xl font-black text-gray-900 mb-2"></h3>
            <p id="custom-modal-message" class="text-sm text-gray-500 mb-6"></p>
            <button type="button" id="custom-modal-btn" onclick="closeCustomModal()" class="w-full px-4 py-3 text-white font-bold rounded-xl transition shadow-md">Okay</button>
        </div>
    </div>

    {{-- Confetti --}}
    <canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-50 hidden"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        const materialData = @json($timeline);

        let state = {
            lesson: {{ $savedProgress->lesson ?? 0 }},
            content: {{ $savedProgress->content ?? 0 }},
            highestUnlockedLesson: {{ $savedProgress->highest_unlocked ?? 0 }}
        };

        // FIX: Automatically lock the study sections if the student resumes inside an exam!
        let isExamLocked = (materialData[state.lesson] && materialData[state.lesson].is_exam) ? true : false;
        
        let pendingExamTarget = null;
        let activeFullscreenId = null;
        let activeFullscreenType = null;

        // --- GLOBAL IDLE TIMER (YOUTUBE-STYLE HIDER) ---
        let globalMediaIdleTimer = null;
        
        function handleMediaActivity() {
            document.body.classList.remove('media-idle');
            
            const fsControls = document.getElementById('fs-global-controls');
            if (fsControls && !fsControls.classList.contains('hidden')) {
                fsControls.classList.remove('opacity-0');
            }

            clearTimeout(globalMediaIdleTimer);
            globalMediaIdleTimer = setTimeout(() => {
                document.body.classList.add('media-idle');
                if (fsControls && !fsControls.classList.contains('hidden')) {
                    fsControls.classList.add('opacity-0');
                }
            }, 2500);
        }

        document.addEventListener('mousemove', handleMediaActivity);
        document.addEventListener('touchstart', handleMediaActivity);
        document.addEventListener('keydown', handleMediaActivity);

        // --- FULLSCREEN LOGIC ---
        function openMediaFullscreen(id, type) {
            const el = document.getElementById(id);
            if (!el) return;
            
            activeFullscreenId = id;
            activeFullscreenType = type;
            
            document.body.classList.add('fs-active'); 
            el.classList.add('media-fullscreen');
            
            document.getElementById('fs-global-controls').classList.remove('hidden');
            document.getElementById('fs-global-controls').classList.add('flex');
            
            handleMediaActivity(); 
            
            if (type === 'pdf' && pdfInstances[id]) {
                pdfInstances[id].hasSetScale = false; 
                renderPdfPage(id);
            }
            updateFsNavButtons();
        }

        function closeMediaFullscreen() {
            document.body.classList.remove('fs-active'); 
            const controls = document.getElementById('fs-global-controls');
            if (controls) controls.classList.add('hidden');

            if (activeFullscreenId) {
                const el = document.getElementById(activeFullscreenId);
                const currentId = activeFullscreenId;
                const currentType = activeFullscreenType;
                
                if (el) {
                    el.classList.remove('media-fullscreen');
                    if (currentType === 'pdf' && pdfInstances[currentId]) {
                        pdfInstances[currentId].hasSetScale = false;
                        setTimeout(() => renderPdfPage(currentId), 300); 
                    }
                }
            }
            activeFullscreenId = null;
            activeFullscreenType = null;
        }

        async function mediaFsNavigate(dir) {
            const oldContent = state.content;
            const oldLesson = state.lesson;
            const oldPdfId = `media-${state.lesson}-${state.content}`;
            const oldPdfInst = pdfInstances[oldPdfId];
            const oldPage = oldPdfInst ? oldPdfInst.pageNum : null;

            await navigateContent(dir); 

            const newPdfId = `media-${state.lesson}-${state.content}`;
            const newPdfInst = pdfInstances[newPdfId];
            const newPage = newPdfInst ? newPdfInst.pageNum : null;

            if (oldContent === state.content && oldLesson === state.lesson && oldPage !== newPage) {
                updateFsNavButtons();
                return; 
            }

            closeMediaFullscreen(); 
            
            setTimeout(() => {
                const activeContent = document.querySelector('.content-block.active');
                if (activeContent) {
                    const newMedia = activeContent.querySelector('.media-container');
                    if (newMedia) {
                        let type = 'image';
                        if (newMedia.classList.contains('pdf-container')) type = 'pdf';
                        if (newMedia.classList.contains('video-wrapper')) type = 'video';
                        openMediaFullscreen(newMedia.id, type);
                    }
                }
            }, 100); 
        }

        function updateFsNavButtons() {
            const pdfId = `media-${state.lesson}-${state.content}`;
            const pdfInst = pdfInstances[pdfId];
            
            let hidePrev = false;
            let hideNext = false;
            
            if (activeFullscreenType === 'video' || activeFullscreenType === 'image') {
                hidePrev = true;
                hideNext = true;
            } 
            else if (activeFullscreenType === 'pdf' && pdfInst) {
                if (pdfInst.pageNum <= 1) hidePrev = true;
                if (pdfInst.pageNum >= pdfInst.doc.numPages) hideNext = true;
            } else {
                hidePrev = true;
                hideNext = true;
            }

            document.getElementById('fs-btn-prev').style.display = hidePrev ? 'none' : 'flex';
            document.getElementById('fs-btn-next').style.display = hideNext ? 'none' : 'flex';
        }

        // --- EXAM CONFIRM MODAL LOGIC ---
        function showExamConfirm() {
            const modal = document.getElementById('exam-confirm-modal');
            const box = document.getElementById('exam-confirm-modal-box');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
            }, 10);
        }

        function closeExamConfirm() {
            const modal = document.getElementById('exam-confirm-modal');
            const box = document.getElementById('exam-confirm-modal-box');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
            pendingExamTarget = null;
        }

        function confirmStartExam() {
            isExamLocked = true;
            closeExamConfirm();
            
            if (pendingExamTarget !== null) {
                saveProgressToServer();
                state.lesson = pendingExamTarget;
                state.content = 0;
                if (state.lesson > state.highestUnlockedLesson) {
                    state.highestUnlockedLesson = state.lesson;
                }
                pendingExamTarget = null;
                renderState();
            }
        }

        // --- CUSTOM MODAL LOGIC ---
        let modalCallback = null;
        function showCustomAlert(title, message, type = 'error', callback = null) {
            const modal = document.getElementById('custom-modal');
            const box = document.getElementById('custom-modal-box');
            const icon = document.getElementById('custom-modal-icon');
            const btn = document.getElementById('custom-modal-btn');
            
            modalCallback = callback;
            document.getElementById('custom-modal-title').innerText = title;
            document.getElementById('custom-modal-message').innerText = message;

            if (type === 'success') {
                icon.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-green-50 text-green-500';
                icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                btn.className = 'w-full px-4 py-3 text-white font-bold rounded-xl transition shadow-md bg-green-600 hover:bg-green-700';
            } else {
                icon.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-red-50 text-red-500';
                icon.innerHTML = '<i class="fas fa-lock"></i>';
                btn.className = 'w-full px-4 py-3 text-white font-bold rounded-xl transition shadow-md bg-red-600 hover:bg-red-700';
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
            }, 10);
        }

        function closeCustomModal() {
            const modal = document.getElementById('custom-modal');
            const box = document.getElementById('custom-modal-box');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            setTimeout(() => { 
                modal.classList.add('hidden'); 
                if (modalCallback) modalCallback();
            }, 300);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initVideoPlayers();
            renderState();
        });

        let isMobileTocOpen = false;
        function toggleMobileTOC() {
            if (window.innerWidth >= 1024) return;
            const dropdown = document.getElementById('mobile-toc-dropdown');
            const overlay = document.getElementById('mobile-toc-overlay');
            isMobileTocOpen = !isMobileTocOpen;
            if (isMobileTocOpen) {
                overlay.classList.remove('hidden');
                dropdown.classList.remove('pointer-events-none');
                setTimeout(() => { overlay.classList.remove('opacity-0'); dropdown.classList.remove('-translate-y-full'); }, 10);
            } else {
                overlay.classList.add('opacity-0');
                dropdown.classList.add('-translate-y-full');
                dropdown.classList.add('pointer-events-none');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        function getAnswerData(id, type, isExam) {
            const prefix = isExam ? 'exam_answer_' : 'answer_';
            const inputName = prefix + id;
            
            if (type === 'mcq' || type === 'true_false') {
                const checked = document.querySelector(`input[name="${inputName}"]:checked`);
                return checked ? checked.value : null;
            } 
            else if (type === 'text') {
                const textarea = document.querySelector(`textarea[name="${inputName}"]`);
                return textarea && textarea.value.trim() !== '' ? textarea.value : null;
            }
            else if (type === 'checkbox') {
                const checkboxes = document.querySelectorAll(`input[name="${inputName}[]"]:checked`);
                if (checkboxes.length > 0) {
                    return Array.from(checkboxes).map(cb => cb.value).join(',');
                }
                return null;
            }
            return null;
        }

        function lockQuizQuestion(id, type, feedbackType) {
            const inputs = document.querySelectorAll(`[name="answer_${id}"], [name="answer_${id}[]"]`);
            inputs.forEach(input => input.disabled = true);

            const container = document.getElementById(`quiz-feedback-${id}`);
            if (container) {
                let html = '';
                if (feedbackType === 'recorded_as_is') {
                    html = '<div class="p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3 text-blue-700 font-bold"><i class="fas fa-info-circle text-xl"></i> Answer recorded as is.</div>';
                } else if (feedbackType === 'correct') {
                    html = '<div class="p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-700 font-bold"><i class="fas fa-check-circle text-xl"></i> Your answer is correct!</div>';
                } else {
                    html = '<div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-700 font-bold"><i class="fas fa-times-circle text-xl"></i> Your answer is incorrect.</div>';
                }
                container.innerHTML = html;
                container.style.display = 'block';
            }
            renderState(); 
        }

        function renderState() {
            document.querySelectorAll('.lesson-container').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.content-block').forEach(el => el.classList.remove('active'));

            const activeLessonEl = document.getElementById(`lesson-${state.lesson}`);
            const activeContentEl = document.getElementById(`content-${state.lesson}-${state.content}`);
            
            if (activeLessonEl) activeLessonEl.classList.add('active');
            if (activeContentEl) {
                activeContentEl.classList.add('active');
                pauseAllVideos(); 
                checkAndLoadPDF(state.lesson, state.content);
            }

            const currentData = materialData[state.lesson];
            const currentItem = currentData.items[state.content];
            document.getElementById('top-lesson-title').innerText = currentData.title;
            const counterText = currentData.is_exam 
                                ? `Question ${state.content + 1} of ${currentData.items.length}` 
                                : `Item ${state.content + 1} of ${currentData.items.length}`;
            document.getElementById('bottom-content-counter').innerText = counterText;
            document.getElementById('mobile-dropdown-counter').innerText = currentData.is_exam ? counterText : `Section ${state.lesson + 1} • ${counterText}`;

            const progressPct = ((state.content + 1) / currentData.items.length) * 100;
            document.getElementById('lesson-progress-bar').style.width = `${progressPct}%`;

            const totalContents = materialData.reduce((acc, curr) => acc + curr.items.length, 0);
            let contentsPassed = 0;
            for(let i=0; i<state.highestUnlockedLesson; i++) contentsPassed += materialData[i].items.length;
            if (state.lesson === state.highestUnlockedLesson) contentsPassed += state.content;
            
            const globalPct = Math.min(100, Math.round((contentsPassed / totalContents) * 100));
            document.getElementById('top-progress-bar').style.width = `${globalPct}%`;
            document.getElementById('top-progress-text').innerText = `${globalPct}%`;

            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');
            const btnNextText = document.getElementById('btn-next-text');
            const btnNextTextMobile = document.getElementById('btn-next-text-mobile');
            const btnNextIcon = document.getElementById('btn-next-icon');

            const pdfId = `media-${state.lesson}-${state.content}`;
            const pdfInst = pdfInstances[pdfId];
            const isFirstExamContent = (currentData.is_exam && state.content === 0 && (state.lesson === 0 || !materialData[state.lesson - 1].is_exam));

            if (state.lesson === 0 && state.content === 0 && (!pdfInst || pdfInst.pageNum === 1)) {
                btnPrev.classList.add('opacity-50', 'cursor-not-allowed');
                btnPrev.disabled = true;
            } else if (isExamLocked && isFirstExamContent && (!pdfInst || pdfInst.pageNum === 1)) {
                btnPrev.classList.add('opacity-50', 'cursor-not-allowed');
                btnPrev.disabled = true;
            } else {
                btnPrev.classList.remove('opacity-50', 'cursor-not-allowed');
                btnPrev.disabled = false;
            }

            const isQuiz = !currentData.is_exam && currentItem && currentItem.type !== 'content';
            let isQuizLocked = false;
            if (isQuiz) {
                const inputEl = document.querySelector(`[name="answer_${currentItem.id}"]`) || document.querySelector(`[name="answer_${currentItem.id}[]"]`);
                if (inputEl && inputEl.disabled) isQuizLocked = true;
            }

            btnNext.className = "px-6 sm:px-8 py-3 sm:py-3.5 text-white font-bold rounded-xl transition bg-[#a52a2a] shadow-lg flex items-center gap-2";
            btnNextIcon.className = 'fas fa-arrow-right';

            let isLastContent = (state.content === currentData.items.length - 1);
            if (pdfInst && pdfInst.pageNum < pdfInst.doc.numPages) {
                isLastContent = false;
            }

            if (isQuiz && !isQuizLocked) {
                btnNextText.innerText = "Submit Answer";
                btnNextTextMobile.innerText = "Submit";
                btnNextIcon.className = "fas fa-paper-plane";
                btnNext.classList.add('bg-blue-600', 'hover:bg-blue-700', 'shadow-blue-600/20');
            } else {
                if (isLastContent) {
                    if (state.lesson === materialData.length - 1) {
                        btnNextText.innerText = "Finish Module";
                        btnNextTextMobile.innerText = "Finish";
                        btnNextIcon.className = "fas fa-flag-checkered";
                        btnNext.classList.add('bg-green-600', 'hover:bg-green-700', 'shadow-green-600/20');
                    } else {
                        btnNextText.innerText = materialData[state.lesson + 1].is_exam ? "Start Exam" : "Next Section";
                        btnNextTextMobile.innerText = materialData[state.lesson + 1].is_exam ? "Exam" : "Next";
                        btnNext.classList.add('bg-[#a52a2a]', 'hover:bg-red-800', 'shadow-[#a52a2a]/20');
                    }
                } else {
                    if (pdfInst && pdfInst.pageNum < pdfInst.doc.numPages) {
                        btnNextText.innerText = "Next Page";
                        btnNextTextMobile.innerText = "Page";
                    } else {
                        btnNextText.innerText = "Next";
                        btnNextTextMobile.innerText = "Next";
                    }
                    btnNext.classList.add('bg-[#a52a2a]', 'hover:bg-red-800', 'shadow-[#a52a2a]/20');
                }
            }

            updateSidebar();
            if (activeFullscreenId) updateFsNavButtons();
            document.getElementById('main-scroll-area').scrollTop = 0;
        }

        async function navigateContent(direction) {
            const currentSection = materialData[state.lesson];
            const currentItem = currentSection.items[state.content];
            const isQuiz = !currentSection.is_exam && currentItem && currentItem.type !== 'content';

            if (direction === 1 && isQuiz) {
                const answerData = getAnswerData(currentItem.id, currentItem.type, false);
                if (!answerData) {
                    showCustomAlert("Answer Required", "You must select or type an answer to proceed.");
                    return; 
                }

                const isLocked = document.querySelector(`[name="answer_${currentItem.id}"]`)?.disabled || 
                                 document.querySelector(`[name="answer_${currentItem.id}[]"]`)?.disabled;

                if (!isLocked) {
                    const btn = document.getElementById('btn-next');
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
                    btn.disabled = true;

                    const result = await saveProgressToServer(true); 

                    btn.innerHTML = originalHtml;
                    btn.disabled = false;

                    if(result && result.success) {
                        lockQuizQuestion(currentItem.id, currentItem.type, result.feedback_type);
                    } else {
                        showCustomAlert("Error", "Failed to submit answer. Check connection.");
                    }
                    return; 
                }
            }

            if (!isQuiz && direction === 1) {
                saveProgressToServer(false);
            }

            const pdfId = `media-${state.lesson}-${state.content}`;
            const pdfInst = pdfInstances[pdfId];

            if (pdfInst) {
                if (direction === 1 && pdfInst.pageNum < pdfInst.doc.numPages) {
                    pdfInst.pageNum++;
                    renderPdfPage(pdfId);
                    renderState();
                    return;
                } else if (direction === -1 && pdfInst.pageNum > 1) {
                    pdfInst.pageNum--;
                    renderPdfPage(pdfId);
                    renderState();
                    return;
                }
            }

            if (direction === 1) {
                if (state.content < currentSection.items.length - 1) {
                    state.content++;
                } else {
                    if (state.lesson < materialData.length - 1) {
                        const nextIsExam = materialData[state.lesson + 1].is_exam;
                        if (!isExamLocked && nextIsExam && !currentSection.is_exam) {
                            pendingExamTarget = state.lesson + 1;
                            showExamConfirm();
                            return;
                        }
                        state.lesson++;
                        state.content = 0;
                        if (state.lesson > state.highestUnlockedLesson) {
                            state.highestUnlockedLesson = state.lesson;
                        }
                    } else {
                        finishModule();
                        return;
                    }
                }
            } else {
                if (state.content > 0) {
                    state.content--;
                } else {
                    if (state.lesson > 0) {
                        const prevIsExam = materialData[state.lesson - 1].is_exam;
                        if (isExamLocked && currentSection.is_exam && !prevIsExam) {
                            showCustomAlert("Locked", "You cannot go back to the study lessons once the examination has started.");
                            return;
                        }
                        state.lesson--;
                        state.content = materialData[state.lesson].items.length - 1;
                        
                        const newPdfId = `media-${state.lesson}-${state.content}`;
                        if (pdfInstances[newPdfId] && pdfInstances[newPdfId].doc) {
                            pdfInstances[newPdfId].pageNum = pdfInstances[newPdfId].doc.numPages;
                        }
                    }
                }
            }
            renderState();
        }

        function attemptGoToLesson(targetLessonIdx) {
            const targetIsExam = materialData[targetLessonIdx].is_exam;
            const currentSection = materialData[state.lesson];

            if (isExamLocked && !targetIsExam) {
                showCustomAlert("Examination Active", "You are currently taking the examination. You cannot go back to study lessons.");
                return;
            }

            if (targetLessonIdx <= state.highestUnlockedLesson) {
                if (!isExamLocked && targetIsExam && !currentSection.is_exam) {
                    pendingExamTarget = targetLessonIdx;
                    showExamConfirm();
                    return;
                }
                saveProgressToServer(false); 
                state.lesson = targetLessonIdx;
                state.content = 0; 
                renderState();
            } else {
                showCustomAlert("Section Locked", "You must complete the previous sections before accessing this one.");
            }
        }

        function updateSidebar() {
            for (let i = 0; i < materialData.length; i++) {
                const btn = document.getElementById(`toc-btn-${i}`);
                const icon = btn ? btn.querySelector('.toc-icon') : null;
                const title = btn ? btn.querySelector('.toc-title') : null;
                const lock = btn ? btn.querySelector('.toc-lock') : null;
                const check = btn ? btn.querySelector('.toc-status') : null;

                const mBtn = document.getElementById(`mobile-toc-btn-${i}`);
                const mIcon = mBtn ? mBtn.querySelector('.mobile-toc-icon') : null;
                const mTitle = mBtn ? mBtn.querySelector('.mobile-toc-title') : null;
                const mLock = mBtn ? mBtn.querySelector('.mobile-toc-lock') : null;
                const mCheck = mBtn ? mBtn.querySelector('.mobile-toc-status') : null;

                const resetStyles = (b, iEl, t, l, c) => {
                    if(!b) return;
                    b.className = 'w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600 ' + (materialData[i].is_exam ? 'bg-red-50/40 mt-2' : '');
                    iEl.className = 'toc-icon mobile-toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500';
                    t.className = 'font-bold text-sm leading-tight truncate toc-title mobile-toc-title text-gray-700';
                    l.classList.add('hidden');
                    c.classList.add('hidden');
                };

                resetStyles(btn, icon, title, lock, check);
                resetStyles(mBtn, mIcon, mTitle, mLock, mCheck);

                const applyState = (b, iEl, t, l, c) => {
                    if(!b) return;
                    
                    if (isExamLocked && !materialData[i].is_exam) {
                        b.classList.add('opacity-50', 'cursor-not-allowed');
                        l.classList.remove('hidden');
                        l.innerHTML = '<i class="fas fa-ban"></i>'; 
                    }
                    else if (i > state.highestUnlockedLesson) {
                        b.classList.add('opacity-50', 'cursor-not-allowed');
                        l.classList.remove('hidden');
                        l.innerHTML = '<i class="fas fa-lock"></i>';
                    } else if (i < state.highestUnlockedLesson) {
                        b.classList.add('hover:bg-gray-100', 'cursor-pointer');
                        c.classList.remove('hidden');
                        iEl.classList.replace('bg-gray-200', 'bg-green-100');
                        iEl.classList.replace('text-gray-500', 'text-green-600');
                    } else {
                        b.classList.add('hover:bg-gray-100', 'cursor-pointer');
                    }

                    if (i === state.lesson) {
                        b.classList.add('bg-[#a52a2a]/10', 'border-[#a52a2a]/20');
                        b.classList.remove('hover:bg-gray-100', 'border-transparent', 'opacity-50', 'bg-red-50/30');
                        iEl.classList.replace('bg-gray-200', 'bg-[#a52a2a]');
                        iEl.classList.replace('text-gray-500', 'text-white');
                        t.classList.replace('text-gray-700', 'text-[#a52a2a]');
                    }
                };

                applyState(btn, icon, title, lock, check);
                applyState(mBtn, mIcon, mTitle, mLock, mCheck);
            }
        }

        async function finishModule() {
            await saveProgressToServer(false); 

            document.getElementById('top-progress-bar').style.width = '100%';
            document.getElementById('top-progress-text').innerText = '100%';
            
            const btn = document.getElementById('btn-next');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            try {
                const response = await fetch(`{{ route('dashboard.materials.complete', $material->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.passed) {
                        if (typeof confetti === 'function') {
                            confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 }, colors: ['#a52a2a', '#22c55e', '#fbbf24', '#3b82f6'] });
                        }
                        showCustomAlert("Congratulations!", "You have passed this module! Redirecting...", "success", function() {
                            window.location.href = data.redirect_url;
                        });
                    } else {
                        window.location.href = data.redirect_url;
                    }
                } else {
                    showCustomAlert("Error", data.message || "Failed to process completion.");
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                showCustomAlert("Error", "A network error occurred. Please check your connection.");
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        async function saveProgressToServer(waitForResult = false) {
            const currentSection = materialData[state.lesson];
            if (!currentSection || !currentSection.items) return { success: false };
            
            const currentItem = currentSection.items[state.content];
            let answerData = null;

            if (currentItem && currentItem.type !== 'content') {
                answerData = getAnswerData(currentItem.id, currentItem.type, currentSection.is_exam);
            }

            const payload = {
                lesson_index: state.lesson,
                content_index: state.content,
                highest_unlocked: state.highestUnlockedLesson,
                is_exam_locked: isExamLocked,
                is_exam: currentSection.is_exam,
                question_id: currentItem ? currentItem.id : null,
                question_type: currentItem ? currentItem.type : null,
                answer_data: answerData
            };

            try {
                const response = await fetch('{{ route("dashboard.materials.progress", $material->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                if (waitForResult) {
                    return await response.json();
                } else {
                    response.json(); 
                    return { success: true };
                }
            } catch (error) {
                console.error('Error saving progress:', error);
                return { success: false };
            }
        }

        // --- VIDEO SCRIPTS ---
        function initVideoPlayers() {
            document.querySelectorAll('.custom-video').forEach(video => {
                const wrapper = video.closest('.video-wrapper');
                const playBtn = wrapper.querySelector('.play-btn i');
                const slider = wrapper.querySelector('.video-progress-slider');
                const currentTimeEl = wrapper.querySelector('.current-time');
                const durationEl = wrapper.querySelector('.duration');
                
                slider.style.background = `linear-gradient(to right, #a52a2a 0%, rgba(255,255,255,0.3) 0%)`;

                const updateDur = () => { durationEl.innerText = formatTime(video.duration); };
                video.addEventListener('loadedmetadata', updateDur);
                if (video.readyState >= 1) updateDur();

                video.addEventListener('timeupdate', () => {
                    if (video.duration && slider.dataset.dragging !== 'true') {
                        const percent = (video.currentTime / video.duration) * 100;
                        slider.value = percent;
                        slider.style.background = `linear-gradient(to right, #a52a2a ${percent}%, rgba(255,255,255,0.3) ${percent}%)`;
                        currentTimeEl.innerText = formatTime(video.currentTime);
                    }
                });

                // FIX: Better Drag & Skip Logic
                slider.addEventListener('mousedown', () => slider.dataset.dragging = 'true');
                slider.addEventListener('touchstart', () => slider.dataset.dragging = 'true');
                
                // While dragging, just update the visual UI (don't choke the video player)
                slider.addEventListener('input', (e) => {
                    slider.dataset.dragging = 'true';
                    if (video.duration) {
                        const percent = e.target.value;
                        currentTimeEl.innerText = formatTime((percent / 100) * video.duration);
                        slider.style.background = `linear-gradient(to right, #a52a2a ${percent}%, rgba(255,255,255,0.3) ${percent}%)`;
                    }
                });

                // When released, actually skip the video to the new time
                slider.addEventListener('change', (e) => {
                    if (video.duration) {
                        const percent = e.target.value;
                        video.currentTime = (percent / 100) * video.duration;
                    }
                    slider.dataset.dragging = 'false';
                });

                // Fallback catch if mouse leaves the slider before releasing
                document.addEventListener('mouseup', () => {
                    if (slider.dataset.dragging === 'true') slider.dataset.dragging = 'false';
                });
                document.addEventListener('touchend', () => {
                    if (slider.dataset.dragging === 'true') slider.dataset.dragging = 'false';
                });

                video.addEventListener('play', () => playBtn.className = 'fas fa-pause');
                video.addEventListener('pause', () => playBtn.className = 'fas fa-play');
                video.addEventListener('ended', () => playBtn.className = 'fas fa-redo');
            });
        }

        function togglePlay(id) { const video = document.getElementById(id).querySelector('.custom-video'); if (video.paused) video.play(); else video.pause(); }
        function pauseAllVideos() { document.querySelectorAll('.custom-video').forEach(v => v.pause()); }
        function toggleMute(id, event) { const video = document.getElementById(id).querySelector('.custom-video'); const icon = event.currentTarget.querySelector('i'); video.muted = !video.muted; icon.className = video.muted ? 'fas fa-volume-mute' : 'fas fa-volume-up'; }
        function changeSpeed(id, speed) { document.getElementById(id).querySelector('.custom-video').playbackRate = parseFloat(speed); }
        function formatTime(seconds) { if (isNaN(seconds)) return "0:00"; const m = Math.floor(seconds / 60); const s = Math.floor(seconds % 60); return `${m}:${s < 10 ? '0' : ''}${s}`; }
        
        // Retained fallback just in case HTML still expects it
        function seekVideoInput(e, id) { }

        // --- PDF SCRIPTS ---
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const pdfInstances = {};

        function checkAndLoadPDF(lessonIdx, contentIdx) {
            const id = `media-${lessonIdx}-${contentIdx}`;
            const container = document.getElementById(id);
            
            // FIX: Must check if the container is actually a PDF container!
            // Otherwise videos/images will crash the renderState function!
            if (!container || !container.classList.contains('pdf-container')) return; 

            if (pdfInstances[id]) {
                pdfInstances[id].hasSetScale = false; 
                renderPdfPage(id);
                return; 
            }

            const url = container.dataset.pdfUrl;
            const canvas = container.querySelector('.pdf-canvas');
            const ctx = canvas.getContext('2d');
            const loadingSpinner = container.querySelector('.pdf-loading');
            loadingSpinner.classList.remove('hidden');

            pdfjsLib.getDocument(url).promise.then(pdfDoc => {
                pdfInstances[id] = { doc: pdfDoc, pageNum: 1, scale: 1.0, hasSetScale: false, canvas: canvas, ctx: ctx, container: container };
                container.querySelector('.pdf-page-count').textContent = pdfDoc.numPages;
                loadingSpinner.classList.add('hidden');
                renderPdfPage(id);
            }).catch(err => {
                console.error('PDF Load Error:', err);
                const errorMessage = err.message ? err.message : "Unknown Error";
                loadingSpinner.innerHTML = `<i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-2"></i><span class="text-sm font-bold text-gray-800">Error Loading PDF</span><span class="text-xs text-gray-500 mt-1 px-4 text-center max-w-sm">${errorMessage}</span>`;
            });
        }

        function renderPdfPage(id) {
            const instance = pdfInstances[id];
            if (!instance) return;

            instance.doc.getPage(instance.pageNum).then(page => {
                if (!instance.hasSetScale) {
                    const unscaledViewport = page.getViewport({ scale: 1.0 });
                    const renderArea = instance.container.querySelector('.pdf-render-area');
                    
                    const targetWidth = renderArea.clientWidth - 32; 
                    const targetHeight = renderArea.clientHeight - 32; 
                    
                    if (targetWidth > 0 && targetHeight > 0) {
                        const scaleW = targetWidth / unscaledViewport.width;
                        const scaleH = targetHeight / unscaledViewport.height;
                        
                        instance.scale = Math.min(scaleW, scaleH);
                        
                        if (instance.scale > 1.0) instance.scale = 1.0; 
                        if (instance.scale < 0.3) instance.scale = 0.3;
                        
                        instance.hasSetScale = true; 
                    }
                }

                const viewport = page.getViewport({ scale: instance.scale });
                const outputScale = window.devicePixelRatio || 1; 
                
                instance.canvas.width = Math.floor(viewport.width * outputScale);
                instance.canvas.height = Math.floor(viewport.height * outputScale);
                
                instance.canvas.style.width = Math.floor(viewport.width) + "px";
                instance.canvas.style.height = Math.floor(viewport.height) + "px";

                const transform = outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null;
                const renderContext = { canvasContext: instance.ctx, transform: transform, viewport: viewport };
                
                page.render(renderContext);
                
                instance.container.querySelector('.pdf-page-num').textContent = instance.pageNum;
                instance.container.querySelector('.pdf-scale').textContent = Math.round(instance.scale * 100) + '%';
            }).catch(err => {
                console.log("PDF render cancelled", err);
            });
        }

        function pdfZoomIn(id) { if(pdfInstances[id]) { pdfInstances[id].scale += 0.02; renderPdfPage(id); } }
        function pdfZoomOut(id) { if(pdfInstances[id] && pdfInstances[id].scale > 0.04) { pdfInstances[id].scale -= 0.02; renderPdfPage(id); } }
    </script>
</body>
</html>