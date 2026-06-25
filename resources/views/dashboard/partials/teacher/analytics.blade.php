<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="relative min-h-screen pb-12 bg-gray-50">

    {{-- Header --}}
    <div class="p-6 pb-2 flex justify-between items-end max-w-7xl mx-auto">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Teacher Analytics</h2>
            <p class="text-gray-500 mt-1">Monitor your overall teaching impact and student outcomes.</p>
        </div>
        <button onclick="toggleExportModal()"
            class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    {{-- FAB Navigation --}}
    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end">
        <div id="fabMenu"
            class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div
                class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-48">
                <p
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">
                    Quick Navigation</p>

                <button onclick="scrollToSection('teaching-overview'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-chalkboard-teacher w-4 text-center"></i> Teaching Overview
                </button>
                <button onclick="scrollToSection('learning-outcomes'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-graduation-cap w-4 text-center"></i> Learning Outcomes
                </button>
                <button onclick="scrollToSection('performance-insights'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-lightbulb w-4 text-center"></i> Performance Insights
                </button>
                <button onclick="scrollToSection('activity-trends'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-chart-line w-4 text-center"></i> Activity Trends
                </button>

            </div>
        </div>

        <button onclick="toggleFabMenu()"
            class="w-14 h-14 bg-[#111827] text-white rounded-full shadow-lg shadow-gray-900/30 flex items-center justify-center hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300">
            <i id="fabIcon" class="fas fa-list-ul text-xl transition-transform duration-300"></i>
        </button>
    </div>

    {{-- Dashboard Content Sections --}}
    <div class="p-6 space-y-12 max-w-7xl mx-auto">

        {{-- 1. TEACHING OVERVIEW --}}
        <section id="teaching-overview" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i
                        class="fas fa-chalkboard-teacher text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Teaching Overview</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Active Modules</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($activeModules) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-purple-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Unique Learners</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($uniqueLearners) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Enrollments</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalEnrollments) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-amber-500">
                    <p class="text-gray-500 text-sm font-medium mb-1">Overall Completion Rate</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $overallCompletionRate }}%</p>
                </div>
            </div>
        </section>

        {{-- 2. LEARNING OUTCOMES --}}
        <section id="learning-outcomes" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"><i
                        class="fas fa-graduation-cap text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Learning Outcomes</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl font-bold border-4 border-emerald-100">
                        <i class="fas fa-spell-check"></i>
                    </div>
                    <div class="w-full">
                        <div class="flex justify-between items-start">
                            <p class="text-gray-500 text-sm font-medium mb-1">Quiz MPS</p>
                            <div class="relative group cursor-help">
                                <i class="fas fa-info-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-80 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] border border-gray-100 text-left font-normal normal-case tracking-normal">
                                    <div class="p-3 max-h-48 overflow-y-auto custom-scrollbar text-gray-700 text-[11px] leading-relaxed">
                                        <strong class="text-gray-900 block mb-1">Quiz Mean Percentage Score (MPS)</strong>
                                        The Quiz MPS represents the average score percentage across all lesson quizzes taken by active students in your published modules.

                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                            <strong class="text-gray-900 block mb-1">How it's calculated:</strong>
                                            <ol class="list-decimal pl-4 space-y-1 m-0">
                                                <li>Sum all correct quiz answers submitted by active students.</li>
                                                <li>Sum all total quiz questions answered by active students.</li>
                                                <li>Divide correct answers by total answered questions and multiply by 100.<br>
                                                    <span class="text-gray-500 italic text-[10px]">Quiz MPS = (Total Correct Answers &divide; Total Questions Answered) &times; 100</span>
                                                </li>
                                            </ol>
                                        </div>

                                        <div class="mt-2 bg-gray-50 p-2.5 rounded border border-gray-100">
                                            <strong class="text-gray-900 block mb-1">Example:</strong>
                                            <p class="mb-1 text-gray-600">Active students answered 40 total quiz items across lessons.</p>
                                            <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-600 m-0">
                                                <li>Total Correct Answers: 32</li>
                                                <li>Total Incorrect Answers: 8</li>
                                            </ul>
                                            <div class="mt-2 pt-2 border-t border-gray-200">
                                                <p class="text-[10px] text-gray-600 m-0">Computation: (32 &divide; 40) &times; 100 = 80%</p>
                                                <p class="font-bold text-gray-900 mt-0.5 mb-0">Result: The Quiz MPS is 80%.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-white"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ $quizMPS }}%</p>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl font-bold border-4 border-blue-100">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="w-full">
                        <div class="flex justify-between items-start">
                            <p class="text-gray-500 text-sm font-medium mb-1">Exam MPS</p>
                            <div class="relative group cursor-help">
                                <i class="fas fa-info-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-80 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] border border-gray-100 text-left font-normal normal-case tracking-normal">
                                    <div class="p-3 max-h-48 overflow-y-auto custom-scrollbar text-gray-700 text-[11px] leading-relaxed">
                                        <strong class="text-gray-900 block mb-1">Exam Mean Percentage Score (MPS)</strong>
                                        The Exam MPS represents the average test performance across all module assessment exams completed by active students.

                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                            <strong class="text-gray-900 block mb-1">How it's calculated:</strong>
                                            <ol class="list-decimal pl-4 space-y-1 m-0">
                                                <li>Sum all correct exam items answered by active students.</li>
                                                <li>Sum all total exam items answered by active students.</li>
                                                <li>Divide correct answers by total answered items and multiply by 100.<br>
                                                    <span class="text-gray-500 italic text-[10px]">Exam MPS = (Total Correct Answers &divide; Total Exam Items Answered) &times; 100</span>
                                                </li>
                                            </ol>
                                        </div>

                                        <div class="mt-2 bg-gray-50 p-2.5 rounded border border-gray-100">
                                            <strong class="text-gray-900 block mb-1">Example:</strong>
                                            <p class="mb-1 text-gray-600">Active students submitted 100 total exam answers.</p>
                                            <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-600 m-0">
                                                <li>Total Correct Answers: 85</li>
                                                <li>Total Incorrect Answers: 15</li>
                                            </ul>
                                            <div class="mt-2 pt-2 border-t border-gray-200">
                                                <p class="text-[10px] text-gray-600 m-0">Computation: (85 &divide; 100) &times; 100 = 85%</p>
                                                <p class="font-bold text-gray-900 mt-0.5 mb-0">Result: The Exam MPS is 85%.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-white"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ $examMPS }}%</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-2xl font-bold border-4 border-amber-100">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="w-full">
                        <div class="flex justify-between items-start">
                            <p class="text-gray-500 text-sm font-medium mb-1">Overall Pass Rate</p>
                            <div class="relative group cursor-help">
                                <i class="fas fa-info-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                <div class="absolute bottom-full right-0 mb-2 w-80 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] border border-gray-100 text-left font-normal normal-case tracking-normal">
                                    <div class="p-3 max-h-48 overflow-y-auto custom-scrollbar text-gray-700 text-[11px] leading-relaxed">
                                        <strong class="text-gray-900 block mb-1">Overall Pass Rate</strong>
                                        The Overall Pass Rate represents the percentage of completed module assessment attempts that resulted in a passing score.

                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                            <strong class="text-gray-900 block mb-1">How it's calculated:</strong>
                                            <ol class="list-decimal pl-4 space-y-1 m-0">
                                                <li>Count all module attempts with status 'completed' (passed).</li>
                                                <li>Count all concluded attempts ('completed' or 'failed').</li>
                                                <li>Divide passed attempts by total concluded attempts and multiply by 100.<br>
                                                    <span class="text-gray-500 italic text-[10px]">Pass Rate = (Passed Attempts &divide; Total Concluded Attempts) &times; 100</span>
                                                </li>
                                            </ol>
                                        </div>

                                        <div class="mt-2 bg-gray-50 p-2.5 rounded border border-gray-100">
                                            <strong class="text-gray-900 block mb-1">Example:</strong>
                                            <p class="mb-1 text-gray-600">25 total students finished taking module exams.</p>
                                            <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-600 m-0">
                                                <li>Passed (Completed): 20</li>
                                                <li>Failed: 5</li>
                                            </ul>
                                            <div class="mt-2 pt-2 border-t border-gray-200">
                                                <p class="text-[10px] text-gray-600 m-0">Computation: (20 &divide; 25) &times; 100 = 80%</p>
                                                <p class="font-bold text-gray-900 mt-0.5 mb-0">Result: The Overall Pass Rate is 80%.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="absolute top-full right-2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-white"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ $overallPassRate }}%</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. MODULE PERFORMANCE INSIGHTS --}}
        <section id="performance-insights" class="scroll-mt-20">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i
                        class="fas fa-lightbulb text-lg"></i></div>
                <h3 class="text-2xl font-bold text-gray-800">Module Performance Insights</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- Best Performing --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2 flex justify-between items-center">
                            <span><i class="fas fa-arrow-trend-up text-green-500 mr-2"></i> Best Performing</span>
                        </h4>
                        @if($bestPerformingModule)
                            <p class="text-lg font-bold text-gray-900 mb-4 line-clamp-2">{{ $bestPerformingModule->title }}</p>
                        @else
                            <div class="flex flex-col items-center justify-center py-6 opacity-70">
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                <p class="text-gray-500 font-medium text-sm">No data yet.</p>
                            </div>
                        @endif
                    </div>
                    @if($bestPerformingModule)
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg mt-auto">
                        <div class="text-center w-1/2">
                            <p class="text-xs text-gray-500 font-medium">Completion</p>
                            <p class="text-lg font-bold text-gray-900">{{ $bestPerformingModule->completion_rate }}%</p>
                        </div>
                        <div class="text-center border-l border-gray-200 pl-4 w-1/2">
                            <p class="text-xs text-gray-500 font-medium">MPS</p>
                            <p class="text-lg font-bold text-gray-900">{{ $bestPerformingModule->assessment_mps }}%</p>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Lowest Performing --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2 flex justify-between items-center">
                            <span><i class="fas fa-arrow-trend-down text-red-500 mr-2"></i> Needs Attention</span>
                        </h4>
                        @if($lowestPerformingModule)
                            <p class="text-lg font-bold text-gray-900 mb-4 line-clamp-2">{{ $lowestPerformingModule->title }}</p>
                        @else
                            <div class="flex flex-col items-center justify-center py-6 opacity-70">
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                <p class="text-gray-500 font-medium text-sm">No data yet.</p>
                            </div>
                        @endif
                    </div>
                    @if($lowestPerformingModule)
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg mt-auto">
                        <div class="text-center w-1/2">
                            <p class="text-xs text-gray-500 font-medium">Completion</p>
                            <p class="text-lg font-bold text-gray-900">{{ $lowestPerformingModule->completion_rate }}%</p>
                        </div>
                        <div class="text-center border-l border-gray-200 pl-4 w-1/2">
                            <p class="text-xs text-gray-500 font-medium">MPS</p>
                            <p class="text-lg font-bold text-gray-900">{{ $lowestPerformingModule->assessment_mps }}%</p>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Most Engaging --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2 flex justify-between items-center">
                            <span><i class="fas fa-fire text-orange-500 mr-2"></i> Most Engaging</span>
                        </h4>
                        @if($mostEngagingModule && $mostEngagingModule->engagement_score > 0)
                            <p class="text-lg font-bold text-gray-900 mb-4 line-clamp-2">{{ $mostEngagingModule->title }}</p>
                        @else
                            <div class="flex flex-col items-center justify-center py-6 opacity-70">
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                <p class="text-gray-500 font-medium text-sm">No data yet.</p>
                            </div>
                        @endif
                    </div>
                    @if($mostEngagingModule && $mostEngagingModule->engagement_score > 0)
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg mt-auto">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 font-medium">Views</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($mostEngagingModule->views) }}</p>
                        </div>
                        <div class="text-center border-l border-gray-200 pl-2">
                            <p class="text-xs text-gray-500 font-medium">DLs</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($mostEngagingModule->downloads) }}</p>
                        </div>
                        <div class="text-center border-l border-gray-200 pl-2">
                            <p class="text-xs text-gray-500 font-medium">Enroll</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($mostEngagingModule->total_enrollments) }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Published vs Draft Chart</h4>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        @if($publishedModules == 0 && $draftModules == 0)
                            <div class="flex flex-col items-center justify-center h-full w-full py-8 opacity-70">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-chart-pie text-2xl text-gray-400 opacity-50"></i>
                                </div>
                                <p class="text-gray-500 font-medium text-sm">No modules to display.</p>
                            </div>
                        @else
                            <canvas id="teacherStatusChart"></canvas>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Top Performing Modules</h4>
                    <div class="flex-1 overflow-auto mt-2">
                        @if(count($topModules) > 0)
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="pb-3 text-xs text-gray-500 font-semibold uppercase tracking-wider">Module</th>
                                        <th class="pb-3 text-xs text-gray-500 font-semibold uppercase tracking-wider text-right">Views</th>
                                        <th class="pb-3 text-xs text-gray-500 font-semibold uppercase tracking-wider text-right">DLs</th>
                                        <th class="pb-3 text-xs text-gray-500 font-semibold uppercase tracking-wider text-right">Enrl</th>
                                        <th class="pb-3 text-xs text-gray-500 font-semibold uppercase tracking-wider text-right">Comp %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topModules as $tm)
                                        <tr class="border-t border-gray-100">
                                            <td class="py-3 pr-4">
                                                <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $tm->title }}</p>
                                            </td>
                                            <td class="py-3 text-sm text-gray-600 text-right">{{ number_format($tm->views) }}</td>
                                            <td class="py-3 text-sm text-gray-600 text-right">{{ number_format($tm->downloads) }}</td>
                                            <td class="py-3 text-sm text-gray-600 text-right">{{ number_format($tm->total_enrollments) }}</td>
                                            <td class="py-3 text-sm text-gray-600 text-right">{{ $tm->completion_rate }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="flex flex-col items-center justify-center h-full w-full py-8 opacity-70">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-list text-2xl text-gray-400 opacity-50"></i>
                                </div>
                                <p class="text-gray-500 font-medium text-sm">No modules to display.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. ACTIVITY TRENDS --}}
        <section id="activity-trends" class="scroll-mt-20">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center"><i
                            class="fas fa-chart-line text-lg"></i></div>
                    <h3 class="text-2xl font-bold text-gray-800">Activity Trends (Last 30 Days)</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-gray-700 font-semibold mb-4 border-b pb-2">Enrollments vs Completions vs Assessments</h4>
                <div class="relative h-80 w-full flex justify-center items-center">
                    @if(array_sum($trendEnrollments ?? []) > 0 || array_sum($trendCompletions ?? []) > 0 || array_sum($trendAssessments ?? []) > 0)
                        <canvas id="teacherTrendChart"></canvas>
                    @else
                        <div class="flex flex-col items-center justify-center h-full w-full py-8 opacity-70">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-chart-line text-2xl text-gray-400 opacity-50"></i>
                            </div>
                            <p class="text-gray-500 font-medium text-sm">No activity recorded in the last 30 days.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

    </div>
