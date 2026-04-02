<div class="relative min-h-screen pb-12">
    
    <div class="p-6 pb-2">
        <h2 class="text-3xl font-bold text-gray-900">Comprehensive Analytics</h2>
        <p class="text-gray-500 mt-1">Platform overview, user activity, and system health.</p>
    </div>

    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end">
        
        <div id="fabMenu" class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-48">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">Quick Navigation</p>
                
                <button onclick="scrollToSection('user-demographics'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left group">
                    <i class="fas fa-users w-4 text-center group-hover:scale-110 transition-transform"></i> Demographics
                </button>
                <button onclick="scrollToSection('content-engagement'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left group">
                    <i class="fas fa-book-open w-4 text-center group-hover:scale-110 transition-transform"></i> Engagement
                </button>
                <button onclick="scrollToSection('assessment-performance'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left group">
                    <i class="fas fa-chart-bar w-4 text-center group-hover:scale-110 transition-transform"></i> Performance
                </button>
                <button onclick="scrollToSection('system-health'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left group">
                    <i class="fas fa-server w-4 text-center group-hover:scale-110 transition-transform"></i> System Health
                </button>
                
                <button onclick="scrollToSection('content-area', true); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2 mt-1 text-xs font-semibold text-gray-400 hover:text-gray-800 bg-gray-50 rounded-xl transition-all text-left justify-center border border-gray-200">
                    <i class="fas fa-arrow-up"></i> Back to Top
                </button>
            </div>
        </div>

        <button onclick="toggleFabMenu()" class="w-14 h-14 bg-[#111827] text-white rounded-full shadow-lg shadow-gray-900/30 flex items-center justify-center hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300">
            <i id="fabIcon" class="fas fa-list-ul text-xl transition-transform duration-300"></i>
        </button>
    </div>

    <div class="p-6 space-y-12 max-w-7xl">

        <section id="user-demographics" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-users text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">User & Demographics</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Daily Active Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($dailyActiveUsers) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Weekly Active Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($weeklyActiveUsers) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalStudents + $totalTeachers) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-red-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Unassigned Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($unassignedUsersCount) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Require school assignment</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">User Distribution</h4>
                    <div class="relative h-64 w-full"><canvas id="adminUsersChart"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Top 5 Schools by Volume</h4>
                    <div class="relative h-64 w-full"><canvas id="adminSchoolsChart"></canvas></div>
                </div>
            </div>
        </section>

        <section id="content-engagement" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-book-open text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Content & Engagement</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Materials Published</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalMaterials) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Enrollments</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalEnrollments) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-purple-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Platform Completion Rate</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $completionRate }}%</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Most Engaged Materials (Views)</h4>
                <div class="relative h-72 w-full"><canvas id="adminMaterialsChart"></canvas></div>
            </div>
        </section>

        <section id="assessment-performance" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center"><i class="fas fa-clipboard-check text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Assessment & Performance</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium mb-1">Global Learner Success Rate</p>
                        <p class="text-4xl font-bold text-gray-900">{{ $globalSuccessRate }}%</p>
                        <p class="text-sm text-gray-400 mt-2">Average correct answers across all platform exams.</p>
                    </div>
                    <div class="w-20 h-20 rounded-full bg-yellow-50 flex items-center justify-center border-4 border-yellow-400 text-yellow-600 text-2xl font-bold">
                        <i class="fas fa-star"></i>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium mb-1">Active Assessments</p>
                        <p class="text-4xl font-bold text-gray-900">{{ number_format($totalAssessments) }}</p>
                        <p class="text-sm text-gray-400 mt-2">Total exams created by teachers.</p>
                    </div>
                    <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 text-2xl">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </section>

        <section id="system-health" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-[#a52a2a]/10 text-[#a52a2a] flex items-center justify-center"><i class="fas fa-server text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">System Health & Storage</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Storage Capacity</h4>
                    <div class="relative h-64 w-full"><canvas id="adminStorageChart"></canvas></div>
                </div>
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center space-y-6">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-1">Storage Usage Status</p>
                        <div class="flex items-end gap-2">
                            <p class="text-3xl font-bold text-gray-900">{{ $usedGb }} GB</p>
                            <p class="text-gray-500 mb-1">/ {{ $totalGb }} GB Used</p>
                        </div>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full {{ $storagePercentage > 85 ? 'bg-red-500' : ($storagePercentage > 60 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                             style="width: {{ $storagePercentage }}%"></div>
                    </div>

                    @if($storagePercentage > 85)
                        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex gap-3">
                            <i class="fas fa-exclamation-triangle mt-1"></i>
                            <p class="text-sm"><b>Warning:</b> Server storage is critically low. Consider clearing old logs or upgrading your server capacity.</p>
                        </div>
                    @else
                        <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex gap-3">
                            <i class="fas fa-check-circle mt-1"></i>
                            <p class="text-sm">System storage levels are healthy and operating normally.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

    </div>
</div>

<script>
    // Toggle the Floating Action Button Menu
    function toggleFabMenu() {
        const menu = document.getElementById('fabMenu');
        const icon = document.getElementById('fabIcon');
        
        const isClosed = menu.classList.contains('opacity-0');
        
        if (isClosed) {
            // Open Menu
            menu.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
            menu.classList.add('opacity-100', 'translate-y-0');
            
            // Morph Icon to an 'X'
            icon.classList.remove('fa-list-ul');
            icon.classList.add('fa-times');
            icon.style.transform = 'rotate(90deg)';
        } else {
            // Close Menu
            menu.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
            menu.classList.remove('opacity-100', 'translate-y-0');
            
            // Morph Icon back to List
            icon.classList.add('fa-list-ul');
            icon.classList.remove('fa-times');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    // Smooth Scrolling Function specifically tailored for your dynamic layout container
    function scrollToSection(id, isTop = false) {
        const container = document.getElementById('content-area');
        if (isTop) {
            container.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const el = document.getElementById(id);
        if(el && container) {
            // Calculate relative offset inside the scrollable area
            const offsetTop = el.offsetTop - 20; 
            container.scrollTo({ top: offsetTop, behavior: 'smooth' });
        }
    }

    // Chart logic destruction (prevents hover ghosting on AJAX reload)
    const chartsToClear = ['adminUsersChart', 'adminSchoolsChart', 'adminMaterialsChart', 'adminStorageChart'];
    chartsToClear.forEach(id => {
        if (window[id + 'Instance']) window[id + 'Instance'].destroy();
    });

    // 1. Demographics: Users Doughnut
    const usersCtx = document.getElementById('adminUsersChart').getContext('2d');
    window.adminUsersChartInstance = new Chart(usersCtx, {
        type: 'doughnut',
        data: {
            labels: ['Students', 'Teachers'],
            datasets: [{
                data: [@json($totalStudents), @json($totalTeachers)],
                backgroundColor: ['#3b82f6', '#10b981'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom' } } }
    });

    // 2. Demographics: Top Schools Bar
    const schoolsCtx = document.getElementById('adminSchoolsChart').getContext('2d');
    window.adminSchoolsChartInstance = new Chart(schoolsCtx, {
        type: 'bar',
        data: {
            labels: @json($schoolLabels),
            datasets: [{
                label: 'Enrolled Students',
                data: @json($schoolData),
                backgroundColor: '#3b82f6',
                borderRadius: 6
            }]
        },
        options: { 
            responsive: true, maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } }
        }
    });

    // 3. Content: Top Materials Bar
    const materialsCtx = document.getElementById('adminMaterialsChart').getContext('2d');
    window.adminMaterialsChartInstance = new Chart(materialsCtx, {
        type: 'bar',
        data: {
            labels: @json($topMaterialsLabels),
            datasets: [{
                label: 'Total Views',
                data: @json($topMaterialsData),
                backgroundColor: '#a855f7', // Purple to match the section icon
                borderRadius: 6
            }]
        },
        options: { 
            indexAxis: 'y', // Horizontal bar is better for long titles
            responsive: true, maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, grid: { borderDash: [2, 4] } }, y: { grid: { display: false } } }
        }
    });

    // 4. System Health: Storage Doughnut
    const storageCtx = document.getElementById('adminStorageChart').getContext('2d');
    window.adminStorageChartInstance = new Chart(storageCtx, {
        type: 'doughnut',
        data: {
            labels: ['Used Space', 'Free Space'],
            datasets: [{
                data: [@json($usedGb), @json($totalGb - $usedGb)],
                backgroundColor: [@json($storagePercentage > 85 ? '#ef4444' : '#a52a2a'), '#f3f4f6'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom' } } }
    });
</script>