<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Instructor Dashboard</h1>
        <p class="text-gray-500 text-sm">Monitor the performance and engagement of your learning modules.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="loadPartial('{{ route('dashboard.home') }}', document.getElementById('nav-home-btn'))"
            class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition shadow-sm"
            title="Refresh Data">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div
        class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg shadow-orange-900/20 relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-orange-100 text-xs font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5"><i
                    class="fas fa-star"></i> Most Popular Module</p>
            @if($topModule)
                <h3 class="text-2xl font-black mb-1 line-clamp-1" title="{{ $topModule->title }}">{{ $topModule->title }}
                </h3>
                <p class="text-sm text-orange-200">{{ number_format($topModule->views) }} Total Views</p>
            @else
                <h3 class="text-xl font-black mb-1 text-white/70">No Modules Yet</h3>
            @endif
        </div>
        <i class="fas fa-fire absolute -bottom-4 -right-4 text-7xl text-white/20 transform -rotate-12"></i>
    </div>

    <div
        class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg shadow-blue-900/20 flex items-center justify-between">
        <div>
            <p class="text-blue-200 text-xs font-bold uppercase tracking-wider mb-1">Average Exam Score</p>
            <h3 class="text-3xl font-black">{{ $examPassingRate }}<span class="text-xl text-blue-300">%</span></h3>
        </div>
        <i class="fas fa-spell-check text-5xl text-white/20"></i>
    </div>

    <div
        class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl p-6 text-white shadow-lg shadow-green-900/20 flex items-center justify-between">
        <div>
            <p class="text-emerald-200 text-xs font-bold uppercase tracking-wider mb-1">Overall Completion Rate</p>
            <h3 class="text-3xl font-black">{{ $moduleCompletionRate }}<span class="text-xl text-emerald-300">%</span>
            </h3>
        </div>
        <i class="fas fa-flag-checkered text-5xl text-white/20"></i>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Active Modules</p>
            <h3 class="text-2xl font-black text-gray-900">{{ number_format($myMaterialsCount) }}</h3>
        </div>
        <i class="fas fa-book-open text-gray-300 text-2xl"></i>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Enrolled Students</p>
            <h3 class="text-2xl font-black text-gray-900">{{ number_format($totalLearners) }}</h3>
        </div>
        <i class="fas fa-users text-gray-300 text-2xl"></i>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Views</p>
            <h3 class="text-2xl font-black text-gray-900">{{ number_format($totalViews) }}</h3>
        </div>
        <i class="fas fa-eye text-gray-300 text-2xl"></i>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Certificates Awarded</p>
            <h3 class="text-2xl font-black text-gray-900">{{ number_format($completedMyModules) }}</h3>
        </div>
        <i class="fas fa-medal text-gray-300 text-2xl"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">

    <div class="lg:col-span-2 space-y-8">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 mb-4">Recent Enrollments (Last 7 Days)</h3>
            <div class="relative h-64">
                <canvas id="teacherActivityChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 mb-4">Most Viewed Modules</h3>
            <div class="relative h-64 flex items-center justify-center">
                @if(array_sum($topMaterialsData) > 0)
                    <canvas id="teacherMaterialsChart"></canvas>
                @else
                    <p class="text-gray-400 text-sm">Not enough data to display chart.</p>
                @endif
            </div>
        </div>

    </div>

    <div class="space-y-8">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Recent Student Activity</h3>
                <i class="fas fa-bolt text-amber-500"></i>
            </div>
            <div class="p-0 flex-1">
                <div class="divide-y divide-gray-50">
                    @forelse($activeStudentsList as $enrollment)
                        <div class="p-4 hover:bg-gray-50 transition flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold border border-blue-100 shrink-0">
                                {{ substr($enrollment->user->first_name, 0, 1) }}{{ substr($enrollment->user->last_name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $enrollment->user->first_name }}
                                    {{ $enrollment->user->last_name }}</p>
                                <p class="text-[10px] text-gray-500 truncate" title="{{ $enrollment->material->title }}">
                                    Studying: <span
                                        class="font-medium text-gray-700">{{ $enrollment->material->title }}</span>
                                </p>
                            </div>
                            <span
                                class="text-[10px] text-gray-400 whitespace-nowrap">{{ $enrollment->updated_at->diffForHumans(null, true, true) }}</span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-400 text-sm">No recent student activity found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl border-2 {{ $pendingInvitesCount > 0 ? 'border-dashed border-amber-200' : 'border-solid border-gray-100' }} shadow-sm overflow-hidden relative">
            @if($pendingInvitesCount > 0)
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full flex items-start justify-end p-3 pointer-events-none">
                    <i class="fas fa-envelope text-amber-500 animate-pulse"></i>
                </div>
            @endif

            <div class="p-5 border-b border-gray-50">
                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                    Pending Invitations <span
                        class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">{{ $pendingInvitesCount }}</span>
                </h3>
                <p class="text-xs text-gray-500 mt-1">Students invited to private modules who have not yet enrolled.</p>
            </div>

            <div class="divide-y divide-gray-50 max-h-[300px] overflow-y-auto">
                @forelse($pendingInvitesList as $invite)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-bold text-gray-800 truncate pr-2">{{ $invite->email }}</p>
                            <span
                                class="text-[10px] text-amber-500 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-100">Waiting</span>
                        </div>
                        <p class="text-[10px] text-gray-500 flex items-center gap-1.5 truncate">
                            <i class="fas fa-book-open text-gray-400"></i> Module:
                            <span
                                class="font-medium text-gray-700">{{ $invite->material->title ?? 'Unknown Module' }}</span>
                        </p>
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <div
                            class="w-10 h-10 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-check"></i>
                        </div>
                        <p class="text-xs font-bold text-gray-500">All caught up!</p>
                        <p class="text-[10px] text-gray-400">No pending invitations.</p>
                    </div>
                @endforelse
            </div>

            @if($pendingInvitesCount > 5)
                <div class="p-3 bg-gray-50 text-center border-t border-gray-100">
                    <span class="text-xs text-gray-500 font-medium">+{{ $pendingInvitesCount - 5 }} more</span>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    // 1. Create a global object to hold our chart instances so they persist between partial loads
    window.dashboardCharts = window.dashboardCharts || {};

    function initDashboardCharts() {
        // 2. Destroy existing charts if they exist to prevent "Canvas already in use" errors
        if (window.dashboardCharts.teacherActivity) window.dashboardCharts.teacherActivity.destroy();
        if (window.dashboardCharts.teacherMaterials) window.dashboardCharts.teacherMaterials.destroy();

        // 3. Line Chart: Teacher Activity
        const ctxActivity = document.getElementById('teacherActivityChart');
        if (ctxActivity) {
            window.dashboardCharts.teacherActivity = new Chart(ctxActivity.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($activityDates ?? []) !!},
                    datasets: [{
                        label: 'New Enrollments',
                        data: {!! json_encode($activityTrend ?? []) !!},
                        borderColor: '#a52a2a',
                        backgroundColor: 'rgba(165, 42, 42, 0.05)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#a52a2a',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [4, 4] }, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 4. Doughnut Chart: Teacher Materials
        const ctxMaterials = document.getElementById('teacherMaterialsChart');
        if (ctxMaterials) {
            window.dashboardCharts.teacherMaterials = new Chart(ctxMaterials.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($topMaterialsLabels ?? []) !!},
                    datasets: [{
                        data: {!! json_encode($topMaterialsData ?? []) !!},
                        backgroundColor: ['#a52a2a', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
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
    }

    // Initialize charts immediately when this script loads via loadPartial
    initDashboardCharts();
</script>