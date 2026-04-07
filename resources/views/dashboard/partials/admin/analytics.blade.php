<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="dashboard-content" class="relative min-h-screen pb-12 bg-gray-50">
    {{-- Header --}}
    <div class="p-6 pb-2 flex justify-between items-end max-w-7xl mx-auto">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Comprehensive Analytics</h2>
            <p class="text-gray-500 mt-1">Platform overview, user activity, and system health.</p>
        </div>
        <button onclick="toggleExportModal()" class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    {{-- =========================================================================
         FLOATING ACTION BUTTON (QUICK NAVIGATION)
         ========================================================================= --}}
    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end">
        <div id="fabMenu" class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-56">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">Quick Navigation</p>
                
                <button onclick="scrollToSection('ui-user-demographics'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-users w-4 text-center"></i> Demographics
                </button>
                <button onclick="scrollToSection('ui-content-engagement'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-book-open w-4 text-center"></i> Content & Engagement
                </button>
                <button onclick="scrollToSection('ui-system-health'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-server w-4 text-center"></i> System Health
                </button>
                
                <button onclick="scrollToSection('dashboard-content', true); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2 mt-1 text-xs font-semibold text-gray-400 hover:text-gray-800 bg-gray-50 rounded-xl transition-all text-left justify-center border border-gray-200">
                    <i class="fas fa-arrow-up"></i> Back to Top
                </button>
            </div>
        </div>

        <button onclick="toggleFabMenu()" class="w-14 h-14 bg-[#111827] text-white rounded-full shadow-lg shadow-gray-900/30 flex items-center justify-center hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300">
            <i id="fabIcon" class="fas fa-list-ul text-xl transition-transform duration-300"></i>
        </button>
    </div>

    {{-- Dashboard Content Sections --}}
    <div class="p-6 space-y-12 max-w-7xl mx-auto">

        {{-- UI Section: Demographics --}}
        <section id="ui-user-demographics" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-users text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">User & Demographics</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Daily Active Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($dailyActiveUsers) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-400">
                    <p class="text-gray-500 text-sm font-medium mb-1">Weekly Active Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($weeklyActiveUsers ?? 0) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Registered Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalStudents + $totalTeachers) }}</p>
                </div>
                {{-- REPLACED: Unassigned Users is now Total Schools --}}
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-emerald-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Schools</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalSchools) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Registered in platform</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Top Schools by Student Count</h4>
                <div class="relative h-72 w-full flex justify-center items-center">
                    @if(count($schoolLabels) == 0)
                        <p class="text-gray-400 text-sm">No school data available yet.</p>
                    @else
                        <canvas id="schoolDistributionChart"></canvas>
                    @endif
                </div>
            </div>
        </section>

        {{-- UI Section: Content --}}
        <section id="ui-content-engagement" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-book-open text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Content & Engagement</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <h4 class="text-gray-500 font-medium mb-2">Total Modules</h4>
                    <p class="text-4xl font-bold text-purple-600">{{ number_format($totalMaterials) }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <h4 class="text-gray-500 font-medium mb-2">Total Enrollments</h4>
                    <p class="text-4xl font-bold text-blue-600">{{ number_format($totalEnrollments ?? 0) }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <h4 class="text-gray-500 font-medium mb-2">Platform Completion Rate</h4>
                    <p class="text-4xl font-bold text-green-500">{{ $completionRate ?? 0 }}%</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Most Popular Modules (By Views)</h4>
                <div class="relative h-72 w-full flex justify-center items-center">
                    @if(count($topMaterialsLabels ?? []) == 0)
                        <p class="text-gray-400 text-sm">No modules have been viewed yet.</p>
                    @else
                        <canvas id="topMaterialsChart"></canvas>
                    @endif
                </div>
            </div>
        </section>

        {{-- UI Section: Health --}}
        <section id="ui-system-health" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="fas fa-server text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">System Health & Storage</h3>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/3 flex justify-center">
                    <div class="relative w-40 h-40">
                        <canvas id="storageChart"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center flex-col">
                            <span class="text-2xl font-bold text-gray-800">{{ $storagePercentage }}%</span>
                            <span class="text-xs text-gray-500 uppercase">Used</span>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-2/3 space-y-4">
                    <div>
                        <div class="flex justify-between text-sm font-medium mb-1">
                            <span class="text-gray-700">Storage Capacity</span>
                            <span class="text-gray-900">{{ $usedGb }} GB / {{ $totalGb }} GB</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3">
                            <div class="h-3 rounded-full {{ $storagePercentage > 85 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $storagePercentage }}%"></div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">
                        @if($storagePercentage > 85)
                            <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i> Warning: Storage capacity is running low. Please clear temporary files or upgrade capacity.
                        @else
                            <i class="fas fa-check-circle text-green-500 mr-1"></i> System storage is within healthy limits.
                        @endif
                    </p>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- =========================================================================
     EXPORT MODAL
     ========================================================================= --}}
