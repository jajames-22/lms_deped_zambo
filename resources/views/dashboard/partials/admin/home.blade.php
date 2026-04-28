<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Division Administrator Portal</h1>
        <p class="text-gray-500 text-sm">Live overview of system usage, student progress, and server status.</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-xs font-bold">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            System Online
        </span>
        <button onclick="loadPartial('{{ route('dashboard.home') }}', document.getElementById('nav-home-btn'))"
            class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition shadow-sm" title="Refresh Dashboard">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg shadow-blue-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform">
        <div>
            <p class="text-blue-200 text-xs font-bold uppercase tracking-wider mb-1">Total Enrolled Students</p>
            <h3 class="text-3xl font-black">{{ number_format($totalStudents) }}</h3>
            <p class="text-blue-100 text-sm mt-1 font-medium">{{ $totalSchools }} Schools</p>
        </div>
        <i class="fas fa-users text-5xl text-white/20"></i>
    </div>

    <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl p-6 text-white shadow-lg shadow-green-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform">
        <div>
            <p class="text-emerald-200 text-xs font-bold uppercase tracking-wider mb-1">Total Registered Teachers</p>
            <h3 class="text-3xl font-black">{{ number_format($totalTeachers) }}</h3>
        </div>
        <i class="fas fa-chalkboard-teacher text-5xl text-white/20"></i>
    </div>

    <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg shadow-orange-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform">
        <div>
            <p class="text-orange-200 text-xs font-bold uppercase tracking-wider mb-1">Total Active Materials</p>
            <h3 class="text-3xl font-black">{{ number_format($totalMaterials) }}</h3>
        </div>
        <i class="fas fa-book-open text-5xl text-white/20"></i>
    </div>

    <div class="bg-gradient-to-r from-rose-500 to-red-600 rounded-2xl p-6 text-white shadow-lg shadow-red-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform">
        <div>
            <p class="text-rose-200 text-xs font-bold uppercase tracking-wider mb-1">Total Active Assessments</p>
            <h3 class="text-3xl font-black">{{ number_format($totalAssessments) }}</h3>
        </div>
        <i class="fas fa-file-signature text-5xl text-white/20"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900">Active Users (Last 7 Days)</h3>
            <div class="text-right">
                <span class="text-xs text-gray-500 font-medium block">Weekly Active Users</span>
                <span class="text-lg font-black text-blue-600">{{ number_format($weeklyActiveUsers) }}</span>
            </div>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col">
        <h3 class="font-bold text-gray-900 mb-2">Most Popular Modules</h3>
        <p class="text-xs text-gray-500 mb-4">Top 5 modules based on student views.</p>
        <div class="relative flex-1 flex items-center justify-center min-h-[200px]">
            <canvas id="materialsChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
    
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900">Top 5 Schools by Enrollment</h3>
            <i class="fas fa-school text-gray-300 text-xl"></i>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="schoolsChart"></canvas>
        </div>
    </div>

    <div class="space-y-6">
        @php 
            $totalAlerts = $pendingTeachersCount;
        @endphp

        @if($totalAlerts > 0)
        <div class="bg-white rounded-2xl border-2 border-dashed border-red-200 p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-red-50 rounded-bl-full flex items-start justify-end p-3">
                <i class="fas fa-bell text-red-500 animate-bounce"></i>
            </div>
            
            <h4 class="text-sm font-bold text-gray-900 mb-4">Action Required</h4>
            
            <div class="space-y-3 mb-6">
                @if($pendingTeachersCount > 0)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600"><i class="fas fa-user-clock text-amber-500 w-5"></i> Teachers Awaiting Approval</span>
                    <span class="font-bold text-gray-900">{{ $pendingTeachersCount }}</span>
                </div>
                @endif
                
            </div>
            
            <button onclick="loadPartial('{{ route('dashboard.teachers') }}', document.getElementById('nav-teachers-btn'))" class="w-full py-2.5 text-xs font-bold text-[#a52a2a] bg-red-50 rounded-xl hover:bg-red-100 transition uppercase tracking-wider shadow-sm">
                Review User Accounts
            </button>
        </div>
        @else
        <div class="bg-green-50/50 rounded-2xl border border-green-100 p-6 shadow-sm text-center">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3 text-lg">
                <i class="fas fa-shield-check"></i>
            </div>
            <h4 class="text-sm font-bold text-green-900 mb-1">System Organized</h4>
            <p class="text-xs text-green-600">No pending approvals or unassigned users.</p>
        </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                <i class="fas fa-server text-gray-400"></i> Server Storage Limit
            </h3>
            <div class="mb-2">
                <div class="flex justify-between text-xs font-bold text-gray-600 mb-2">
                    <span>System File Storage</span>
                    <span>{{ $storagePercentage }}% Used</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                    <div class="h-3 rounded-full transition-all duration-1000 {{ $storagePercentage > 85 ? 'bg-red-500' : ($storagePercentage > 60 ? 'bg-amber-400' : 'bg-green-500') }}" style="width: {{ $storagePercentage }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 text-right">{{ $usedGb }} GB / {{ $totalGb }} GB</p>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Create a global object to hold our chart instances so they persist between partial loads
    window.dashboardCharts = window.dashboardCharts || {};

    function initDashboardCharts() {
        // 2. Destroy existing charts if they exist to prevent "Canvas already in use" errors
        if (window.dashboardCharts.activity) window.dashboardCharts.activity.destroy();
        if (window.dashboardCharts.materials) window.dashboardCharts.materials.destroy();
        if (window.dashboardCharts.schools) window.dashboardCharts.schools.destroy();

        // 3. Activity Line Chart
        const ctxActivity = document.getElementById('activityChart');
        if (ctxActivity) {
            window.dashboardCharts.activity = new Chart(ctxActivity.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($activityDates ?? []) !!},
                    datasets: [{
                        label: 'Active Users',
                        data: {!! json_encode($activityTrend ?? []) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 4. Top Materials Doughnut Chart
        const ctxMaterials = document.getElementById('materialsChart');
        if (ctxMaterials) {
            window.dashboardCharts.materials = new Chart(ctxMaterials.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($topMaterialsLabels ?? []) !!},
                    datasets: [{
                        data: {!! json_encode($topMaterialsData ?? []) !!},
                        backgroundColor: ['#a52a2a', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                    }
                }
            });
        }

        // 5. Top Schools Horizontal Bar Chart
        const ctxSchools = document.getElementById('schoolsChart');
        if (ctxSchools) {
            window.dashboardCharts.schools = new Chart(ctxSchools.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topSchoolLabels ?? []) !!},
                    datasets: [{
                        label: 'Enrolled Students',
                        data: {!! json_encode($topSchoolData ?? []) !!},
                        backgroundColor: '#a52a2a',
                        borderRadius: 4,
                        barThickness: 16
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                        y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }
    }

    // Initialize charts immediately when this script loads via loadPartial
    initDashboardCharts();
</script>