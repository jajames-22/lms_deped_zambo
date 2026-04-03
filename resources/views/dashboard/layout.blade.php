<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Dashboard - DepEd Zamboanga</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                transform: none;
            }
        }

        .animate-float-in {
            animation: floatIn 0.2s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-900 h-screen overflow-hidden">

    <div class="flex h-full">
        <div id="sidebarBackdrop"
            class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none md:hidden transition-opacity duration-300"
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
                class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 md:px-8 shrink-0 z-20 relative">
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

                <div class="flex items-center space-x-2 sm:space-x-4 relative">
                    
                    <div class="relative" id="notificationContainer">
                        <button onclick="toggleNotifications()" class="relative text-gray-600 hover:bg-gray-100 p-2 rounded-full focus:outline-none transition-colors flex items-center justify-center h-10 w-10">
                            <i class="fas fa-bell text-xl"></i>
                            <span id="notificationBadge" class="hidden absolute top-0 right-0 h-3 w-3 bg-red-600 rounded-full border-2 border-white shadow-sm"></span>
                        </button>

                        <div id="notificationDropdown" class="hidden fixed inset-0 m-4 sm:m-0 sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:mt-2 sm:w-[380px] bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col z-[100] sm:z-50 transform opacity-0 sm:scale-95 transition-all duration-200 origin-top-right sm:max-h-[85vh] overflow-hidden">
        
                            <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center shrink-0 bg-white shadow-sm z-10 relative">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-gray-900 text-2xl tracking-tight">Notifications</h3>
                                    <span id="notificationCountText" class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-md hidden">0 New</span>
                                </div>
                                <button onclick="toggleNotifications()" class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 flex items-center justify-center transition-colors focus:outline-none text-lg">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div id="notificationList" class="flex-1 overflow-y-auto bg-gray-50/50 sidebar-scroll pb-4 relative z-0">
                                <div class="flex justify-center items-center py-8">
                                    <i class="fas fa-circle-notch fa-spin text-2xl text-gray-300"></i>
                                </div>
                            </div>

                            <div class="w-full flex items-center p-4">
                                <p class="text-xs text-center text-gray-500">Notifications that are more than 30 days will automatically be deleted</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 border-l pl-4 border-gray-200 cursor-pointer 
                            hover:bg-gray-100 p-1.5 
                            rounded-none hover:rounded-xl 
                            transition-all duration-300 ease-in-out"
                        onclick="loadPartial('{{ url('/dashboard/profile') }}', document.getElementById('nav-profile-btn'))">

                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold">{{ auth()->user()->first_name }}
                                {{ auth()->user()->last_name }}
                            </p>
                            <p class="text-[10px] text-gray-500 uppercase">
                                {{ ucfirst(auth()->user()->role ?? 'Student') }}
                            </p>
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

    <div id="logoutModal" class="fixed inset-0 z-[110] opacity-0 pointer-events-none transition-opacity duration-300">
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
                        <button type="submit" onclick="sessionStorage.clear();"
                            class="w-full px-4 py-2.5 bg-[#a52a2a] text-white font-semibold rounded-xl hover:opacity-90 transition shadow-lg shadow-[#a52a2a]/30 text-sm">
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

                    contentArea.addEventListener('animationend', function handler() {
                        contentArea.classList.remove('animate-float-in');
                        contentArea.removeEventListener('animationend', handler); 
                    });
                    
                    const scripts = contentArea.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    document.querySelectorAll('.nav-btn').forEach(btn => {
                        btn.classList.remove('bg-[#a52a2a]/10', 'text-[#a52a2a]', 'font-bold', 'border-r-4', 'border-[#a52a2a]');
                        btn.classList.add('text-gray-600', 'hover:bg-gray-100');
                    });

                    let targetBtn = element;
                    if (!targetBtn || !targetBtn.classList) {
                        const roleIsStudent = document.getElementById('nav-explore-btn') !== null;
                        if (roleIsStudent) {
                            if (url.includes('/explore') || url.includes('/materials')) targetBtn = document.getElementById('nav-explore-btn');
                            else if (url.includes('/enrolled')) targetBtn = document.getElementById('nav-enrolled-btn');
                            else if (url.includes('/home')) targetBtn = document.getElementById('nav-student-home-btn') || document.querySelector('.nav-btn');
                        } else {
                            if (url.includes('/materials')) targetBtn = document.getElementById('nav-materials-btn');
                            else if (url.includes('/assessment')) targetBtn = document.getElementById('nav-assessment-btn');
                            else if (url.includes('/explore-layout')) targetBtn = document.getElementById('nav-explore-layout-btn');
                            else if (url.includes('/schools')) targetBtn = document.getElementById('nav-schools-btn');
                            else if (url.includes('/teachers')) targetBtn = document.getElementById('nav-teachers-btn');
                            else if (url.includes('/students')) targetBtn = document.getElementById('nav-students-btn');
                            else if (url.includes('/home')) targetBtn = document.querySelector('.nav-btn');
                        }
                    }

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
                const targetBtn = document.getElementById(savedBtnId) || document.querySelector('.nav-btn');
                loadPartial(savedUrl, targetBtn);
            } else {
                const dashboardBtn = document.querySelector('.nav-btn');
                loadPartial('{{ url("/dashboard/home") }}', dashboardBtn);
            }
            
            // Check for notifications immediately when dashboard loads
            fetchNotifications();
        };

        window.onclick = function (event) {
            if (event.target == logoutModal.firstElementChild) toggleLogoutModal();
        }

        // --- NOTIFICATION SYSTEM LOGIC ---
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationBadge = document.getElementById('notificationBadge');
        const notificationList = document.getElementById('notificationList');
        const notificationCountText = document.getElementById('notificationCountText');
        let isNotificationOpen = false;

        function toggleNotifications() {
            isNotificationOpen = !isNotificationOpen;
            if (isNotificationOpen) {
                notificationDropdown.classList.remove('hidden');
                // Small delay to allow display:block to apply before animating opacity/transform
                setTimeout(() => {
                    notificationDropdown.classList.remove('opacity-0', 'sm:scale-95');
                    notificationDropdown.classList.add('opacity-100', 'sm:scale-100');
                }, 10);
                fetchNotifications(); // Refresh data when opened
            } else {
                notificationDropdown.classList.remove('opacity-100', 'sm:scale-100');
                notificationDropdown.classList.add('opacity-0', 'sm:scale-95');
                setTimeout(() => notificationDropdown.classList.add('hidden'), 200);
            }
        }

        async function fetchNotifications() {
            try {
                const response = await fetch('{{ route("dashboard.notifications") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                if (data.success) {
                    // We now pass both the list AND the unread count
                    renderNotifications(data.notifications, data.unread_count);
                }
            } catch (error) {
                console.error("Failed to load notifications:", error);
            }
        }

        async function markAsReadAndGo(notifId, targetUrl, isRead) {
            // Only ping the server if the notification hasn't been read yet
            if (!isRead) {
                try {
                    // ADDED /dashboard back into the URL string here!
                    await fetch(`{{ url('/dashboard/notifications') }}/${notifId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                } catch (error) {
                    console.error("Network error while marking as read:", error);
                }
            }
            
            toggleNotifications(); 
            window.location.href = targetUrl;
        }

        function renderNotifications(notifications, unreadCount) {
            // Badge logic now depends on the actual unreadCount, not the total list length
            if (unreadCount > 0) {
                notificationBadge.classList.remove('hidden');
                notificationCountText.classList.remove('hidden');
                notificationCountText.innerText = `${unreadCount} New`;
            } else {
                notificationBadge.classList.add('hidden');
                notificationCountText.classList.add('hidden');
            }

            notificationList.innerHTML = '';
            
            if (notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="px-6 py-12 text-center flex flex-col items-center">
                        <img src="https://cdn-icons-png.flaticon.com/512/3237/3237472.png" class="w-20 h-20 opacity-20 mb-4 grayscale" alt="Empty">
                        <p class="text-base font-bold text-gray-500">You're all caught up!</p>
                        <p class="text-sm text-gray-400 mt-1">No notifications from the last 30 days.</p>
                    </div>`;
                return;
            }

            notifications.forEach(notif => {
                const item = document.createElement('div');
                
                // Dynamic styling based on whether it is read or unread
                const bgDefault = notif.is_read ? 'bg-transparent' : 'bg-blue-50/30';
                const textColor = notif.is_read ? 'text-gray-600 font-medium' : 'text-gray-900 font-bold';
                const timeColor = notif.is_read ? 'text-gray-400' : 'text-[#a52a2a] font-semibold';
                const iconOpacity = notif.is_read ? 'opacity-60' : 'opacity-100';

                item.className = `px-4 py-3 hover:bg-gray-100 transition-colors cursor-pointer flex gap-3 group border-b border-gray-50 ${bgDefault}`;
                item.onclick = () => markAsReadAndGo(notif.id, notif.url, notif.is_read);

                const bgClass = notif.colorClass.replace('text-', 'bg-').replace('-600', '-500').replace('-500', '-500');

                item.innerHTML = `
                    <div class="relative shrink-0 ${iconOpacity}">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white shadow-sm ${bgClass}">
                            <i class="${notif.icon} text-lg"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0 pt-1">
                        <p class="text-[14px] ${textColor} leading-snug break-words">
                            ${notif.message}
                        </p>
                        <p class="text-[12px] ${timeColor} mt-1">
                            ${notif.time_ago}
                        </p>
                    </div>
                    <div class="shrink-0 self-center pl-2">
                        ${notif.is_read ? '' : '<div class="w-2.5 h-2.5 bg-[#a52a2a] rounded-full shadow-sm"></div>'}
                    </div>
                `;
                notificationList.appendChild(item);
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            const container = document.getElementById('notificationContainer');
            if (isNotificationOpen && container && !container.contains(event.target)) {
                toggleNotifications();
            }
        });
    </script>
</body>

</html>