<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assessment->title }} - Results</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900 min-h-screen flex flex-col items-center py-12 md:py-20 px-4">

    <div class="max-w-3xl w-full mb-6">
        <a href="{{ url('/dashboard') }}" 
           class="inline-flex items-center text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Dashboard
        </a>
    </div>
    
    <div class="max-w-3xl w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden h-fit">
        
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
                     src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->first_name . '+' . auth()->user()->last_name) }}&background=a52a2a&color=fff&bold=true"
                     alt="Profile">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Completed By</p>
                    <p class="font-bold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                </div>
            </div>

            @if($assessment->show_results)
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center shadow-sm">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Final Score</p>
                        <p class="text-5xl font-black text-gray-900">
                            <span class="text-[#a52a2a]">{{ $score ?? 0 }}</span><span class="text-2xl text-gray-400">/{{ $totalQuestions ?? 0 }}</span>
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center shadow-sm">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Percentage</p>
                        <p class="text-5xl font-black text-gray-900">
                            @php 
                                $percentage = ($totalQuestions > 0) ? round(($score / $totalQuestions) * 100) : 0; 
                            @endphp
                            {{ $percentage }}<span class="text-2xl text-gray-400">%</span>
                        </p>
                    </div>
                </div>

                @if(isset($detailedResults) && count($detailedResults) > 0)
                    <div class="pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-6">Detailed Breakdown</h3>
                        
                        <div class="space-y-6">
                            @foreach($detailedResults as $index => $result)
                                <div class="bg-white rounded-2xl p-5 border shadow-sm {{ $result->is_correct ? 'border-green-200' : ($result->is_pending ? 'border-amber-200' : 'border-red-200') }}">
                                    
                                    <div class="flex items-start gap-3 mb-4">
                                        <div class="flex items-center justify-center bg-gray-100 text-gray-600 rounded-lg font-black w-8 h-8 shrink-0 text-sm">
                                            {{ $index + 1 }}
                                        </div>
                                        <h4 class="text-base font-bold text-gray-900 mt-1 leading-snug">
                                            {{ $result->question->question_text }}
                                        </h4>
                                    </div>

                                    <div class="ml-11 space-y-3">
                                        
                                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                            <p class="text-[10px] font-bold uppercase tracking-wider mb-1 {{ $result->is_correct ? 'text-green-600' : ($result->is_pending ? 'text-amber-600' : 'text-red-600') }}">
                                                @if($result->is_correct)
                                                    <i class="fas fa-check-circle mr-1"></i> Your Answer
                                                @elseif($result->is_pending)
                                                    <i class="fas fa-hourglass-half mr-1"></i> Your Answer (Pending Grading)
                                                @else
                                                    <i class="fas fa-times-circle mr-1"></i> Your Answer
                                                @endif
                                            </p>
                                            <p class="text-gray-800 text-sm font-medium whitespace-pre-wrap">{{ $result->student_answer_text ?? 'No answer provided' }}</p>
                                        </div>

                                        @if(!$result->is_correct && !$result->is_pending)
                                            <div class="bg-green-50 p-3 rounded-xl border border-green-100">
                                                <p class="text-[10px] font-bold uppercase tracking-wider mb-1 text-green-700">
                                                    <i class="fas fa-check text-green-500 mr-1"></i> Correct Answer
                                                </p>
                                                <p class="text-gray-800 text-sm font-medium">{{ $result->correct_answer_text }}</p>
                                            </div>
                                        @endif
                                        
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            @else
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4 class="font-bold text-blue-900 text-lg mb-1">Results are Hidden</h4>
                    <p class="text-sm text-blue-700 max-w-sm mx-auto">
                        The instructor has chosen to hide the detailed results and scores for this assessment until further notice.
                    </p>
                </div>
            @endif

            <div class="pt-4">
                <a href="{{ url('/dashboard') }}" class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gray-900 text-white font-bold rounded-2xl shadow-xl shadow-gray-900/20 hover:bg-gray-800 transition hover:-translate-y-1">
                    <i class="fas fa-home"></i>
                    <span>Return to Dashboard</span>
                </a>
            </div>

        </div>
    </div>

</body>
</html>