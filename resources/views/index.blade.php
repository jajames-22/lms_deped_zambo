<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>DepEd Zamboanga - LMS</title>
    <style>
        * {
            box-sizing: border-box;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <header style="background-color: #a52a2a;" class="fixed p-1 flex justify-center z-40 w-full items-center shadow-md">
        <img src="{{ asset('storage/images/deped_zambo_header.png') }}" class="w-full max-w-4xl h-auto block"
            alt="DepEd Zamboanga Header">
    </header>
    <main>
        <section style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');"
            class="relative bg-cover bg-center bg-no-repeat px-6 pt-40 md:pt-40 min-h-screen">
            <div class="absolute inset-0 bg-red-900/50"></div>

            <div class="relative z-10 max-w-7xl mx-auto text-center flex items-center justify-center flex-col">
                <img src="{{ asset('storage/images/hero-text.png') }}" class="w-full max-w-xl h-auto block"
                    alt="DepEd Zamboanga Header">
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight text-shadow-lg/30">
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
                        class="px-8 py-3 bg-white! hover:bg-white-100 text-red-700! font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        Create Account
                    </a>
                    <a href="{{ route('explore.public') }}" 
                        class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 border border-gray-300">
                        Explore Materials
                    </a>
                </div>
            </div>
        </section>

        <section class="py-24 px-6 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Empowering Education Through Technology</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto text-lg">Discover tailored tools designed to enhance the teaching and learning experience for everyone in Zamboanga City.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-10">
                    <div class="bg-gray-50 p-8 rounded-2xl border-t-4 shadow-sm hover:shadow-md transition-shadow" style="border-color: #a52a2a;">
                        <div class="flex items-center mb-6">
                            <div class="p-3 rounded-full mr-4" style="background-color: rgba(165, 42, 42, 0.1);">
                                <svg class="w-8 h-8" style="color: #a52a2a;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"></path></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">For Students</h3>
                        </div>
                        <ul class="space-y-4 text-gray-700">
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-3 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><strong>24/7 Material Access:</strong> Browse, study, and download division-standard modules anytime, anywhere.</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-3 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><strong>Track Progress & Certificates:</strong> Monitor your learning journey and earn certificates for completing materials.</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-3 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><strong>Interactive Assessments:</strong> Take quizzes and exams securely directly within the platform and see instant results.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-gray-50 p-8 rounded-2xl border-t-4 shadow-sm hover:shadow-md transition-shadow" style="border-color: #a52a2a;">
                        <div class="flex items-center mb-6">
                            <div class="p-3 rounded-full mr-4" style="background-color: rgba(165, 42, 42, 0.1);">
                                <svg class="w-8 h-8" style="color: #a52a2a;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">For Teachers</h3>
                        </div>
                        <ul class="space-y-4 text-gray-700">
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-3 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><strong>Centralized Management:</strong> Easily organize classes, track enrolled students, and manage materials in a unified dashboard.</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-3 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><strong>Detailed Analytics:</strong> Gain real-time insights into student performance and identify areas needing intervention.</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-3 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span><strong>Streamlined Grading:</strong> Automate quiz grading and manage robust assessments with advanced security and access controls.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 text-center px-6" style="background-color: rgba(165, 42, 42, 0.05);">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Ready to start learning?</h2>
            <p class="text-gray-600 mb-8 max-w-xl mx-auto">Join thousands of students and teachers in Zamboanga City utilizing our centralized digital repository.</p>
            <a href="{{ route('register') }}" style="background-color: #a52a2a;" class="px-10 py-4 hover:bg-red-800 text-white font-bold rounded-lg transition-all duration-300 shadow-md">
                Create Your Free Account
            </a>
        </section>
    </main>
</body>
</html>