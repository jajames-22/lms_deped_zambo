<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assessment->title }} - Exam Lobby</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        
        <div class="bg-[#a52a2a] p-8 text-center relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <span class="inline-block px-3 py-1 bg-white/20 text-white text-xs font-bold uppercase tracking-widest rounded-full mb-4">
                    Grade {{ $assessment->year_level }} Assessment
                </span>
                <h1 class="text-3xl font-black text-white mb-2">{{ $assessment->title }}</h1>
            </div>
        </div>

        <div class="p-8 space-y-8">
            
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                <img class="h-12 w-12 rounded-full border-2 border-white shadow-sm"
                     src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->first_name . '+' . auth()->user()->last_name) }}&background=a52a2a&color=fff&bold=true"
                     alt="Profile">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Logged in as</p>
                    <p class="font-bold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">Instructions</h3>
                <div class="prose prose-sm text-gray-600">
                    {{-- Assuming description is plain text. If it's HTML, use {!! !!} --}}
                    {{ $assessment->description ?: 'No specific instructions provided for this assessment. Please read each question carefully before submitting your answers.' }}
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                <div class="flex gap-3">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-amber-900 text-sm">Before you begin:</h4>
                        <ul class="text-xs text-amber-700 mt-2 space-y-1 list-disc pl-4">
                            <li>Ensure you have a stable internet connection.</li>
                            <li>Do not refresh or close the browser window during the exam.</li>
                            <li>Once started, the timer (if applicable) cannot be paused.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <a href="{{ url('/dashboard') }}" 
                   class="flex-1 px-6 py-4 bg-white text-gray-600 border border-gray-200 font-bold rounded-2xl hover:bg-gray-50 transition text-center">
                    Return to Dashboard
                </a>
                
                {{-- This button would link to your actual exam/quiz execution route --}}
                <form action="#" method="GET" class="flex-1">
                    <button type="submit" class="w-full h-full flex items-center justify-center gap-2 px-6 py-4 bg-[#a52a2a] text-white font-bold rounded-2xl shadow-xl shadow-red-900/20 hover:bg-red-800 transition hover:-translate-y-1">
                        <i class="fas fa-play-circle"></i>
                        <span>Start Assessment Now</span>
                    </button>
                </form>
            </div>

        </div>
    </div>

</body>
</html>