<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="relative min-h-screen pb-12 bg-gray-50">

    {{-- Header --}}
    <div class="p-6 pb-2 flex justify-between items-end max-w-7xl mx-auto">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">My Progress</h2>
            <p class="text-gray-500 mt-1">Track your learning journey, module completion, and Exam performance.</p>
        </div>
        {{-- GENERATE REPORT BUTTON --}}
        <button onclick="toggleExportModal()"
            class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    {{-- FLOATING ACTION BUTTON (QUICK NAVIGATION) --}}
    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end">
        <div id="fabMenu"
            class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div
                class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-52">
                <p
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">
                    Quick Navigation</p>

                <button onclick="scrollToSection('my-achievements'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-trophy w-4 text-center"></i> Achievements
                </button>
                <button onclick="scrollToSection('learning-progress'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-book-reader w-4 text-center"></i> Learning Progress
                </button>
                <button onclick="scrollToSection('Exam-performance'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-clipboard-check w-4 text-center"></i> Exam Stats
                </button>

                <button onclick="scrollToSection('dashboard-content', true); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2 mt-1 text-xs font-semibold text-gray-400 hover:text-gray-800 bg-gray-50 rounded-xl transition-all text-left justify-center border border-gray-200">
                    <i class="fas fa-arrow-up"></i> Back to Top
                </button>
            </div>
        </div>

        <button onclick="toggleFabMenu()"
            class="w-14 h-14 bg-[#111827] text-white rounded-full shadow-lg shadow-gray-900/30 flex items-center justify-center hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300">
            <i id="fabIcon" class="fas fa-list-ul text-xl transition-transform duration-300"></i>
        </button>
    </div>

    <div class="p-6 space-y-12 max-w-7xl mx-auto">

        {{-- Section 1: Achievements --}}
        <section id="my-achievements" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center"><i
                        class="fas fa-trophy text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">My Achievements</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-orange-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Learning Streak</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $streak }} <span class="text-lg text-orange-500">Days
                            🔥</span></p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-[#a52a2a]">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Enrolled Modules</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalEnrollments) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Completed Modules</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($completedCount) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Average Exam Score</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $averageScore }}%</p>
                </div>
            </div>
        </section>

        {{-- Section 2: Learning Progress --}}
        <section id="learning-progress" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i
                        class="fas fa-book-reader text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Learning Progress</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Module Completion Status</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        @if($completedCount == 0 && $inProgressCount == 0)
                            <p class="text-gray-400 text-sm">You haven't started any modules yet.</p>
                        @else
                            <canvas id="studentStatusChart"></canvas>
                        @endif
                    </div>
                </div>

                <div
                    class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center text-center">
                    <div
                        class="w-20 h-20 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 border-4 border-blue-100">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4 class="text-gray-500 font-medium mb-1">Overall Curriculum Progress</h4>
                    <p class="text-5xl font-bold text-gray-900">{{ $completionRate }}%</p>

                    <div class="w-full bg-gray-100 rounded-full h-3 mt-6">
                        <div class="h-3 rounded-full bg-blue-500" style="width: {{ $completionRate }}%"></div>
                    </div>
                    <p class="text-sm text-gray-400 mt-4 px-4">Keep going! Finish your "In Progress" modules to increase
                        your score.</p>
                </div>
            </div>
        </section>

        {{-- Section 3: Performance --}}
        <section id="Exam-performance" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i
                        class="fas fa-clipboard-check text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Module Exam Performance</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">All-Time Exam Accuracy</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        @if($totalAnswers == 0)
                            <p class="text-gray-400 text-sm mt-10">No Exam taken yet.</p>
                        @else
                            <canvas id="studentAccuracyChart"></canvas>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Recent Exam Scores (Last 7 Days)</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        <canvas id="studentScoresChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Topic Mastery</h4>
                <p class="text-sm text-gray-500 mb-4">See which modules you are strongest in based on your Exam scores.
                </p>
                <div class="relative h-72 w-full flex justify-center items-center">
                    @if(count($masteryLabels) == 0)
                        <p class="text-gray-400 text-sm">Complete Exam to see your topic mastery.</p>
                    @else
                        <canvas id="topicMasteryChart"></canvas>
                    @endif
                </div>
            </div>
        </section>

    </div>
</div>

{{-- EXPORT MODAL --}}
<div id="exportModal"
    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300"
        id="exportModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Export Learning Report</h3>
            <button onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i
                    class="fas fa-times text-lg"></i></button>
        </div>

        <p class="text-sm text-gray-500 mb-4">Select the sections to include in your personal learning report:</p>

        <form action="{{ route('analytics.export.student') }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_achievements" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Achievements & Streak</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_progress" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Learning Progress</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_performance" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Detailed Exam Stats</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="toggleExportModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 rounded-xl font-medium transition-colors border-0">
                    Cancel
                </button>
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleExportModal, 500)"
                    class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-2.5 rounded-xl font-medium transition-colors flex items-center justify-center gap-2 border-0">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="submit" name="action" value="pdf" onclick="setTimeout(toggleExportModal, 500)"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-medium transition-colors flex items-center justify-center gap-2 border-0">
                    <i class="fas fa-file-pdf"></i> Download
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal Toggle Logic
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
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

