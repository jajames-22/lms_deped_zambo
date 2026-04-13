<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previewing: {{ $material->title }} - LMS</title>
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
        
        body.media-idle .video-wrapper { cursor: none !important; }
        body.media-idle .video-wrapper .video-controls { opacity: 0 !important; pointer-events: none; }
        body.media-idle .media-fullscreen { cursor: none !important; }
        body.media-idle .media-fullscreen .pdf-toolbar,
        body.media-idle .media-fullscreen .fs-toggle-btn { opacity: 0 !important; pointer-events: none; transition: opacity 0.4s; }

        .pdf-container { background: #e5e7eb; border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column; height: 75vh; width: 100%; }
        .pdf-toolbar { background: #1f2937; color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; }
        .pdf-render-area { overflow-y: auto; padding: 1rem; flex-grow: 1; background: #d1d5db; position: relative; }

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

    </style>
</head>

@php
// --- ASSESSMENT DETECTION (Matching Manage Logic) ---
    $hasExams = isset($material->exams) && $material->exams->count() > 0;
    $hasQuizzes = false;
    
    if(isset($material->lessons)) {
        foreach($material->lessons as $lesson) {
            // Check if any lesson content is a graded type
            if($lesson->contents->whereIn('type', ['mcq', 'checkbox', 'true_false', 'text'])->count() > 0) {
                $hasQuizzes = true;
                break;
            }
        }
    }

    // Determine if certification is even possible for this material
    $isCertifiable = ($hasExams || $hasQuizzes);

    // --- TIMELINE BUILDING ---
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
        $groupedExams = $material->exams->groupBy(function($e) { return $e->created_at ? \Carbon\Carbon::parse($e->created_at)->format('Y-m-d H:i:s') : '0'; });
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

    {{-- APP HEADER --}}
    <header class="bg-white border-b border-gray-200 h-16 shrink-0 flex items-center justify-between px-4 lg:px-6 z-50 shadow-sm relative transition-all duration-300">
        <div class="flex items-center gap-4 w-1/4 shrink-0">
            <button onclick="exitPreview('{{ route('dashboard.materials.manage', $material->id) }}')" 
               class="flex items-center text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group px-3 py-2 rounded-xl hover:bg-red-50">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                <span class="hidden sm:inline">Exit Preview</span>
            </button>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-[#a52a2a]/10 text-[#a52a2a] text-[9px] font-black uppercase tracking-widest rounded border border-[#a52a2a]/20">Material Preview Mode</span>
            </div>
            <h1 class="font-black text-gray-900 text-lg truncate max-w-md">{{ $material->title }}</h1>
        </div>

        <div class="w-1/4 flex items-center justify-end gap-4 shrink-0"></div>
    </header>

    {{-- MAIN PREVIEW AREA --}}
    <div id="preview-workspace" class="flex flex-1 overflow-hidden relative z-10 transition-all duration-300">

        {{-- LEFT SIDEBAR --}}
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col z-20 shrink-0 hidden lg:flex h-full shadow-sm relative">
            <div class="p-5 border-b border-gray-100 bg-gray-50 shrink-0">
                <h2 class="font-black text-gray-900 text-sm mb-1">Course Sections</h2>
                <p class="text-[10px] text-gray-500 font-medium">Click any section to jump directly.</p>
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

        {{-- CONTENT RENDER AREA --}}
        <main class="flex-1 flex flex-col min-w-0 bg-gray-50 h-full relative z-10">
            <div id="main-scroll-area" class="flex-1 overflow-y-auto w-full relative p-4 lg:p-8">
                <div class="w-full max-w-4xl mx-auto flex flex-col min-h-full">
                    
                    @foreach($timeline as $lessonIndex => $section)
                        <div id="lesson-{{ $lessonIndex }}" class="lesson-container w-full">
                            @forelse($section->items as $contentIndex => $block)
                                <div id="content-{{ $lessonIndex }}-{{ $contentIndex }}" class="content-block w-full">
                                    
                                    @if($section->is_exam)
                                        <div class="text-center mb-6 w-full"><span class="inline-block px-4 py-1.5 bg-[#a52a2a] text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-sm">Examination Section</span></div>
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
                                                            <button onclick="pdfZoomOut('media-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-[#a52a2a] transition"><i class="fas fa-search-minus"></i></button>
                                                            <span class="pdf-scale text-xs font-bold w-12 text-center">100%</span>
                                                            <button onclick="pdfZoomIn('media-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-[#a52a2a] transition"><i class="fas fa-search-plus"></i></button>
                                                            <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'pdf')" class="fs-toggle-btn hover:text-[#a52a2a] transition ml-3 border-l border-gray-600 pl-3" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="pdf-render-area relative">
                                                        <div class="pdf-loading absolute inset-0 flex flex-col items-center justify-center bg-gray-100 z-10">
                                                            <i class="fas fa-circle-notch fa-spin text-3xl text-[#a52a2a] mb-3"></i>
                                                            <span class="text-sm font-bold text-gray-500 tracking-widest uppercase">Loading Document...</span>
                                                        </div>
                                                        <div class="pdf-pages-wrapper"></div>
                                                    </div>
                                                </div>
                                            @elseif($isVideo)
                                                <div class="video-wrapper shadow-xl border border-gray-800 media-container" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                    <video class="w-full max-h-[60vh] object-contain custom-video" controls>
                                                        <source src="{{ $mediaUrl }}" type="video/{{ $ext === 'webm' ? 'webm' : 'mp4' }}">
                                                    </video>
                                                    <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'video')" class="fs-toggle-btn absolute top-4 right-4 bg-black/60 text-white w-10 h-10 flex items-center justify-center rounded-xl opacity-0 group-hover:opacity-100 transition hover:bg-[#a52a2a] shadow-lg z-20" title="Full Screen"><i class="fas fa-expand"></i></button>
                                                </div>
                                            @elseif($isImage)
                                                <div class="rounded-2xl overflow-hidden bg-gray-200 border border-gray-200 flex justify-center w-full relative group media-container" id="media-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                    <img src="{{ $mediaUrl }}" class="object-contain max-h-[60vh] w-full">
                                                    <button onclick="openMediaFullscreen('media-{{ $lessonIndex }}-{{ $contentIndex }}', 'image')" class="fs-toggle-btn absolute top-4 right-4 bg-black/60 text-white w-10 h-10 flex items-center justify-center rounded-xl opacity-0 group-hover:opacity-100 transition hover:bg-[#a52a2a] shadow-lg" title="Full Screen"><i class="fas fa-expand"></i></button>
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
                                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border-2 border-[#a52a2a]/30 relative overflow-hidden mx-auto max-w-2xl">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                            <div class="flex justify-between items-start gap-4 mb-4">
                                                <h3 class="text-xl font-bold text-gray-900">{{ $block->question_text }}</h3>
                                                <span class="px-2 py-1 bg-[#a52a2a]/10 text-[#a52a2a] text-[10px] font-black uppercase tracking-widest rounded border border-[#a52a2a]/20 shrink-0">Answer Key</span>
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
            <div id="bottom-nav-bar" class="bg-white border-t border-gray-200 p-4 flex justify-between items-center shrink-0 w-full shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20 transition-all duration-300">
                <button type="button" id="btn-prev" onclick="navigateContent(-1)" class="px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-2"><i class="fas fa-arrow-left"></i> Prev</button>
                <div class="text-center">
                    <span id="bottom-content-counter" class="text-xs font-black text-gray-400 uppercase tracking-widest">Content ? of ?</span>
                </div>
                <button type="button" id="btn-next" onclick="navigateContent(1)" class="px-5 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-md flex items-center gap-2"><span id="btn-next-text">Next</span> <i class="fas fa-arrow-right" id="btn-next-icon"></i></button>
            </div>
        </main>
    </div>

    {{-- MOCK CERTIFICATE SCREEN --}}
    <div id="mock-certificate-screen" class="hidden flex-col w-full bg-gray-50 z-[100] relative">
        {{-- Notice justify-start and explicit top padding instead of justify-center to prevent clipping when scrolling --}}
        <div class="max-w-6xl mx-auto pt-16 sm:pt-24 pb-12 px-4 sm:px-6 relative flex flex-col items-center justify-start w-full min-h-screen">
            
            {{-- Subtle Background Glow --}}
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-red-400/10 rounded-full blur-3xl pointer-events-none"></div>

            {{-- DYNAMIC HEADER --}}
            <div class="text-center mb-10 relative z-10 w-full max-w-2xl mx-auto">
                {{-- CELEBRATION HEADER --}}
                <div class="w-24 h-24 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-red-500/30 border-4 border-white">
                    <i class="fas fa-trophy text-4xl text-white"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-4 tracking-tight">Preview Module Completed</h1>
                <p class="text-lg text-gray-600">Students who meet the grading criteria at the end of this module will receive the certificate displayed below.</p>
            </div>

            {{-- CERTIFICATE PREVIEW CARD --}}
            <div class="w-full max-w-5xl bg-white p-4 sm:p-8 rounded-3xl shadow-2xl border border-gray-100 relative z-10 mb-10 mx-auto overflow-hidden flex justify-center">
                
                {{-- Clean Scaling Wrapper: Adjusts exact height to avoid white space --}}
                <div class="cert-wrapper w-full flex justify-center">
                    <div class="cert-content relative bg-white shrink-0 origin-top flex flex-col items-center">
                        
                        <div class="w-full h-full border-[16px] border-[#a52a2a] p-10 flex flex-col items-center justify-between text-center bg-[url('https://www.transparenttextures.com/patterns/cream-paper.png')] relative box-border">
                            
                            {{-- Header Image --}}
                            <div class="w-full h-[150px] flex items-center justify-center mb-2">
                                <img src="{{ asset('images/lms-cert-header.png') }}" class="h-full w-auto object-contain" alt="Header">
                            </div>

                            <h2 class="text-[#a52a2a] text-[40px] font-black uppercase tracking-[4px] mb-2">
                                Certificate of Completion
                            </h2>

                            <p class="text-gray-500 text-[20px] my-5">This is proudly presented to</p>

                            <h3 class="text-[50px] font-black text-gray-900 italic border-b-2 border-gray-300 pb-2 mb-4 w-[80%]">
                                Student Name (Placeholder)
                            </h3>

                            <p class="text-gray-500 text-[20px] mb-4">for successfully completing the learning module</p>

                            <h4 class="text-[34px] font-bold text-[#a52a2a] mb-12">"{{ $material->title }}"</h4>

                            {{-- Footer Table --}}
                            <table class="w-full text-center mt-auto border-collapse">
                                <tr>
                                    <td class="w-1/3 align-bottom pb-2">
                                        <div class="border-t border-black w-[250px] inline-block pt-2">
                                            <strong class="text-[18px] block">{{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}</strong>
                                            <span class="text-[#555] text-[14px]">Instructor</span>
                                        </div>
                                    </td>
                                    
                                    <td class="w-1/3 align-bottom text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <div class="w-[110px] h-[110px] bg-[#d97706] rounded-full border-[8px] border-[#fde68a] flex items-center justify-center text-white text-[55px] shadow-inner mb-2 leading-none pb-2">★</div>
                                            <div class="text-[12px] text-[#555] font-bold uppercase tracking-widest mt-1">Official Award</div>
                                        </div>
                                    </td>
                                    
                                    <td class="w-1/3 align-bottom pb-2">
                                        <div class="border-t border-black w-[250px] inline-block pt-2">
                                            <strong class="text-[18px] block">{{ now()->format('F j, Y') }}</strong>
                                            <span class="text-[#555] text-[14px]">Date of Completion</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Certificate ID --}}
                            <div class="absolute bottom-6 right-8 text-[12px] text-gray-400 font-mono">
                                Certificate ID: CERT-XXXXXX
                            </div>

                        </div>
                    </div>
                </div>
                
                {{-- Responsive Height and Scale Adjustments --}}
                <style>
                    .cert-content { width: 1122px; height: 794px; transform: scale(0.28); }
                    .cert-wrapper { height: 230px; }
                    
                    @media (min-width: 480px) {
                        .cert-content { transform: scale(0.4); }
                        .cert-wrapper { height: 320px; }
                    }
                    @media (min-width: 640px) {
                        .cert-content { transform: scale(0.55); }
                        .cert-wrapper { height: 440px; }
                    }
                    @media (min-width: 768px) {
                        .cert-content { transform: scale(0.65); }
                        .cert-wrapper { height: 520px; }
                    }
                    @media (min-width: 1024px) {
                        .cert-content { transform: scale(0.85); }
                        .cert-wrapper { height: 680px; }
                    }
                </style>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10 w-full max-w-lg mx-auto">
                <button onclick="exitPreview('{{ route('dashboard.materials.manage', $material->id) }}')"
                    class="w-full py-4 bg-white border-2 border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i id="bottom-return-icon" class="fas fa-home"></i>
                    <span id="bottom-return-text">Exit Preview and Return</span>
                </button>
            </div>
        </div>
    </div>

    {{-- GLOBAL FULLSCREEN OVERLAY CONTROLS --}}
    <div id="fs-global-controls" class="fixed inset-0 pointer-events-none hidden z-[999999] transition-opacity duration-300 opacity-100">
        <button onclick="closeMediaFullscreen()" class="absolute top-4 right-4 sm:top-15 sm:right-8 pointer-events-auto bg-black/60 hover:bg-[#a52a2a] text-white rounded-full w-12 h-12 flex items-center justify-center backdrop-blur transition-colors shadow-2xl border border-white/10" title="Exit Full Screen">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <script>
        const materialData = @json($timeline);
        // Pass the certifiable status from PHP to JS
        const isCertifiable = {{ $isCertifiable ? 'true' : 'false' }};

        let state = { lesson: 0, content: 0 };
        let activeFullscreenId = null;
        let activeFullscreenType = null;
        let globalMediaIdleTimer = null;

        // Custom Exit Function for LoadPartial Rehydration
        function exitPreview(targetUrl) {
            sessionStorage.setItem('lastActiveTab', targetUrl);
            window.location.href = "{{ url('/dashboard') }}";
        }

        function renderState() {
            // Hide all containers first
            document.querySelectorAll('.lesson-container, .content-block').forEach(el => el.classList.remove('active'));

            const activeLessonEl = document.getElementById(`lesson-${state.lesson}`);
            const activeContentEl = document.getElementById(`content-${state.lesson}-${state.content}`);

            if (activeLessonEl) activeLessonEl.classList.add('active');
            if (activeContentEl) {
                activeContentEl.classList.add('active');
                checkAndLoadPDF(state.lesson, state.content);
            }

            const currentData = materialData[state.lesson];
            const itemsCount = currentData.items ? currentData.items.length : 0;

            const displayContentNum = itemsCount === 0 ? 0 : state.content + 1;
            const itemLabel = currentData.is_exam ? 'Question' : 'Item';
            document.getElementById('bottom-content-counter').innerText = `${currentData.title} • ${itemLabel} ${displayContentNum} of ${itemsCount}`;

            // Button States
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');
            const btnNextText = document.getElementById('btn-next-text');
            const btnNextIcon = document.getElementById('btn-next-icon');

            btnPrev.disabled = (state.lesson === 0 && state.content === 0);
            btnPrev.className = btnPrev.disabled 
                ? 'px-5 py-2.5 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed flex items-center gap-2' 
                : 'px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-2';

            const isLast = (state.lesson === materialData.length - 1 && (itemsCount === 0 || state.content === itemsCount - 1));

            if (isLast) {
                btnNextText.innerText = "Finish Preview";
                btnNextIcon.className = "fas fa-flag-checkered";
                btnNext.className = 'px-5 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-md flex items-center gap-2';
            } else {
                btnNextText.innerText = "Next";
                btnNextIcon.className = "fas fa-arrow-right";
                btnNext.className = 'px-5 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-md flex items-center gap-2';
            }

            // Sidebar TOC active state
            document.querySelectorAll('[id^="toc-btn-"]').forEach(b => {
                b.classList.remove('bg-[#a52a2a]/10', 'border-[#a52a2a]/20', 'bg-red-50');
                const icon = b.querySelector('.toc-icon');
                icon.classList.remove('bg-[#a52a2a]', 'text-white');
                icon.classList.add('bg-gray-200', 'text-gray-500');
            });

            const activeBtn = document.getElementById(`toc-btn-${state.lesson}`);
            if (activeBtn) {
                activeBtn.classList.add('bg-[#a52a2a]/10', 'border-[#a52a2a]/20');
                const icon = activeBtn.querySelector('.toc-icon');
                icon.classList.remove('bg-gray-200', 'text-gray-500');
                icon.classList.add('bg-[#a52a2a]', 'text-white');
            }

            document.getElementById('main-scroll-area').scrollTop = 0;
        }

        function navigateContent(dir) {
            const currentSection = materialData[state.lesson];
            const itemsCount = currentSection.items ? currentSection.items.length : 0;

            if (dir === 1) {
                if (itemsCount > 0 && state.content < itemsCount - 1) {
                    state.content++;
                } else if (state.lesson < materialData.length - 1) {
                    state.lesson++;
                    state.content = 0;
                } else {
                    // Determine completion screen based on assessment existence
                    if (isCertifiable) {
                        showMockCertificate();
                    } else {
                        showCompletionNoCert();
                    }
                    return;
                }
            } else {
                if (state.content > 0) {
                    state.content--;
                } else if (state.lesson > 0) {
                    state.lesson--;
                    const prevItemsCount = materialData[state.lesson].items ? materialData[state.lesson].items.length : 0;
                    state.content = prevItemsCount > 0 ? prevItemsCount - 1 : 0;
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

        function hideMainPreview() {
            document.querySelector('header').style.display = 'none';
            document.getElementById('preview-workspace').style.display = 'none';
            document.body.classList.remove('h-screen', 'overflow-hidden');
            document.body.classList.add('overflow-y-auto');
        }

        function showMockCertificate() {
            hideMainPreview();
            const screen = document.getElementById('mock-certificate-screen');
            screen.classList.remove('hidden');
            screen.classList.add('flex');

            window.scrollTo(0, 0);
            if (typeof confetti === 'function') {
                confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 }, colors: ['#a52a2a', '#fbbf24', '#3b82f6', '#22c55e'] });
            }
        }

        function showCompletionNoCert() {
            hideMainPreview();
            const screen = document.getElementById('mock-certificate-screen');

            // Modify the screen content to show completion without certificate
            const headerTitle = screen.querySelector('h1');
            const headerDesc = screen.querySelector('p');
            const certCard = screen.querySelector('.cert-wrapper').parentElement;
            const trophy = screen.querySelector('.from-red-400');

            headerTitle.innerText = "Module Finished";
            headerDesc.innerText = "You've reached the end of the preview. This module does not contain assessments, so no certificate is issued.";
            certCard.classList.add('hidden');
            trophy.innerHTML = '<i class="fas fa-check text-4xl text-white"></i>';

            screen.classList.remove('hidden');
            screen.classList.add('flex');
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

        // --- MULTI-PAGE PDF LOGIC ---
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const pdfInstances = {};

        function checkAndLoadPDF(lessonIdx, contentIdx) {
            const id = `media-${lessonIdx}-${contentIdx}`;
            const container = document.getElementById(id);
            if (!container || !container.classList.contains('pdf-container')) return; 

            if (pdfInstances[id]) return; 

            const url = container.dataset.pdfUrl;
            const renderAreaWrapper = container.querySelector('.pdf-pages-wrapper');
            const loadingSpinner = container.querySelector('.pdf-loading');
            
            loadingSpinner.classList.remove('hidden');

            pdfjsLib.getDocument(url).promise.then(pdfDoc => {
                pdfInstances[id] = { doc: pdfDoc, scale: 1.0, container: container, canvases: [] };
                const pageCountEl = container.querySelector('.pdf-page-count');
                if(pageCountEl) pageCountEl.textContent = pdfDoc.numPages;
                loadingSpinner.classList.add('hidden');
                renderAreaWrapper.innerHTML = ''; 

                for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                    const canvasWrapper = document.createElement('div');
                    canvasWrapper.className = 'mb-6 w-full flex justify-center';
                    const canvas = document.createElement('canvas');
                    canvas.className = 'shadow-lg rounded border border-gray-300 max-w-full bg-white';
                    canvas.id = `${id}-page-${pageNum}`;
                    canvasWrapper.appendChild(canvas);
                    renderAreaWrapper.appendChild(canvasWrapper);
                    pdfInstances[id].canvases.push({ pageNum: pageNum, canvas: canvas });
                }

                renderAllPdfPages(id);
            }).catch(err => {
                console.error('PDF Load Error:', err);
                const errMsg = err.message || "Invalid or empty PDF file.";
                loadingSpinner.innerHTML = `<i class="fas fa-exclamation-triangle text-3xl text-[#a52a2a] mb-2"></i><span class="text-sm font-bold text-gray-800">Error Loading PDF</span><span class="text-xs text-gray-500 mt-1 max-w-xs text-center px-4">${errMsg}</span>`;
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

        // Confetti Library loading
        const confettiScript = document.createElement('script');
        confettiScript.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js';
        document.body.appendChild(confettiScript);

        // Initialize
        document.addEventListener('DOMContentLoaded', () => { renderState(); });
    </script>
</body>
</html>