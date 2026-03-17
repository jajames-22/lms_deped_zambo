<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assessment->title }} - Exam</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Smooth transitions for questions */
        .question-card {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .question-card.active {
            display: block;
            opacity: 1;
        }
        
        /* Custom radio button styles */
        .option-label:has(input:checked) {
            background-color: #fef2f2; /* red-50 */
            border-color: #a52a2a;
            box-shadow: 0 0 0 1px #a52a2a;
        }

        /* --- Custom High-Contrast Shadows --- */
        
        /* Glass Shadow for the Header */
        .shadow-header {
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.3), 0 2px 10px -4px rgba(0, 0, 0, 0.2);
        }

        /* Deep Elevation Shadow for Question Cards */
        .shadow-card {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }

        /* Floating Navigation Shadow (Bottom Bar) */
        .shadow-nav {
            box-shadow: 0 -10px 40px -5px rgba(0, 0, 0, 0.35);
        }

        /* Square Number Badge Shadow */
        .shadow-badge {
            box-shadow: 0 10px 15px -3px rgba(165, 42, 42, 0.4);
        }
    </style>
</head>
<body class="bg-[#a6a6a6] min-h-screen font-sans text-gray-800 relative selection:bg-[#a52a2a] selection:text-white flex flex-col">

    <div id="exam-start-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-[#a52a2a] backdrop-blur-xl transition-opacity duration-300 p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all text-center">
            
            <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i class="fas fa-file-signature text-3xl text-[#a52a2a]"></i>
            </div>
            
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Section {{ $assessment->categories->search(fn($cat) => $cat->id === $currentCategory->id) + 1 }}</h2>
            <h1 class="text-2xl font-black text-gray-900 mb-6">{{ $currentCategory->title }}</h1>
            
            <div class="bg-gray-50 rounded-2xl p-5 mb-8 border border-gray-100 flex items-center justify-center gap-4">
                <div class="h-12 w-12 bg-white rounded-xl shadow-sm flex items-center justify-center">
                    <i class="fas fa-stopwatch text-gray-400 text-2xl"></i>
                </div>
                <div class="text-left">
                    <p class="text-xs font-bold text-gray-400 uppercase">Time Limit</p>
                    <p class="text-lg font-black text-gray-900">
                        {{ $currentCategory->time_limit > 0 ? $currentCategory->time_limit . ' Minutes' : 'No Time Limit' }}
                    </p>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-8 px-4 font-medium leading-relaxed">Once you click start, the timer will begin. Do not refresh or close this page.</p>
            
            <button onclick="startExam()" class="w-full py-4 bg-[#a52a2a] text-white font-black rounded-xl hover:bg-red-800 transition-all shadow-xl shadow-[#a52a2a]/30 flex items-center justify-center gap-3 group active:scale-[0.98]">
                START SECTION <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </button>
        </div>
    </div>

    <div class="fixed top-0 left-0 right-0 z-50">
        <header class="bg-white border-b border-gray-200 shadow-header px-4 md:px-8 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-gray-900 leading-tight">{{ $assessment->title }}</h2>
                <p class="text-sm text-gray-500 font-medium">{{ $currentCategory->title }}</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 text-sm font-bold text-gray-500 bg-gray-100 px-4 py-2 rounded-lg">
                    <span id="progress-text">1 / {{ count($currentCategory->questions) }}</span>
                </div>

                <div id="timer-display" class="hidden items-center gap-2 bg-gray-900 text-white px-4 py-2 md:px-5 md:py-2.5 rounded-xl font-mono text-base md:text-lg font-bold tracking-wider shadow-sm transition-colors duration-300">
                    <i class="fas fa-clock text-gray-400 text-sm"></i>
                    <span id="time-remaining">--:--</span>
                </div>
            </div>
        </header>

        <div class="w-full bg-gray-200 h-1">
            <div id="progress-bar" class="bg-[#a52a2a] h-1 transition-all duration-300 w-0"></div>
        </div>
    </div>

    <div class="h-24 md:h-28"></div>

 <main class="flex-grow max-w-5xl w-full mx-auto px-4 pb-32 blur-sm transition-all duration-500 flex flex-col" id="exam-content">
        <form id="assessment-form" onsubmit="submitAssessment(event)" class="flex-grow flex flex-col" action="{{ route('student.assessment.submit', $assessment->access_key) }}" method="POST" >
            @csrf
            
            <input type="hidden" name="category_id" value="{{ $currentCategory->id }}">
            
            <div class="flex-grow mb-20">
                @foreach($currentCategory->questions as $index => $question)
                <div class="question-card w-full" id="question-{{ $index }}" data-index="{{ $index }}">
                        <div class="bg-white rounded-3xl border-10 p-6 shadow-card border border-gray-300 relative mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                                <div class="flex flex-col">
                                    <div class="flex flex-row items-start mb-4">
                                        <div class="flex items-center justify-center bg-[#a52a2a] text-white rounded-2xl font-black shadow-badge w-12 h-12 aspect-square shrink-0">
                                            {{ $index + 1 }}
                                        </div>
                                        <h3 class="text-base font-bold ml-4 w-full text-gray-900 mt-2 leading-none">
                                            {{ $question->question_text }}
                                        </h3>
                                    </div>
                                    @if($question->media_url)
                                        <div class="rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 shadow-inner flex justify-center mt-4">
                                            @php $ext = strtolower(pathinfo($question->media_url, PATHINFO_EXTENSION)); @endphp
                                            @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']))
                                                <img src="{{ $question->media_url }}" alt="Question Media" class="max-w-full max-h-80 object-contain p-2">
                                            @elseif(in_array($ext, ['mp4', 'webm', 'ogg']))
                                                <video controls class="w-full max-h-64 rounded-lg bg-black">
                                                    <source src="{{ $question->media_url }}" type="video/{{ $ext }}">
                                                </video>
                                            @elseif(in_array($ext, ['mp3', 'wav', 'ogg']))
                                                <audio controls class="w-full m-4"><source src="{{ $question->media_url }}" type="audio/{{ $ext }}"></audio>
                                            @else
                                                <img src="{{ $question->media_url }}" alt="Question Media" class="max-w-full max-h-64 object-contain p-2">
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col justify-center">
                                    @if($question->type === 'text')
                                        
                                        @php
                                            $savedText = isset($existingAnswers) && isset($existingAnswers[$question->id]) 
                                                            ? $existingAnswers[$question->id]->answer_text 
                                                            : '';
                                        @endphp
                                        
                                        <div class="w-full">
                                            <textarea name="answers[{{ $question->id }}]" rows="6" placeholder="Type your answer here..." class="w-full p-5 border-2 border-gray-300 rounded-2xl focus:ring-4 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-gray-700 font-medium resize-y bg-gray-50/50">{{ $savedText }}</textarea>
                                        </div>

                                    @else
                                        
                                        @php
                                            $savedOptions = [];
                                            if(isset($existingAnswers) && isset($existingAnswers[$question->id]) && $existingAnswers[$question->id]->selected_options) {
                                                $decoded = json_decode($existingAnswers[$question->id]->selected_options, true);
                                                $savedOptions = is_array($decoded) ? $decoded : [];
                                            }
                                        @endphp
                                        
                                        <div class="space-y-3">
                                            @foreach($question->options as $option)
                                                
                                                @php
                                                    $isChecked = in_array($option['id'], $savedOptions) ? 'checked' : '';
                                                @endphp
                                                
                                                <label class="option-label flex items-center p-3 border-2 border-gray-300 rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-gray-200 transition-all group">
                                                    <div class="relative flex items-center justify-center shrink-0">
                                                        @if($question->type === 'checkbox')
                                                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option['id'] }}" {{ $isChecked }} class="w-6 h-6 accent-[#a52a2a] border-gray-300 rounded focus:ring-[#a52a2a] bg-gray-50 transition-all cursor-pointer">
                                                        @else
                                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option['id'] }}" {{ $isChecked }} class="w-6 h-6 accent-[#a52a2a] border-gray-300 focus:ring-[#a52a2a] bg-gray-50 transition-all cursor-pointer">
                                                        @endif
                                                    </div>
                                                    <span class="ml-4 text-gray-700 font-medium text-base md:text-lg group-hover:text-gray-900 leading-snug">{{ $option['option_text'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="h-24 md:h-28"></div>

            <div class="fixed bottom-0 left-0 right-0 z-50 p-4 md:p-6 border-gray-300">
                <div class="max-w-5xl mx-auto flex items-center justify-between bg-white border border-gray-300 p-4 md:p-5 rounded-3xl">
                    <button type="button" id="prev-btn" onclick="navigateQuestion(-1)" class="px-6 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-arrow-left"></i> <span class="hidden md:inline">Previous</span>
                    </button>
                    
                    <button type="button" id="next-btn" onclick="navigateQuestion(1)" class="px-8 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors flex items-center gap-2 shadow-md">
                        <span class="hidden md:inline">Next</span> <i class="fas fa-arrow-right"></i>
                    </button>

                    <button type="submit" id="submit-btn" class="hidden px-8 py-3 bg-[#a52a2a] text-white font-black rounded-xl hover:bg-red-800 transition-all shadow-lg shadow-[#a52a2a]/30 flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> SUBMIT SECTION
                    </button>
                </div>
            </div>
        </form>
    </main>

  <script>
        // --- Initialization & Timer Variables ---
        // Fetch saved time from session if it exists, otherwise fall back to null
        const savedSeconds = {{ isset($session) && $session && $session->time_remaining !== null ? $session->time_remaining : 'null' }};
        const timeLimitMinutes = {{ $currentCategory->time_limit ?? 0 }};
        
        // Create a boolean to easily check if this section actually uses a timer
        const isTimed = timeLimitMinutes > 0;
        
        // Determine starting time: Only use saved time if the section is actually timed!
        let totalSeconds = (isTimed && savedSeconds !== null) ? savedSeconds : (timeLimitMinutes * 60);
        let timerInterval;

        // --- Navigation Variables ---
        let currentIndex = 0;
        const totalQuestions = {{ count($currentCategory->questions) }};
        const questions = document.querySelectorAll('.question-card');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const form = document.getElementById('assessment-form');

        // Initialize UI State
        updateNavigationUI();

        // ==========================================
        // BACKGROUND AUTO-SAVE LOGIC
        // ==========================================
        let isSaving = false;

        function triggerAutoSave() {
            // Only prevent saving if the exam IS timed AND the timer hit 0.
            if (isSaving || (isTimed && totalSeconds <= 0)) return; 
            
            isSaving = true;

            const originalText = submitBtn.innerHTML;
            if(!submitBtn.classList.contains('hidden')) {
                 submitBtn.innerHTML = '<i class="fas fa-sync fa-spin"></i> Saving...';
            }

            const formData = new FormData(form);
            
            // Only append the timer if the exam is actually timed
            if (isTimed) {
                formData.append('time_remaining', totalSeconds);
            } else {
                formData.append('time_remaining', 0);
            }

            fetch("{{ route('student.assessment.autosave', $assessment->access_key) }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (!response.ok) throw new Error('Network response was not ok');
                isSaving = false;
                if(!submitBtn.classList.contains('hidden')) submitBtn.innerHTML = originalText;
            })
            .catch(error => {
                isSaving = false;
                if(!submitBtn.classList.contains('hidden')) submitBtn.innerHTML = originalText;
            });
        }

        form.addEventListener('change', triggerAutoSave);
        form.addEventListener('focusout', triggerAutoSave);
        setInterval(triggerAutoSave, 15000);

        // ==========================================
        // EXAM MECHANICS & NAVIGATION
        // ==========================================
        function startExam() {
            const modal = document.getElementById('exam-start-modal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => modal.classList.add('hidden'), 300);
            
            document.getElementById('exam-content').classList.remove('blur-sm');

            if (questions.length > 0) {
                questions[0].classList.add('active');
                updateProgress();
            }

            // Only trigger timer logic if the section actually has a time limit
            if (isTimed) {
                const timerDisplay = document.getElementById('timer-display');
                timerDisplay.classList.remove('hidden');
                timerDisplay.classList.add('flex');
                updateTimerDisplay();
                
                // Only start ticking if there's actually time left
                if (totalSeconds > 0) {
                    timerInterval = setInterval(tickTimer, 1000);
                } else {
                    handleTimeUp();
                }
            }
        }

        function navigateQuestion(direction) {
            questions[currentIndex].classList.remove('active');
            
            currentIndex += direction;
            
            if (currentIndex < 0) currentIndex = 0;
            if (currentIndex >= totalQuestions) currentIndex = totalQuestions - 1;

            questions[currentIndex].classList.add('active');
            
            updateNavigationUI();
            updateProgress();
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateNavigationUI() {
            prevBtn.disabled = currentIndex === 0;

            if (currentIndex === totalQuestions - 1) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        function updateProgress() {
            progressText.innerText = `${currentIndex + 1} / ${totalQuestions}`;
            const percentage = ((currentIndex + 1) / totalQuestions) * 100;
            progressBar.style.width = `${percentage}%`;
        }

        function tickTimer() {
            if (!isTimed) return; // Extra safety check

            totalSeconds--;
            updateTimerDisplay();

            if (totalSeconds === 60) {
                const timerEl = document.getElementById('timer-display');
                timerEl.classList.remove('bg-gray-900');
                timerEl.classList.add('bg-red-600', 'animate-pulse');
            }

            if (totalSeconds <= 0) {
                handleTimeUp();
            }
        }

        function updateTimerDisplay() {
            const displaySeconds = Math.max(0, totalSeconds);
            const minutes = Math.floor(displaySeconds / 60);
            const seconds = displaySeconds % 60;
            const formattedMinutes = minutes.toString().padStart(2, '0');
            const formattedSeconds = seconds.toString().padStart(2, '0');
            document.getElementById('time-remaining').innerText = `${formattedMinutes}:${formattedSeconds}`;
        }

        function handleTimeUp() {
            clearInterval(timerInterval);
            document.getElementById('time-remaining').innerText = "00:00";
            
            triggerAutoSave();
            
            alert("Time is up! Submitting this section automatically.");
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
            form.submit();
        }

        function submitAssessment(event) {
            if (event && event.preventDefault) {
                event.preventDefault();
                
                let answeredCount = 0;
                questions.forEach(q => {
                    const checkedInputs = q.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked');
                    const textArea = q.querySelector('textarea');
                    
                    if (checkedInputs.length > 0) {
                        answeredCount++;
                    } else if (textArea && textArea.value.trim() !== '') {
                        answeredCount++;
                    }
                });

                let confirmMessage = "Are you sure you want to submit this section? You cannot return to it later.";
                
                if (answeredCount < totalQuestions) {
                    confirmMessage = `You have only answered ${answeredCount} out of ${totalQuestions} questions. Are you sure you want to submit this section?`;
                }

                if(confirm(confirmMessage)) {
                    clearInterval(timerInterval);
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                    submitBtn.disabled = true;
                    
                    triggerAutoSave();
                    
                    event.target.submit();
                }
            }
        }
    </script>
</body>
</html>