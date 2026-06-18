<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/png" href="{{ asset('deped_lms_logo.png') }}">
    <title>{{ $assessment->title }} - Results</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900 min-h-screen flex flex-col items-center py-12 md:py-20 px-4">

    <div class="max-w-4xl w-full mb-6">
        <button onclick="sessionStorage.setItem('lastActiveTab', '{{ url('/dashboard/materials/' . $material->id . '/manage') }}'); sessionStorage.setItem('lastActiveBtn', 'nav-materials-btn'); window.location.href = '{{ url('/dashboard') }}';" 
           class="inline-flex items-center text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Material Management
        </button>
    </div>
    
    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden h-fit">
        
        <div class="bg-[#a52a2a] p-8 text-center relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <span class="inline-block px-3 py-1 bg-white/20 text-white text-xs font-bold uppercase tracking-widest rounded-full mb-4">
                    Assessment Completed
                </span>
                <h1 class="text-3xl font-black text-white mb-2">{{ $assessment->title }}</h1>
                <p class="text-white/80 text-sm font-medium">Your responses have been successfully recorded.</p>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-8">
            
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                <img class="h-12 w-12 rounded-full border-2 border-white shadow-sm"
                     src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . '+' . $student->last_name) }}&background=a52a2a&color=fff&bold=true"
                     alt="Profile">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Completed By</p>
                    <p class="font-bold text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</p>
                </div>
            </div>

            @php 
                $globalQ = 0;
            @endphp

            {{-- Score Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
               
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm text-sm">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-3 border-b border-gray-200 pb-2">Score Breakdown</p>
                    <div class="space-y-3">
                        @if($hasQuizzes)
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-700">Quiz ({{ $quizWeight }}%)</span>
                            <span class="text-gray-900 font-bold">{{ $quizTotalQuestions > 0 ? round(($quizScore / $quizTotalQuestions) * 100) : 100 }}% <span class="text-xs text-gray-400 ml-1">({{ $quizScore }}/{{ $quizTotalQuestions }})</span></span>
                        </div>
                        @endif
                        @if($hasExams)
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-700">Exam ({{ $examWeight }}%)</span>
                            <span class="text-gray-900 font-bold">{{ $examTotalQuestions > 0 ? round(($examScore / $examTotalQuestions) * 100) : 100 }}% <span class="text-xs text-gray-400 ml-1">({{ $examScore }}/{{ $examTotalQuestions }})</span></span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center shadow-sm flex flex-col justify-center">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Final Weighted Grade</p>
                    <p class="text-5xl font-black text-gray-900">
                        {{ $finalPercentage }}<span class="text-2xl text-gray-400">%</span>
                    </p>
                </div>
            </div>

            {{-- ===================== QUIZ SECTION ===================== --}}
            @if(!empty($quizLessons))
                <div class="pt-4 border-t border-gray-100">
                    {{-- Quiz Section Header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-blue-600 text-sm"></i>
                        </div>
                        <h2 class="text-base font-black text-gray-900 uppercase tracking-widest">Quiz</h2>
                    </div>

                    <div class="space-y-3 ml-2">
                        @foreach($quizLessons as $lIdx => $lesson)
                            {{-- Chapter Accordion --}}
                            <div class="border border-blue-100 rounded-2xl bg-white overflow-hidden shadow-sm">
                                <button type="button" onclick="toggleAccordion('quiz-{{ $lIdx }}')"
                                    class="w-full px-5 py-3.5 flex items-center justify-between bg-blue-50/60 hover:bg-blue-50 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-book-open text-blue-400 text-xs"></i>
                                        <span class="font-bold text-gray-800 text-sm">{{ $lesson->title }}</span>
                                        <span class="text-[10px] text-blue-500 font-bold bg-blue-100 px-2 py-0.5 rounded-full">{{ count($lesson->items) }} items</span>
                                    </div>
                                    <i id="icon-quiz-{{ $lIdx }}" class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-300"></i>
                                </button>

                                <div id="content-quiz-{{ $lIdx }}" class="hidden px-5 py-5 border-t border-blue-100 space-y-4">
                                    @foreach($lesson->items as $result)
                                        @php $globalQ++; @endphp
                                        @include('dashboard.partials.student.assessmentExam._result_item', ['result' => $result, 'num' => $globalQ])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ===================== EXAM SECTION ===================== --}}
            @if(!empty($examItems))
                <div class="pt-4 border-t border-gray-100">
                    {{-- Exam Section Header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center">
                            <i class="fas fa-file-alt text-[#a52a2a] text-sm"></i>
                        </div>
                        <h2 class="text-base font-black text-gray-900 uppercase tracking-widest">Final Exam</h2>
                        <span class="text-[10px] text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded-full">{{ count($examItems) }} items</span>
                    </div>

                    <div class="space-y-4 ml-2">
                        @foreach($examItems as $result)
                            @php $globalQ++; @endphp
                            @include('dashboard.partials.student.assessmentExam._result_item', ['result' => $result, 'num' => $globalQ])
                        @endforeach
                    </div>
                </div>
            @endif

            @if(empty($quizLessons) && empty($examItems))
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500 font-bold">No quiz or exam answers recorded for this student.</p>
                </div>
            @endif

            <div class="pt-4 flex flex-col md:flex-row gap-3">
                <button onclick="sessionStorage.setItem('lastActiveTab', '{{ url('/dashboard/materials/' . $material->id . '/manage') }}'); sessionStorage.setItem('lastActiveBtn', 'nav-materials-btn'); window.location.href = '{{ url('/dashboard') }}';" class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl shadow-sm hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Management</span>
                </button>
            </div>

        </div>
    </div>

    <script>
        function toggleAccordion(id) {
            const content = document.getElementById('content-' + id);
            const icon = document.getElementById('icon-' + id);
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
    </script>
</body>
</html>
