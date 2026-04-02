<div class="relative min-h-screen pb-12">
    
    <div class="p-6 pb-2">
        <h2 class="text-3xl font-bold text-gray-900">My Progress</h2>
        <p class="text-gray-500 mt-1">Track your learning journey, module completion, and exam performance.</p>
    </div>

    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end">
        <div id="fabMenu" class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-52">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">Quick Navigation</p>
                
                <button onclick="scrollToSection('my-achievements'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-trophy w-4 text-center"></i> Achievements
                </button>
                <button onclick="scrollToSection('learning-progress'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-book-reader w-4 text-center"></i> Learning Progress
                </button>
                <button onclick="scrollToSection('assessment-performance'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-clipboard-check w-4 text-center"></i> Assessment Stats
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

        <section id="my-achievements" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center"><i class="fas fa-trophy text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">My Achievements</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-[#a52a2a]">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Enrolled Modules</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalEnrollments) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Completed Modules</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($completedCount) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-yellow-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Certificates Earned</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($completedCount) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">All-Time Average Score</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $averageScore }}%</p>
                </div>
            </div>
        </section>

        <section id="learning-progress" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-book-reader text-lg"></i></div>
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

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center text-center">
                    <div class="w-20 h-20 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 border-4 border-blue-100">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4 class="text-gray-500 font-medium mb-1">Overall Curriculum Progress</h4>
                    <p class="text-5xl font-bold text-gray-900">{{ $completionRate }}%</p>
                    
                    <div class="w-full bg-gray-100 rounded-full h-3 mt-6">
                        <div class="h-3 rounded-full bg-blue-500" style="width: {{ $completionRate }}%"></div>
                    </div>
                    <p class="text-sm text-gray-400 mt-4 px-4">Keep going! Finish your "In Progress" modules to increase your score.</p>
                </div>
            </div>
        </section>

        <section id="assessment-performance" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-clipboard-check text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Assessment Performance</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">All-Time Accuracy</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        @if($totalAnswers == 0)
                            <p class="text-gray-400 text-sm mt-10">No exam attempts recorded yet.</p>
                        @else
                            <canvas id="studentAccuracyChart"></canvas>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Recent Assessment Scores (Last 7 Days)</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        <canvas id="studentScoresChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<script>
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
        const container = document.getElementById('content-area');
        if (isTop) {
            container.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        const el = document.getElementById(id);
        if(el && container) {
            const offsetTop = el.offsetTop - 20; 
            container.scrollTo({ top: offsetTop, behavior: 'smooth' });
        }
    }

    // Destroy existing instances to prevent hover bugs on AJAX load
    const chartsToClear = ['studentStatusChart', 'studentAccuracyChart', 'studentScoresChart'];
    chartsToClear.forEach(id => {
        if (window[id + 'Instance']) window[id + 'Instance'].destroy();
    });

    // 1. Status Doughnut Chart
    @if($completedCount > 0 || $inProgressCount > 0)
        const statusCtx = document.getElementById('studentStatusChart').getContext('2d');
        window.studentStatusChartInstance = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                    data: [@json($completedCount), @json($inProgressCount)],
                    backgroundColor: ['#10b981', '#fbbf24'], // Green and Amber
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });
    @endif

    // 2. Accuracy Doughnut Chart
    @if($totalAnswers > 0)
        const accuracyCtx = document.getElementById('studentAccuracyChart').getContext('2d');
        window.studentAccuracyChartInstance = new Chart(accuracyCtx, {
            type: 'pie', // Using pie for contrast to the doughnut
            data: {
                labels: ['Correct Answers', 'Incorrect Answers'],
                datasets: [{
                    data: [@json($correctAnswers), @json($incorrectAnswers)],
                    backgroundColor: ['#3b82f6', '#ef4444'], // Blue and Red
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    @endif

    // 3. Scores Trend Line Chart
    const scoresCtx = document.getElementById('studentScoresChart').getContext('2d');
    window.studentScoresChartInstance = new Chart(scoresCtx, {
        type: 'line',
        data: {
            labels: @json($examDates),
            datasets: [{
                label: 'Correct Answers',
                data: @json($examScores),
                borderColor: '#a52a2a', // Your brand red
                backgroundColor: 'rgba(165, 42, 42, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4 // Smooth curves
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
</script>