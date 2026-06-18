<div class="bg-white rounded-2xl p-5 border shadow-sm {{ $result->is_correct ? 'border-green-200' : ($result->is_pending ? 'border-amber-200' : 'border-red-200') }}">
    <div class="flex items-start gap-3 mb-4">
        <div class="flex items-center justify-center {{ $result->is_correct ? 'bg-green-100 text-green-700' : ($result->is_pending ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }} rounded-lg font-black w-8 h-8 shrink-0 text-sm">
            {{ $num }}
        </div>
        <h4 class="text-sm font-bold text-gray-900 mt-1 leading-snug whitespace-pre-wrap">{{ $result->question->question_text }}</h4>
    </div>

    <div class="ml-11 space-y-2">
        {{-- Student's Answer --}}
        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
            <p class="text-[10px] font-bold uppercase tracking-wider mb-1 {{ $result->is_correct ? 'text-green-600' : ($result->is_pending ? 'text-amber-600' : 'text-red-600') }}">
                @if($result->is_correct)
                    <i class="fas fa-check-circle mr-1"></i> Student's Answer
                @elseif($result->is_pending)
                    <i class="fas fa-hourglass-half mr-1"></i> Student's Answer (Pending)
                @else
                    <i class="fas fa-times-circle mr-1"></i> Student's Answer
                @endif
            </p>
            <p class="text-gray-800 text-sm font-medium whitespace-pre-wrap">{{ $result->student_answer_text ?? 'No answer provided' }}</p>
        </div>

        {{-- Correct Answer — always shown --}}
        @if(!$result->is_pending && $result->correct_answer_text)
            <div class="bg-green-50 p-3 rounded-xl border border-green-100">
                <p class="text-[10px] font-bold uppercase tracking-wider mb-1 text-green-700">
                    <i class="fas fa-check text-green-500 mr-1"></i> Correct Answer
                </p>
                <p class="text-gray-800 text-sm font-medium">{{ $result->correct_answer_text }}</p>
            </div>
        @endif
    </div>
</div>
