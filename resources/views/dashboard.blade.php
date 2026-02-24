<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Smooth transition for the sidebar */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
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
            <div class="p-4 flex items-center md:justify-between">
                <!-- Logo Container -->
                <div class="flex-shrink-0">
                    <img src="{{ asset('storage/images/lms-logo-red.png') }}" class="w-40 h-auto object-contain mx-auto"
                        alt="DepEd Zamboanga Header">
                </div>

                <!-- Close Button (Mobile Only) -->
                <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-gray-700 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-1 mt-4">
                <a href="#"
                    class="flex items-center px-4 py-3 bg-red-50 text-red-700 font-medium border-r-4 border-red-600">
                    <i class="fas fa-th-large w-5 mr-3"></i> Dashboard
                </a>
                <a href="#" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition">
                    <i class="fas fa-book-open w-5 mr-3"></i> My Courses
                </a>
                <a href="#" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition">
                    <i class="fas fa-tasks w-5 mr-3"></i> Assignments
                </a>
                <a href="#" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition">
                    <i class="fas fa-chart-line w-5 mr-3"></i> Statistics
                </a>
                <a href="#" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition">
                    <i class="fas fa-cog w-5 mr-3"></i> Settings
                </a>
            </nav>

            <div class="p-4 border-t border-gray-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center px-4 py-3 text-red-500 hover:bg-red-50 rounded-lg transition font-medium">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                    </button>
                </form>

            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header
                class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 md:px-8 shrink-0">
                <div class="flex items-center gap-4 flex-1">
                    <!-- 3-LINE ICON (Hamburger) -->
                    <button onclick="toggleSidebar()"
                        class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <div class="flex items-center bg-gray-100 px-3 py-2 rounded-lg w-full max-w-md hidden sm:flex">
                        <i class="fas fa-search text-gray-400 mr-2"></i>
                        <input type="text" placeholder="Search courses..."
                            class="bg-transparent border-none outline-none text-sm w-full">
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <button class="relative text-gray-500 hover:text-indigo-600">
                        <i class="fas fa-bell text-xl"></i>
                        <span
                            class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <div class="flex items-center space-x-3 border-l pl-4 border-gray-200">
                        <img class="h-9 w-9 rounded-full border-2 border-indigo-100"
                            src="https://ui-avatars.com/api/?name=Alex+Johnson&background=6366f1&color=fff"
                            alt="Profile">
                    </div>
                </div>
            </header>

            <!-- Dashboard Body -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8">
                <!-- Welcome Section -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome back, Alex! 👋</h2>
                    <p class="text-gray-500 mt-1">You've completed <span
                            class="text-indigo-600 font-semibold">80%</span> of your weekly goal.</p>
                </section>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-blue-100 text-blue-600 rounded-xl"><i class="fas fa-book text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Enrolled</p>
                                <p class="text-xl font-bold">12</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-green-100 text-green-600 rounded-xl"><i
                                    class="fas fa-check-circle text-xl"></i></div>
                            <div>
                                <p class="text-xs text-gray-500">Completed</p>
                                <p class="text-xl font-bold">04</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-yellow-100 text-yellow-600 rounded-xl"><i
                                    class="fas fa-clock text-xl"></i></div>
                            <div>
                                <p class="text-xs text-gray-500">Hours</p>
                                <p class="text-xl font-bold">58h</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-purple-100 text-purple-600 rounded-xl"><i
                                    class="fas fa-certificate text-xl"></i></div>
                            <div>
                                <p class="text-xs text-gray-500">Badges</p>
                                <p class="text-xl font-bold">02</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-4">
                        <h3 class="text-lg font-bold text-gray-800">Continue Learning</h3>

                        <div
                            class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center gap-4">
                            <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=300"
                                class="w-full sm:w-24 h-24 object-cover rounded-xl" alt="Course">
                            <div class="flex-1 w-full">
                                <h4 class="font-bold text-gray-800 text-sm">UI/UX Design Masterclass</h4>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: 75%"></div>
                                </div>
                            </div>
                            <button
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-indigo-700 w-full sm:w-auto">Continue</button>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Upcoming</h3>
                        <div class="space-y-4 text-sm text-gray-600">
                            <p class="flex items-center"><i class="fas fa-circle text-[8px] text-red-500 mr-2"></i>
                                Final Quiz Today</p>
                            <p class="flex items-center"><i class="fas fa-circle text-[8px] text-gray-300 mr-2"></i> Mar
                                24: Project Due</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript to toggle Sidebar -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                // Close
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else {
                // Open
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                // Prevent scrolling main body when menu is open on mobile
                document.body.classList.add('overflow-hidden');
            }
        }
    </script>
</body>

</html>