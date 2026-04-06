<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="relative min-h-screen pb-12 bg-gray-50">
    
    {{-- Header --}}
    <div class="p-6 pb-2 flex justify-between items-end max-w-7xl mx-auto">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Material Analytics</h2>
            <p class="text-gray-500 mt-1">Monitor how students are engaging with your modules and assessments.</p>
        </div>
        <button onclick="toggleExportModal()" class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    {{-- FAB Navigation --}}
    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end">
        <div id="fabMenu" class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-48">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">Quick Navigation</p>
                
                <button onclick="scrollToSection('class-overview'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-users w-4 text-center"></i> Material Overview
                </button>
                <button onclick="scrollToSection('material-engagement'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-book-open w-4 text-center"></i> Engagement
                </button>
                <button onclick="scrollToSection('assessment-performance'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-chart-pie w-4 text-center"></i> Performance
                </button>
                <button onclick="scrollToSection('activity-trend'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-chart-line w-4 text-center"></i> Activity Trends
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

    {{-- Dashboard Content Sections --}}
    <div class="p-6 space-y-12 max-w-7xl mx-auto">

        <section id="class-overview" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-users text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Material Overview</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Learners</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalLearners) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Active (Last 7 Days)</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($activeLearners) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-amber-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Pending Enrollment Invites</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($pendingRequests) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-purple-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Average Exam Material Score</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $averageScore }}%</p>
                </div>
            </div>
        </section>

        <section id="material-engagement" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-book-open text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Material Engagement</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Most Viewed Modules</h4>
                    <div class="relative h-64 w-full">
                        <canvas id="teacherViewsChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Overall Student Completion</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        @if($completedCount == 0 && $inProgressCount == 0)
                            <p class="text-gray-400 text-sm">No enrollment data available yet.</p>
                        @else
                            <canvas id="teacherProgressChart"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section id="assessment-performance" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center"><i class="fas fa-check-circle text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Assessment Performance</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center">
                    <div class="text-center w-full">
                        <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2 text-left">Correct vs Incorrect Answers</h4>
                        <div class="relative h-56 w-full flex justify-center mt-4">
                            @if($correctAnswers == 0 && $incorrectAnswers == 0)
                                <p class="text-gray-400 text-sm mt-10">No exam attempts recorded yet.</p>
                            @else
                                <canvas id="teacherScoresChart"></canvas>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center text-center">
                    <div class="w-20 h-20 bg-yellow-50 text-yellow-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 border-4 border-yellow-100">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h4 class="text-gray-500 font-medium mb-1">Global Material Average</h4>
                    <p class="text-5xl font-bold text-gray-900">{{ $averageScore }}%</p>
                    <p class="text-sm text-gray-400 mt-4 px-4">Based on all student responses to Exam embedded in your modules.</p>
                </div>
            </div>
        </section>

        <section id="activity-trend" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="fas fa-chart-line text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Activity Trends</h3>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">New Enrollments (Last 7 Days)</h4>
                <div class="relative h-72 w-full">
                    <canvas id="teacherTrendChart"></canvas>
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
            <h3 class="text-xl font-bold text-gray-900">Export Material Report</h3>
            <button onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i class="fas fa-times text-lg"></i></button>
        </div>

        <p class="text-sm text-gray-500 mb-4">Select the sections to include in your plain text report:</p>

        <form action="{{ route('analytics.export.teacher') }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_overview" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Material Overview</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_engagement" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Material Engagement</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_performance" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Assessment Performance</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_trends" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Activity Trends</span>
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
    // Modal Logic
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

    // FAB Logic
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

    // Scroll Logic
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

    // Destroy existing instances to prevent hover bugs
    const chartsToClear = ['teacherViewsChart', 'teacherProgressChart', 'teacherScoresChart', 'teacherTrendChart'];
    chartsToClear.forEach(id => {
        if (window[id + 'Instance']) window[id + 'Instance'].destroy();
    });

    // 1. Views Bar Chart
    const viewsCtx = document.getElementById('teacherViewsChart').getContext('2d');
    window.teacherViewsChartInstance = new Chart(viewsCtx, {
        type: 'bar',
        data: {
            labels: @json($materialLabels),
            datasets: [{
                label: 'Total Views',
                data: @json($materialViews),
                backgroundColor: '#8b5cf6', // Purple
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y', 
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, grid: { borderDash: [2, 4] } }, y: { grid: { display: false } } }
        }
    });

    // 2. Progress Doughnut Chart
    @if($completedCount > 0 || $inProgressCount > 0)
        const progressCtx = document.getElementById('teacherProgressChart').getContext('2d');
        window.teacherProgressChartInstance = new Chart(progressCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                    data: [@json($completedCount), @json($inProgressCount)],
                    backgroundColor: ['#10b981', '#f59e0b'], 
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });
    @endif

    // 3. Scores Doughnut Chart
    @if($correctAnswers > 0 || $incorrectAnswers > 0)
        const scoresCtx = document.getElementById('teacherScoresChart').getContext('2d');
        window.teacherScoresChartInstance = new Chart(scoresCtx, {
            type: 'doughnut',
            data: {
                labels: ['Correct', 'Incorrect'],
                datasets: [{
                    data: [@json($correctAnswers), @json($incorrectAnswers)],
                    backgroundColor: ['#3b82f6', '#ef4444'], // Blue and Red
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right' } } }
        });
    @endif

    // 4. Trend Line Chart
    const trendCtx = document.getElementById('teacherTrendChart').getContext('2d');
    window.teacherTrendChartInstance = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: @json($activityDates),
            datasets: [{
                label: 'New Enrollments',
                data: @json($activityTrend),
                borderColor: '#10b981', // Green
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4 
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } }
        }
    });
</script>