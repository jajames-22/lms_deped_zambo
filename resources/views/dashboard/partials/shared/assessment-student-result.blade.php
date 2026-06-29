<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('deped_lms_logo.png') }}">
    <title>{{ $assessment->title }} - Student Result</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900 min-h-screen">
<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full animate-[fadeIn_0.3s_ease-out]">
    {{-- Top Back Button --}}
    <div class="w-full mb-6 flex items-center justify-between">
        <button type="button" onclick="sessionStorage.setItem('lastActiveTab', '{{ route('dashboard.assessments.manage', $assessment->id) }}'); sessionStorage.setItem('lastActiveBtn', 'nav-assessment-btn'); window.location.href = '{{ url('/dashboard') }}';" 
           class="inline-flex items-center text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group text-sm">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Assessment Manage
        </button>
        <div class="flex items-center gap-2">
            @if($status === 'Completed')
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-check-circle"></i> Completed
                </span>
            @else
                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-clock"></i> Incomplete
                </span>
            @endif
        </div>
    </div>

    {{-- Header Banner Card --}}
    <div class="w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8">
        <div class="bg-[#a52a2a] p-8 text-center relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <span class="inline-block px-3.5 py-1 bg-white/20 text-white text-xs font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm shadow-sm">
                    Assessment Result
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-2">{{ $assessment->title }}</h1>
                <p class="text-white/80 text-sm font-medium">Detailed performance breakdown and answer sheet analysis</p>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-8">
            {{-- Student Profile & Result Badge --}}
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-150 flex-wrap gap-4 shadow-sm">
                <div class="flex items-center gap-4 min-w-0">
                    <img class="h-12 w-12 rounded-full border-2 border-white shadow shrink-0"
                         src="https://ui-avatars.com/api/?name={{ urlencode(($student->first_name ?? 'S') . '+' . ($student->last_name ?? '')) }}&background=a52a2a&color=fff&bold=true"
                         alt="Profile">
                    <div class="min-w-0">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Student Information</p>
                        <p class="font-bold text-gray-900 text-base truncate">{{ $student->first_name }} {{ $student->last_name }}</p>
                        <p class="text-xs text-gray-500 font-mono truncate">LRN: {{ $student->lrn }} • {{ $student->school->name ?? 'Independent / Unassigned' }} • Grade: {{ $student->grade_level ?? $assessment->year_level ?? 'N/A' }} • Section: {{ $student->section ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Score Breakdown & Grade Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 shadow-sm text-sm flex flex-col justify-center">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-3 border-b border-gray-200 pb-2 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-[#a52a2a]"></i> Attempt Summary
                    </p>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-gray-150/80">
                            <span class="font-bold text-gray-600 pl-1">Total Score:</span>
                            <span class="font-mono font-extrabold text-gray-900 text-base pr-1">{{ $totalScore }} <span class="text-xs font-normal text-gray-400">/ {{ $totalQuestions }}</span></span>
                        </div>
                        <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-gray-150/80">
                            <span class="font-bold text-gray-600 pl-1">Time Spent:</span>
                            <span class="font-mono font-bold text-[#a52a2a] pr-1">{{ $timeSpent }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-gray-400 px-1 pt-1">
                            <span>Started: {{ $startTime }}</span>
                            <span>Finished: {{ $submissionTime }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center shadow-sm flex flex-col justify-center relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-[#a52a2a]/5 rounded-full pointer-events-none"></div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Mean Percentage Score (MPS)</p>
                    <p class="text-5xl sm:text-6xl font-black {{ $percentage >= 90 ? 'text-[#10b981]' : ($percentage >= 75 ? 'text-[#3b82f6]' : ($percentage >= 50 ? 'text-[#f59e0b]' : 'text-[#ef4444]')) }} tracking-tight">
                        {{ $percentage }}<span class="text-3xl text-gray-400 font-bold">%</span>
                    </p>
                    <p class="text-xs text-gray-700 font-extrabold mt-3 px-3 py-1 bg-white border border-gray-200 rounded-full inline-block mx-auto shadow-sm">
                        Proficiency: <span class="text-[#a52a2a]">{{ $proficiencyLevel }}</span>
                    </p>
                </div>
            </div>

            {{-- Category Breakdown Section --}}
            @if(!empty($categoryStats))
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 shadow-sm">
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-4 border-b border-gray-200 pb-2 flex items-center gap-2">
                    <i class="fas fa-layer-group text-[#a52a2a]"></i> Category Performance Breakdown
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($categoryStats as $stat)
                    <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-extrabold text-gray-900 mb-2 truncate" title="{{ $stat->name }}">{{ $stat->name }}</h4>
                            <div class="flex items-baseline justify-between text-xs text-gray-500 mb-2">
                                <span>Score: <strong class="text-gray-900 font-mono">{{ $stat->correct }} / {{ $stat->total }}</strong></span>
                                <span class="font-extrabold font-mono text-gray-700">{{ $stat->percentage }}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-[#a52a2a] transition-all duration-500" style="width: {{ $stat->percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Detailed Question Breakdown --}}
            <div class="space-y-8 pt-4">
                @php $qCounter = 0; @endphp
                @foreach($detailedCategories as $category)
                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-200 flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-xl bg-[#a52a2a]/10 flex items-center justify-center text-[#a52a2a]">
                                <i class="fas fa-folder-open text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-black text-gray-900 uppercase tracking-wider">{{ $category->title }}</h2>
                                <p class="text-xs text-gray-500">Itemized response analysis for this category</p>
                            </div>
                        </div>
                        @if($category->time_limit)
                            <span class="text-xs bg-white text-gray-700 font-bold border border-gray-200 px-3 py-1 rounded-full font-mono shadow-sm">
                                <i class="fas fa-stopwatch text-[#a52a2a] mr-1"></i> {{ $category->time_limit }} mins limit
                            </span>
                        @endif
                    </div>

                    <div class="space-y-4">
                        @foreach($category->items as $item)
                            @if($item->is_instruction)
                                <div class="py-4 bg-amber-50/80 p-4 rounded-xl border border-amber-200 text-sm text-gray-800 mb-4 shadow-sm">
                                    <span class="font-extrabold text-amber-900 uppercase text-xs tracking-wider block mb-1.5 flex items-center gap-1.5">
                                        <i class="fas fa-info-circle text-amber-600"></i> Section Instructions
                                    </span>
                                    <div class="prose prose-sm max-w-none text-gray-700">
                                        {!! $item->question->question_text !!}
                                    </div>
                                </div>
                            @else
                                @php $qCounter++; @endphp
                                <div class="bg-white rounded-2xl p-5 border shadow-sm transition-all {{ $item->is_correct ? 'border-green-200 bg-green-50/10' : ($item->is_pending ? 'border-amber-200 bg-amber-50/10' : 'border-red-200 bg-red-50/10') }}">
                                    <div class="flex items-start gap-3 mb-4">
                                        <div class="flex items-center justify-center {{ $item->is_correct ? 'bg-green-100 text-green-700' : ($item->is_pending ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700') }} rounded-xl font-black w-8 h-8 shrink-0 text-sm shadow-sm">
                                            {{ $qCounter }}
                                        </div>
                                        <div class="w-full">
                                            <div class="text-sm font-bold text-gray-900 mt-1 leading-snug prose prose-sm max-w-none">
                                                {!! $item->question->question_text !!}
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            @if($item->is_pending)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 text-[11px] font-extrabold whitespace-nowrap">
                                                    <i class="fas fa-hourglass-half text-[10px]"></i> Pending
                                                </span>
                                            @elseif($item->is_correct)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-green-100 text-green-700 text-[11px] font-extrabold whitespace-nowrap">
                                                    <i class="fas fa-check text-[10px]"></i> 1 / 1 pt
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-100 text-red-700 text-[11px] font-extrabold whitespace-nowrap">
                                                    <i class="fas fa-times text-[10px]"></i> 0 / 1 pt
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="ml-11 space-y-2">
                                        <div class="bg-gray-50/80 p-3 rounded-xl border border-gray-150">
                                            <p class="text-[10px] font-bold uppercase tracking-wider mb-1 {{ $item->is_correct ? 'text-green-600' : ($item->is_pending ? 'text-amber-600' : 'text-red-600') }}">
                                                <i class="fas {{ $item->is_correct ? 'fa-check-circle' : ($item->is_pending ? 'fa-hourglass-half' : 'fa-times-circle') }} mr-1"></i> Student's Answer
                                            </p>
                                            <p class="text-gray-800 text-sm font-mono font-medium whitespace-pre-wrap">{{ $item->student_answer_text ?: 'No answer provided' }}</p>
                                        </div>

                                        @if(!$item->is_pending && $item->correct_answer_text)
                                            <div class="bg-green-50 p-3 rounded-xl border border-green-150">
                                                <p class="text-[10px] font-bold uppercase tracking-wider mb-1 text-green-700">
                                                    <i class="fas fa-check text-green-500 mr-1"></i> Expected / Correct Answer
                                                </p>
                                                <p class="text-gray-800 text-sm font-mono font-medium">{{ $item->correct_answer_text }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Bottom Back Button --}}
            <div class="pt-6 border-t border-gray-100 flex flex-col md:flex-row gap-3">
                <button type="button" onclick="sessionStorage.setItem('lastActiveTab', '{{ route('dashboard.assessments.manage', $assessment->id) }}'); sessionStorage.setItem('lastActiveBtn', 'nav-assessment-btn'); window.location.href = '{{ url('/dashboard') }}';" class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl shadow-sm hover:bg-gray-200 hover:text-[#a52a2a] transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Assessment Manage</span>
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
