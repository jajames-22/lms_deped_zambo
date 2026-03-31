<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $material->title }} - LMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- PAGE TRANSITION ANIMATIONS --}}
    <style>
        /* Slide in from right to left */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Slide out from left to right */
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .animate-slide-in {
            animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-slide-out {
            animation: slideOutRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>

<body
    class="bg-gray-50 font-sans text-gray-900 min-h-screen overflow-x-hidden selection:bg-[#a52a2a] selection:text-white">

    {{-- WRAPPER FOR ANIMATION --}}
    <div id="page-wrapper" class="min-h-screen flex flex-col animate-slide-in">

        {{-- CLEAN HEADER --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
            <div class="max-w-6xl mx-auto px-4 md:px-8 h-16 flex items-center justify-between">

                {{-- NATIVE BROWSER BACK BUTTON --}}
                <button onclick="navigateBack()"
                    class="flex items-center text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group px-2 py-1 rounded-lg hover:bg-red-50">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="hidden sm:inline">Back</span>
                </button>

                {{-- Center Title (Hidden on very small screens) --}}
                <div class="font-black text-gray-900 text-lg truncate px-4 hidden md:block max-w-xl">
                    {{ $material->title }}
                </div>

                {{-- User Profile Snippet --}}
                <div class="flex items-center gap-3 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-gray-900 leading-tight">{{ auth()->user()->first_name }}</p>
                        <p class="text-[9px] text-[#a52a2a] uppercase font-black tracking-widest">
                            {{ auth()->user()->role ?? 'Student' }}
                        </p>
                    </div>
                    <img class="h-8 w-8 rounded-full border-2 border-[#a52a2a]/20"
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->first_name . '+' . auth()->user()->last_name) }}&background=a52a2a&color=fff"
                        alt="Profile">
                </div>
            </div>
        </header>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 max-w-5xl mx-auto w-full py-8 px-4 sm:px-6 lg:px-8 pb-24">

            {{-- Hero Section: Side-by-Side Layout --}}
            <div
                class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-10 flex flex-col md:flex-row gap-8 lg:gap-12">

                {{-- LEFT: Strict 4:3 Thumbnail --}}
                <div class="w-full md:w-1/2 lg:w-5/12 shrink-0">
                    <div
                        class="w-full aspect-[4/3] rounded-2xl overflow-hidden shadow-md bg-gray-100 relative border border-gray-200">
                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=800' }}"
                            class="w-full h-full object-cover">

                        <div class="absolute top-4 left-4">
                            @if($material->is_public)
                                <span
                                    class="px-3 py-1 bg-white/90 backdrop-blur text-green-700 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm">Public
                                    Module</span>
                            @else
                                <span
                                    class="px-3 py-1 bg-gray-900/90 backdrop-blur text-white text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm"><i
                                        class="fas fa-lock mr-1"></i> Private</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Course Details & Action --}}
                <div class="w-full md:w-1/2 lg:w-7/12 flex flex-col">

                    <div class="flex flex-wrap gap-2 mb-4">
                        @forelse($material->tags as $tag)
                            <span
                                class="px-3 py-1 bg-[#a52a2a]/10 text-[#a52a2a] border border-[#a52a2a]/20 text-[10px] font-black uppercase tracking-wider rounded-md">
                                {{ $tag->name }}
                            </span>
                        @empty
                            <span
                                class="px-3 py-1 bg-gray-100 text-gray-500 border border-gray-200 text-[10px] font-black uppercase tracking-wider rounded-md">
                                General
                            </span>
                        @endforelse
                    </div>

                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 mb-4 leading-tight">
                        {{ $material->title }}
                    </h1>
                    <p class="text-gray-600 text-sm md:text-base leading-relaxed mb-6 line-clamp-4">
                        {{ $material->description }}
                    </p>

                    <div class="flex flex-wrap items-center gap-6 py-4 border-y border-gray-100 mt-auto mb-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 text-lg border border-gray-200 shadow-sm shrink-0">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Instructor</p>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $material->instructor->first_name ?? 'Unknown' }}
                                    {{ $material->instructor->last_name ?? '' }}
                                </p>
                            </div>
                        </div>

                        <div class="hidden sm:block w-px h-8 bg-gray-200"></div>

                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Total Views</p>
                            <p class="text-sm font-bold text-gray-900"><i
                                    class="fas fa-eye text-[#a52a2a] mr-1.5"></i>{{ number_format($material->views) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        @if($isEnrolled)
                            <button
                                class="w-full sm:w-auto px-8 py-3.5 bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-600/20 flex items-center justify-center gap-2 cursor-default">
                                <i class="fas fa-check-circle text-lg"></i> Enrolled
                            </button>
                        @else
                            <button onclick="enrollInMaterial({{ $material->id }}, this)"
                                class="w-full sm:w-auto px-8 py-3.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition shadow-lg shadow-[#a52a2a]/20 flex items-center justify-center gap-2">
                                <i class="fas fa-user-plus text-lg"></i> Enroll Now
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Course Content / Lessons List --}}
            <h3 class="text-xl font-black text-gray-900 mb-4 px-2">Course Content</h3>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-2 md:p-4">

                @forelse($material->lessons as $index => $lesson)
                    <div
                        class="p-4 rounded-2xl transition-colors border border-transparent flex items-start gap-4 group {{ $isEnrolled ? 'hover:bg-gray-50 hover:border-gray-100 cursor-pointer' : 'opacity-70 cursor-not-allowed' }}">
                        <div
                            class="h-10 w-10 shrink-0 rounded-xl bg-gray-100 text-gray-400 font-black flex items-center justify-center {{ $isEnrolled ? 'group-hover:bg-[#a52a2a]/10 group-hover:text-[#a52a2a]' : '' }} transition-colors">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0 pt-1.5">
                            <h4
                                class="font-bold text-gray-900 {{ $isEnrolled ? 'group-hover:text-[#a52a2a]' : '' }} transition-colors text-lg">
                                {{ $lesson->title }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-4 mt-1">
                                <p
                                    class="text-xs text-gray-500 font-medium flex items-center gap-1.5 uppercase tracking-wider">
                                    <i class="fas fa-book-open text-gray-400"></i> {{ ucfirst($lesson->section_type) }}
                                </p>
                                @if($lesson->time_limit)
                                    <p
                                        class="text-xs text-gray-500 font-medium flex items-center gap-1.5 uppercase tracking-wider">
                                        <i class="far fa-clock text-gray-400"></i> {{ $lesson->time_limit }} mins
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="pt-3 pl-4">
                            @if($isEnrolled)
                                <i class="fas fa-chevron-right text-gray-300 group-hover:text-[#a52a2a] transition-colors"></i>
                            @else
                                <i class="fas fa-lock text-gray-300 tooltip" title="Enroll to unlock"></i>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div
                            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                            <i class="fas fa-folder-open text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-medium">The instructor hasn't added any lessons to this module yet.</p>
                    </div>
                @endforelse

            </div>
        </main>

        {{-- Replace the <form> wrapper with just this button --}}
            <div class="flex justify-center md:justify-start mt-4">
                <button onclick="completeModule({{ $material->id }}, this)"
                    class="px-8 py-4 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-600/20 flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle text-xl"></i> Mark Module as Complete
                </button>
            </div>
    </div>

    {{-- Standalone Alert Modal --}}
    <div id="standaloneAlertModal"
        class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60" onclick="closeStandaloneAlert()"></div>
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10"
            id="standaloneAlertBox">
            <div id="standaloneAlertIconContainer"
                class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl">
                <i id="standaloneAlertIcon" class="fas fa-info"></i>
            </div>
            <h3 id="standaloneAlertTitle" class="text-xl font-black text-gray-900 mb-2">Notice</h3>
            <p id="standaloneAlertMessage" class="text-sm text-gray-500 mb-6"></p>
            <button type="button" onclick="closeStandaloneAlert()"
                class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                Okay
            </button>
        </div>
    </div>

    <script>
        // PAGE TRANSITION LOGIC
        function navigateBack() {
            const wrapper = document.getElementById('page-wrapper');

            // 1. Trigger the slide-out animation
            wrapper.classList.remove('animate-slide-in');
            wrapper.classList.add('animate-slide-out');

            // 2. Secretly tell the dashboard's memory to open the Enrolled tab next
            sessionStorage.setItem('lastActiveTab', '{{ route('student.enrolled.index') }}');
            sessionStorage.setItem('lastActiveBtn', 'nav-enrolled-btn');

            // 3. Do a clean redirect back to the main dashboard
            setTimeout(() => {
                window.location.href = "{{ url('/dashboard') }}";
            }, 300);
        }
        // Alert Modal Logic
        function showStandaloneAlert(message, type) {
            const modal = document.getElementById('standaloneAlertModal');
            const box = document.getElementById('standaloneAlertBox');
            const iconContainer = document.getElementById('standaloneAlertIconContainer');
            const icon = document.getElementById('standaloneAlertIcon');
            const title = document.getElementById('standaloneAlertTitle');
            const msg = document.getElementById('standaloneAlertMessage');

            msg.innerText = message;

            if (type === 'success') {
                title.innerText = 'Success!';
                iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-green-100 text-green-500';
                icon.className = 'fas fa-check-circle';
            } else {
                title.innerText = 'Error!';
                iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-red-100 text-red-500';
                icon.className = 'fas fa-exclamation-circle';
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
            }, 10);
        }

        function closeStandaloneAlert() {
            const modal = document.getElementById('standaloneAlertModal');
            const box = document.getElementById('standaloneAlertBox');

            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');

            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        // Enrollment Logic
        async function enrollInMaterial(materialId, btn) {
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i> Enrolling...';

            try {
                const response = await fetch(`{{ url('/dashboard/materials') }}/${materialId}/enroll`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    btn.className = "w-full sm:w-auto px-8 py-3.5 bg-green-600 text-white font-bold rounded-xl flex items-center justify-center gap-2 cursor-default";
                    btn.innerHTML = '<i class="fas fa-check-circle text-lg"></i> Enrolled';
                    btn.onclick = null;

                    showStandaloneAlert('Successfully enrolled! The page will now refresh to unlock your lessons.', 'success');

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showStandaloneAlert(data.message || 'Failed to enroll.', 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                showStandaloneAlert('A network error occurred. Please check your connection.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        async function completeModule(materialId, btn) {
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i> Processing...';

            try {
                const response = await fetch(`{{ url('/dashboard/student/materials') }}/${materialId}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // 1. Tell the dashboard memory to load the ENROLLED page when they eventually click "Return"
                    sessionStorage.setItem('lastActiveTab', '{{ route('student.enrolled.index') }}');
                    sessionStorage.setItem('lastActiveBtn', 'nav-enrolled-btn');

                    // 2. Redirect DIRECTLY to the standalone celebration page
                    window.location.href = data.redirect_url;
                } else {
                    showStandaloneAlert(data.message || 'Failed to mark as complete.', 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                showStandaloneAlert('A network error occurred. Please try again.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    </script>
</body>

</html>