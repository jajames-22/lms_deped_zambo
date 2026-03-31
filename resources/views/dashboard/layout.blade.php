<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Dashboard - DepEd Zamboanga</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a52a2a;
        }

        @keyframes floatIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-float-in {
            animation: floatIn 0.2s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 h-screen overflow-hidden">

    <div class="flex h-full">
        <div id="sidebarBackdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none md:hidden transition-opacity duration-300"
            onclick="toggleSidebar()"></div>

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 z-50 transform -translate-x-full md:translate-x-0 md:relative transition-all flex flex-col h-full">
            <div class="px-6 py-8 flex items-center justify-between lg:justify-center shrink-0">
                <div class="flex-shrink-0">
                    <a href="{{ url('/dashboard') }}">
                        <img src="{{ asset('storage/images/lms-logo-red.png') }}"
                            class="w-48 h-auto object-contain block" alt="LMS Logo">
                    </a>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto">
                @yield('sidebar_nav')
            </nav>

            <div class="border-t border-gray-100 shrink-0">
                <button onclick="toggleLogoutModal()"
                    class="w-full flex items-center px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 transition font-medium">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                </button>
            </div>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            <header
                class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 md:px-8 shrink-0 z-20">
                <div class="flex items-center gap-4 flex-1">
                    <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div
                        class="flex items-center bg-gray-100 px-3 py-2 rounded-lg w-full max-w-md hidden sm:flex border border-transparent focus-within:border-[#a52a2a]/30 transition">
                        <i class="fas fa-search text-gray-400 mr-2"></i>
                        <input type="text" placeholder="Search..."
                            class="bg-transparent border-none outline-none text-sm w-full focus:ring-0">
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <button class="relative text-gray-500 hover:text-[#a52a2a]">
                        <i class="fas fa-bell text-xl"></i>
                        <span
                            class="absolute top-0 right-0 h-2 w-2 bg-[#a52a2a] rounded-full border-2 border-white"></span>
                    </button>

                    <div class="flex items-center space-x-3 border-l pl-4 border-gray-200 cursor-pointer 
                            hover:bg-gray-100 p-1.5 
                            rounded-none hover:rounded-xl 
                            transition-all duration-300 ease-in-out"
                        onclick="loadPartial('{{ url('/dashboard/profile') }}', document.getElementById('nav-profile-btn'))">

                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold">{{ auth()->user()->first_name }}
                                {{ auth()->user()->last_name }}</p>
                            <p class="text-[10px] text-gray-500 uppercase">
                                {{ ucfirst(auth()->user()->role ?? 'Student') }}</p>
                        </div>
                        <img class="h-9 w-9 rounded-full border-2 border-[#a52a2a]/20"
                            src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->first_name . '+' . auth()->user()->last_name) }}&background=a52a2a&color=fff"
                            alt="Profile">
                    </div>
                </div>
            </header>

            <div id="content-area" class="flex-1 overflow-y-auto bg-gray-50 p-5 md:p-8"></div>
        </main>
    </div>

    <div id="logoutModal" class="fixed inset-0 z-[60] opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="absolute inset-0 bg-gray-900/60" onclick="toggleLogoutModal()"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            
            <div id="logoutModalBox"
                class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transform scale-95 transition-all duration-300 border border-gray-100">
                <div
                    class="w-16 h-16 bg-[#a52a2a]/10 text-[#a52a2a] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-sign-out-alt text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Confirm Logout</h3>
                <p class="text-gray-500 mb-6 text-sm">Are you sure you want to end your session? Your progress will be
                    saved.</p>
                <div class="flex space-x-3">
                    <button onclick="toggleLogoutModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition text-sm">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" onclick="sessionStorage.clear();" class="w-full px-4 py-2.5 bg-[#a52a2a] text-white font-semibold rounded-xl hover:opacity-90 transition shadow-lg shadow-[#a52a2a]/30 text-sm">
                            Yes, Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

   @stack('scripts')

    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const logoutModal = document.getElementById('logoutModal');
        const logoutModalBox = document.getElementById('logoutModalBox'); 
        const contentArea = document.getElementById('content-area');

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('-translate-x-full');
            if (isOpen) {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100');
            } else {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100');
            }
        }

        function toggleLogoutModal() {
            const isClosed = logoutModal.classList.contains('opacity-0');
            if (isClosed) {
                logoutModal.classList.remove('opacity-0', 'pointer-events-none');
                logoutModal.classList.add('opacity-100');
                
                logoutModalBox.classList.remove('scale-95');
                logoutModalBox.classList.add('scale-100');
            } else {
                logoutModal.classList.add('opacity-0', 'pointer-events-none');
                logoutModal.classList.remove('opacity-100');
                
                logoutModalBox.classList.remove('scale-100');
                logoutModalBox.classList.add('scale-95');
            }
        }

        function loadPartial(url, element) {
            sessionStorage.setItem('lastActiveTab', url);
            if (element && element.id) sessionStorage.setItem('lastActiveBtn', element.id);

            contentArea.classList.remove('animate-float-in');
            contentArea.innerHTML = '<div class="flex justify-center items-center h-full"><i class="fas fa-circle-notch fa-spin text-3xl text-[#a52a2a]"></i></div>';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                if (!response.ok) {
                    contentArea.innerHTML = `<div class="p-6 bg-red-50 text-red-700"><b>Error ${response.status}:</b> Check your browser console or Laravel logs.</div>`;
                    throw new Error('Server returned an error');
                }
                return response.text();
            })
            .then(html => {
                contentArea.innerHTML = html;
                contentArea.scrollTop = 0;
                contentArea.classList.add('animate-float-in');

                // SAFER SCRIPT INJECTION (Fixes Carousel issues)
                const scripts = contentArea.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.text = oldScript.textContent; // Uses textContent instead of innerHTML to prevent string parsing bugs
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                // Clear previous active states
                document.querySelectorAll('.nav-btn').forEach(btn => {
                    btn.classList.remove('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-bold', 'border-r-4', 'border-[#a52a2a]');
                    btn.classList.add('text-gray-600', 'hover:bg-gray-100');
                });

                // UPDATED INTELLIGENT ROUTE DETECTION (Fixes Sidebar disappearing)
                let targetBtn = element;
                if (!targetBtn || !targetBtn.classList) {
                    
                    const roleIsStudent = document.getElementById('nav-explore-btn') !== null;
                    
                    if (roleIsStudent) {
                        // Student Fallbacks
                        if (url.includes('/explore') || url.includes('/materials')) targetBtn = document.getElementById('nav-explore-btn');
                        else if (url.includes('/enrolled')) targetBtn = document.getElementById('nav-enrolled-btn');
                        else if (url.includes('/home')) targetBtn = document.getElementById('nav-student-home-btn') || document.querySelector('.nav-btn');
                    } else {
                        // Admin Fallbacks
                        if (url.includes('/materials')) targetBtn = document.getElementById('nav-materials-btn');
                        else if (url.includes('/assessment')) targetBtn = document.getElementById('nav-assessment-btn');
                        else if (url.includes('/explore-layout')) targetBtn = document.getElementById('nav-explore-layout-btn');
                        else if (url.includes('/schools')) targetBtn = document.getElementById('nav-schools-btn');
                        else if (url.includes('/teachers')) targetBtn = document.getElementById('nav-teachers-btn');
                        else if (url.includes('/students')) targetBtn = document.getElementById('nav-students-btn');
                        else if (url.includes('/home')) targetBtn = document.querySelector('.nav-btn');
                    }
                }

                // Apply active state safely
                if (targetBtn) {
                    targetBtn.classList.add('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-bold', 'border-r-4', 'border-[#a52a2a]');
                    targetBtn.classList.remove('text-gray-600', 'hover:bg-gray-100');
                }

                if (window.innerWidth < 768 && !sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            })
            .catch(err => console.error("Fetch failed entirely:", err));
        }

        window.onload = () => {
            const savedUrl = sessionStorage.getItem('lastActiveTab');
            const savedBtnId = sessionStorage.getItem('lastActiveBtn');
            
            if (savedUrl) {
                // If memory exists, load what they were last looking at (Explore, Enrolled, etc.)
                const targetBtn = document.getElementById(savedBtnId) || document.querySelector('.nav-btn');
                loadPartial(savedUrl, targetBtn);
            } else {
                // Default to home if no memory exists
                const dashboardBtn = document.querySelector('.nav-btn');
                loadPartial('{{ url("/dashboard/home") }}', dashboardBtn);
            }
        };

        window.onclick = function (event) {
            if (event.target == logoutModal.firstElementChild) toggleLogoutModal();
        }
    </script>   
</body>

</html>