<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Ensure Cinzel Font is Loaded --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&display=swap" rel="stylesheet">
    
    <title>DepEd Zamboanga - LMS</title>
    <style>
        * {
            box-sizing: border-box;
        }
        .font-cinzel {
            font-family: 'Cinzel', serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans selection:bg-red-900 selection:text-white">
    {{-- Responsive Header --}}
        <header class="bg-[#a52a2a] text-white flex justify-center shadow-lg absolute top-0 z-50 w-full no-print">
            <div class="px-4 py-3 md:px-8 md:py-6 max-w-[1200px] w-full">
                <div class="flex flex-row sm:flex-row items-center justify-between gap-2 md:gap-6 relative">
                    
                    {{-- Mobile Menu Trigger --}}
                    <button @click="sidebarOpen = true" class="absolute left-0 top-0 lg:hidden text-white hover:text-white/80 transition p-1">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    {{-- Left Logos --}}
                    <div class="flex items-center gap-2 md:gap-4 shrink-0 md:mt-0">
                        <img src="{{ asset('images/deped.png') }}" alt="DepEd" class="h-10 sm:h-12 md:h-16 w-auto drop-shadow-md">
                        <img src="{{ asset('images/r9.png') }}" alt="Region IX" class="h-10 sm:h-12 md:h-16 w-auto drop-shadow-md">
                    </div>

                    {{-- Central Branding --}}
                    <div class="flex flex-col font-cinzel text-white items-start sm:items-start text-center sm:text-left flex-1 px-4 w-full">
                        {{-- Wrapper to constrain the width of the horizontal line to exactly the text width --}}
                        <div class="inline-flex flex-col items-start sm:items-start w-fit">
                            <span class="text-[8px] sm:text-[10px] tracking-widest leading-tight font-bold">Republic of the Philippines</span>
                            <span class="text-[8px] sm:text-[10px] tracking-widest leading-tight font-bold">Department of Education</span>
                            
                            {{-- Horizontal Line (Now restricted by the parent wrapper) --}}
                            <div class="w-full border-b border-white my-1"></div>
                            
                            <h1 class="text-sm sm:text-lg md:text-xl lg:text-2xl tracking-wide font-bold leading-tight">
                                {{ $site_settings->header_title ?? 'Zamboanga City Division' }}
                            </h1>
                        </div>
                    </div>

                    {{-- Right Logo --}}
                    <div class="block md:block shrink-0">
                        <img src="{{ asset('images/ts.png') }}" alt="Transparency Seal" class=" opacity-90 h-10 sm:h-12 md:h-16 w-auto drop-shadow-md">
                    </div>
                </div>
            </div>
        </header>

    <main>
        <section style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');"
            class="relative bg-cover bg-center bg-no-repeat px-6 pt-25 md:pt-40 min-h-screen">
            <div class="absolute inset-0 bg-red-900/50"></div>

            <div class="relative z-10 max-w-7xl pt-10 lg:pt-5 mx-auto text-center flex items-center justify-center flex-col">
                <img src="{{ asset('storage/images/hero-text.png') }}" class="w-full max-w-xl h-auto block"
                    alt="DepEd Zamboanga Header">
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight leading-[1.2] text-shadow-lg/30">
                    Learning Management <br> System
                </h1>
                <p class="text-white max-w-2xl">A centralized digital repository and learning management system (LMS)
                    that facilitates the distribution of accessible, division-standard learning materials to ensure
                    educational continuity across Zamboanga City.</p>
                <div class="mt-7 flex flex-col w-full sm:flex-row gap-3 justify-center">
                    <a href="{{ route('login') }}" style="background-color: #a52a2a;"
                        class="px-8 py-3 hover:bg-red-800 text-white font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        Login
                    </a>
                    <a href="{{ route('register') }}" style="background-color: #a52a2a;"
                        class="px-8 py-3 bg-white hover:bg-gray-100 text-white font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        Create Account
                    </a>
                </div>
            </div>
        </section>

        <section class="py-20 bg-gray-50 relative px-4 sm:px-10 lg:px-[10rem]">
            <div
                class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-64 bg-red-900/5 rounded-full blur-3xl -z-0">
            </div>

            <div class="relative z-10 w-full mx-auto">
                <div class="text-center mb-12">
                    <span class="text-red-700 font-bold tracking-widest uppercase text-xs">Platform Benefits</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-2 mb-4 tracking-tight">Empowering
                        Education Through Technology</h2>
                    <p class="text-gray-500 max-w-2xl mx-auto text-lg">Tailored tools designed to enhance the teaching
                        and learning experience.</p>
                </div>

                <div
                    class="bg-white rounded-3xl shadow-xl overflow-hidden grid md:grid-cols-2 border border-gray-100 transform transition-all hover:shadow-2xl">

                    <div class="p-10 md:p-14">
                        <div class="flex items-center mb-8">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mr-5 shadow-sm"
                                style="background-color: rgba(165, 42, 42, 0.08);">
                                <svg class="w-7 h-7" style="color: #a52a2a;" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">For Students</h3>
                        </div>
                        <ul class="space-y-6 text-gray-600">
                            <li class="flex items-start">
                                <div
                                    class="bg-green-50 p-1.5 rounded-full mr-4 flex-shrink-0 mt-0.5 border border-green-100">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <strong class="block text-gray-900 mb-1">24/7 Material Access</strong>
                                    <span class="text-sm leading-relaxed block">Browse, study, and download
                                        division-standard modules anytime, anywhere.</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="bg-green-50 p-1.5 rounded-full mr-4 flex-shrink-0 mt-0.5 border border-green-100">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <strong class="block text-gray-900 mb-1">Track Progress & Certificates</strong>
                                    <span class="text-sm leading-relaxed block">Monitor your learning journey and earn
                                        certificates for completing materials.</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="bg-green-50 p-1.5 rounded-full mr-4 flex-shrink-0 mt-0.5 border border-green-100">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <strong class="block text-gray-900 mb-1">Interactive Assessments</strong>
                                    <span class="text-sm leading-relaxed block">Take quizzes and exams securely directly
                                        within the platform and see instant results.</span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="p-10 md:p-14 bg-gray-50/50 border-t md:border-t-0 md:border-l border-gray-100">
                        <div class="flex items-center mb-8">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mr-5 shadow-sm"
                                style="background-color: rgba(165, 42, 42, 0.08);">
                                <svg class="w-7 h-7" style="color: #a52a2a;" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">For Teachers</h3>
                        </div>
                        <ul class="space-y-6 text-gray-600">
                            <li class="flex items-start">
                                <div
                                    class="bg-green-50 p-1.5 rounded-full mr-4 flex-shrink-0 mt-0.5 border border-green-100">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <strong class="block text-gray-900 mb-1">Centralized Management</strong>
                                    <span class="text-sm leading-relaxed block">Easily organize classes, track enrolled
                                        students, and manage materials in a unified dashboard.</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="bg-green-50 p-1.5 rounded-full mr-4 flex-shrink-0 mt-0.5 border border-green-100">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <strong class="block text-gray-900 mb-1">Detailed Analytics</strong>
                                    <span class="text-sm leading-relaxed block">Gain real-time insights into student
                                        performance and identify areas needing intervention.</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="bg-green-50 p-1.5 rounded-full mr-4 flex-shrink-0 mt-0.5 border border-green-100">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <strong class="block text-gray-900 mb-1">Streamlined Grading</strong>
                                    <span class="text-sm leading-relaxed block">Automate quiz grading and manage robust
                                        assessments with advanced security and access controls.</span>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </section>

        <section class="py-20 bg-white px-4 sm:px-10 lg:px-[10rem]">
            <div class="w-full mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12">
                    <div class="max-w-2xl">
                        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Available Course Content</h2>
                        <p class="text-gray-500 mt-2 text-lg">Access a rich variety of dynamic digital resources
                            tailored for DepEd Zamboanga learners.</p>
                    </div>
                    <a href="{{ route('explore.public') }}"
                        class="mt-6 md:mt-0 group flex items-center text-red-800 font-semibold hover:text-red-600 transition-colors bg-red-50 hover:bg-red-100 px-6 py-3 rounded-full">
                        Browse Materials
                        <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div
                        class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer text-left group relative overflow-hidden flex items-start">
                        <div
                            class="w-16 h-16 mr-6 flex-shrink-0 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-transform shadow-inner border border-blue-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-xl mb-2">Self-Paced Modules</h3>
                            <p class="text-sm text-gray-500 mb-3">Standardized Self-Learning Modules (SLMs) and
                                interactive activity sheets mapped to the curriculum.</p>
                            <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1 rounded-full">Primary
                                Learning</span>
                        </div>
                    </div>

                    <div
                        class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer text-left group relative overflow-hidden flex items-start">
                        <div
                            class="w-16 h-16 mr-6 flex-shrink-0 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:-rotate-3 transition-transform shadow-inner border border-red-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-xl mb-2">Video Lessons</h3>
                            <p class="text-sm text-gray-500 mb-3">Engaging recorded lectures and demonstrations prepared
                                by top division educators.</p>
                            <span
                                class="text-xs font-semibold text-red-700 bg-red-50 px-3 py-1 rounded-full">Multimedia</span>
                        </div>
                    </div>

                    <div
                        class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer text-left group relative overflow-hidden flex items-start">
                        <div
                            class="w-16 h-16 mr-6 flex-shrink-0 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-transform shadow-inner border border-green-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-xl mb-2">Formative Assessments</h3>
                            <p class="text-sm text-gray-500 mb-3">Secure quizzes, periodic tests, and performance tasks
                                to effectively measure mastery.</p>
                            <span
                                class="text-xs font-semibold text-green-700 bg-green-50 px-3 py-1 rounded-full">Evaluation</span>
                        </div>
                    </div>

                    <div
                        class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer text-left group relative overflow-hidden flex items-start">
                        <div
                            class="w-16 h-16 mr-6 flex-shrink-0 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:-rotate-3 transition-transform shadow-inner border border-purple-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-xl mb-2">Supplementary Files</h3>
                            <p class="text-sm text-gray-500 mb-3">Downloadable presentations, reading materials, charts,
                                and additional learning aides.</p>
                            <span
                                class="text-xs font-semibold text-purple-700 bg-purple-50 px-3 py-1 rounded-full">Resources</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 bg-gray-50 border-t border-gray-200 px-4 sm:px-10 lg:px-[10rem]">
            <div class="w-full mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Get Started in 3 Simple
                        Steps</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-10 text-center relative">
                    <div
                        class="hidden md:block absolute top-8 left-[20%] right-[20%] h-0 border-t-2 border-dashed border-gray-300 z-0">
                    </div>

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full text-white font-bold text-2xl flex items-center justify-center mb-6 shadow-lg border-4 border-gray-50"
                            style="background-color: #a52a2a;">1</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Register</h3>
                        <p class="text-gray-500 text-base px-4">Create your secure account using your email.</p>
                    </div>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full text-white font-bold text-2xl flex items-center justify-center mb-6 shadow-lg border-4 border-gray-50"
                            style="background-color: #a52a2a;">2</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Enroll</h3>
                        <p class="text-gray-500 text-base px-4">Join your designated classes or easily assign students
                            to your advisory.</p>
                    </div>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full text-white font-bold text-2xl flex items-center justify-center mb-6 shadow-lg border-4 border-gray-50"
                            style="background-color: #a52a2a;">3</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Learn</h3>
                        <p class="text-gray-500 text-base px-4">Access modules, take quizzes, and track educational
                            progress instantly.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 text-center relative overflow-hidden px-4 sm:px-10 lg:px-[10rem]"
            style="background-color: #a52a2a;">
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
            <div
                class="relative z-10 w-full mx-auto bg-white/10 backdrop-blur-md p-10 md:p-14 rounded-3xl border border-white/20 shadow-2xl">
                <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6 tracking-tight">Ready to elevate your
                    learning experience?</h2>
                <p class="text-red-100 mb-10 text-lg md:text-xl font-light">Join thousands of students and teachers in
                    Zamboanga City utilizing our centralized digital repository today.</p>
                <a href="{{ route('register') }}"
                    class="inline-block px-10 py-4 bg-white hover:bg-gray-50 text-red-900 font-bold text-lg rounded-full transition-all duration-300 shadow-xl transform hover:-translate-y-1">
                    Create Your Free Account
                </a>
            </div>
        </section>
    </main>

    <footer class="bg-gray-950 text-gray-400 py-16 border-t-4 border-red-900 px-4 sm:px-10 lg:px-[10rem]">
        <div class="w-full mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <img src="{{ asset('storage/images/lms-logo-red.png') }}" alt="DepEd Logo"
                        class="h-20 w-50 opacity-90">
                </div>
                <p class="text-sm leading-relaxed max-w-xs">Providing accessible, high-quality, and standard-aligned
                    digital education for all learners in Zamboanga City.</p>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-6">Quick Links</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors flex items-center"><span
                                class="w-1.5 h-1.5 rounded-full bg-red-800 mr-2"></span> Home</a></li>
                    <li><a href="{{ route('explore.public') }}"
                            class="hover:text-white transition-colors flex items-center"><span
                                class="w-1.5 h-1.5 rounded-full bg-red-800 mr-2"></span> Public Materials</a></li>
                    <li><a href="{{ route('login') }}"
                            class="hover:text-white transition-colors flex items-center"><span
                                class="w-1.5 h-1.5 rounded-full bg-red-800 mr-2"></span> Login Account</a></li>
                    <li><a href="{{ route('register') }}"
                            class="hover:text-white transition-colors flex items-center"><span
                                class="w-1.5 h-1.5 rounded-full bg-red-800 mr-2"></span> Create Account</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-6">Contact & Support</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-red-800 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        DepEd – Division of Zamboanga City, Baliwasan Chico, Zamboanga City
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-red-800 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                        support@depedzamboanga.ph
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-red-800 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Mon - Fri, 8:00 AM - 5:00 PM PST
                    </li>
                </ul>
            </div>
        </div>
        <div
            class="w-full mx-auto border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm">
            <p>&copy; {{ date('Y') }} Department of Education - Zamboanga City. All rights reserved.</p>
            <div class="mt-4 md:mt-0 flex space-x-4">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>
</body>

</html>