</script>

<script>
    // 1. Create a global object to hold our chart instances so they persist between partial loads
    window.dashboardCharts = window.dashboardCharts || {};

    function initAnalyticsCharts() {
        // 2. Destroy existing charts to prevent duplicate "canvas already in use" errors
        if (window.dashboardCharts.studentStatus) window.dashboardCharts.studentStatus.destroy();
        if (window.dashboardCharts.studentAccuracy) window.dashboardCharts.studentAccuracy.destroy();
        if (window.dashboardCharts.studentScores) window.dashboardCharts.studentScores.destroy();
        if (window.dashboardCharts.topicMastery) window.dashboardCharts.topicMastery.destroy();

        // 3. Status Doughnut Chart
        @if($completedCount > 0 || $inProgressCount > 0)
            const ctxStatus = document.getElementById('studentStatusChart');
            if (ctxStatus) {
                window.dashboardCharts.studentStatus = new Chart(ctxStatus.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress'],
                        datasets: [{
                            data: [@json($completedCount), @json($inProgressCount)],
                            backgroundColor: ['#10b981', '#fbbf24'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
                });
            }
        @endif

            // 4. Accuracy Pie Chart
            @if($totalAnswers > 0)
                const ctxAccuracy = document.getElementById('studentAccuracyChart');
                if (ctxAccuracy) {
                    window.dashboardCharts.studentAccuracy = new Chart(ctxAccuracy.getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: ['Correct Answers', 'Incorrect Answers'],
                            datasets: [{
                                data: [@json($correctAnswers), @json($incorrectAnswers)],
                                backgroundColor: ['#3b82f6', '#ef4444'],
                                borderWidth: 0
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                    });
                }
            @endif

        // 5. Scores Trend Line Chart
        const ctxScores = document.getElementById('studentScoresChart');
        if (ctxScores) {
            window.dashboardCharts.studentScores = new Chart(ctxScores.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($examDates ?? []),
                    datasets: [{
                        label: 'Correct Answers',
                        data: @json($examScores ?? []),
                        borderColor: '#a52a2a',
                        backgroundColor: 'rgba(165, 42, 42, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] }, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 6. Topic Mastery Bar Chart
        @if(count($masteryLabels ?? []) > 0)
            const ctxMastery = document.getElementById('topicMasteryChart');
            if (ctxMastery) {
                window.dashboardCharts.topicMastery = new Chart(ctxMastery.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: @json($masteryLabels),
                        datasets: [{
                            label: 'Average Score (%)',
                            data: @json($masteryScores),
                            backgroundColor: '#8b5cf6',
                            borderRadius: 6,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, max: 100, grid: { borderDash: [2, 4] } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        @endif
    }

    // Initialize charts immediately when the analytics partial is loaded
    initAnalyticsCharts();
</script>