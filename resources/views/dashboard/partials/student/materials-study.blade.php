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
        
        /* Hide everything by default, JS handles visibility */
        .lesson-container, .content-block { display: none; }
        .lesson-container.active, .content-block.active { 
            display: block; 
            animation: fadeIn 0.4s ease-out forwards; 
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* CUSTOM VIDEO PLAYER STYLES */
        .video-wrapper { position: relative; width: 100%; border-radius: 1rem; overflow: hidden; background: #000; }
        .video-controls {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 1.5rem 1rem 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;
            opacity: 0; transition: opacity 0.3s;
        }
        .video-wrapper:hover .video-controls { opacity: 1; }
        .progress-bar-container { width: 100%; height: 6px; background: rgba(255,255,255,0.3); border-radius: 3px; cursor: pointer; position: relative; }
        .progress-bar-fill { height: 100%; background: #a52a2a; border-radius: 3px; width: 0%; pointer-events: none; }
        .controls-row { display: flex; align-items: center; justify-content: space-between; color: white; }
        
        /* PDF VIEWER STYLES */
        .pdf-container { 
            background: #e5e7eb; 
            border-radius: 1rem; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            height: auto; 
            min-height: 400px; 
            width: 100%;
        }
        .pdf-toolbar { 
            background: #1f2937; 
            color: white; 
            padding: 0.75rem 1rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .pdf-render-area { 
            overflow-x: auto; 
            overflow-y: hidden; 
            display: flex; 
            justify-content: center; 
            align-items: flex-start; 
            padding: 1.5rem 1rem; 
            position: relative;
            min-height: 400px; 
        }
        .pdf-render-area canvas { 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
            border-radius: 4px; 
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 h-screen overflow-hidden flex flex-col selection:bg-[#a52a2a] selection:text-white">

    {{-- FIXED TOP NAVIGATION BAR --}}
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
            <h1 id="top-lesson-title" class="font-black text-gray-900 text-sm sm:text-lg truncate max-w-[150px] sm:max-w-md">Loading Lesson...</h1>
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

    {{-- MOBILE TOC OVERLAY & DROPDOWN --}}
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
            @php $totalSections = 0; @endphp
            @foreach($material->lessons as $lessonIndex => $lesson)
                @php $totalSections++; @endphp
                <button onclick="attemptGoToLesson({{ $lessonIndex }}); toggleMobileTOC();" id="mobile-toc-btn-{{ $lessonIndex }}" 
                    class="w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600">
                    <div class="mobile-toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500">{{ $lessonIndex + 1 }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm leading-tight truncate mobile-toc-title text-gray-700">{{ $lesson->title }}</p>
                        <p class="text-[10px] uppercase tracking-wider font-bold mt-1 mobile-toc-meta text-gray-400">{{ $lesson->contents->count() }} Items</p>
                    </div>
                    <div class="shrink-0 mobile-toc-lock text-gray-300 mt-1"><i class="fas fa-lock"></i></div>
                    <div class="shrink-0 mobile-toc-status hidden text-green-500 mt-1"><i class="fas fa-check-circle"></i></div>
                </button>
            @endforeach
            
            @if($material->exams && $material->exams->count() > 0)
                @php $examIndex = $totalSections; @endphp
                <button onclick="attemptGoToLesson({{ $examIndex }}); toggleMobileTOC();" id="mobile-toc-btn-{{ $examIndex }}" 
                    class="w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600 mt-2">
                    <div class="mobile-toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500"><i class="fas fa-star"></i></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm leading-tight truncate mobile-toc-title text-gray-700">Final Exam</p>
                        <p class="text-[10px] uppercase tracking-wider font-bold mt-1 mobile-toc-meta text-gray-400">{{ $material->exams->count() }} Questions</p>
                    </div>
                    <div class="shrink-0 mobile-toc-lock text-gray-300 mt-1"><i class="fas fa-lock"></i></div>
                    <div class="shrink-0 mobile-toc-status hidden text-green-500 mt-1"><i class="fas fa-check-circle"></i></div>
                </button>
            @endif
        </nav>
    </div>

    {{-- MAIN LAYOUT WRAPPER --}}
    <div class="flex flex-1 overflow-hidden relative z-10">

        {{-- LEFT SIDEBAR: Table of Contents (Desktop Only) --}}
        <aside class="w-80 bg-white border-r border-gray-200 flex flex-col z-20 shrink-0 hidden lg:flex h-full shadow-sm relative">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 shrink-0">
                <h2 class="font-black text-gray-900 text-lg">Course Content</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $material->lessons->count() . ($material->exams && $material->exams->count() > 0 ? ' Lessons + 1 Exam' : ' Lessons') }}</p>
            </div>
            
            <nav class="flex-1 overflow-y-auto sidebar-scroll p-3 space-y-1" id="sidebar-nav">
                @foreach($material->lessons as $lessonIndex => $lesson)
                    <button onclick="attemptGoToLesson({{ $lessonIndex }})" id="toc-btn-{{ $lessonIndex }}" 
                        class="w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600">
                        <div class="toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500">{{ $lessonIndex + 1 }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm leading-tight truncate toc-title text-gray-700">{{ $lesson->title }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-bold mt-1 toc-meta text-gray-400">{{ $lesson->contents->count() }} Items</p>
                        </div>
                        <div class="shrink-0 toc-lock text-gray-300 mt-1"><i class="fas fa-lock"></i></div>
                        <div class="shrink-0 toc-status hidden text-green-500 mt-1"><i class="fas fa-check-circle"></i></div>
                    </button>
                @endforeach
                
                @if($material->exams && $material->exams->count() > 0)
                    <button onclick="attemptGoToLesson({{ $examIndex }})" id="toc-btn-{{ $examIndex }}" 
                        class="w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600 mt-4 bg-red-50/30">
                        <div class="toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500"><i class="fas fa-star"></i></div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm leading-tight truncate toc-title text-gray-700">Final Exam</p>
                            <p class="text-[10px] uppercase tracking-wider font-bold mt-1 toc-meta text-gray-400">{{ $material->exams->count() }} Questions</p>
                        </div>
                        <div class="shrink-0 toc-lock text-gray-300 mt-1"><i class="fas fa-lock"></i></div>
                        <div class="shrink-0 toc-status hidden text-green-500 mt-1"><i class="fas fa-check-circle"></i></div>
                    </button>
                @endif
            </nav>
        </aside>

        {{-- RIGHT AREA: Scrollable Content & Footer --}}
        <main class="flex-1 flex flex-col min-w-0 bg-gray-50 h-full relative z-10">
            <div id="main-scroll-area" class="flex-1 overflow-y-auto w-full relative">
                <div class="max-w-4xl mx-auto w-full px-4 py-8 sm:px-8 lg:px-12 flex flex-col justify-center min-h-full">
                    
                    {{-- 1. STANDARD LESSONS --}}
                    @foreach($material->lessons as $lessonIndex => $lesson)
                        <div id="lesson-{{ $lessonIndex }}" class="lesson-container w-full">
                            @forelse($lesson->contents as $contentIndex => $block)
                                <div id="content-{{ $lessonIndex }}-{{ $contentIndex }}" class="content-block w-full">
                                    
                                    {{-- MEDIA PARSER --}}
                                    @if($block->media_url)
                                        @php
                                            $mediaUrl = str_starts_with($block->media_url, 'http') ? $block->media_url : asset('storage/' . $block->media_url);
                                            $pathForExt = parse_url($mediaUrl, PHP_URL_PATH) ?? $mediaUrl;
                                            $ext = strtolower(pathinfo($pathForExt, PATHINFO_EXTENSION));
                                            
                                            $isPdf = $ext === 'pdf';
                                            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp

                                        @if($isPdf)
                                            {{-- PDF VIEWER (Prev/Next controls removed, Zoom stays) --}}
                                            <div class="pdf-container shadow-sm border border-gray-200 mb-6" data-pdf-url="{{ $mediaUrl }}" id="pdf-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                <div class="pdf-toolbar shrink-0">
                                                    <div class="flex items-center gap-4">
                                                        <span class="text-sm font-bold"><i class="fas fa-file-pdf mr-2"></i> Page <span class="pdf-page-num text-[#a52a2a]">1</span> of <span class="pdf-page-count">?</span></span>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <button onclick="pdfZoomOut('pdf-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-[#a52a2a] transition"><i class="fas fa-search-minus"></i></button>
                                                        <span class="pdf-scale text-xs font-bold w-12 text-center">100%</span>
                                                        <button onclick="pdfZoomIn('pdf-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-[#a52a2a] transition"><i class="fas fa-search-plus"></i></button>
                                                        <a href="{{ $mediaUrl }}" target="_blank" class="ml-2 pl-4 border-l border-gray-600 hover:text-[#a52a2a] transition" title="Open PDF in new tab"><i class="fas fa-external-link-alt"></i></a>
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
                                            {{-- CUSTOM VIDEO PLAYER --}}
                                            <div class="video-wrapper shadow-xl mb-6 group border border-gray-800">
                                                <video class="w-full h-auto custom-video" id="video-{{ $lessonIndex }}-{{ $contentIndex }}">
                                                    <source src="{{ $mediaUrl }}" type="video/{{ $ext === 'webm' ? 'webm' : 'mp4' }}">
                                                </video>
                                                <div class="video-controls">
                                                    <div class="progress-bar-container" onclick="seekVideo(event, 'video-{{ $lessonIndex }}-{{ $contentIndex }}')">
                                                        <div class="progress-bar-fill"></div>
                                                    </div>
                                                    <div class="controls-row mt-2">
                                                        <div class="flex items-center gap-4">
                                                            <button onclick="togglePlay('video-{{ $lessonIndex }}-{{ $contentIndex }}')" class="play-btn text-xl hover:text-[#a52a2a] transition w-6"><i class="fas fa-play"></i></button>
                                                            <div class="text-xs font-mono font-bold"><span class="current-time">0:00</span> / <span class="duration">0:00</span></div>
                                                        </div>
                                                        <div class="flex items-center gap-4">
                                                            <select onchange="changeSpeed('video-{{ $lessonIndex }}-{{ $contentIndex }}', this.value)" class="bg-transparent text-xs font-bold outline-none cursor-pointer hover:text-[#a52a2a] transition hidden sm:block">
                                                                <option class="text-black" value="1" selected>1.0x</option>
                                                                <option class="text-black" value="1.5">1.5x</option>
                                                                <option class="text-black" value="2">2.0x</option>
                                                            </select>
                                                            <button onclick="toggleMute('video-{{ $lessonIndex }}-{{ $contentIndex }}', event)" class="mute-btn hover:text-[#a52a2a] transition"><i class="fas fa-volume-up"></i></button>
                                                            <button onclick="toggleFullscreen('video-{{ $lessonIndex }}-{{ $contentIndex }}')" class="hover:text-[#a52a2a] transition"><i class="fas fa-expand"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($isImage)
                                            <div class="mb-6 rounded-2xl overflow-hidden bg-gray-200 border border-gray-200 flex justify-center relative group">
                                                <img src="{{ $mediaUrl }}" class="object-contain max-h-[600px] w-full transition-transform duration-300" id="img-{{ $lessonIndex }}-{{ $contentIndex }}">
                                            </div>
                                        @endif
                                    @endif

                                    {{-- TEXT/QUESTIONS PARSER --}}
                                    @if($block->type === 'content' && $block->question_text)
                                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 prose prose-gray max-w-none text-gray-800 text-base sm:text-lg leading-relaxed">
                                            {!! nl2br(e($block->question_text)) !!}
                                        </div>
                                    @elseif(in_array($block->type, ['mcq', 'true_false']))
                                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                            <h3 class="text-xl font-bold text-gray-900 mb-6">{{ $block->question_text }}</h3>
                                            <div class="space-y-3">
                                                @foreach($block->options as $option)
                                                    <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:border-[#a52a2a] has-[:checked]:bg-[#a52a2a]/5">
                                                        <input type="radio" name="answer_{{ $block->id }}" value="{{ $option->id }}" class="w-5 h-5 text-[#a52a2a] bg-gray-100 border-gray-300 focus:ring-[#a52a2a]">
                                                        <span class="text-gray-800 font-medium text-base sm:text-lg">{{ $option->option_text }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($block->type === 'checkbox')
                                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                            <div class="flex items-start justify-between gap-4 mb-6">
                                                <h3 class="text-xl font-bold text-gray-900">{{ $block->question_text }}</h3>
                                                <span class="shrink-0 text-[10px] uppercase font-black tracking-wider text-gray-400 bg-gray-100 px-2 py-1 rounded hidden sm:inline-block">Select Multiple</span>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach($block->options as $option)
                                                    <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:border-[#a52a2a] has-[:checked]:bg-[#a52a2a]/5">
                                                        <input type="checkbox" name="answer_{{ $block->id }}[]" value="{{ $option->id }}" class="w-5 h-5 text-[#a52a2a] bg-gray-100 border-gray-300 rounded focus:ring-[#a52a2a]">
                                                        <span class="text-gray-800 font-medium text-base sm:text-lg">{{ $option->option_text }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($block->type === 'text')
                                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $block->question_text }}</h3>
                                            <textarea name="answer_{{ $block->id }}" rows="5" placeholder="Type your answer here..." 
                                                class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a] outline-none transition-all resize-none text-base sm:text-lg"></textarea>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="content-block w-full active bg-white rounded-3xl p-12 shadow-sm border border-gray-100 text-center">
                                    <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">No Content</h3>
                                </div>
                            @endforelse
                        </div>
                    @endforeach

                    {{-- 2. FINAL EXAM SECTION --}}
                    @if($material->exams && $material->exams->count() > 0)
                        <div id="lesson-{{ $examIndex }}" class="lesson-container w-full">
                            @foreach($material->exams as $contentIndex => $examBlock)
                                <div id="content-{{ $examIndex }}-{{ $contentIndex }}" class="content-block w-full">
                                    
                                    {{-- EXAM MEDIA PARSER --}}
                                    @if($examBlock->media_url)
                                        @php
                                            $mediaUrl = str_starts_with($examBlock->media_url, 'http') ? $examBlock->media_url : asset('storage/' . $examBlock->media_url);
                                            $pathForExt = parse_url($mediaUrl, PHP_URL_PATH) ?? $mediaUrl;
                                            $ext = strtolower(pathinfo($pathForExt, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp
                                        @if($isImage)
                                            <div class="mb-6 rounded-2xl overflow-hidden bg-gray-200 border border-gray-200 flex justify-center">
                                                <img src="{{ $mediaUrl }}" class="object-contain max-h-[400px] w-full">
                                            </div>
                                        @endif
                                    @endif

                                    {{-- EXAM TEXT/QUESTIONS PARSER --}}
                                    @if(in_array($examBlock->type, ['mcq', 'true_false']))
                                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                            <span class="inline-block px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] text-[10px] font-black uppercase tracking-widest rounded-lg mb-3">Final Exam</span>
                                            <h3 class="text-xl font-bold text-gray-900 mb-6">{{ $examBlock->question_text }}</h3>
                                            <div class="space-y-3">
                                                @foreach($examBlock->options as $option)
                                                    <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:border-[#a52a2a] has-[:checked]:bg-[#a52a2a]/5">
                                                        <input type="radio" name="exam_answer_{{ $examBlock->id }}" value="{{ $option->id }}" class="w-5 h-5 text-[#a52a2a] bg-gray-100 border-gray-300 focus:ring-[#a52a2a]">
                                                        <span class="text-gray-800 font-medium text-base sm:text-lg">{{ $option->option_text }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($examBlock->type === 'checkbox')
                                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                            <div class="flex items-start justify-between gap-4 mb-6">
                                                <div>
                                                    <span class="inline-block px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] text-[10px] font-black uppercase tracking-widest rounded-lg mb-3">Final Exam</span>
                                                    <h3 class="text-xl font-bold text-gray-900">{{ $examBlock->question_text }}</h3>
                                                </div>
                                                <span class="shrink-0 text-[10px] uppercase font-black tracking-wider text-gray-400 bg-gray-100 px-2 py-1 rounded hidden sm:inline-block">Select Multiple</span>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach($examBlock->options as $option)
                                                    <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:border-[#a52a2a] has-[:checked]:bg-[#a52a2a]/5">
                                                        <input type="checkbox" name="exam_answer_{{ $examBlock->id }}[]" value="{{ $option->id }}" class="w-5 h-5 text-[#a52a2a] bg-gray-100 border-gray-300 rounded focus:ring-[#a52a2a]">
                                                        <span class="text-gray-800 font-medium text-base sm:text-lg">{{ $option->option_text }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($examBlock->type === 'text')
                                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#a52a2a]/20 relative overflow-hidden">
                                            <div class="absolute top-0 left-0 w-1.5 h-full bg-[#a52a2a]"></div>
                                            <span class="inline-block px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] text-[10px] font-black uppercase tracking-widest rounded-lg mb-3">Final Exam</span>
                                            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $examBlock->question_text }}</h3>
                                            <textarea name="exam_answer_{{ $examBlock->id }}" rows="5" placeholder="Type your answer here..." 
                                                class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a] outline-none transition-all resize-none text-base sm:text-lg"></textarea>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>

            {{-- FIXED BOTTOM NAVIGATION --}}
            <div class="bg-white border-t border-gray-200 p-4 lg:px-8 flex justify-between items-center shrink-0 z-30 w-full shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] relative">
                <button id="btn-prev" onclick="navigateContent(-1)" class="px-5 sm:px-6 py-3 sm:py-3.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Previous</span>
                </button>
                
                <div class="flex-1 flex flex-col items-center px-2 sm:px-4">
                    <span id="bottom-content-counter" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Content 1 of ?</span>
                    <div class="w-full max-w-md mx-auto bg-gray-200 rounded-full h-1.5">
                        <div id="lesson-progress-bar" class="bg-[#a52a2a] h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <button id="btn-next" onclick="navigateContent(1)" class="px-6 sm:px-8 py-3 sm:py-3.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 flex items-center gap-2">
                    <span id="btn-next-text" class="hidden sm:inline">Next</span> 
                    <span id="btn-next-text-mobile" class="sm:hidden">Next</span>
                    <i id="btn-next-icon" class="fas fa-arrow-right"></i>
                </button>
            </div>
            
        </main>
    </div>

    {{-- Confetti --}}
    <canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-50 hidden"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        // --- DATA STRUCTURE ---
        const materialData = [
            @foreach($material->lessons as $lesson)
                { 
                    id: {{ $lesson->id }}, 
                    title: @json($lesson->title),
                    contentCount: {{ max(1, $lesson->contents->count()) }},
                    isExam: false
                },
            @endforeach
            @if($material->exams && $material->exams->count() > 0)
                { 
                    id: 'exam', 
                    title: 'Final Exam',
                    contentCount: {{ max(1, $material->exams->count()) }},
                    isExam: true
                }
            @endif
        ];

        let state = {
            lesson: 0,
            content: 0,
            highestUnlockedLesson: 0
        };

        // --- INITIALIZATION ---
        document.addEventListener('DOMContentLoaded', () => {
            initVideoPlayers();
            renderState();
        });

        // --- MOBILE TOC LOGIC ---
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

        // --- CORE NAVIGATION LOGIC ---
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
            document.getElementById('top-lesson-title').innerText = currentData.title;
            const counterText = currentData.isExam 
                                ? `Question ${state.content + 1} of ${currentData.contentCount}` 
                                : `Content ${state.content + 1} of ${currentData.contentCount}`;
            document.getElementById('bottom-content-counter').innerText = counterText;
            document.getElementById('mobile-dropdown-counter').innerText = currentData.isExam ? counterText : `Lesson ${state.lesson + 1} • ${counterText}`;

            const progressPct = ((state.content + 1) / currentData.contentCount) * 100;
            document.getElementById('lesson-progress-bar').style.width = `${progressPct}%`;

            const totalContents = materialData.reduce((acc, curr) => acc + curr.contentCount, 0);
            let contentsPassed = 0;
            for(let i=0; i<state.highestUnlockedLesson; i++) contentsPassed += materialData[i].contentCount;
            if (state.lesson === state.highestUnlockedLesson) contentsPassed += state.content;
            
            const globalPct = Math.min(100, Math.round((contentsPassed / totalContents) * 100));
            document.getElementById('top-progress-bar').style.width = `${globalPct}%`;
            document.getElementById('top-progress-text').innerText = `${globalPct}%`;

            const btnPrev = document.getElementById('btn-prev');
            const btnNextText = document.getElementById('btn-next-text');
            const btnNextTextMobile = document.getElementById('btn-next-text-mobile');
            const btnNextIcon = document.getElementById('btn-next-icon');
            const btnNext = document.getElementById('btn-next');

            // Prev Button Logic
            const pdfId = `pdf-${state.lesson}-${state.content}`;
            const pdfInst = pdfInstances[pdfId];
            
            // Check if we are at the absolute beginning of the material (and not inside a multi-page PDF)
            if (state.lesson === 0 && state.content === 0 && (!pdfInst || pdfInst.pageNum === 1)) {
                btnPrev.classList.add('opacity-50', 'cursor-not-allowed');
                btnPrev.disabled = true;
            } else {
                btnPrev.classList.remove('opacity-50', 'cursor-not-allowed');
                btnPrev.disabled = false;
            }

            btnNext.classList.remove('bg-green-600', 'hover:bg-green-700', 'shadow-green-600/20');
            btnNext.classList.add('bg-[#a52a2a]', 'hover:bg-red-800', 'shadow-[#a52a2a]/20');
            btnNextIcon.className = 'fas fa-arrow-right';

            // Next Button Text Logic (Account for PDFs)
            let isLastContent = (state.content === currentData.contentCount - 1);
            if (pdfInst && pdfInst.pageNum < pdfInst.doc.numPages) {
                isLastContent = false; // Still have PDF pages left
            }

            if (isLastContent) {
                if (state.lesson === materialData.length - 1) {
                    btnNextText.innerText = "Finish";
                    btnNextTextMobile.innerText = "Finish";
                    btnNextIcon.className = "fas fa-flag-checkered";
                    btnNext.classList.remove('bg-[#a52a2a]', 'hover:bg-red-800', 'shadow-[#a52a2a]/20');
                    btnNext.classList.add('bg-green-600', 'hover:bg-green-700', 'shadow-green-600/20');
                } else {
                    btnNextText.innerText = materialData[state.lesson + 1].isExam ? "Start Exam" : "Next Lesson";
                    btnNextTextMobile.innerText = materialData[state.lesson + 1].isExam ? "Exam" : "Lesson";
                }
            } else {
                btnNextText.innerText = "Next";
                btnNextTextMobile.innerText = "Next";
            }

            updateSidebar();
            document.getElementById('main-scroll-area').scrollTop = 0;
        }

        function navigateContent(direction) {
            // Check if current view is a PDF
            const pdfId = `pdf-${state.lesson}-${state.content}`;
            const pdfInst = pdfInstances[pdfId];

            if (pdfInst) {
                if (direction === 1 && pdfInst.pageNum < pdfInst.doc.numPages) {
                    pdfInst.pageNum++;
                    renderPdfPage(pdfId);
                    renderState(); // Re-trigger state to update global progress/buttons
                    return;
                } else if (direction === -1 && pdfInst.pageNum > 1) {
                    pdfInst.pageNum--;
                    renderPdfPage(pdfId);
                    renderState();
                    return;
                }
            }

            if (direction === 1) {
                if (state.content < materialData[state.lesson].contentCount - 1) {
                    state.content++;
                } else {
                    if (state.lesson < materialData.length - 1) {
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
                        state.lesson--;
                        state.content = materialData[state.lesson].contentCount - 1;
                        
                        // Optional: if moving back lands on a PDF, set it to its last page
                        const newPdfId = `pdf-${state.lesson}-${state.content}`;
                        if (pdfInstances[newPdfId] && pdfInstances[newPdfId].doc) {
                            pdfInstances[newPdfId].pageNum = pdfInstances[newPdfId].doc.numPages;
                        }
                    }
                }
            }
            renderState();
        }

        function attemptGoToLesson(targetLessonIdx) {
            if (targetLessonIdx <= state.highestUnlockedLesson) {
                state.lesson = targetLessonIdx;
                state.content = 0; 
                renderState();
            } else {
                alert("You must complete the previous lessons before accessing this one.");
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
                    b.className = 'w-full text-left p-3 rounded-xl flex items-start gap-3 transition-all duration-200 border border-transparent text-gray-600 ' + (materialData[i].isExam ? 'mt-4 bg-red-50/30' : '');
                    iEl.className = 'toc-icon mobile-toc-icon mt-0.5 shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black bg-gray-200 text-gray-500';
                    t.className = 'font-bold text-sm leading-tight truncate toc-title mobile-toc-title text-gray-700';
                    l.classList.add('hidden');
                    c.classList.add('hidden');
                };

                resetStyles(btn, icon, title, lock, check);
                resetStyles(mBtn, mIcon, mTitle, mLock, mCheck);

                const applyState = (b, iEl, t, l, c) => {
                    if(!b) return;
                    if (i > state.highestUnlockedLesson) {
                        b.classList.add('opacity-50', 'cursor-not-allowed');
                        l.classList.remove('hidden');
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

        function finishModule() {
            document.getElementById('top-progress-bar').style.width = '100%';
            document.getElementById('top-progress-text').innerText = '100%';
            
            if (typeof confetti === 'function') {
                confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 }, colors: ['#a52a2a', '#22c55e', '#fbbf24', '#3b82f6'] });
            }
            setTimeout(() => {
                alert("Congratulations! You've completed the module.");
                window.location.href = "{{ route('dashboard.materials.show', $material->id) }}";
            }, 2500);
        }

        // --- VIDEO & PDF SCRIPTS ---
        function initVideoPlayers() {
            document.querySelectorAll('.custom-video').forEach(video => {
                const wrapper = video.closest('.video-wrapper');
                const playBtn = wrapper.querySelector('.play-btn i');
                const progressBar = wrapper.querySelector('.progress-bar-fill');
                const currentTimeEl = wrapper.querySelector('.current-time');
                const durationEl = wrapper.querySelector('.duration');
                video.addEventListener('loadedmetadata', () => { durationEl.innerText = formatTime(video.duration); });
                video.addEventListener('timeupdate', () => {
                    const percent = (video.currentTime / video.duration) * 100;
                    progressBar.style.width = `${percent}%`;
                    currentTimeEl.innerText = formatTime(video.currentTime);
                });
                video.addEventListener('play', () => playBtn.className = 'fas fa-pause');
                video.addEventListener('pause', () => playBtn.className = 'fas fa-play');
                video.addEventListener('ended', () => playBtn.className = 'fas fa-redo');
            });
        }

        function togglePlay(id) { const video = document.getElementById(id); if (video.paused) video.play(); else video.pause(); }
        function pauseAllVideos() { document.querySelectorAll('.custom-video').forEach(v => v.pause()); }
        function toggleMute(id, event) { const video = document.getElementById(id); const icon = event.currentTarget.querySelector('i'); video.muted = !video.muted; icon.className = video.muted ? 'fas fa-volume-mute' : 'fas fa-volume-up'; }
        function changeSpeed(id, speed) { document.getElementById(id).playbackRate = parseFloat(speed); }
        function toggleFullscreen(id) { const video = document.getElementById(id).closest('.video-wrapper'); if (!document.fullscreenElement) { video.requestFullscreen().catch(err => console.log(err)); } else { document.exitFullscreen(); } }
        function seekVideo(e, id) { const video = document.getElementById(id); const container = e.currentTarget; const rect = container.getBoundingClientRect(); const pos = (e.clientX - rect.left) / container.offsetWidth; video.currentTime = pos * video.duration; }
        function formatTime(seconds) { if (isNaN(seconds)) return "0:00"; const m = Math.floor(seconds / 60); const s = Math.floor(seconds % 60); return `${m}:${s < 10 ? '0' : ''}${s}`; }

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const pdfInstances = {};

        function checkAndLoadPDF(lessonIdx, contentIdx) {
            const id = `pdf-${lessonIdx}-${contentIdx}`;
            const container = document.getElementById(id);
            if (!container || pdfInstances[id]) return; 

            const url = container.dataset.pdfUrl;
            const canvas = container.querySelector('.pdf-canvas');
            const ctx = canvas.getContext('2d');
            const loadingSpinner = container.querySelector('.pdf-loading');
            loadingSpinner.classList.remove('hidden');

            pdfjsLib.getDocument(url).promise.then(pdfDoc => {
                pdfInstances[id] = { doc: pdfDoc, pageNum: 1, scale: 1.2, canvas: canvas, ctx: ctx, container: container };
                container.querySelector('.pdf-page-count').textContent = pdfDoc.numPages;
                loadingSpinner.classList.add('hidden');
                renderPdfPage(id);
                // Trigger a renderState update so the global progress bar calculates PDF pages
                renderState(); 
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
                const viewport = page.getViewport({ scale: instance.scale });
                instance.canvas.height = viewport.height;
                instance.canvas.width = viewport.width;
                const renderContext = { canvasContext: instance.ctx, viewport: viewport };
                page.render(renderContext);
                instance.container.querySelector('.pdf-page-num').textContent = instance.pageNum;
                instance.container.querySelector('.pdf-scale').textContent = Math.round(instance.scale * 100) + '%';
            });
        }

        function pdfZoomIn(id) { if(pdfInstances[id]) { pdfInstances[id].scale += 0.2; renderPdfPage(id); } }
        function pdfZoomOut(id) { if(pdfInstances[id] && pdfInstances[id].scale > 0.4) { pdfInstances[id].scale -= 0.2; renderPdfPage(id); } }
    </script>
</body>
</html>