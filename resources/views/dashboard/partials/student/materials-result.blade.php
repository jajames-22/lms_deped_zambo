<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Results - {{ $material->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-pop-in { animation: popIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 min-h-screen flex items-center justify-center p-4 selection:bg-[#a52a2a] selection:text-white relative">

    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden animate-pop-in relative z-10">
        
        {{-- HEADER --}}
        <div class="bg-red-50 p-8 text-center border-b border-red-100 relative overflow-hidden">
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm relative z-10">
                <i class="fas fa-times text-4xl text-red-500"></i>
            </div>
            <h1 class="text-3xl font-black text-gray-900 mb-2 relative z-10">Module Incomplete</h1>
            <p class="text-gray-600 relative z-10">You did not meet the passing requirements for <span class="font-bold text-[#a52a2a]">"{{ $material->title }}"</span>.</p>
        </div>

        {{-- SCORES --}}
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 bg-gray-50 rounded-2xl p-6 border border-gray-200 mb-8">
                <div class="text-center flex-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Your Total Score</p>
                    <p class="text-5xl font-black text-red-600">{{ $grades['totalScore'] }}%</p>
                </div>
                <div class="w-px h-16 bg-gray-300 hidden md:block"></div>
                <div class="text-center flex-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Passing Score</p>
                    <p class="text-3xl font-black text-gray-800">{{ $grades['passingScore'] }}%</p>
                </div>
            </div>

            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Score Breakdown</h3>
            <div class="space-y-3 mb-8">
                @if($grades['hasQuizzes'])
                    <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center"><i class="fas fa-list-ul"></i></div>
                            <div>
                                <p class="font-bold text-gray-800">Quizzes</p>
                                <p class="text-xs text-gray-500">Weight: {{ $grades['quizWeight'] }}%</p>
                            </div>
                        </div>
                        <span class="font-black text-lg text-gray-900">{{ $grades['quizScore'] }}%</span>
                    </div>
                @endif

                @if($grades['hasExams'])
                    <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center"><i class="fas fa-star"></i></div>
                            <div>
                                <p class="font-bold text-gray-800">Final Exam</p>
                                <p class="text-xs text-gray-500">Weight: {{ $grades['examWeight'] }}%</p>
                            </div>
                        </div>
                        <span class="font-black text-lg text-gray-900">{{ $grades['examScore'] }}%</span>
                    </div>
                @endif
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex flex-col sm:flex-row gap-4">
                
                {{-- Retake Entire Module --}}
                <button onclick="openModal('retake-module-modal')" class="w-full py-4 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-redo-alt"></i> Retake Entire Module
                </button>

                {{-- Retake Exam Only (With Logic Lock) --}}
                @if($grades['hasExams'])
                    @if($canPassWithExamRetake)
                        <button onclick="openModal('retake-exam-modal')" class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 flex items-center justify-center gap-2">
                            <i class="fas fa-pen-alt"></i> Retake Exam Only
                        </button>
                    @else
                        <button onclick="openModal('impossible-exam-modal')" class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 flex items-center justify-center gap-2">
                            <i class="fas fa-pen-alt"></i> Retake Exam Only
                        </button>
                    @endif
                @endif
            </div>
            
            <div class="mt-6 text-center">
                <a href="{{ route('dashboard.materials.show', $material->id) }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition">Return to Module Details</a>
            </div>
        </div>
    </div>

    {{-- MODAL: Retake Entire Module --}}
    <div id="retake-module-modal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('retake-module-modal')"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10 popup-box">
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-red-50 text-red-500">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Warning: Full Reset</h3>
            <p class="text-sm text-gray-500 mb-6">Retaking the entire module will permanently delete all your previous quiz and exam answers. Your progress will return to zero.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('retake-module-modal')" class="w-1/2 px-4 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <form action="{{ route('dashboard.materials.retake', $material->id) }}" method="POST" class="w-1/2">
                    @csrf
                    <input type="hidden" name="type" value="module">
                    <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition shadow-md">Reset & Start</button>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Retake Exam Only (Allowed) --}}
    <div id="retake-exam-modal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('retake-exam-modal')"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10 popup-box">
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-blue-50 text-blue-500">
                <i class="fas fa-info-circle"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Retake Final Exam?</h3>
            <p class="text-sm text-gray-500 mb-6">This will delete your previous exam score and jump you straight to the examination section. Your quiz scores will be saved.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('retake-exam-modal')" class="w-1/2 px-4 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <form action="{{ route('dashboard.materials.retake', $material->id) }}" method="POST" class="w-1/2">
                    @csrf
                    <input type="hidden" name="type" value="exam">
                    <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-md">Start Exam</button>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Retake Exam Only (Impossible to Pass) --}}
    <div id="impossible-exam-modal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeModal('impossible-exam-modal')"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10 popup-box">
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-amber-50 text-amber-500">
                <i class="fas fa-lock"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Insufficient Score</h3>
            <p class="text-sm text-gray-500 mb-4">Because your Quiz scores are too low, even getting a perfect 100% on the Exam retake will only bring your total score to <strong class="text-gray-800">{{ round($maxPossibleScore, 1) }}%</strong>, which is below the {{ $grades['passingScore'] }}% requirement.</p>
            <p class="text-sm text-[#a52a2a] font-bold mb-6">You must retake the entire module.</p>
            <button type="button" onclick="closeModal('impossible-exam-modal')" class="w-full px-4 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition shadow-md">Understood</button>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            const box = modal.querySelector('.popup-box');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
            }, 10);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            const box = modal.querySelector('.popup-box');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    </script>
</body>
</html>