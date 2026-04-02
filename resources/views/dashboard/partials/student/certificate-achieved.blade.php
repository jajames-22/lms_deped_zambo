<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate {{ auth()->check() && auth()->id() === $enrollment->user_id ? 'Achieved' : 'Verification' }} - {{ $enrollment->material->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes floatIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: none; }
        }
        .animate-float-in { animation: floatIn 0.6s ease-out forwards; }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 min-h-screen selection:bg-[#a52a2a] selection:text-white">

    @php
        // Determine if the current viewer is the actual owner of the certificate
        $isOwner = auth()->check() && auth()->id() === $enrollment->user_id;
    @endphp

    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 relative min-h-screen flex flex-col items-center justify-center">

        {{-- SMART BACK BUTTON (Only for the Owner) --}}
        @if($isOwner)
            <div class="absolute top-4 left-4 sm:top-6 sm:left-6 z-20 hidden" id="top-left-back-btn">
                <button onclick="goBackToDashboard()"
                    class="flex items-center text-gray-600 hover:text-[#a52a2a] font-bold transition-all group px-4 py-2.5 bg-white/80 backdrop-blur-md rounded-xl border border-gray-200 shadow-sm hover:shadow-md active:scale-95">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    Back
                </button>
            </div>
        @endif

        {{-- Subtle Background Glow --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] {{ $isOwner ? 'bg-yellow-400/10' : 'bg-green-400/10' }} rounded-full blur-3xl pointer-events-none">
        </div>

        {{-- DYNAMIC HEADER --}}
        <div class="text-center mb-10 relative z-10 animate-float-in w-full max-w-2xl mx-auto">
            
            {{-- LMS Logo --}}
            <img src="{{ asset('storage/images/lms-logo-red.png') }}" alt="LMS Logo" class="h-14 sm:h-16 mx-auto mb-8 object-contain">

            @if($isOwner)
                {{-- CELEBRATION HEADER (For the Student) --}}
                <div class="w-24 h-24 bg-gradient-to-br from-yellow-400 to-amber-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-yellow-500/30 border-4 border-white">
                    <i class="fas fa-trophy text-4xl text-white"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-4 tracking-tight">Congratulations,
                    {{ $enrollment->user->first_name }}!
                </h1>
                <p class="text-lg text-gray-600">You have successfully mastered <span
                        class="font-bold text-[#a52a2a]">"{{ $enrollment->material->title }}"</span>.</p>
            @else
                {{-- VALIDATION HEADER (For Guests / Scanners) --}}
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-green-500/20 border-4 border-white">
                    <i class="fas fa-check-circle text-5xl text-green-500"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-4 tracking-tight">Official Certificate Record</h1>
                <p class="text-lg text-gray-600">This page serves as official verification that <strong class="text-gray-900">{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</strong> successfully completed the learning module <span class="font-bold text-[#a52a2a]">"{{ $enrollment->material->title }}"</span>.</p>
            @endif

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
                            {{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}
                        </h3>

                        <p class="text-gray-500 text-[20px] mb-4">for successfully completing the learning module</p>

                        <h4 class="text-[34px] font-bold text-[#a52a2a] mb-12">"{{ $enrollment->material->title }}"</h4>

                        {{-- Footer Table (Matches PDF) --}}
                        <table class="w-full text-center mt-auto border-collapse">
                            <tr>
                                <td class="w-1/3 align-bottom pb-2">
                                    <div class="border-t border-black w-[250px] inline-block pt-2">
                                        <strong class="text-[18px] block">{{ $enrollment->material->instructor->first_name ?? 'Instructor' }} {{ $enrollment->material->instructor->last_name ?? '' }}</strong>
                                        <span class="text-[#555] text-[14px]">Instructor</span>
                                    </div>
                                </td>
                                
                                <td class="w-1/3 align-bottom text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <div class="w-[110px] h-[110px] bg-[#d97706] rounded-full border-[8px] border-[#fde68a] flex items-center justify-center text-white text-[55px] shadow-inner mb-2 leading-none pb-2">
                                            ★
                                        </div>
                                        <div class="text-[12px] text-[#555] font-bold uppercase tracking-widest mt-1">Official Award</div>
                                    </div>
                                </td>
                                
                                <td class="w-1/3 align-bottom pb-2">
                                    <div class="border-t border-black w-[250px] inline-block pt-2">
                                        <strong class="text-[18px] block">{{ $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : now()->format('F j, Y') }}</strong>
                                        <span class="text-[#555] text-[14px]">Date of Completion</span>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{-- Certificate ID --}}
                        <div class="absolute bottom-6 right-8 text-[12px] text-gray-400 font-mono">
                            Certificate ID: CERT-{{ str_pad($enrollment->id, 6, '0', STR_PAD_LEFT) }}
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

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10 w-full max-w-lg mx-auto">

            {{-- Publicly Visible Download Button --}}
            <a href="{{ URL::signedRoute('student.certificate.download', ['enrollment_id' => $enrollment->id]) }}" target="_blank"
                class="w-full py-4 px-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-900 transition-all shadow-lg shadow-[#a52a2a]/30 flex items-center justify-center gap-2 text-center active:scale-[0.98]">
                <i class="fas fa-file-pdf text-xl"></i> Download Official PDF
            </a>   

            {{-- Navigation Button --}}
            @if($isOwner)
                <button onclick="goBackToDashboard()"
                    class="w-full py-4 bg-white border-2 border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i id="bottom-return-icon" class="fas fa-home"></i>
                    <span id="bottom-return-text">Return to Modules</span>
                </button>
            @else
                <a href="{{ url('/') }}"
                    class="w-full py-4 bg-white border-2 border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex items-center justify-center gap-2 active:scale-[0.98] text-center">
                    <i class="fas fa-arrow-right"></i>
                    Go to LMS Homepage
                </a>
            @endif
        </div>
    </div>

    {{-- SCRIPTS (Only needed for the Owner's dashboard routing) --}}
    @if($isOwner)
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
    @endif
</body>

</html>