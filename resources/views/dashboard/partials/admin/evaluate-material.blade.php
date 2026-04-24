<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluating: {{ $material->title }} - LMS</title>
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
        
        /* Using transform: none prevents the element from trapping fixed-position children! */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: none; }
        }

        .video-wrapper { position: relative; width: 100%; border-radius: 1rem; overflow: hidden; background: #000; }
        .video-controls {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 1.5rem 1rem 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;
            opacity: 0; transition: opacity 0.3s;
        }
        .video-wrapper:hover .video-controls { opacity: 1; }
        
        .video-progress-slider { -webkit-appearance: none; width: 100%; background: rgba(255,255,255,0.3); height: 6px; border-radius: 3px; outline: none; }
        .video-progress-slider::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 14px; height: 14px; border-radius: 50%; background: #7f1d1d; cursor: pointer; }

        /* Fullscreen Video Overrides */
        body.media-idle .video-wrapper { cursor: none !important; }
        body.media-idle .video-wrapper .video-controls { opacity: 0 !important; pointer-events: none; }
        body.media-idle .media-fullscreen { cursor: none !important; }
        body.media-idle .media-fullscreen .pdf-toolbar,
        body.media-idle .media-fullscreen .fs-toggle-btn { opacity: 0 !important; pointer-events: none; transition: opacity 0.4s; }

        .pdf-container { background: #e5e7eb; border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column; height: 75vh; width: 100%; }
        .pdf-toolbar { background: #1f2937; color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; }
        
        /* FIX: Changed overflow-y to auto (both axes) and added text-align center */
        .pdf-render-area { overflow: auto; padding: 1rem; flex-grow: 1; background: #d1d5db; position: relative; text-align: center; }
        
        /* BULLETPROOF FULLSCREEN OVERRIDE: Completely destroys all layout constraints */
        body.fs-active header,
        body.fs-active aside,
        body.fs-active #sidebar,
        body.fs-active #sidebarBackdrop,
        body.fs-active #bottom-nav-bar {
            display: none !important;
        }

        body.fs-active #content-area,
        body.fs-active main,
        body.fs-active #main-scroll-area,
        body.fs-active .lesson-container,
        body.fs-active .content-block {
            padding: 0 !important;
            margin: 0 !important;
            z-index: 99999 !important;
            position: static !important;
            transform: none !important;
            animation: none !important;
            will-change: auto !important;
        }

        .media-fullscreen {
            position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important;
            z-index: 999999 !important; background: rgba(15, 15, 15, 0.95) !important; backdrop-filter: blur(8px);
            border-radius: 0 !important; border: none !important; display: flex; flex-direction: column;
            justify-content: center; align-items: center; margin: 0 !important; padding: 0 !important;
        }
        .media-fullscreen > img, .media-fullscreen > video { max-height: 100vh !important; max-width: 100vw !important; object-fit: contain; margin: auto; }
        .media-fullscreen .pdf-toolbar { width: 100%; background: rgba(20, 25, 30, 0.95) !important; }
        .media-fullscreen .pdf-render-area { width: 100%; flex-grow: 1; background: transparent !important; }
        .media-fullscreen .video-controls { padding-bottom: 2rem; background: linear-gradient(to top, rgba(0,0,0,0.95), transparent); }
        .media-fullscreen .fs-toggle-btn { display: none !important; }

        /* Custom Radio Buttons for Rubric (Dark Red) */
        .eval-radio input:checked + div { background-color: #7f1d1d; color: white; border-color: #7f1d1d; } 
    </style>
</head>

@php
    // --- LOAD TIMELINE DATA ---
    $timeline = collect();
    if(isset($material->lessons)) {
        foreach($material->lessons as $lesson) {
            $timeline->push((object)[
                'is_exam' => false,
                'id' => 'lesson_'.$lesson->id,
                'title' => $lesson->title,
                'items' => $lesson->contents,
                'order_val' => $lesson->sort_order ?? 0 // Use the actual sort order
            ]);
        }
    }
    if(isset($material->exams) && $material->exams->count() > 0) {
        $groupedExams = $material->exams->groupBy(function($e) { return $e->created_at ? \Carbon\Carbon::parse($e->created_at)->format('Y-m-d H:i:s') : '0'; });
        $examCounter = 1;
        foreach($groupedExams as $time => $questions) {
            $timeline->push((object)[
                'is_exam' => true,
                'id' => 'exam_group_'.$examCounter,
                'title' => 'Examination',
                'items' => $questions,
                'order_val' => 999999 + $examCounter // Force exams to stay at the very end
            ]);
            $examCounter++;
        }
    }
    // Sort by our new custom order value instead of creation date
    $timeline = $timeline->sortBy('order_val')->values();

    // --- FETCH GLOBAL RUBRIC ---
    $rubricData = [];
    $passingRate = 75;
    if (\Illuminate\Support\Facades\Storage::exists('global_rubric.json')) {
        $parsed = json_decode(\Illuminate\Support\Facades\Storage::get('global_rubric.json'), true);
        if (isset($parsed['rubric'])) {
            $rubricData = $parsed['rubric'];
            $passingRate = $parsed['passing_rate'] ?? 75;
        } else {
            $rubricData = $parsed; 
        }
    }
@endphp

<body class="bg-gray-50 font-sans text-gray-900 h-screen overflow-hidden flex flex-col selection:bg-red-900 selection:text-white">

    <header class="bg-white border-b border-gray-200 h-16 shrink-0 flex items-center justify-between px-4 lg:px-6 z-50 shadow-sm relative">
        <div class="flex items-center gap-4 w-1/4 shrink-0">
            <a href="{{ url('/dashboard') }}" 
               class="flex items-center text-gray-500 hover:text-red-900 font-bold transition-colors group px-3 py-2 rounded-xl hover:bg-red-50">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                <span class="hidden sm:inline">Back to Manage</span>
            </a>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-black uppercase tracking-widest rounded">Admin Evaluation Mode</span>
            </div>
            <h1 class="font-black text-gray-900 text-lg truncate max-w-md">{{ $material->title }}</h1>
        </div>

        <div class="w-1/4 flex items-center justify-end gap-4 shrink-0">
            <button onclick="toggleMobileEval()" class="lg:hidden px-4 py-2 bg-red-900 text-white text-xs font-bold rounded-lg shadow-sm">
                <i class="fas fa-clipboard-check mr-1"></i> Evaluate
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden relative z-10">

        {{-- LEFT SIDEBAR: TOC & DETAILS --}}
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col z-20 shrink-0 hidden lg:flex h-full shadow-sm relative">
            
            <div class="p-5 border-b border-gray-100 bg-gray-50 shrink-0">
                <div class="w-full h-32 bg-gray-200 rounded-xl overflow-hidden mb-3 border border-gray-200">
                    @if($material->thumbnail)
                        <img src="{{ asset('storage/' . $material->thumbnail) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center"><i class="fas fa-book-open text-3xl text-gray-300"></i></div>
                    @endif
                </div>
                
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @forelse($material->tags as $tag)
                        <span class="px-2 py-1 bg-gray-200 text-gray-600 text-[9px] font-bold uppercase tracking-widest rounded-md">{{ $tag->name }}</span>
                    @empty
                        <span class="text-xs text-gray-400 italic">No tags</span>
                    @endforelse
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-600 font-medium">
                    <div class="w-6 h-6 rounded-full bg-red-900/10 text-red-900 flex items-center justify-center shrink-0"><i class="fas fa-chalkboard-teacher text-[10px]"></i></div>
                    <span class="truncate">{{ $material->instructor->first_name ?? '' }} {{ $material->instructor->last_name ?? '' }}</span>
                </div>

                <div class="mt-3">
                    @if($material->is_public)
                        <span class="text-[13px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-bold uppercase tracking-widest">
                            Public Module
                        </span>
                    @else
                        <span class="text-[13px] bg-amber-200 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-widest">
                            Private Module
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="px-4 py-3 bg-white border-b border-gray-100 shrink-0 flex justify-between items-center">
                <h2 class="font-black text-gray-900 text-sm">Course Content</h2>
            </div>

            <nav class="flex-1 overflow-y-auto sidebar-scroll p-3 space-y-1">
                @foreach($timeline as $index => $section)
                    <button onclick="goToLesson({{ $index }})" id="toc-btn-{{ $index }}" 
                        class="w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600 hover:bg-gray-100 {{ $section->is_exam ? 'bg-red-50/40 mt-4' : '' }}">
                        <div class="toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500">
                            @if($section->is_exam) <i class="fas fa-star"></i> @else {{ $index + 1 }} @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm leading-tight truncate toc-title text-gray-700">{{ $section->title }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-bold mt-1 toc-meta text-gray-400">{{ $section->items->count() }} {{ $section->is_exam ? 'Questions' : 'Items' }}</p>
                        </div>
                    </button>
                @endforeach
            </nav>
        </aside>

        {{-- MIDDLE: MAIN CONTENT AREA --}}
        <main class="flex-1 flex flex-col min-w-0 bg-gray-50 h-full relative z-10 border-r border-gray-200">
            <div id="main-scroll-area" class="flex-1 overflow-y-auto w-full relative p-4 lg:p-8">
                <div class="w-full max-w-4xl mx-auto flex flex-col min-h-full">
                    
                    @foreach($timeline as $lessonIndex => $section)
                        <div id="lesson-{{ $lessonIndex }}" class="lesson-container w-full">
                            @forelse($section->items as $contentIndex => $block)
                                <div id="content-{{ $lessonIndex }}-{{ $contentIndex }}" class="content-block w-full">
                                    
                                    @if($section->is_exam)
                                        <div class="text-center mb-6 w-full"><span class="inline-block px-4 py-1.5 bg-red-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-sm">Examination Section</span></div>
                                    @endif

                                    @php
                                        $hasMedia = !empty($block->media_url);
                                        $isQuizOrExam = in_array($block->type, ['mcq', 'true_false', 'checkbox', 'text']);
                                        $isTextContent = ($block->type === 'content' && !empty($block->question_text));
                                    @endphp

                                    {{-- MEDIA PARSER --}}
                                    @if($hasMedia)
                                        @php
                                            $mediaUrl = str_starts_with($block->media_url, 'http') ? $block->media_url : asset('storage/' . $block->media_url);
                                            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? $mediaUrl, PATHINFO_EXTENSION));
                                            $isPdf = $ext === 'pdf';
                                            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp
                                        <div class="mb-6 w-full {{ ($isQuizOrExam || $isTextContent) ? 'max-w-2xl mx-auto' : 'max-w-4xl mx-auto' }}">
                                            @if($isPdf)
                                                <div class="pdf-container media-container shadow-sm border border-gray-200" data-pdf-url="{{ $mediaUrl }}" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                    <div class="pdf-toolbar shrink-0">
                                                        <div class="flex items-center gap-4">
                                                            <span class="text-sm font-bold"><i class="fas fa-file-pdf mr-2"></i> PDF Document (<span class="pdf-page-count">?</span> Pages)</span>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <button onclick="pdfZoomOut('media-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-red-400 transition"><i class="fas fa-search-minus"></i></button>
                                                            <span class="pdf-scale text-xs font-bold w-12 text-center">100%</span>
                                                            <button onclick="pdfZoomIn('media-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-red-400 transition"><i class="fas fa-search-plus"></i></button>
                                                            
                                                            {{-- Fullscreen Button --}}
                                                            <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'pdf')" class="fs-toggle-btn hover:text-red-400 transition ml-3 border-l border-gray-600 pl-3" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="pdf-render-area relative">
                                                        <div class="pdf-loading absolute inset-0 flex flex-col items-center justify-center bg-gray-100 z-10">
                                                            <i class="fas fa-circle-notch fa-spin text-3xl text-red-900 mb-3"></i>
                                                            <span class="text-sm font-bold text-gray-500 tracking-widest uppercase">Loading Document...</span>
                                                        </div>
                                                        {{-- Multi-page Canvases will be injected inside this wrapper automatically --}}
                                                        <div class="pdf-pages-wrapper"></div>
                                                    </div>
                                                </div>
                                            @elseif($isVideo)
                                                <div class="video-wrapper shadow-xl border border-gray-800 media-container" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                    <video class="w-full max-h-[60vh] object-contain custom-video" controls>
                                                        <source src="{{ $mediaUrl }}" type="video/{{ $ext === 'webm' ? 'webm' : 'mp4' }}">
                                                    </video>
                                                    <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'video')" class="fs-toggle-btn absolute top-4 right-4 bg-black/60 text-white w-10 h-10 flex items-center justify-center rounded-xl opacity-0 group-hover:opacity-100 transition hover:bg-red-900 shadow-lg z-20" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                </div>
                                            @elseif($isImage)
                                                <div class="rounded-2xl overflow-hidden bg-gray-200 border border-gray-200 flex justify-center w-full relative group media-container" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                    <img src="{{ $mediaUrl }}" class="object-contain max-h-[60vh] w-full">
                                                    <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'image')" class="fs-toggle-btn absolute top-4 right-4 bg-black/60 text-white w-10 h-10 flex items-center justify-center rounded-xl opacity-0 group-hover:opacity-100 transition hover:bg-red-900 shadow-lg" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- ANSWER KEY / TEXT PARSER --}}
                                    @if($isTextContent)
                                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100 prose prose-gray max-w-none text-gray-800 text-lg leading-relaxed mx-auto">
                                            {!! nl2br(e($block->question_text)) !!}
                                        </div>
                                    @elseif($isQuizOrExam)
                                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border-2 border-blue-200 relative overflow-hidden mx-auto max-w-2xl">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>
                                            <div class="flex justify-between items-start gap-4 mb-4">
                                                <h3 class="text-xl font-bold text-gray-900">{{ $block->question_text }}</h3>
                                                <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded border border-blue-100 shrink-0">Answer Key</span>
                                            </div>

                                            <div class="space-y-3">
                                                @if(in_array($block->type, ['mcq', 'true_false', 'checkbox']))
                                                    @foreach($block->options as $option)
                                                        <div class="flex items-center p-4 rounded-xl border {{ $option->is_correct ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                                                            <div class="w-6 h-6 rounded-full flex items-center justify-center mr-3 shrink-0 {{ $option->is_correct ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                                                                <i class="fas {{ $option->is_correct ? 'fa-check' : 'fa-times' }} text-xs"></i>
                                                            </div>
                                                            <span class="text-base {{ $option->is_correct ? 'text-green-900 font-bold' : 'text-gray-500' }}">{{ $option->option_text }}</span>
                                                        </div>
                                                    @endforeach
                                                @elseif($block->type === 'text')
                                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                                                        <div class="flex justify-between mb-2">
                                                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Accepted Answers:</h4>
                                                            <span class="text-[10px] font-bold {{ $block->is_case_sensitive ? 'text-red-500' : 'text-gray-400' }}">{{ $block->is_case_sensitive ? 'Case Sensitive' : 'Not Case Sensitive' }}</span>
                                                        </div>
                                                        <ul class="list-disc list-inside text-gray-800 ml-2 font-medium">
                                                            @forelse($block->options->where('is_correct', 1) as $opt)
                                                                <li>{{ $opt->option_text }}</li>
                                                            @empty
                                                                <li class="text-gray-400 italic list-none text-sm">No specific auto-grade answers provided.</li>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="content-block w-full active bg-white rounded-3xl p-12 shadow-sm border border-gray-100 text-center max-w-xl mx-auto">
                                    <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">No Content</h3>
                                </div>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- BOTTOM NAVIGATION --}}
            <div id="bottom-nav-bar" class="bg-white border-t border-gray-200 p-4 flex justify-between items-center shrink-0 w-full shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20">
                <button type="button" id="btn-prev" onclick="navigateContent(-1)" class="px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-2"><i class="fas fa-arrow-left"></i> Prev</button>
                <div class="text-center">
                    <span id="bottom-content-counter" class="text-xs font-black text-gray-400 uppercase tracking-widest">Content ? of ?</span>
                </div>
                <button type="button" id="btn-next" onclick="navigateContent(1)" class="px-5 py-2.5 bg-red-900 text-white font-bold rounded-xl hover:bg-red-950 transition shadow-md flex items-center gap-2">Next <i class="fas fa-arrow-right"></i></button>
            </div>
        </main>

        {{-- RIGHT SIDEBAR: RUBRIC EVALUATION --}}
        <aside id="evaluation-sidebar" class="w-80 lg:w-[400px] bg-white flex flex-col z-40 h-full shadow-2xl lg:shadow-none absolute right-0 lg:relative transform translate-x-full lg:translate-x-0 transition-transform duration-300">
            
            <div class="p-4 bg-red-900 text-white shrink-0 flex justify-between items-center shadow-md z-10">
                <div>
                    <h2 class="font-black text-lg flex items-center gap-2"><i class="fas fa-clipboard-check text-red-200"></i> Official Rubric</h2>
                    <p class="text-[10px] text-red-200 uppercase tracking-widest font-bold mt-0.5">Required to Pass: {{ $passingRate }}%</p>
                </div>
                <button onclick="toggleMobileEval()" class="lg:hidden w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition"><i class="fas fa-times"></i></button>
            </div>

            <div class="flex-1 overflow-y-auto sidebar-scroll p-4 bg-gray-50 border-l border-gray-200">
                
                {{-- SCORE DISPLAY --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm text-center mb-6">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Score</p>
                    <div class="text-3xl font-black text-gray-300 transition-colors duration-300" id="live-score-display">0%</div>
                    <div id="score-status-badge" class="mt-2 inline-block px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-400">Pending Evaluation</div>
                </div>

                {{-- CLEANER RATING GUIDE --}}
                <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm mb-6">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">Rating Guide</p>
                    <div class="flex justify-between text-center gap-1">
                        <div class="flex-1 bg-gray-50 rounded border border-gray-100 p-1"><span class="block font-bold text-gray-800 text-sm">1</span><span class="text-[9px] text-gray-500 font-medium">Poor</span></div>
                        <div class="flex-1 bg-gray-50 rounded border border-gray-100 p-1"><span class="block font-bold text-gray-800 text-sm">2</span><span class="text-[9px] text-gray-500 font-medium">Fair</span></div>
                        <div class="flex-1 bg-gray-50 rounded border border-gray-100 p-1"><span class="block font-bold text-gray-800 text-sm">3</span><span class="text-[9px] text-gray-500 font-medium">Avg</span></div>
                        <div class="flex-1 bg-gray-50 rounded border border-gray-100 p-1"><span class="block font-bold text-gray-800 text-sm">4</span><span class="text-[9px] text-gray-500 font-medium">Good</span></div>
                        <div class="flex-1 bg-gray-50 rounded border border-gray-100 p-1"><span class="block font-bold text-gray-800 text-sm">5</span><span class="text-[9px] text-gray-500 font-medium">Exc</span></div>
                    </div>
                </div>

                <form id="evaluation-form" class="space-y-6">
                    @php $globalItemCounter = 0; @endphp
                    @forelse($rubricData as $index => $category)
                        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                            <h4 class="font-black text-gray-900 text-sm mb-4 uppercase tracking-wide border-b border-gray-100 pb-2">{{ $category['category'] }}</h4>
                            
                            <div class="space-y-5">
                                @foreach($category['items'] as $item)
                                    <div class="rubric-item-group" data-item-index="{{ $globalItemCounter }}">
                                        <p class="text-xs text-gray-700 font-medium mb-2 leading-snug">{{ $item }}</p>
                                        <div class="flex justify-between gap-1.5">
                                            @for($i=1; $i<=5; $i++)
                                                <label class="flex-1 eval-radio cursor-pointer text-center group">
                                                    <input type="radio" name="item_{{ $globalItemCounter }}" value="{{ $i }}" class="hidden" onchange="calculateScore()">
                                                    <div class="py-1.5 rounded border border-gray-200 bg-gray-50 text-gray-500 font-bold text-xs transition-all group-hover:bg-gray-100">{{ $i }}</div>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    @php $globalItemCounter++; @endphp
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-sm text-gray-500 italic border border-gray-200 rounded-xl">No global rubric configured.</div>
                    @endforelse

                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                        <label class="font-bold text-gray-900 text-sm mb-1 block">Admin Remarks & Feedback <span class="text-red-500">*</span></label>
                        <p class="text-[10px] text-gray-500 mb-3 leading-tight">These comments will be sent to the instructor if returned to draft.</p>
                        <textarea id="admin-remarks" rows="4" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-red-900 outline-none transition-all resize-none" placeholder="Provide actionable feedback here..."></textarea>
                    </div>
                </form>
            </div>

            <div class="p-4 bg-white border-t border-gray-200 shrink-0 space-y-2 z-10">
                <button type="button" id="btn-approve" onclick="submitEvaluation('published')" disabled class="w-full py-3 bg-green-600 text-white font-bold rounded-xl shadow-md hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Approve & Publish
                </button>
                <button type="button" id="btn-reject" onclick="submitEvaluation('draft')" class="w-full py-3 bg-gray-100 text-gray-700 border border-gray-200 font-bold rounded-xl shadow-sm hover:bg-gray-200 hover:text-red-600 hover:border-red-200 transition flex items-center justify-center gap-2">
                    <i class="fas fa-undo"></i> Return to Draft
                </button>
            </div>
        </aside>

    </div>

    {{-- GLOBAL FULLSCREEN OVERLAY CONTROLS --}}
    <div id="fs-global-controls" class="fixed inset-0 pointer-events-none hidden z-[999999] transition-opacity duration-300 opacity-100">
        <button onclick="closeMediaFullscreen()" class="absolute top-4 right-4 sm:top-15 sm:right-8 pointer-events-auto bg-black/60 hover:bg-red-600 text-white rounded-full w-12 h-12 flex items-center justify-center backdrop-blur transition-colors shadow-2xl border border-white/10" title="Exit Full Screen">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-6 pointer-events-auto">
            <button onclick="mediaFsNavigate(-1)" class="bg-black/60 hover:bg-red-900 text-white rounded-full h-14 w-14 flex items-center justify-center backdrop-blur transition-colors shadow-2xl border border-white/10" id="fs-btn-prev">
                <i class="fas fa-chevron-left text-xl pr-1"></i>
            </button>
            
            <button onclick="mediaFsNavigate(1)" class="bg-black/60 hover:bg-red-900 text-white rounded-full h-14 w-14 flex items-center justify-center backdrop-blur transition-colors shadow-2xl border border-white/10" id="fs-btn-next">
                <i class="fas fa-chevron-right text-xl pl-1"></i>
            </button>
        </div>
    </div>

    {{-- SNACKBAR --}}
    <div id="eval-snackbar" class="fixed bg-gray-900 bottom-6 right-6 z-[9999] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white">
        <span id="eval-snackbar-message">Message here</span>
    </div>

    <script>
        const materialData = @json($timeline);
        const requiredPassingRate = {{ $passingRate }};
        const totalItems = {{ $globalItemCounter ?? 0 }};
        const maxScore = totalItems * 5;

        let state = { lesson: 0, content: 0 };
        
        let activeFullscreenId = null;
        let activeFullscreenType = null;
        let globalMediaIdleTimer = null;

        // --- RUBRIC SCORING LOGIC ---
        function calculateScore() {
            let currentScore = 0;
            let answeredItems = 0;

            document.querySelectorAll('.rubric-item-group').forEach((block) => {
                const itemIndex = block.dataset.itemIndex;
                const checked = block.querySelector(`input[name="item_${itemIndex}"]:checked`);
                if (checked) {
                    currentScore += parseInt(checked.value);
                    answeredItems++;
                }
            });

            const scoreDisplay = document.getElementById('live-score-display');
            const badge = document.getElementById('score-status-badge');
            const approveBtn = document.getElementById('btn-approve');

            if (answeredItems === 0 || maxScore === 0) {
                scoreDisplay.innerText = '0%';
                scoreDisplay.className = 'text-3xl font-black text-gray-300 transition-colors duration-300';
                badge.innerText = 'Pending Evaluation';
                badge.className = 'mt-2 inline-block px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-400';
                approveBtn.disabled = true;
                return;
            }

            const percentage = Math.round((currentScore / maxScore) * 100);
            scoreDisplay.innerText = percentage + '%';

            if (percentage >= requiredPassingRate) {
                scoreDisplay.className = 'text-3xl font-black text-green-500 transition-colors duration-300';
                badge.innerText = 'Passing Standard Met';
                badge.className = 'mt-2 inline-block px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest bg-green-100 text-green-700';
                approveBtn.disabled = (answeredItems < totalItems); // Only enable if ALL items are graded
            } else {
                scoreDisplay.className = 'text-3xl font-black text-red-500 transition-colors duration-300';
                badge.innerText = 'Below Minimum Standard';
                badge.className = 'mt-2 inline-block px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest bg-red-100 text-red-700';
                approveBtn.disabled = true;
            }
        }

        // --- SUBMISSION LOGIC ---
        async function submitEvaluation(targetStatus) {
            const remarks = document.getElementById('admin-remarks').value.trim();
            
            if (targetStatus === 'draft' && remarks === '') {
                showToast('You must provide remarks before returning a material to the draft state.', 'error');
                return;
            }

            // 1. Gather the specific score and Category for EVERY individual rubric item
            const evaluationDetails = [];
            document.querySelectorAll('.rubric-item-group').forEach((block) => {
                const itemIndex = block.dataset.itemIndex;
                const criteriaText = block.querySelector('p').innerText;
                const checkedRadio = block.querySelector(`input[name="item_${itemIndex}"]:checked`);
                const score = checkedRadio ? parseInt(checkedRadio.value) : 0;
                
                // Traverse up the DOM to find the category header
                const categoryBlock = block.closest('.bg-white.rounded-xl.border.p-4');
                const categoryTitle = categoryBlock ? categoryBlock.querySelector('h4').innerText : 'General';
                
                evaluationDetails.push({
                    category: categoryTitle,
                    criteria: criteriaText,
                    score: score,
                    max: 5
                });
            });

            const btn = targetStatus === 'published' ? document.getElementById('btn-approve') : document.getElementById('btn-reject');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving Evaluation...';
            btn.disabled = true;

            try {
                const response = await fetch('{{ route("dashboard.materials.toggle-status", $material->id) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        status: targetStatus,
                        admin_remarks: remarks,
                        score_percentage: document.getElementById('live-score-display').innerText,
                        evaluation_details: evaluationDetails
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    showToast('Evaluation saved successfully!', 'success');
                    setTimeout(() => {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = '{{ url("/dashboard") }}'; 
                        }
                    }, 1000);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                showToast(error.message || 'Network error occurred.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        function showToast(msg, type='success') {
            const sb = document.getElementById('eval-snackbar');
            sb.querySelector('span').innerText = msg;
            sb.classList.remove('translate-y-24', 'opacity-0');
            sb.classList.add(type === 'error' ? 'bg-[#a52a2a]' : 'bg-green-600');
            setTimeout(() => { sb.classList.add('translate-y-24', 'opacity-0'); }, 3000);
        }

        function toggleMobileEval() {
            const sidebar = document.getElementById('evaluation-sidebar');
            if (sidebar.classList.contains('translate-x-full')) {
                sidebar.classList.remove('translate-x-full');
            } else {
                sidebar.classList.add('translate-x-full');
            }
        }

        // --- NAVIGATION & RENDER LOGIC ---
        function renderState() {
            document.querySelectorAll('.lesson-container, .content-block').forEach(el => el.classList.remove('active'));
            
            const activeLessonEl = document.getElementById(`lesson-${state.lesson}`);
            const activeContentEl = document.getElementById(`content-${state.lesson}-${state.content}`);
            
            if (activeLessonEl) activeLessonEl.classList.add('active');
            if (activeContentEl) {
                activeContentEl.classList.add('active');
                checkAndLoadPDF(state.lesson, state.content);
            }

            const currentData = materialData[state.lesson];
            document.getElementById('bottom-content-counter').innerText = `${currentData.title} • Item ${state.content + 1} of ${currentData.items.length}`;

            // Button States
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');

            btnPrev.disabled = (state.lesson === 0 && state.content === 0);
            btnPrev.className = btnPrev.disabled ? 'px-5 py-2.5 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed flex items-center gap-2' : 'px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-2';

            btnNext.disabled = (state.lesson === materialData.length - 1 && state.content === currentData.items.length - 1);
            btnNext.className = btnNext.disabled ? 'px-5 py-2.5 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed flex items-center gap-2' : 'px-5 py-2.5 bg-red-900 text-white font-bold rounded-xl hover:bg-red-950 transition shadow-md flex items-center gap-2';

            // Sidebar TOC active state
            document.querySelectorAll('[id^="toc-btn-"]').forEach(b => {
                b.classList.remove('bg-red-900/10', 'border-red-900/20');
                b.querySelector('.toc-icon').classList.replace('bg-red-900', 'bg-gray-200');
                b.querySelector('.toc-icon').classList.replace('text-white', 'text-gray-500');
            });

            const activeBtn = document.getElementById(`toc-btn-${state.lesson}`);
            if (activeBtn) {
                activeBtn.classList.add('bg-red-900/10', 'border-red-900/20');
                activeBtn.querySelector('.toc-icon').classList.replace('bg-gray-200', 'bg-red-900');
                activeBtn.querySelector('.toc-icon').classList.replace('text-gray-500', 'text-white');
            }

            document.getElementById('main-scroll-area').scrollTop = 0;
            if (activeFullscreenId) updateFsNavButtons();
        }

        function navigateContent(dir) {
            if (dir === 1) {
                if (state.content < materialData[state.lesson].items.length - 1) {
                    state.content++;
                } else if (state.lesson < materialData.length - 1) {
                    state.lesson++;
                    state.content = 0;
                }
            } else {
                if (state.content > 0) {
                    state.content--;
                } else if (state.lesson > 0) {
                    state.lesson--;
                    state.content = materialData[state.lesson].items.length - 1;
                }
            }
            renderState();
        }

        function goToLesson(idx) {
            state.lesson = idx;
            state.content = 0;
            renderState();
            closeMediaFullscreen();
        }

        // --- FULLSCREEN MEDIA CONTROLS ---
        function handleMediaActivity() {
            document.body.classList.remove('media-idle');
            const fsControls = document.getElementById('fs-global-controls');
            if (fsControls && !fsControls.classList.contains('hidden')) fsControls.classList.remove('opacity-0');

            clearTimeout(globalMediaIdleTimer);
            globalMediaIdleTimer = setTimeout(() => {
                document.body.classList.add('media-idle');
                if (fsControls && !fsControls.classList.contains('hidden')) fsControls.classList.add('opacity-0');
            }, 2500);
        }

        document.addEventListener('mousemove', handleMediaActivity);
        document.addEventListener('keydown', handleMediaActivity);

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
                pdfInstances[id].scale = 1.3; 
                renderAllPdfPages(id);
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
                        pdfInstances[currentId].scale = 1.0;
                        setTimeout(() => renderAllPdfPages(currentId), 300); 
                    }
                }
            }
            activeFullscreenId = null;
            activeFullscreenType = null;
        }

        function mediaFsNavigate(dir) {
            closeMediaFullscreen(); 
            navigateContent(dir); 
            
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
            let hidePrev = (state.lesson === 0 && state.content === 0);
            let hideNext = (state.lesson === materialData.length - 1 && state.content === materialData[state.lesson].items.length - 1);

            document.getElementById('fs-btn-prev').style.display = hidePrev ? 'none' : 'flex';
            document.getElementById('fs-btn-next').style.display = hideNext ? 'none' : 'flex';
        }

        // --- MULTI-PAGE PDF LOGIC ---
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const pdfInstances = {};

        function checkAndLoadPDF(lessonIdx, contentIdx) {
            const id = `media-${lessonIdx}-${contentIdx}`;
            const container = document.getElementById(id);
            if (!container || !container.classList.contains('pdf-container')) return; 

            if (pdfInstances[id]) return; // Already loaded

            const url = container.dataset.pdfUrl;
            const renderAreaWrapper = container.querySelector('.pdf-pages-wrapper');
            const loadingSpinner = container.querySelector('.pdf-loading');
            
            loadingSpinner.classList.remove('hidden');

            pdfjsLib.getDocument(url).promise.then(pdfDoc => {
                pdfInstances[id] = { doc: pdfDoc, scale: 1.0, container: container, canvases: [] };
                
                // FIXED: Check if the element exists before setting textContent!
                const pageCountEl = container.querySelector('.pdf-page-count');
                if(pageCountEl) pageCountEl.textContent = pdfDoc.numPages;
                
                loadingSpinner.classList.add('hidden');
                
                // Clear the inner wrapper only, preserving the loading spinner DOM element
                renderAreaWrapper.innerHTML = ''; 

                // Generate a canvas for every page in the document
                for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                    const canvasWrapper = document.createElement('div');
                    // FIX: Removed 'w-full flex justify-center' so it defaults to block layout
                    canvasWrapper.className = 'mb-6';
                    
                    const canvas = document.createElement('canvas');
                    // FIX: Removed max-w-full and added inline-block for centering
                    canvas.className = 'shadow-lg rounded border border-gray-300 bg-white inline-block';
                    canvas.id = `${id}-page-${pageNum}`;
                    
                    canvasWrapper.appendChild(canvas);
                    renderAreaWrapper.appendChild(canvasWrapper);
                    
                    pdfInstances[id].canvases.push({ pageNum: pageNum, canvas: canvas });
                }

                renderAllPdfPages(id);
            }).catch(err => {
                console.error('PDF Load Error:', err);
                const errMsg = err.message || "Invalid or empty PDF file.";
                loadingSpinner.innerHTML = `<i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-2"></i><span class="text-sm font-bold text-gray-800">Error Loading PDF</span><span class="text-xs text-gray-500 mt-1 max-w-xs text-center px-4">${errMsg}</span>`;
            });
        }

        function renderAllPdfPages(id) {
            const instance = pdfInstances[id];
            if (!instance) return;

            const scaleEl = instance.container.querySelector('.pdf-scale');
            if (scaleEl) scaleEl.textContent = Math.round(instance.scale * 100) + '%';

            instance.canvases.forEach(item => {
                instance.doc.getPage(item.pageNum).then(page => {
                    const viewport = page.getViewport({ scale: instance.scale });
                    const outputScale = window.devicePixelRatio || 1; 
                    const ctx = item.canvas.getContext('2d');
                    
                    item.canvas.width = Math.floor(viewport.width * outputScale);
                    item.canvas.height = Math.floor(viewport.height * outputScale);
                    item.canvas.style.width = Math.floor(viewport.width) + "px";
                    item.canvas.style.height = Math.floor(viewport.height) + "px";

                    const transform = outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null;
                    page.render({ canvasContext: ctx, transform: transform, viewport: viewport });
                });
            });
        }

        function pdfZoomIn(id) { if(pdfInstances[id]) { pdfInstances[id].scale += 0.2; renderAllPdfPages(id); } }
        function pdfZoomOut(id) { if(pdfInstances[id] && pdfInstances[id].scale > 0.4) { pdfInstances[id].scale -= 0.2; renderAllPdfPages(id); } }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => { renderState(); });
    </script>
</body>
</html>