<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="material-analytics-content" class="relative min-h-screen pb-12 bg-gray-50">

    {{-- Header --}}
    <div class="pb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 max-w-7xl mx-auto">
        <div>
            <button
                onclick="loadPartial('{{ route('dashboard.materials.manage', $material->id) }}', document.getElementById('nav-materials-btn'))"
                class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group mb-2 text-sm cursor-pointer border-0 bg-transparent">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Material Management
            </button>
            <h2 class="text-3xl font-bold text-gray-900">{{ $material->title }} Analytics</h2>
            <p class="text-gray-500 mt-1">Monitor student engagement, progress, and assessment performance.</p>
        </div>
        <button onclick="toggleExportModal()"
            class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0 whitespace-nowrap cursor-pointer">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    {{-- FAB Navigation --}}
    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end pointer-events-none">
        <div id="fabMenu"
            class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div
                class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-56 pointer-events-auto">
                <p
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">
                    Quick Navigation
                </p>
                <button onclick="scrollToSection('class-overview'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-users w-4 text-center"></i> Key Metrics
                </button>
                <button onclick="scrollToSection('activity-trend'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-chart-line w-4 text-center"></i> Activity & Progress
                </button>
                <button onclick="scrollToSection('competency-breakdown'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-layer-group w-4 text-center"></i> Competency & Leaders
                </button>
                <button onclick="scrollToSection('item-analysis'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-microscope w-4 text-center"></i> Item Analysis
                </button>
                <button onclick="scrollToSection('material-analytics-content', true); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2 mt-1 text-xs font-semibold text-gray-400 hover:text-gray-800 bg-gray-50 rounded-xl transition-all text-left justify-center border border-gray-200 cursor-pointer">
                    <i class="fas fa-arrow-up"></i> Back to Top
                </button>
            </div>
        </div>
        <button onclick="toggleFabMenu()"
            class="pointer-events-auto w-14 h-14 bg-[#111827] text-white rounded-full shadow-lg shadow-gray-900/30 flex items-center justify-center hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300 border-0 cursor-pointer">
            <i id="fabIcon" class="fas fa-list-ul text-xl transition-transform duration-300"></i>
        </button>
    </div>

    @php 
        $hasLearnerData = ($completedCount > 0 || $inProgressCount > 0); 
    @endphp

    {{-- Content Sections --}}
    <div class="space-y-12 max-w-7xl mx-auto">

        {{-- Section 1: KPIs --}}
        <section id="class-overview" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Key Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Learners</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalLearners) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-amber-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Pending Enrollments</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($pendingRequests) }}</p>
                </div>

                {{-- Total Dropped Card --}}
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-purple-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Dropped</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalDropped) }}</p>
                </div>

                {{-- Overall Average Card --}}
                <div
                    class="bg-gradient-to-br from-gray-900 to-gray-800 p-5 rounded-2xl shadow-sm border border-gray-800 border-l-4 border-l-red-500">
                    <p class="text-gray-400 text-sm font-medium mb-1">Overall Student Average</p>
                    @if($hasQuizzes || $hasExams)
                        @if(!$hasLearnerData)
                            <p class="text-lg font-bold text-gray-400 mt-2 italic">No Data</p>
                        @else
                            <p class="text-3xl font-bold text-white">{{ $overallAverage }}%</p>
                        @endif
                    @else
                        <p class="text-lg font-bold text-gray-500 mt-2 italic">N/A</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- Section 2: Activity Trend & Progress --}}
        <section id="activity-trend" class="scroll-mt-20">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Activity Trends (Last 7 Days)</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        @php $hasTrends = isset($activityTrend) && collect($activityTrend)->sum() > 0; @endphp
                        @if(!$hasTrends)
                            <div class="text-center flex flex-col items-center">
                                <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mb-3 border border-gray-100">
                                    <i class="fas fa-chart-line text-gray-300 text-2xl"></i>
                                </div>
                                <p class="text-gray-500 font-bold">No recent activity.</p>
                                <p class="text-xs text-gray-400 mt-1">New enrollments in the last 7 days will show here.</p>
                            </div>
                        @else
                            <canvas id="activityTrendChart"></canvas>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Student Progress</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        @if(!$hasLearnerData)
                            <div class="text-center flex flex-col items-center">
                                <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mb-3 border border-gray-100">
                                    <i class="fas fa-chart-pie text-gray-300 text-2xl"></i>
                                </div>
                                <p class="text-gray-500 font-bold">No enrollments yet.</p>
                                <p class="text-xs text-gray-400 mt-1">Progress will appear once students enroll.</p>
                            </div>
                        @else
                            <canvas id="studentProgressChart"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Section 3: Competency Breakdown & Student Leaderboard --}}
        <section id="competency-breakdown" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Competency & Standings</h3>
            <div class="grid grid-cols-1 gap-8 mt-4">

                {{-- Competency Table --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex gap-3">
                            <h4 class="text-gray-700 font-semibold mb-1">Competency Breakdown </h4>
                            <div class="relative group cursor-help">
                                <i class="fas fa-info-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-60 p-3 bg-gray-900 text-white text-[11px] rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] pointer-events-none">
                                    <strong class="text-green-700 block mb-1">Advanced (90% - 100%)</strong>
                                    <p class="text-gray-400 mb-2">Excellent mastery; near-perfect accuracy.</p>
                                    <strong class="text-green-500 block mb-1">Upper Intermediate (75% - 89%)</strong>
                                    <p class="text-gray-400 mb-2">Strong understanding with minor errors.</p>
                                    <strong class="text-blue-600 block mb-1">Intermediate (60% - 74%)</strong>
                                    <p class="text-gray-400 mb-2">Solid understanding but needs refinement.</p>
                                    <strong class="text-amber-600 block mb-1">Basic (40% - 59%)</strong>
                                    <p class="text-gray-400 mb-2">Basic understanding; inconsistent performance.</p>
                                    <strong class="text-red-600 block mb-1">Beginner (0% - 39%)</strong>
                                    <p class="text-gray-400">Limited understanding; requires support.</p>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400">Mean Percentage Score (MPS) across module sections. </p>
                    </div>
                    <div class="overflow-y-auto max-h-[400px] custom-scrollbar">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-6 py-4 w-1/2">Section / Topic Name</th>
                                    <th class="px-6 py-4 text-center">MPS (Accuracy)</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($competencies ?? [] as $cat)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900">
    {{ $cat->title ?: 'Untitled Section' }}
</td>
                                        @if($cat->has_quiz)
                                            @if(!$hasLearnerData || !isset($cat->mps))
                                                <td class="px-6 py-4 text-center">
                                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold text-gray-500 bg-gray-100">--</span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="text-xs text-gray-400 font-bold italic">No data yet</span>
                                                </td>
                                            @else
                                                <td class="px-6 py-4 text-center">
                                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $cat->mps >= 75 ? 'text-green-700 bg-green-100' : ($cat->mps <= 40 ? 'text-red-700 bg-red-100' : 'text-amber-700 bg-amber-100') }}">{{ $cat->mps }}%</span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    @if($cat->mps >= 90)
                                                        <span class="text-xs text-green-700 font-bold">
                                                            <i class="fas fa-star"></i> Advanced
                                                        </span>
                                                    @elseif($cat->mps >= 75)
                                                        <span class="text-xs text-green-500 font-bold">
                                                            <i class="fas fa-check-circle"></i> Upper Intermediate
                                                        </span>
                                                    @elseif($cat->mps >= 60)
                                                        <span class="text-xs text-blue-600 font-bold">
                                                            <i class="fas fa-arrow-up"></i> Intermediate
                                                        </span>
                                                    @elseif($cat->mps >= 40)
                                                        <span class="text-xs text-amber-600 font-bold">
                                                            <i class="fas fa-minus-circle"></i> Basic
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-red-600 font-bold">
                                                            <i class="fas fa-times-circle"></i> Beginner
                                                        </span>
                                                    @endif
                                                </td>
                                            @endif
                                        @else
                                            <td class="px-6 py-4 text-center text-gray-400 italic text-xs" colspan="2">
                                                Section does not have a quiz
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">No competency data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Student Leaderboard --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-[#a52a2a] to-red-900 text-white">
                        <div>
                            <div class="flex">
                                <div class="flex gap-3 ">
                                    <h4 class="font-semibold mb-1">Top Performing Students</h4>
                                    <div class="relative group cursor-help">
                                        <i class="fas fa-question-circle text-white transition-colors group-hover:text-white"></i>
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-64 p-4 bg-gray-900 text-white text-[11px] rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] font-normal normal-case tracking-normal pointer-events-none text-left">
                                            <strong class="text-white block mb-2 border-b border-gray-700 pb-1">Calculation Method</strong>
                                            <p class="mb-2 leading-relaxed">The Overall score is the total accuracy across the module:</p>
                                            <div class="bg-black/30 p-2 rounded font-mono text-center mb-2">
                                                (Σ Correct Quiz + Σ Correct Exam) / (Σ Total Items Answered) × 100
                                            </div>
                                            <p class="text-gray-400 italic">Note: Items not yet reached in the timeline are excluded from the denominator until attempted.</p>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-white/70 flex items-center gap-1">
                                Metrics based on {{ $quizItemsCount }} Quiz item(s) and {{ $examItemsCount }} Exam item(s).
                            </p>
                        </div>
                        <i class="fas fa-medal text-3xl text-yellow-400 opacity-80"></i>
                    </div>
                    <div class="overflow-y-auto max-h-[400px] custom-scrollbar">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-4 w-12 text-center">Rank</th>
                                    <th class="px-4 py-4">Student Name</th>
                                    <th class="px-4 py-4 text-center">Progress</th>
                                    <th class="px-4 py-4 text-center">Quiz Score</th>
                                    <th class="px-4 py-4 text-center">Exam Score</th>
                                    <th class="px-4 py-4 text-center">Overall</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($studentLeaderboard ?? [] as $index => $student)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-4 py-4 text-center font-bold {{ $index < 3 ? 'text-[#a52a2a]' : 'text-gray-400' }}">{{ $index + 1 }}</td>
                                        <td class="px-4 py-4 font-medium text-gray-900">{{ $student->name }}</td>
                                        <td class="px-4 py-4 text-center text-gray-500">{{ $student->progress }}%</td>
                                        <td class="px-4 py-4 text-center font-medium">{{ (!$hasQuizzes) ? 'N/A' : $student->quiz_score_raw }}</td>
                                        <td class="px-4 py-4 text-center font-medium">{{ (!$hasExams) ? 'N/A' : $student->exam_score_raw }}</td>
                                        <td class="px-4 py-4 text-center font-bold text-gray-800 bg-gray-50/50">{{ (!$hasQuizzes && !$hasExams) ? 'N/A' : $student->score . '%' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center flex flex-col items-center justify-center">
                                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-2 border border-gray-100">
                                                <i class="fas fa-medal text-gray-300 text-xl"></i>
                                            </div>
                                            <span class="text-gray-500 font-medium">No student enrollments yet.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>

        {{-- Section 4: Item Analysis --}}
        <section id="item-analysis" class="scroll-mt-20 mt-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Item Analysis</h3>

            @if(!$hasQuizzes && !$hasExams)
                <div class="flex flex-col items-center justify-center p-12 text-center bg-gray-50/50 rounded-2xl border-2 border-gray-200 border-dashed">
                    <div class="h-16 w-16 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center text-2xl mb-4">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-700">No Items to Analyze</h4>
                    <p class="text-sm text-gray-500 mt-1">Add items to your module to see question statistics.</p>
                </div>
            @else

                {{-- Quiz Item Table --}}
                @if($hasQuizzes)
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-tasks text-purple-600"></i> Quiz Items
                            </h4>
                            <div class="flex gap-4 text-sm font-bold">
                                <span
                                    class="text-purple-700 bg-purple-50 px-3 py-1.5 rounded-lg border border-purple-100 flex items-center gap-2">
                                    <i class="fas fa-chart-pie"></i> Average Quiz Score: {{ !$hasLearnerData ? '--' : $avgQuizScore . '%' }}
                                </span>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="overflow-x-auto max-h-[500px] custom-scrollbar">
                                <table class="w-full text-left text-sm text-gray-600 min-w-[800px]">
                                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th class="px-6 py-4 w-12 text-center">Item</th>
                                            <th class="px-6 py-4 w-1/3">Question Base</th>
                                            <th class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    Difficulty Level
                                                    <div class="relative group cursor-help">
                                                        <i
                                                            class="fas fa-question-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                                        <div
                                                            class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-64 p-3 bg-gray-900 text-white text-[11px] rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] font-normal normal-case tracking-normal pointer-events-none">
                                                            <strong
                                                                class="text-white block mb-2 border-b border-gray-700 pb-1">Difficulty
                                                                Classification</strong>
                                                            <ul class="space-y-1">
                                                                <li class="flex justify-between"><span class="text-blue-400 font-bold">81% - 100%</span><span>Very Easy</span></li>
                                                                <li class="flex justify-between"><span class="text-green-400 font-bold">61% - 80%</span><span>Easy</span></li>
                                                                <li class="flex justify-between"><span class="text-amber-400 font-bold">41% - 60%</span><span>Average</span></li>
                                                                <li class="flex justify-between"><span class="text-orange-400 font-bold">21% - 40%</span><span>Difficult</span></li>
                                                                <li class="flex justify-between"><span class="text-red-400 font-bold">0% - 20%</span><span>Very Difficult</span></li>
                                                            </ul>
                                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                            <th class="px-6 py-4 w-1/2 text-center">Answers Chosen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($quizItemAnalysis ?? [] as $index => $item)
                                            @php 
                                                $correct = $item->correct_count ?? 0;
                                                $wrong = $item->wrong_count ?? 0;
                                                $totalAns = $correct + $wrong;
                                                $hasAnswers = $totalAns > 0;

                                                if (!$hasAnswers && isset($item->distractor_stats)) {
                                                    foreach($item->distractor_stats as $stat) {
                                                        if (($stat->pct ?? 0) > 0 || ($stat->count ?? 0) > 0) {
                                                            $hasAnswers = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-900 font-medium line-clamp-2 mb-1">
                                                        {!! strip_tags($item->question_text) !!}</p>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $item->category_name }}</span>
                                                </td>

                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex flex-col items-center">
                                                        <span class="font-bold text-gray-900 text-base mb-1">{{ !$hasAnswers ? '--' : ($item->difficulty_index ?? 0) . '%' }}</span>
                                                        <div class="flex gap-2 text-[10px] font-bold text-gray-400 mb-1">
                                                            <span class="text-green-600"><i class="fas fa-check"></i> {{ $correct }}</span>
                                                            <span class="text-red-500"><i class="fas fa-times"></i> {{ $wrong }}</span>
                                                        </div>
                                                        @if(!$hasAnswers)
                                                            <span class="text-[10px] text-gray-500 font-bold uppercase bg-gray-100 px-2 py-0.5 rounded border border-gray-200">No Answers</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 81)
                                                            <span class="text-[10px] text-blue-500 font-bold uppercase">Very Easy</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 61)
                                                            <span class="text-[10px] text-green-500 font-bold uppercase">Easy</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 41)
                                                            <span class="text-[10px] text-amber-500 font-bold uppercase">Average</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 21)
                                                            <span class="text-[10px] text-orange-500 font-bold uppercase">Difficult</span>
                                                        @else
                                                            <span class="text-[10px] text-red-500 font-bold uppercase">Very Difficult</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        @if(!$hasAnswers)
                                                            <span class="text-xs text-gray-400 italic">No responses recorded yet.</span>
                                                        @elseif(isset($item->distractor_stats))
                                                            @foreach($item->distractor_stats as $opt)
                                                                @php
                                                                    $isCorrect = $opt->is_correct;
                                                                    $isDeadDistractor = (!$isCorrect && $opt->pct == 0);
                                                                @endphp
                                                                <span
                                                                    class="px-2 py-1 text-[10px] rounded border {{ $isCorrect ? 'bg-green-50 border-green-200 text-green-700 font-bold shadow-sm' : ($isDeadDistractor ? 'bg-gray-100 border-dashed border-gray-300 text-gray-400 opacity-70' : 'bg-red-50 border-red-100 text-red-600') }}"
                                                                    title="{{ $isDeadDistractor ? 'Dead Distractor/Response: No student selected or answered this.' : '' }}">
                                                                    {!! \Illuminate\Support\Str::limit(strip_tags($opt->text), 40) !!}:
                                                                    {{ $opt->pct }}%
                                                                    @if($isCorrect) <i class="fas fa-check ml-1"></i> @endif
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-2 border border-gray-100">
                                                            <i class="fas fa-microscope text-gray-300 text-xl"></i>
                                                        </div>
                                                        <span class="font-medium">No quiz items available for analysis.</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Exam Item Table --}}
                @if($hasExams)
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-file-signature text-red-600"></i> Exam Items
                            </h4>
                            <div class="flex gap-4 text-sm font-bold">
                                <span
                                    class="text-red-700 bg-red-50 px-3 py-1.5 rounded-lg border border-red-100 flex items-center gap-2">
                                    <i class="fas fa-chart-pie"></i> Average Exam Score: {{ !$hasLearnerData ? '--' : $avgExamScore . '%' }}
                                </span>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="overflow-x-auto max-h-[500px] custom-scrollbar">
                                <table class="w-full text-left text-sm text-gray-600 min-w-[800px]">
                                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th class="px-6 py-4 w-12 text-center">Item</th>
                                            <th class="px-6 py-4 w-1/3">Question Base</th>
                                            <th class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    Difficulty Level
                                                    <div class="relative group cursor-help">
                                                        <i
                                                            class="fas fa-question-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                                        <div
                                                            class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-64 p-3 bg-gray-900 text-white text-[11px] rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] font-normal normal-case tracking-normal pointer-events-none">
                                                            <strong
                                                                class="text-white block mb-2 border-b border-gray-700 pb-1">Difficulty
                                                                Classification</strong>
                                                            <ul class="space-y-1">
                                                                <li class="flex justify-between"><span class="text-blue-400 font-bold">81% - 100%</span><span>Very Easy</span></li>
                                                                <li class="flex justify-between"><span class="text-green-400 font-bold">61% - 80%</span><span>Easy</span></li>
                                                                <li class="flex justify-between"><span class="text-amber-400 font-bold">41% - 60%</span><span>Average</span></li>
                                                                <li class="flex justify-between"><span class="text-orange-400 font-bold">21% - 40%</span><span>Difficult</span></li>
                                                                <li class="flex justify-between"><span class="text-red-400 font-bold">0% - 20%</span><span>Very Difficult</span></li>
                                                            </ul>
                                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                            <th class="px-6 py-4 w-1/2 text-center">Answers Chosen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($examItemAnalysis ?? [] as $index => $item)
                                            @php 
                                                $correct = $item->correct_count ?? 0;
                                                $wrong = $item->wrong_count ?? 0;
                                                $totalAns = $correct + $wrong;
                                                $hasAnswers = $totalAns > 0;

                                                if (!$hasAnswers && isset($item->distractor_stats)) {
                                                    foreach($item->distractor_stats as $stat) {
                                                        if (($stat->pct ?? 0) > 0 || ($stat->count ?? 0) > 0) {
                                                            $hasAnswers = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-900 font-medium line-clamp-2 mb-1">
                                                        {!! strip_tags($item->question_text) !!}</p>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $item->category_name }}</span>
                                                </td>

                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex flex-col items-center">
                                                        <span class="font-bold text-gray-900 text-base mb-1">{{ !$hasAnswers ? '--' : ($item->difficulty_index ?? 0) . '%' }}</span>
                                                        <div class="flex gap-2 text-[10px] font-bold text-gray-400 mb-1">
                                                            <span class="text-green-600"><i class="fas fa-check"></i> {{ $correct }}</span>
                                                            <span class="text-red-500"><i class="fas fa-times"></i> {{ $wrong }}</span>
                                                        </div>
                                                        @if(!$hasAnswers)
                                                            <span class="text-[10px] text-gray-500 font-bold uppercase bg-gray-100 px-2 py-0.5 rounded border border-gray-200">No Answers</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 81)
                                                            <span class="text-[10px] text-blue-500 font-bold uppercase">Very Easy</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 61)
                                                            <span class="text-[10px] text-green-500 font-bold uppercase">Easy</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 41)
                                                            <span class="text-[10px] text-amber-500 font-bold uppercase">Average</span>
                                                        @elseif(($item->difficulty_index ?? 0) >= 21)
                                                            <span class="text-[10px] text-orange-500 font-bold uppercase">Difficult</span>
                                                        @else
                                                            <span class="text-[10px] text-red-500 font-bold uppercase">Very Difficult</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        @if(!$hasAnswers)
                                                            <span class="text-xs text-gray-400 italic">No responses recorded yet.</span>
                                                        @elseif(isset($item->distractor_stats))
                                                            @foreach($item->distractor_stats as $opt)
                                                                @php
                                                                    $isCorrect = $opt->is_correct;
                                                                    $isDeadDistractor = (!$isCorrect && $opt->pct == 0);
                                                                @endphp
                                                                <span
                                                                    class="px-2 py-1 text-[10px] rounded border {{ $isCorrect ? 'bg-green-50 border-green-200 text-green-700 font-bold shadow-sm' : ($isDeadDistractor ? 'bg-gray-100 border-dashed border-gray-300 text-gray-400 opacity-70' : 'bg-red-50 border-red-100 text-red-600') }}"
                                                                    title="{{ $isDeadDistractor ? 'Dead Distractor/Response: No student selected or answered this.' : '' }}">
                                                                    {!! \Illuminate\Support\Str::limit(strip_tags($opt->text), 40) !!}:
                                                                    {{ $opt->pct }}%
                                                                    @if($isCorrect) <i class="fas fa-check ml-1"></i> @endif
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-2 border border-gray-100">
                                                            <i class="fas fa-microscope text-gray-300 text-xl"></i>
                                                        </div>
                                                        <span class="font-medium">No exam items available for analysis.</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            @endif
        </section>

    </div>
