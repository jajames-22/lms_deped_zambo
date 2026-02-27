<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 overflow-x-hidden">

    <div class="flex min-h-screen">

        <!-- Backdrop Overlay (Mobile only) -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"
            onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 z-50 transform -translate-x-full md:translate-x-0 md:relative sidebar-transition flex flex-col">
            <div class="px-6 py-8 flex items-center justify-between lg:justify-center">
                <div class="flex-shrink-0">
                    <a href="{{ url('/dashboard') }}">
                        <img src="{{ asset('storage/images/lms-logo-red.png') }}"
                            class="w-48 h-auto object-contain block" alt="LMS Logo">
                    </a>
                </div>

                <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-[#a52a2a] p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <nav class="flex-1">
                <!-- Navigation Links -->
                <button onclick="loadPartial('{{ url('/dashboard/home') }}', this)"
                    class="nav-btn w-full flex items-center px-4 py-3 bg-[#a52a2a]/10 text-[#a52a2a] font-medium border-r-4 border-[#a52a2a] group transition-all">
                    <i class="fas fa-th-large w-5 mr-3"></i> Dashboard
                </button>

                <button onclick="loadPartial('{{ url('/dashboard/courses') }}', t
                his)"
                    class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
                    <i class="fas fa-book-open w-5 mr-3 group-hover:text-[#a52a2a]"></i> My Courses
                </button>

                <button onclick="loadPartial('{{ url('/dashboard/assignments') }}', this)"
                    class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
                    <i class="fas fa-tasks w-5 mr-3 group-hover:text-[#a52a2a]"></i> Assignments
                </button>

                <button onclick="loadPartial('{{ url('/dashboard/statistics') }}', this)"
                    class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
                    <i class="fas fa-chart-line w-5 mr-3 group-hover:text-[#a52a2a]"></i> Statistics
                </button>

                <button onclick="loadPartial('{{ url('/dashboard/settings') }}', this)"
                    class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
                    <i class="fas fa-cog w-5 mr-3 group-hover:text-[#a52a2a]"></i> Settings
                </button>
            </nav>

            <div class="border-t border-gray-100">
                <button onclick="toggleLogoutModal()"
                    class="w-full flex items-center px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 transition font-medium">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <!-- Added 'sticky top-0' and 'z-20' to keep it at the top and above the body content -->
            <header
                class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 md:px-8 shrink-0 sticky top-0 z-20">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Hamburger Menu Button -->
                    <button onclick="toggleSidebar()"
                        class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <div
                        class="flex items-center bg-gray-100 px-3 py-2 rounded-lg w-full max-w-md hidden sm:flex border border-transparent focus-within:border-[#a52a2a]/30 transition">
                        <i class="fas fa-search text-gray-400 mr-2"></i>
                        <input type="text" placeholder="Search courses, lessons..."
                            class="bg-transparent border-none outline-none text-sm w-full focus:ring-0">
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <button class="relative text-gray-500 hover:text-[#a52a2a] transition">
                        <i class="fas fa-bell text-xl"></i>
                        <span
                            class="absolute top-0 right-0 h-2 w-2 bg-[#a52a2a] rounded-full border-2 border-white"></span>
                    </button>
                    <div class="flex items-center space-x-3 border-l pl-4 border-gray-200">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold">Alex Johnson</p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">Student ID: 2024001</p>
                        </div>
                        <img class="h-9 w-9 rounded-full border-2 border-[#a52a2a]/20"
                            src="https://ui-avatars.com/api/?name=Alex+Johnson&background=a52a2a&color=fff"
                            alt="Profile">
                    </div>
                </div>
            </header>

            <div id="content-area" class="flex-1 overflow-y-auto">
                </div>
        </main>
    </div>

    <div id="logoutModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div
                class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transform transition-all border border-gray-100">
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
                        <button type="submit"
                            class="w-full px-4 py-2.5 bg-[#a52a2a] text-white font-semibold rounded-xl hover:opacity-90 transition shadow-lg shadow-[#a52a2a]/30 text-sm">
                            Yes, Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const logoutModal = document.getElementById('logoutModal');
        const contentArea = document.getElementById('content-area');

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('-translate-x-full');
            if (isOpen) {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            } else {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            }
        }

        function toggleLogoutModal() {
            logoutModal.classList.toggle('hidden');
        }

        function loadPartial(url, element) {
            contentArea.innerHTML = '<div class="flex justify-center items-center h-full"><i class="fas fa-circle-notch fa-spin text-3xl text-[#a52a2a]"></i></div>';

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    contentArea.innerHTML = html;
                    
                    // Reset scroll position to top when new content loads
                    contentArea.scrollTop = 0;

                    document.querySelectorAll('.nav-btn').forEach(btn => {
                        btn.classList.remove('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
                        btn.classList.add('text-gray-600', 'hover:bg-gray-100');
                    });

                    element.classList.add('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-medium', 'border-r-4', 'border-[#a52a2a]');
                    element.classList.remove('text-gray-600', 'hover:bg-gray-100');

                    if (window.innerWidth < 768) toggleSidebar();
                })
                .catch(err => {
                    contentArea.innerHTML = '<p class="text-center text-red-500 p-10">Error loading page content.</p>';
                });
        }

        window.onload = () => {
            const dashboardBtn = document.querySelector('.nav-btn');
            loadPartial('{{ url("/dashboard/home") }}', dashboardBtn);
        };

        window.onclick = function (event) {
            if (event.target == logoutModal.firstElementChild) toggleLogoutModal();
        }
    </script>
</body>

</html>