</div>

{{-- =========================================================================
EXPORT MODAL
========================================================================= --}}
<div id="exportModal"
    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300"
        id="exportModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Export Analytics Report</h3>
            <button onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i
                    class="fas fa-times text-lg"></i></button>
        </div>

        <p class="text-sm text-gray-500 mb-4">Select the sections to include in your report:</p>

        <form action="{{ route('analytics.export.teacher') }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_overview" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Teaching Overview</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_outcomes" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Learning Outcomes</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_insights" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Module Performance Insights</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_trends" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Activity Trends</span>
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
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

</script>
<script>
    window.dashboardCharts = window.dashboardCharts || {};

    function initDashboardCharts() {
        if (window.dashboardCharts.teacherStatus) window.dashboardCharts.teacherStatus.destroy();
        if (window.dashboardCharts.teacherTrend) window.dashboardCharts.teacherTrend.destroy();

        // 1. Module Status Doughnut Chart
        @if($publishedModules > 0 || $draftModules > 0)
            const statusEl = document.getElementById('teacherStatusChart');
            if (statusEl) {
                const statusCtx = statusEl.getContext('2d');
                window.dashboardCharts.teacherStatus = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Published', 'Draft'],
                        datasets: [{
                            data: [@json($publishedModules), @json($draftModules)],
                            backgroundColor: ['#10b981', '#9ca3af'], // Green and Gray
                            borderWidth: 0
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        cutout: '70%', 
                        plugins: { legend: { position: 'bottom' } } 
                    }
                });
            }
        @endif

        // 2. Trend Line Chart
        const trendEl = document.getElementById('teacherTrendChart');
        if (trendEl) {
            const trendCtx = trendEl.getContext('2d');
            window.dashboardCharts.teacherTrend = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($activityDates),
                    datasets: [
                        {
                            label: 'Enrollments',
                            data: @json($trendEnrollments),
                            borderColor: '#3b82f6', // Blue
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Completions',
                            data: @json($trendCompletions),
                            borderColor: '#10b981', // Green
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Assessments',
                            data: @json($trendAssessments),
                            borderColor: '#8b5cf6', // Purple
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: { 
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, 
                        x: { grid: { display: false } } 
                    },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false }
                }
            });
        }
    }

    initDashboardCharts();
</script>