</div>

{{-- Export Modal --}}
<div id="exportModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="exportModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Export Report</h3>
            <button onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent cursor-pointer">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Select sections to include:</p>
        <form action="{{ route('dashboard.materials.export', $material->id) }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="check_metrics" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Metrics & Progress</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="check_competency" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Competency & Leaderboard</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="check_item_analysis" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Item Analysis Data</span>
                </label>
            </div>
            <div class="flex gap-3">
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 rounded-xl font-medium flex items-center justify-center gap-2 border-0 cursor-pointer transition">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="submit" name="action" value="pdf" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-2.5 rounded-xl font-medium flex items-center justify-center gap-2 border-0 cursor-pointer transition shadow-md">
                    <i class="fas fa-file-pdf"></i> Save PDF
                </button>
            </div>
        </form>    
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 10px;
        transition: background-color 0.2s ease;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #a52a2a;
    }
</style>

<script>
    function toggleFabMenu() {
        const menu = document.getElementById('fabMenu');
        const icon = document.getElementById('fabIcon');

        if (menu.classList.contains('opacity-0')) {
            menu.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
            menu.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
            icon.classList.remove('fa-list-ul');
            icon.classList.add('fa-times');
            icon.style.transform = 'rotate(90deg)';
        } else {
            menu.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
            menu.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
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
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function toggleExportModal() {
        const modal = document.getElementById('exportModal');
        const content = document.getElementById('exportModalContent');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.remove('opacity-0'); content.classList.remove('scale-95'); }, 10);
        } else {
            modal.classList.add('opacity-0'); content.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    }

    window.matCharts = window.matCharts || {};
    function initMaterialCharts() {
        if (window.matCharts.trend) window.matCharts.trend.destroy();
        if (window.matCharts.progress) window.matCharts.progress.destroy();

        // Trend Chart
        @if($hasTrends)
            const trendCtx = document.getElementById('activityTrendChart');
            if (trendCtx) {
                window.matCharts.trend = new Chart(trendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($activityDates),
                        datasets: [{
                            label: 'Active Learners',
                            data: @json($activityTrend),
                            borderColor: '#a52a2a',
                            backgroundColor: 'rgba(165, 42, 42, 0.1)',
                            borderWidth: 3, fill: true, tension: 0.4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } } }
                });
            }
        @endif

        // Progress Doughnut
        @if($hasLearnerData)
            const progCtx = document.getElementById('studentProgressChart');
            if (progCtx) {
                window.matCharts.progress = new Chart(progCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress'],
                        datasets: [{
                            data: [@json($completedCount), @json($inProgressCount)],
                            backgroundColor: ['#10b981', '#f59e0b'], borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
                });
            }
        @endif
    }
    initMaterialCharts();
</script>