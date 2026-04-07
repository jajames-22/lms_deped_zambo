<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="assessment-analytics-content" class="relative min-h-screen pb-12 bg-gray-50">
    <div class="p-6 pb-2 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 max-w-7xl mx-auto">
        <div>
            <button onclick="loadPartial('{{ route('dashboard.assessments.manage', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group mb-2 text-sm">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Assessment
            </button>
            <h2 class="text-3xl font-bold text-gray-900">{{ $assessment->title }}</h2>
            <p class="text-gray-500 mt-1">Outcome-driven analytics and item analysis.</p>
        </div>
        <button onclick="toggleExportModal()" class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0 whitespace-nowrap">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    <div class="p-6 space-y-8 max-w-7xl mx-auto">

        {{-- NEW KPI Section: Focus on Participation & High/Low --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500 flex flex-col justify-center">
                <p class="text-gray-500 text-sm font-medium mb-1">Total Enrolled</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-lg"><i class="fas fa-users"></i></div>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($totalStudents ?? 0) }}</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500 flex flex-col justify-center">
                <p class="text-gray-500 text-sm font-medium mb-1">Total Submissions</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-lg"><i class="fas fa-file-signature"></i></div>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($completedCount ?? 0) }}</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-green-500 flex flex-col justify-center">
                <p class="text-gray-500 text-sm font-medium mb-1">Highest Score</p>
                <div class="flex items-baseline gap-1">
                    <p class="text-4xl font-bold text-gray-900">{{ $highestScoreRaw ?? 0 }}</p>
                    <p class="text-xl text-gray-400 font-bold">/ {{ $totalQuestions ?? 0 }}</p>
                </div>
                <p class="text-xs text-green-600 mt-1 font-bold">{{ $highestScorePct ?? 0 }}% equivalent</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-red-500 flex flex-col justify-center">
                <p class="text-gray-500 text-sm font-medium mb-1">Lowest Score</p>
                <div class="flex items-baseline gap-1">
                    <p class="text-4xl font-bold text-gray-900">{{ $lowestScoreRaw ?? 0 }}</p>
                    <p class="text-xl text-gray-400 font-bold">/ {{ $totalQuestions ?? 0 }}</p>
                </div>
                <p class="text-xs text-red-600 mt-1 font-bold">{{ $lowestScorePct ?? 0 }}% equivalent</p>
            </div>
        </div>

        {{-- UI Section: Actionable Insights --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Box 1: Pass/Fail & Average --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <h4 class="text-gray-700 font-semibold mb-2">Performance Summary</h4>
                <div class="flex gap-4 mb-4">
                    <div class="flex-1 bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide">Class Avg</p>
                        <p class="text-xl font-black text-[#a52a2a]">{{ $averageScorePct ?? 0 }}%</p>
                    </div>
                    <div class="flex-1 bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide">Avg Time</p>
                        <p class="text-xl font-black text-gray-700">{{ $overallAvgTime }}</p>
                    </div>
                </div>
                <div class="relative flex-1 w-full min-h-[150px] flex justify-center items-center">
                    @if(($completedCount ?? 0) == 0)
                        <p class="text-gray-400 text-sm">No submissions yet.</p>
                    @else
                        <canvas id="passFailChart"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center flex-col pointer-events-none mt-4">
                            <span class="text-2xl font-bold text-gray-800">{{ $completedCount > 0 ? round(($passedCount / $completedCount) * 100) : 0 }}%</span>
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Passed</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Box 2: Not Taken Students (LRNs) --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-gray-700 font-semibold mb-1">Completion Rate</h4>
                        <p class="text-2xl font-bold text-gray-900">{{ $completionRate }}% <span class="text-xs text-gray-400 font-normal">completed</span></p>
                    </div>
                    <div class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold border border-gray-200">
                        {{ $notTakenStudents->count() }} Not Taken
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-2">Students who haven't started</p>
                <div class="flex-1 overflow-y-auto max-h-[180px] bg-gray-50 border border-gray-100 rounded-xl p-2 pr-3 custom-scrollbar">
                    @forelse($notTakenStudents as $student)
                        <div class="flex justify-between items-center py-2 px-2 border-b border-gray-200/60 last:border-0 hover:bg-gray-100 transition-colors rounded-lg">
                            <span class="text-sm font-mono text-gray-700 font-bold">{{ $student->lrn }}</span>
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-gray-200 text-gray-600">
                                OFFLINE
                            </span>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center py-6">
                            <i class="fas fa-check-circle text-green-400 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-500 font-medium">All students have started or finished!</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            {{-- Box 3: Score Distribution --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <h4 class="text-gray-700 font-semibold mb-2">Score Distribution</h4>
                <p class="text-xs text-gray-400 mb-4">Number of students within each bracket</p>
                <div class="relative flex-1 w-full min-h-[200px] flex justify-center items-center">
                    @if(($completedCount ?? 0) == 0)
                        <p class="text-gray-400 text-sm">No submissions yet.</p>
                    @else
                        <canvas id="distributionChart"></canvas>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section / Category Analysis --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-2">Category Mastery</h4>
                <p class="text-xs text-gray-400 mb-4">Average score across different sections.</p>
                <div class="relative h-64 w-full flex justify-center items-center">
                    @if(count($categoryLabels ?? []) == 0)
                        <p class="text-gray-400 text-sm">No section data available.</p>
                    @else
                        <canvas id="categoryPerformanceChart"></canvas>
                    @endif
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-1">Category Analysis</h4>
                    <p class="text-xs text-gray-400">Score mastery and average time spent per section.</p>
                </div>
                <div class="overflow-y-auto flex-1 p-0 max-h-[300px]">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0">
                            <tr>
                                <th class="px-6 py-4">Section Name</th>
                                <th class="px-6 py-4 text-center">Avg Score</th>
                                <th class="px-6 py-4 text-center">Avg Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($categoryData ?? [] as $cat)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $cat['title'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $cat['score_pct'] >= 75 ? 'bg-green-100 text-green-700' : ($cat['score_pct'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $cat['score_pct'] }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono text-gray-500 text-xs">
                                        <i class="far fa-clock mr-1 text-gray-400"></i> {{ $cat['avg_time'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">No data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Most Missed & Perfect Questions --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            @if(isset($mostMissed) && count($mostMissed) > 0)
            <div class="bg-red-50 border border-red-100 p-6 rounded-2xl">
                <div class="flex items-center gap-3 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    <h4 class="text-red-900 font-bold text-lg">Most Missed Questions</h4>
                </div>
                <div class="space-y-3">
                    @foreach($mostMissed as $missed)
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-red-100 flex flex-col">
                            <span class="text-red-500 font-bold text-xs uppercase tracking-wider mb-1">Accuracy: {{ $missed->accuracy }}%</span>
                            <p class="text-gray-700 text-sm font-medium line-clamp-2 mb-2">{!! strip_tags($missed->question_text) !!}</p>
                            <div class="text-xs text-gray-500 flex gap-4">
                                <span><i class="fas fa-check text-green-500"></i> {{ $missed->correct_count }}</span>
                                <span><i class="fas fa-times text-red-500"></i> {{ $missed->wrong_count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($perfectQuestions) && count($perfectQuestions) > 0)
            <div class="bg-green-50 border border-green-100 p-6 rounded-2xl">
                <div class="flex items-center gap-3 mb-4">
                    <i class="fas fa-star text-green-500 text-xl"></i>
                    <h4 class="text-green-900 font-bold text-lg">Perfectly Answered (100%)</h4>
                </div>
                <div class="space-y-3">
                    @foreach($perfectQuestions->take(3) as $perfect)
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-green-100 flex flex-col">
                            <span class="text-green-600 font-bold text-xs uppercase tracking-wider mb-1">Mastered</span>
                            <p class="text-gray-700 text-sm font-medium line-clamp-2">{!! strip_tags($perfect->question_text) !!}</p>
                        </div>
                    @endforeach
                    @if(count($perfectQuestions) > 3)
                        <p class="text-xs text-green-700 font-bold text-center mt-2">+{{ count($perfectQuestions) - 3 }} more perfect questions</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Complete Item Analysis Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-8">
            <div class="p-6 border-b border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-1">Complete Item Analysis</h4>
            </div>
            <div class="overflow-x-auto max-h-[500px]">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-4 w-16 text-center">#</th>
                            <th class="px-6 py-4">Question</th>
                            <th class="px-6 py-4">Section</th>
                            <th class="px-6 py-4 text-center">Correct</th>
                            <th class="px-6 py-4 text-center">Wrong</th>
                            <th class="px-6 py-4 text-center">Accuracy</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($itemAnalysis ?? [] as $index => $item)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-900 font-medium line-clamp-2">{!! strip_tags($item->question_text) !!}</p>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">{{ $item->category_name }}</td>
                                <td class="px-6 py-4 text-center text-green-600 font-bold">{{ $item->correct_count }}</td>
                                <td class="px-6 py-4 text-center text-red-500 font-bold">{{ $item->wrong_count }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $accClass = 'text-gray-600 bg-gray-100';
                                        if ($item->accuracy >= 75) $accClass = 'text-green-700 bg-green-100';
                                        elseif ($item->accuracy <= 40) $accClass = 'text-red-700 bg-red-100';
                                        else $accClass = 'text-amber-700 bg-amber-100';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $accClass }}">{{ $item->accuracy }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No item data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="exportModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="exportModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Export Report</h3>
            <button onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i class="fas fa-times text-lg"></i></button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Select the sections to include:</p>
        <form action="{{ route('dashboard.assessments.export', $assessment->id) }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_overview" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300">
                    <span class="text-gray-700 font-medium">Overview & Scores</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_category" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300">
                    <span class="text-gray-700 font-medium">Category Time & Mastery</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_item_analysis" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300">
                    <span class="text-gray-700 font-medium">Full Item Analysis</span>
                </label>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="toggleExportModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 rounded-xl font-medium border-0">Cancel</button>
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-2.5 rounded-xl font-medium border-0">Print</button>
                <button type="submit" name="action" value="pdf" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-medium border-0">Download</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Custom thin scrollbar for lists */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 10px; }
</style>

<script>
    function toggleExportModal() {
        const modal = document.getElementById('exportModal');
        const content = document.getElementById('exportModalContent');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.remove('opacity-0'); content.classList.remove('scale-95'); }, 10);
        } else {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    }

    window.assessmentCharts = window.assessmentCharts || {};
    function initAssessmentCharts() {
        if (window.assessmentCharts.passFail) window.assessmentCharts.passFail.destroy();
        if (window.assessmentCharts.distribution) window.assessmentCharts.distribution.destroy();
        if (window.assessmentCharts.category) window.assessmentCharts.category.destroy();

        @if(($completedCount ?? 0) > 0)
            const ctxPassFail = document.getElementById('passFailChart');
            if (ctxPassFail) {
                window.assessmentCharts.passFail = new Chart(ctxPassFail.getContext('2d'), {
                    type: 'doughnut',
                    data: { labels: ['Passed', 'Failed'], datasets: [{ data: [{{ $passedCount ?? 0 }}, {{ $failedCount ?? 0 }}], backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } } } }
                });
            }

            const ctxDist = document.getElementById('distributionChart');
            if (ctxDist) {
                window.assessmentCharts.distribution = new Chart(ctxDist.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['90-100%', '80-89%', '70-79%', '60-69%', 'Below 60%'],
                        datasets: [{ data: [{{ $scoreDistribution['90-100%'] ?? 0 }}, {{ $scoreDistribution['80-89%'] ?? 0 }}, {{ $scoreDistribution['70-79%'] ?? 0 }}, {{ $scoreDistribution['60-69%'] ?? 0 }}, {{ $scoreDistribution['Below 60%'] ?? 0 }}], backgroundColor: '#3b82f6', borderRadius: 4, borderWidth: 0 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
                });
            }
        @endif

        @if(count($categoryLabels ?? []) > 0)
            const ctxCategory = document.getElementById('categoryPerformanceChart');
            if (ctxCategory) {
                window.assessmentCharts.category = new Chart(ctxCategory.getContext('2d'), {
                    type: 'bar',
                    data: { labels: @json($categoryLabels), datasets: [{ data: @json($categoryScores), backgroundColor: '#a52a2a', borderRadius: 6, borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 }, x: { grid: { display: false } } } }
                });
            }
        @endif
    }
    initAssessmentCharts();
</script>