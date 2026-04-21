<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative animate-float-in">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Curriculum Implementation Division (CID)</h1>
        <p class="text-gray-500 text-sm">Overview of learning materials, evaluation pipeline, and curriculum delivery.</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="flex items-center gap-1.5 px-3 py-1.5 bg-purple-100 text-purple-700 rounded-full text-xs font-bold border border-purple-200">
            <i class="fas fa-clipboard-check"></i>
            Evaluation Mode Active
        </span>
        <button onclick="loadPartial('{{ url('/dashboard/home') }}', document.getElementById('nav-home-btn'))"
            class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition shadow-sm" title="Refresh Dashboard">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</div>

{{-- TOP METRIC CARDS --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 relative">
    
    <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg shadow-amber-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform cursor-default">
        <div>
            <p class="text-amber-100 text-sm font-bold uppercase tracking-wider mb-1">Pending Evaluation</p>
            <h3 class="text-3xl font-black">{{ number_format($pendingMaterials ?? 0) }}</h3>
        </div>
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl backdrop-blur-sm">
            <i class="fas fa-file-signature"></i>
        </div>
    </div>

    <div class="bg-gradient-to-r from-green-600 to-emerald-500 rounded-2xl p-6 text-white shadow-lg shadow-green-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform cursor-default">
        <div>
            <p class="text-green-100 text-sm font-bold uppercase tracking-wider mb-1">Published Materials</p>
            <h3 class="text-3xl font-black">{{ number_format($publishedMaterials ?? 0) }}</h3>
        </div>
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl backdrop-blur-sm">
            <i class="fas fa-book-open"></i>
        </div>
    </div>

    <div class="bg-gradient-to-r from-[#a52a2a] to-red-800 rounded-2xl p-6 text-white shadow-lg shadow-red-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform cursor-default">
        <div>
            <p class="text-red-100 text-sm font-bold uppercase tracking-wider mb-1">Active Educators</p>
            <h3 class="text-3xl font-black">{{ number_format($activeTeachers ?? 0) }}</h3>
        </div>
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl backdrop-blur-sm">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
    </div>

    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-blue-900/20 flex items-center justify-between hover:scale-[1.02] transition-transform cursor-default">
        <div>
            <p class="text-blue-100 text-sm font-bold uppercase tracking-wider mb-1">Avg. Assessment Score</p>
            <h3 class="text-3xl font-black">{{ number_format($averageScore ?? 0, 1) }}%</h3>
        </div>
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl backdrop-blur-sm">
            <i class="fas fa-chart-line"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 relative ">
    {{-- MAIN CHART --}}
    <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-gray-900">Curriculum Mastery Trend</h3>
                <p class="text-xs text-gray-500">Average student assessment scores over the last 6 months</p>
            </div>
            <button class="text-gray-400 hover:text-[#a52a2a] transition"><i class="fas fa-ellipsis-v"></i></button>
        </div>
        <div class="h-72 w-full relative">
            <canvas id="masteryChart"></canvas>
        </div>
    </div>

    {{-- DOUGHNUT CHART --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col">
        <h3 class="font-bold text-gray-900 mb-1">Approved Material Types</h3>
        <p class="text-xs text-gray-500 mb-6">Distribution of published curriculum resources</p>
        <div class="flex-1 relative w-full flex justify-center items-center min-h-[200px]">
            <canvas id="materialTypeChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative">
    {{-- RECENT EVALUATIONS FEED --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-900">Recent Evaluations</h3>
            <button onclick="loadPartial('{{ url('/dashboard/materials') }}', document.getElementById('nav-materials-btn'))" class="text-xs font-bold text-[#a52a2a] hover:underline">View All</button>
        </div>
        <div class="p-0 overflow-y-auto max-h-96">
            <ul class="divide-y divide-gray-50">
                @forelse($recentEvaluations ?? [] as $eval)
                    <li class="p-4 hover:bg-gray-50 transition flex gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-sm
                            @if(isset($eval->status) && $eval->status == 'published') bg-green-50 text-green-600 
                            @elseif(isset($eval->status) && $eval->status == 'draft') bg-red-50 text-red-600
                            @else bg-amber-50 text-amber-600 @endif">
                            <i class="fas @if(isset($eval->status) && $eval->status == 'published') fa-check @elseif(isset($eval->status) && $eval->status == 'draft') fa-undo @else fa-clock @endif"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-800 font-medium">
                                <span class="font-bold text-gray-900">{{ $eval->title ?? 'Material' }}</span> 
                                was marked as <span class="font-bold uppercase text-[10px]">{{ $eval->status ?? 'pending' }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                <i class="far fa-clock"></i> {{ isset($eval->updated_at) ? \Carbon\Carbon::parse($eval->updated_at)->diffForHumans() : 'Recently' }}
                                &bull; <i class="fas fa-chalkboard-user"></i> {{ $eval->instructor->last_name ?? 'Instructor' }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-gray-500 text-sm">
                        <i class="fas fa-clipboard text-3xl text-gray-300 mb-3 block"></i>
                        No recent evaluations found.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- TOP PERFORMING SCHOOLS --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-gray-900">Top Performing Schools</h3>
                <p class="text-xs text-gray-500">Based on highest average assessment scores</p>
            </div>
        </div>
        <div class="h-80 w-full relative">
            <canvas id="schoolsChart"></canvas>
        </div>
    </div>
</div>

<script>
    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded.');
            return;
        }

        if (window.dashboardCharts) {
            Object.values(window.dashboardCharts).forEach(chart => chart.destroy());
        }
        window.dashboardCharts = {};

        // 1. Mastery Trend Chart
        const ctxMastery = document.getElementById('masteryChart');
        if (ctxMastery) {
            window.dashboardCharts.mastery = new Chart(ctxMastery.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($masteryLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                    datasets: [
                        {
                            label: 'Average Score (%)',
                            data: {!! json_encode($masteryData ?? [75, 78, 80, 79, 82, 85]) !!},
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', align: 'end', labels: { boxWidth: 12, usePointStyle: true } }
                    },
                    scales: {
                        y: { beginAtZero: false, min: 50, max: 100, grid: { borderDash: [4, 4] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. Material Types Chart
        const ctxType = document.getElementById('materialTypeChart');
        if (ctxType) {
            window.dashboardCharts.type = new Chart(ctxType.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($materialTypeLabels ?? ['Modules', 'Videos', 'Assessments']) !!},
                    datasets: [{
                        data: {!! json_encode($materialTypeData ?? [40, 30, 30]) !!},
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

        // 3. Top Schools Chart
        const ctxSchools = document.getElementById('schoolsChart');
        if (ctxSchools) {
            window.dashboardCharts.schools = new Chart(ctxSchools.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topSchoolLabels ?? ['ZCHS Main', 'Ayala NHS', 'Don Pablo NHS']) !!},
                    datasets: [{
                        label: 'Avg Score (%)',
                        data: {!! json_encode($topSchoolData ?? [88, 85, 82]) !!},
                        backgroundColor: '#10b981',
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
                        x: { beginAtZero: false, min: 50, max: 100, grid: { borderDash: [4, 4] } },
                        y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }
    }

    initDashboardCharts();
</script>