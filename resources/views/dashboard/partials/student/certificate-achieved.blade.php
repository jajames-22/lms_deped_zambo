<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Achieved - {{ $enrollment->material->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .animate-float-in {
            animation: floatIn 0.6s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 min-h-screen selection:bg-[#a52a2a] selection:text-white">

    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 relative min-h-screen flex flex-col items-center justify-center">

        {{-- SMART BACK BUTTON (Hidden from public guests) --}}
        @auth
            <div class="absolute top-4 left-4 sm:top-6 sm:left-6 z-20 hidden" id="top-left-back-btn">
                <button onclick="goBackToDashboard()"
                    class="flex items-center text-gray-600 hover:text-[#a52a2a] font-bold transition-all group px-4 py-2.5 bg-white/80 backdrop-blur-md rounded-xl border border-gray-200 shadow-sm hover:shadow-md active:scale-95">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    Back
                </button>
            </div>
        @endauth

        {{-- Subtle Background Glow --}}
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-yellow-400/10 rounded-full blur-3xl pointer-events-none">
        </div>

        {{-- CELEBRATION HEADER --}}
        <div class="text-center mb-10 relative z-10 animate-float-in">
            <div
                class="w-24 h-24 bg-gradient-to-br from-yellow-400 to-amber-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-yellow-500/30 border-4 border-white">
                <i class="fas fa-trophy text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-4 tracking-tight">Congratulations,
                {{ $enrollment->user->first_name }}!
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">You have successfully mastered <span
                    class="font-bold text-[#a52a2a]">"{{ $enrollment->material->title }}"</span>.</p>
        </div>

        {{-- CERTIFICATE PREVIEW CARD --}}
        <div
            class="w-full max-w-3xl bg-white p-2 rounded-3xl shadow-2xl border border-gray-100 relative z-10 transform transition-all hover:scale-[1.01] duration-500 mb-10">
            <div
                class="border-8 border-[#a52a2a]/10 rounded-2xl p-8 sm:p-12 text-center bg-[url('https://www.transparenttextures.com/patterns/cream-paper.png')]">

                <h2 class="text-[#a52a2a] text-xl sm:text-2xl font-black uppercase tracking-[0.3em] mb-2 opacity-80">
                    Certificate of Completion</h2>
                <div class="w-16 h-1 bg-[#a52a2a]/20 mx-auto mb-8 rounded-full"></div>

                <p class="text-gray-500 text-sm font-medium uppercase tracking-widest mb-4">This proudly certifies that
                </p>
                <h3 class="text-3xl sm:text-4xl font-black text-gray-900 mb-6 italic">
                    {{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}
                </h3>

                <p class="text-gray-500 text-sm mb-2">has successfully completed the learning module</p>
                <h4 class="text-xl sm:text-2xl font-bold text-[#a52a2a] mb-12">"{{ $enrollment->material->title }}"</h4>

                <div
                    class="flex flex-col sm:flex-row items-center justify-center sm:justify-between px-4 sm:px-12 gap-8">
                    <div class="text-center">
                        <div class="border-b-2 border-gray-300 w-40 pb-1 mb-2">
                            <span
                                class="font-bold text-gray-800">{{ $enrollment->material->instructor->first_name ?? 'Instructor' }}
                                {{ $enrollment->material->instructor->last_name ?? '' }}</span>
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Instructor</p>
                    </div>

                    <div
                        class="w-16 h-16 bg-gradient-to-br from-amber-300 to-yellow-600 rounded-full flex items-center justify-center shadow-inner border border-amber-200 opacity-80">
                        <i class="fas fa-award text-white text-2xl"></i>
                    </div>

                    <div class="text-center">
                        <div class="border-b-2 border-gray-300 w-40 pb-1 mb-2">
                            <span
                                class="font-bold text-gray-800">{{ $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : now()->format('F j, Y') }}</span>
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Date Achieved</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10 w-full max-w-md mx-auto">

            {{-- Publicly Visible Download Button (UPDATED) --}}
        <a href="{{ URL::signedRoute('student.certificate.download', ['enrollment_id' => $enrollment->id]) }}" target="_blank"
            class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-900 transition-all shadow-lg shadow-[#a52a2a]/30 flex items-center justify-center gap-2 text-center active:scale-[0.98]">
            <i class="fas fa-file-pdf text-xl"></i> Download Official PDF
        </a>   

            {{-- Navigation Button (Hidden from public guests) --}}
            @auth
                <button onclick="goBackToDashboard()"
                    class="w-full py-4 bg-white border-2 border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i id="bottom-return-icon" class="fas fa-home"></i>
                    <span id="bottom-return-text">Return to Modules</span>
                </button>
            @endauth
        </div>
    </div>

    @auth
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const previousTab = sessionStorage.getItem('lastActiveTab') || '';
                const topLeftBtn = document.getElementById('top-left-back-btn');
                const bottomReturnText = document.getElementById('bottom-return-text');
                const bottomReturnIcon = document.getElementById('bottom-return-icon');

                if (previousTab.includes('/certificates')) {
                    topLeftBtn.classList.remove('hidden');
                    bottomReturnText.innerText = 'Back to Certificates';
                    bottomReturnIcon.className = 'fas fa-medal';
                } else {
                    topLeftBtn.classList.add('hidden');
                    bottomReturnText.innerText = 'Return to Modules';
                    bottomReturnIcon.className = 'fas fa-home';
                }
            });

            function goBackToDashboard() {
                window.location.href = "{{ url('/dashboard') }}";
            }
        </script>
    @endauth
</body>

</html>