<div id="exportModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="exportModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Export Admin Report</h3>
            <button onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i class="fas fa-times text-lg"></i></button>
        </div>

        <p class="text-sm text-gray-500 mb-4">Select the sections to include in your plain text report:</p>

        <form action="{{ route('analytics.export.admin') }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_users" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">User & Demographics</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_content" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Content & Engagement</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_health" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">System Health & Storage</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="toggleExportModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 rounded-xl font-medium transition-colors border-0">
                    Cancel
                </button>
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-2.5 rounded-xl font-medium transition-colors flex items-center justify-center gap-2 border-0">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="submit" name="action" value="pdf" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-medium transition-colors flex items-center justify-center gap-2 border-0">
                    <i class="fas fa-file-pdf"></i> Download
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ==========================================
    // FAB Logic & Smooth Scrolling
    // ==========================================
    function toggleFabMenu() {
        const menu = document.getElementById('fabMenu');
        const icon = document.getElementById('fabIcon');
        
        if (menu.classList.contains('opacity-0')) {
            menu.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
            menu.classList.add('opacity-100', 'translate-y-0');
            icon.classList.remove('fa-list-ul');
            icon.classList.add('fa-times');
            icon.style.transform = 'rotate(90deg)';
        } else {
            menu.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
            menu.classList.remove('opacity-100', 'translate-y-0');
            icon.classList.add('fa-list-ul');
            icon.classList.remove('fa-times');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    function scrollToSection(id, isTop = false) {
        if (isTop) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        const el = document.getElementById(id);
        if(el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // ==========================================
    // Modal Toggle Logic
    // ==========================================
    function toggleExportModal() {
        const modal = document.getElementById('exportModal');
        const content = document.getElementById('exportModalContent');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);
        } else {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    }

    // ==========================================
    // INITIALIZE UI CHARTS
    // ==========================================

</script>

<script>
    // 1. Create a global object to hold our chart instances so they persist between partial loads
    window.dashboardCharts = window.dashboardCharts || {};

    function initDashboardCharts() {
        // 2. Destroy existing charts if they exist to prevent "Canvas already in use" errors
        if (window.dashboardCharts.schoolDistribution) window.dashboardCharts.schoolDistribution.destroy();
        if (window.dashboardCharts.topMaterials) window.dashboardCharts.topMaterials.destroy();
        if (window.dashboardCharts.storage) window.dashboardCharts.storage.destroy();

        // 3. School Distribution
        @if(count($schoolLabels ?? []) > 0)
        const ctxSchool = document.getElementById('schoolDistributionChart');
        if (ctxSchool) {
            window.dashboardCharts.schoolDistribution = new Chart(ctxSchool.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json($schoolLabels),
                    datasets: [{
                        data: @json($schoolData),
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'right' } } }
            });
        }
        @endif

        // 4. Top Materials
        @if(count($topMaterialsLabels ?? []) > 0)
        const ctxMaterials = document.getElementById('topMaterialsChart');
        if (ctxMaterials) {
            window.dashboardCharts.topMaterials = new Chart(ctxMaterials.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($topMaterialsLabels),
                    datasets: [{
                        label: 'Total Views',
                        data: @json($topMaterialsData),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } } }
            });
        }
        @endif

        // 5. Storage Chart
        const ctxStorage = document.getElementById('storageChart');
        if (ctxStorage) {
            window.dashboardCharts.storage = new Chart(ctxStorage.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Used Storage', 'Free Storage'],
                    datasets: [{
                        data: [@json($usedGb), @json($totalGb - $usedGb)],
                        backgroundColor: [@json($storagePercentage > 85 ? '#ef4444' : '#10b981'), '#e5e7eb'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
            });
        }
    }

    // Initialize charts immediately when this script loads via loadPartial
    initDashboardCharts();
</script>