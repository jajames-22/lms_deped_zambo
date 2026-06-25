<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="assessment-analytics-content" class="relative min-h-screen pb-12 bg-gray-50">
    <div class="p-6 pb-2 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 max-w-7xl mx-auto">
        <div>
            <button
                onclick="loadPartial('{{ route('dashboard.assessments.manage', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group mb-2 text-sm cursor-pointer border-0 bg-transparent">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Assessment
            </button>
            <h2 class="text-3xl font-bold text-gray-900">{{ $assessment->title }} Analytics</h2>
            <p class="text-gray-500 mt-1">Student Performance and Assessment Analysis (Completed Data Only).</p>
        </div>
        <button onclick="toggleExportModal()"
            class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0 whitespace-nowrap cursor-pointer">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    {{-- =========================================================================
    FLOATING ACTION BUTTON (QUICK NAVIGATION)
    ========================================================================= --}}
    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end pointer-events-none">
        <div id="fabMenu"
            class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div
                class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-64">
                <p
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">
                    Quick Navigation
                </p>
                <button onclick="scrollToSection('ui-executive-summary'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-chart-pie w-4 text-center"></i> Executive Summary
                </button>
                <button onclick="scrollToSection('ui-score-distribution'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-chart-area w-4 text-center"></i> Score Distribution
                </button>
                <button onclick="scrollToSection('ui-competency-breakdown'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-layer-group w-4 text-center"></i> Competency Breakdown
                </button>
                <button onclick="scrollToSection('ui-item-analysis'); toggleFabMenu();"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left bg-transparent border-0 cursor-pointer">
                    <i class="fas fa-microscope w-4 text-center"></i> Item Analysis
                </button>

            </div>
        </div>

        <button onclick="toggleFabMenu()"
            class="pointer-events-auto w-14 h-14 bg-[#111827] text-white rounded-full shadow-lg shadow-gray-900/30 flex items-center justify-center hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300 border-0 cursor-pointer">
            <i id="fabIcon" class="fas fa-list-ul text-xl transition-transform duration-300"></i>
        </button>
    </div>

    <div class="p-6 space-y-6 max-w-7xl mx-auto">

        {{-- Section: Executive Summary KPI --}}
        <section id="ui-executive-summary" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Executive Summary</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- 1. Division/Cohort MPS --}}
                <div
                    class="bg-gradient-to-br from-gray-900 to-gray-800 p-5 rounded-2xl shadow-sm text-white flex flex-col justify-center lg:col-span-1">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Overall MPS</p>
                        <i
                            class="fas fa-question-circle text-gray-500 text-[10px] transition-colors group-hover:text-gray-300"></i>
                        <div
                            class="absolute bottom-full left-0 mb-2 w-80 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 border border-gray-100">
                            <div
                                class="p-3 max-h-40 overflow-y-auto custom-scrollbar text-gray-700 text-[11px] leading-relaxed font-normal tracking-normal">
                                <strong class="text-gray-900 block mb-1">Mean Percentage Score (MPS)</strong>
                                The Mean Percentage Score (MPS) represents the average test performance of all students
                                who completed the assessment.

                                <div class="mt-2 pt-2 border-t border-gray-100">
                                    <strong class="text-gray-900 block mb-1">How it's calculated:</strong>
                                    <ol class="list-decimal pl-4 space-y-1">
                                        <li>Compute each student's percentage score:<br>
                                            <span class="text-gray-500 italic text-[10px]">Student Percentage = (Correct
                                                Answers &divide; Total Questions) &times; 100</span>
                                        </li>
                                        <li>Add all student percentages together.</li>
                                        <li>Divide by the number of completed students.</li>
                                    </ol>
                                </div>

                                <div class="mt-2 bg-gray-50 p-2.5 rounded border border-gray-100">
                                    <strong class="text-gray-900 block mb-1">Example:</strong>
                                    <p class="mb-1 text-gray-600">Assessment has 10 questions.</p>
                                    <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-600">
                                        <li>Student A: 8 correct &rarr; (8 &divide; 10) &times; 100 = 80%</li>
                                        <li>Student B: 6 correct &rarr; (6 &divide; 10) &times; 100 = 60%</li>
                                        <li>Student C: 4 correct &rarr; (4 &divide; 10) &times; 100 = 40%</li>
                                    </ul>
                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                        <p class="text-[10px] text-gray-600">Overall MPS: (80 + 60 + 40) &divide; 3 =
                                            60%</p>
                                        <p class="font-bold text-gray-900 mt-0.5">Result: The Overall MPS is 60%.</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-white">
                            </div>
                        </div>
                    </div>
                    <p class="text-4xl font-black text-white">{{ $overallMPS }}<span
                            class="text-xl text-gray-400">%</span></p>
                    <p class="text-[10px] text-gray-400 mt-1">Average student score</p>
                </div>

                {{-- 2. Descriptive Overall Mastery Level --}}
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">Descriptive Level</p>
                        <i
                            class="fas fa-question-circle text-gray-300 text-[10px] transition-colors group-hover:text-[#a52a2a]"></i>
                        <div
                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Mastery Category</strong>
                            Groups the students' overall average score into standard levels:
                            <ul class="space-y-1">
                                <li class="flex justify-between">
                                    <span class="text-green-600 font-bold">90% - 100%</span>
                                    <span>Highly Proficient</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="text-blue-600 font-bold">75% - 89%</span>
                                    <span>Proficient</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="text-amber-600 font-bold">50% - 74%</span>
                                    <span>Nearly Proficient</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="text-orange-600 font-bold">25% - 49%</span>
                                    <span>Low Proficient</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="text-red-600 font-bold">0% - 24%</span>
                                    <span>Not Proficient</span>
                                </li>
                            </ul>
                            <div
                                class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-gray-900">
                            </div>
                        </div>
                    </div>
                    <p class="text-lg font-black {{ $masteryColor }} leading-tight mb-1">{{ $overallMasteryLevel }}</p>
                    <p class="text-[10px] text-gray-500 mt-auto">Based on standard scale</p>
                </div>

                {{-- 3 Most Mastered Competency (MMC) --}}
                <div
                    class="bg-blue-50/50 p-5 rounded-2xl shadow-sm border border-blue-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-blue-700 text-[10px] font-bold uppercase tracking-wider">Most Mastered</p>
                        <i class="fas fa-check-circle text-blue-400 text-[10px]"></i>
                        <div
                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Highest Scoring Area</strong>
                            The specific subject or topic where the students scored the highest.
                            <div
                                class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-gray-900">
                            </div>
                        </div>
                    </div>
                    @if($mostMastered && ($completedCount ?? 0) > 0)
                        <p class="text-sm font-bold text-gray-900 line-clamp-2 leading-tight">{{ $mostMastered->title }}</p>
                        <p class="text-[10px] font-bold text-blue-600 mt-1">{{ $mostMastered->mps }}% Accuracy</p>
                    @else
                        <p class="text-xs text-gray-500 italic">Data pending</p>
                    @endif
                </div>

                {{-- 4. Participation & Completion --}}
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">Participation Rate</p>
                        <i
                            class="fas fa-question-circle text-gray-300 text-[10px] transition-colors group-hover:text-[#a52a2a]"></i>
                        <div
                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Student Participation</strong>
                            The percentage of assigned students who successfully finished the test.
                            <div
                                class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-gray-900">
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $completionRate }}<span
                            class="text-lg text-gray-400">%</span></p>
                    <p class="text-[10px] text-gray-500 mt-1 leading-tight">{{ $completedCount }} of
                        {{ $totalStudents }} takers finished
                    </p>
                </div>

                {{-- 5. Proficient Students Count --}}
                @php $proficientCount = ($proficiencyLevels['Highly Proficient (90-100%)'] ?? 0) + ($proficiencyLevels['Proficient (75-89%)'] ?? 0); @endphp
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">Proficient Students</p>
                        <i
                            class="fas fa-question-circle text-gray-300 text-[10px] transition-colors group-hover:text-[#a52a2a]"></i>
                        <div
                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Standard Met</strong>
                            The number of students who achieved a Highly Proficient or Proficient rating (scored 75% or
                            higher on the assessment).
                            <div
                                class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-gray-900">
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-blue-600">
                        {{ $proficientCount }} <span class="text-sm font-bold text-gray-400 tracking-wide uppercase">/
                            {{ $completedCount }} Completed</span>
                    </p>
                    <p class="text-[10px] text-gray-500 mt-1 leading-tight">
                        <strong>{{ $proficiencyRate ?? 0 }}%</strong> met the 75% standard.
                    </p>
                </div>

                {{-- 6. Least Mastered Competency (LMC) --}}
                <div class="bg-red-50/50 p-5 rounded-2xl shadow-sm border border-red-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-red-700 text-[10px] font-bold uppercase tracking-wider">Least Mastered</p>
                        <i class="fas fa-exclamation-triangle text-red-400 text-[10px]"></i>
                        <div
                            class="absolute bottom-full right-0 sm:left-1/2 sm:-translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Area for Improvement</strong>
                            The specific subject or topic with the lowest scores. This shows where students need more
                            help or review.
                            <div
                                class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-gray-900">
                            </div>
                        </div>
                    </div>
                    @if($leastMastered && ($completedCount ?? 0) > 0)
                        <p class="text-sm font-bold text-gray-900 line-clamp-2 leading-tight">{{ $leastMastered->title }}
                        </p>
                        <p class="text-[10px] font-bold text-red-600 mt-1">{{ $leastMastered->mps }}% Accuracy</p>
                    @else
                        <p class="text-xs text-gray-500 italic">Data pending</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- Section: Score Distribution, Proficiency & Time Analytics --}}
        <section id="ui-score-distribution" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4 border-b border-gray-200 pb-2">Score Distribution,
                Proficiency & Time Analytics</h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                    <div class="flex flex-col md:flex-row justify-between items-start mb-6 gap-4">
                        <div>
                            <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                                <h4 class="text-gray-900 text-lg font-bold mb-1">Class Proficiency Curve</h4>
                                <i
                                    class="fas fa-question-circle text-gray-300 text-sm transition-colors group-hover:text-[#a52a2a]"></i>
                                <div
                                    class="absolute bottom-full left-0 mb-2 w-72 p-4 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                                    <strong class="text-white block mb-1">Combined Insight</strong>
                                    This chart maps the frequency of student scores directly into their corresponding
                                    mastery categories, providing an immediate visual curve of class performance. Only
                                    completed tests are shown.
                                    <div
                                        class="absolute top-full left-6 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-gray-900">
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Frequency of completed student scores mapped to standard
                                proficiency levels.</p>
                        </div>
                    </div>

                    <div class="relative flex-1 w-full min-h-[300px] md:min-h-[350px] flex justify-center items-center">
                        @if(empty($combinedDistribution) || ($completedCount ?? 0) == 0)
                            <div class="flex flex-col items-center justify-center text-center p-6 w-full">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-chart-bar text-gray-300 text-3xl"></i>
                                </div>
                                <p class="text-base font-bold text-gray-700">No distribution data yet</p>
                                <p class="text-sm text-gray-500 mt-1">The proficiency curve will generate once students
                                    finish the test.</p>
                            </div>
                        @else
                            <canvas id="combinedDistributionChart"></canvas>
                        @endif
                    </div>
                </div>

                {{-- Category Time Analytics Card --}}
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                    <div class="flex flex-col md:flex-row justify-between items-start mb-6 gap-4">
                        <div>
                            <div class="flex flex-wrap justify-between gap-4">
                                <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                                    <h4 class="text-gray-900 text-lg font-bold mb-1">Average Time Allocation</h4>
                                    <i
                                        class="fas fa-question-circle text-gray-300 text-sm transition-colors group-hover:text-[#a52a2a]"></i>
                                    <div
                                        class="absolute bottom-full left-0 mb-2 w-72 p-4 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                                        <strong class="text-white block mb-1">Pacing Insight</strong>
                                        Displays the average duration completed test takers spent answering questions in
                                        each assessment section/category.
                                        <div
                                            class="absolute top-full left-6 w-0 h-0 border-x-[6px] border-x-transparent border-t-[8px] border-t-gray-900">
                                        </div>
                                    </div>
                                </div>
                                </d iv>
                                <div class="bg-amber-50 text-amber-800 px-4 py-2 rounded-xl text-xs font-bold border border-amber-100 shadow-sm">
                                    <i class="fas fa-clock mr-1"></i> Avg Overall: {{ $avgTimeFormat }}
                                </div>
                            </div>
                            
                            
                            <p class="text-xs text-gray-500">Average time allocation per category across completed attempts.</p>
                        </div>
                       
                    </div>

                    <div class="space-y-4 flex-1 flex flex-col justify-center py-2">
                        @if(empty($categoryTimeStats) || count($categoryTimeStats) == 0)
                            <div class="flex flex-col items-center justify-center text-center p-6 w-full h-full">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-hourglass text-gray-300 text-3xl"></i>
                                </div>
                                <p class="text-base font-bold text-gray-700">No category pacing data yet</p>
                                <p class="text-sm text-gray-500 mt-1">Time metrics will calculate once students submit attempts.</p>
                            </div>
                        @else
                            <div class="relative flex-1 w-full min-h-[300px] md:min-h-[350px] flex justify-center items-center">
                                <canvas id="categoryTimeChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Section: Competency & Benchmarking --}}
        <section id="ui-competency-breakdown" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4 border-b border-gray-200 pb-2">Competency Breakdown
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">

                {{-- Complete Competency Table --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex gap-2 items-center mb-1">
                                    <h4 class="text-gray-700 font-semibold">Full Competency Breakdown</h4>
                                    <div class="relative group cursor-help">
                                        <i
                                            class="fas fa-question-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                        <div
                                            class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-80 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] border border-gray-100">
                                            <div
                                                class="p-3 max-h-40 overflow-y-auto custom-scrollbar text-gray-700 text-[11px] leading-relaxed font-normal tracking-normal text-left">
                                                <strong class="text-gray-900 block mb-1">Competency Mean Percentage
                                                    Score (MPS)</strong>
                                                The Competency MPS shows how well students performed on a specific topic
                                                or subject area.

                                                <div class="mt-2 pt-2 border-t border-gray-100">
                                                    <strong class="text-gray-900 block mb-1">How it's
                                                        calculated:</strong>
                                                    <ol class="list-decimal pl-4 space-y-1">
                                                        <li>Count all correct answers for questions under this specific
                                                            topic.</li>
                                                        <li>Count the total possible answers (Total students &times;
                                                            Questions in topic).</li>
                                                        <li>Divide correct answers by total possible answers and
                                                            multiply by 100.<br>
                                                            <span class="text-gray-500 italic text-[10px]">Competency
                                                                MPS = (Correct Answers &divide; Total Possible Answers)
                                                                &times; 100</span>
                                                        </li>
                                                    </ol>
                                                </div>

                                                <div class="mt-2 bg-gray-50 p-2.5 rounded border border-gray-100">
                                                    <strong class="text-gray-900 block mb-1">Example:</strong>
                                                    <p class="mb-1 text-gray-600">"Topic A" has 5 questions. 10 students
                                                        took the test.</p>
                                                    <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-600">
                                                        <li>Total Possible Answers: 5 &times; 10 = 50</li>
                                                        <li>Total Correct Answers by all students: 35</li>
                                                    </ul>
                                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                                        <p class="text-[10px] text-gray-600">Competency MPS: (35
                                                            &divide; 50) &times; 100 = 70%</p>
                                                        <p class="font-bold text-gray-900 mt-0.5">Result: The Competency
                                                            MPS is 70%.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-b-[8px] border-b-white">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">Shows the average scores for each specific topic or
                                    subject area.</p>
                            </div>

                        </div>
                    </div>

                    @if(!isset($competencies) || count($competencies ?? []) === 0)
                        <div class="p-12 flex flex-col items-center justify-center text-center flex-1">
                            <p class="text-sm font-medium text-gray-600">No competency data available</p>
                        </div>
                    @else
                        <div id="competency-table-wrapper" class="flex-1 flex flex-col">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-4 w-1/2">Topic / Subject Name</th>
                                        <th class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                Score (Accuracy)
                                                <div class="relative group cursor-help">
                                                    <i
                                                        class="fas fa-info-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                                    <div
                                                        class="absolute top-full right-0 sm:left-1/2 sm:-translate-x-1/2 mt-2 w-64 bg-gray-900 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100]">
                                                        <div
                                                            class="p-3 max-h-60 overflow-y-auto custom-scrollbar text-gray-300 text-[11px] leading-relaxed font-normal tracking-normal text-left">
                                                            <strong class="text-green-400 block mb-1">Advanced (90% -
                                                                100%)</strong>
                                                            <p class="text-gray-400 mb-2 normal-case">Excellent mastery;
                                                                near-perfect accuracy.</p>
                                                            <strong class="text-green-300 block mb-1">Upper Intermediate
                                                                (75% - 89%)</strong>
                                                            <p class="text-gray-400 mb-2 normal-case">Strong understanding
                                                                with minor errors.</p>
                                                            <strong class="text-blue-400 block mb-1">Intermediate (60% -
                                                                74%)</strong>
                                                            <p class="text-gray-400 mb-2 normal-case">Solid understanding
                                                                but needs refinement.</p>
                                                            <strong class="text-amber-400 block mb-1">Basic (40% -
                                                                59%)</strong>
                                                            <p class="text-gray-400 mb-2 normal-case">Basic understanding;
                                                                inconsistent performance.</p>
                                                            <strong class="text-red-400 block mb-1">Beginner (0% -
                                                                39%)</strong>
                                                            <p class="text-gray-400 normal-case">Limited understanding;
                                                                requires support.</p>
                                                        </div>
                                                        <div
                                                            class="absolute bottom-full right-2 sm:left-1/2 sm:-translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-b-[8px] border-b-gray-900">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($competencies ?? [] as $cat)
                                        <tr class="competency-row hover:bg-gray-50/50 transition">
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $cat->title ?? 'Unknown' }}</td>
                                            <td class="px-6 py-4 text-center">
                                                @if(($cat->total_answers ?? 0) == 0)
                                                    <span
                                                        class="px-2.5 py-1 rounded-md text-xs font-bold text-gray-500 bg-gray-100 border border-gray-200">--</span>
                                                @else
                                                    <span
                                                        class="px-2.5 py-1 rounded-md text-xs font-bold {{ ($cat->mps ?? 0) >= 75 ? 'text-green-700 bg-green-100' : (($cat->mps ?? 0) < 40 ? 'text-red-700 bg-red-100' : 'text-amber-700 bg-amber-100') }}">
                                                        {{ $cat->mps ?? 0 }}%
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                @if(($cat->total_answers ?? 0) == 0)
                                                    <span class="text-[10px] text-gray-400 font-bold tracking-wider uppercase"><i
                                                            class="fas fa-hourglass-half"></i> No Data</span>
                                                @elseif(($cat->mps ?? 0) >= 90)
                                                    <span class="text-xs text-green-700 font-bold"><i class="fas fa-star"></i>
                                                        Advanced</span>
                                                @elseif(($cat->mps ?? 0) >= 75)
                                                    <span class="text-xs text-green-500 font-bold"><i
                                                            class="fas fa-check-circle"></i> Upper Intermediate</span>
                                                @elseif(($cat->mps ?? 0) >= 60)
                                                    <span class="text-xs text-blue-600 font-bold"><i class="fas fa-arrow-up"></i>
                                                        Intermediate</span>
                                                @elseif(($cat->mps ?? 0) >= 40)
                                                    <span class="text-xs text-amber-600 font-bold"><i
                                                            class="fas fa-minus-circle"></i> Basic</span>
                                                @else
                                                    <span class="text-xs text-red-600 font-bold"><i class="fas fa-times-circle"></i>
                                                        Beginner</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-12 text-center">
                                                <p class="text-sm font-medium text-gray-600">No competency data available</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- School Leaderboard --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div
                        class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-900 to-gray-800 text-white">
                        <div>
                            <div class="flex gap-2 items-center mb-1">
                                <h4 class="font-semibold">School Performance Benchmarking</h4>
                                <div class="relative group cursor-help">
                                    <i
                                        class="fas fa-question-circle text-gray-400 transition-colors group-hover:text-white"></i>
                                    <div
                                        class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-80 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] border border-gray-100">
                                        <div
                                            class="p-3 max-h-40 overflow-y-auto custom-scrollbar text-gray-700 text-[11px] leading-relaxed font-normal tracking-normal text-left">
                                            <strong class="text-gray-900 block mb-1">School Mean Percentage Score
                                                (MPS)</strong>
                                            The School MPS represents the average test performance of all students who
                                            completed the assessment from a specific school.

                                            <div class="mt-2 pt-2 border-t border-gray-100">
                                                <strong class="text-gray-900 block mb-1">How it's calculated:</strong>
                                                <ol class="list-decimal pl-4 space-y-1">
                                                    <li>Compute each student's percentage score:<br>
                                                        <span class="text-gray-500 italic text-[10px]">Student
                                                            Percentage = (Correct Answers &divide; Total Questions)
                                                            &times; 100</span>
                                                    </li>
                                                    <li>Add all student percentages from that school together.</li>
                                                    <li>Divide by the number of completed students in that school.</li>
                                                </ol>
                                            </div>

                                            <div class="mt-2 bg-gray-50 p-2.5 rounded border border-gray-100">
                                                <strong class="text-gray-900 block mb-1">Example:</strong>
                                                <p class="mb-1 text-gray-600">School X has 3 completed students on a
                                                    10-question test.</p>
                                                <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-600">
                                                    <li>Student A: 8 correct &rarr; (8 &divide; 10) &times; 100 = 80%
                                                    </li>
                                                    <li>Student B: 6 correct &rarr; (6 &divide; 10) &times; 100 = 60%
                                                    </li>
                                                    <li>Student C: 4 correct &rarr; (4 &divide; 10) &times; 100 = 40%
                                                    </li>
                                                </ul>
                                                <div class="mt-2 pt-2 border-t border-gray-200">
                                                    <p class="text-[10px] text-gray-600">School MPS: (80 + 60 + 40)
                                                        &divide; 3 = 60%</p>
                                                    <p class="font-bold text-gray-900 mt-0.5">Result: The School MPS is
                                                        60%.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="absolute bottom-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-b-[8px] border-b-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400">Compares and ranks schools based on their average scores.
                            </p>
                        </div>
                        <i class="fas fa-trophy text-3xl text-yellow-500 opacity-50"></i>
                    </div>

                    @if(!isset($schoolLeaderboard) || count($schoolLeaderboard ?? []) === 0)
                        <div class="p-12 flex flex-col items-center justify-center text-center flex-1">
                            <p class="text-sm font-medium text-gray-600">No school rankings available</p>
                        </div>
                    @else
                        <div id="school-table-wrapper" class="flex-1 flex flex-col">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-4 w-12 text-center">Rank</th>
                                        <th class="px-6 py-4">School Name</th>
                                        <th class="px-6 py-4 text-center">Takers</th>
                                        <th class="px-6 py-4 text-center">Score</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($schoolLeaderboard as $index => $school)
                                        <tr class="school-row hover:bg-gray-50/50 transition">
                                            <td
                                                class="px-6 py-4 text-center font-bold {{ $index < 3 ? 'text-[#a52a2a]' : 'text-gray-400' }}">
                                                {{ $index + 1 }}</td>
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $school->name ?? 'Unknown' }}
                                            </td>
                                            <td class="px-6 py-4 text-center text-gray-500">{{ $school->student_count ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 text-center font-bold text-gray-800">
                                                @if(($completedCount ?? 0) == 0)
                                                    <span class="text-gray-400 font-normal italic">--</span>
                                                @else
                                                    {{ $school->mps ?? 0 }}%
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Section: Item Analysis --}}
        <section id="ui-item-analysis" class="scroll-mt-20 mt-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Item Analysis</h3>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex gap-2 items-center mb-1">
                        <h4 class="text-gray-700 font-semibold">Item Statistics</h4>
                        <div class="relative group cursor-help">
                            <i
                                class="fas fa-question-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                            <div
                                class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-75 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] border border-gray-100">
                                <div
                                    class="p-3 max-h-40 overflow-y-auto custom-scrollbar text-gray-700 text-[11px] leading-relaxed font-normal tracking-normal text-left">
                                    <strong class="text-gray-900 block mb-1">Difficulty Index (p)</strong>
                                    The Difficulty Index measures how easy or difficult a specific question was based on
                                    the performance of all students who completed the assessment.

                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <strong class="text-gray-900 block mb-1">How it's calculated:</strong>
                                        <ol class="list-decimal pl-4 space-y-1">
                                            <li>Count the number of students who answered the question correctly.</li>
                                            <li>Divide it by the total number of students who answered the question.
                                            </li>
                                            <li>Multiply by 100 to get the percentage.<br>
                                                <span class="text-gray-500 italic text-[10px]">Difficulty Index =
                                                    (Correct Answers &divide; Total Answers) &times; 100</span>
                                            </li>
                                        </ol>
                                    </div>

                                    <div class="mt-2 bg-gray-50 p-2.5 rounded border border-gray-100">
                                        <strong class="text-gray-900 block mb-1">Example:</strong>
                                        <p class="mb-1 text-gray-600">A question was answered by 10 students.</p>
                                        <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-600">
                                            <li>Total Answers: 10</li>
                                            <li>Correct Answers: 6</li>
                                        </ul>
                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                            <p class="text-[10px] text-gray-600">Difficulty Index: (6 &divide; 10)
                                                &times; 100 = 60%</p>
                                            <p class="font-bold text-gray-900 mt-0.5">Result: The Difficulty Index is
                                                60% (Easy).</p>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-b-[8px] border-b-white">
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">Shows how difficult each question was and which answers the
                        completed students chose.</p>
                </div>

                @if(!isset($itemAnalysis) || count($itemAnalysis ?? []) === 0)
                    <div class="p-16 flex flex-col items-center justify-center text-center">
                        <p class="text-base font-medium text-gray-600">No items available to analyze</p>
                    </div>
                @else
                    <div id="item-table-wrapper" class="overflow-x-auto custom-scrollbar flex-1 flex flex-col">
                        <table class="w-full text-left text-sm text-gray-600 min-w-[800px]">
                            <thead
                                class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-6 py-4 w-12 text-center">Item</th>
                                    <th class="px-6 py-4 w-1/3">Question Base</th>
                                    <th class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            Difficulty Index (p)
                                            <div class="relative group cursor-help">
                                                <i
                                                    class="fas fa-question-circle text-gray-400 transition-colors group-hover:text-[#a52a2a]"></i>
                                                <div
                                                    class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-56 bg-gray-900 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100]">
                                                    <div
                                                        class="p-3 max-h-40 overflow-y-auto custom-scrollbar text-gray-300 text-[11px] leading-relaxed font-normal tracking-normal text-left">
                                                        <strong
                                                            class="text-white block mb-2 border-b border-gray-700 pb-1">Difficulty
                                                            Classification</strong>
                                                        <ul class="space-y-1">
                                                            <li class="flex justify-between"><span
                                                                    class="text-blue-400 font-bold">81% -
                                                                    100%</span><span>Very Easy</span></li>
                                                            <li class="flex justify-between"><span
                                                                    class="text-green-400 font-bold">61% -
                                                                    80%</span><span>Easy</span></li>
                                                            <li class="flex justify-between"><span
                                                                    class="text-amber-400 font-bold">41% -
                                                                    60%</span><span>Average</span></li>
                                                            <li class="flex justify-between"><span
                                                                    class="text-orange-400 font-bold">21% -
                                                                    40%</span><span>Difficult</span></li>
                                                            <li class="flex justify-between"><span
                                                                    class="text-red-400 font-bold">0% - 20%</span><span>Very
                                                                    Difficult</span></li>
                                                        </ul>
                                                    </div>
                                                    <div
                                                        class="absolute bottom-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-[6px] border-x-transparent border-b-[8px] border-b-gray-900">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 w-1/2 text-center">Answers Chosen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($itemAnalysis as $index => $item)
                                    @php
                                        $correct = $item->correct_count ?? 0;
                                        $wrong = $item->wrong_count ?? 0;
                                        $totalItemAnswers = $correct + $wrong;
                                    @endphp
                                    <tr class="item-row hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <p class="text-gray-900 font-medium line-clamp-2 mb-1">
                                                {!! strip_tags($item->question_text ?? '') !!}</p>
                                            <span
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $item->category_name ?? '' }}</span>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center">
                                                @if($totalItemAnswers == 0)
                                                    <span class="font-bold text-gray-400 text-base mb-1">--</span>
                                                    <span class="text-[10px] text-gray-400 font-bold tracking-wider uppercase"><i
                                                            class="fas fa-hourglass-half"></i> No Data</span>
                                                @else
                                                    <span
                                                        class="font-bold text-gray-900 text-base mb-1">{{ $item->difficulty_index ?? 0 }}%</span>
                                                    <div class="flex gap-2 text-[10px] font-bold text-gray-400 mb-1">
                                                        <span class="text-green-600"><i class="fas fa-check"></i>
                                                            {{ $correct }}</span>
                                                        <span class="text-red-500"><i class="fas fa-times"></i> {{ $wrong }}</span>
                                                    </div>
                                                    @if(($item->difficulty_index ?? 0) >= 81) <span
                                                        class="text-[10px] text-blue-500 font-bold uppercase">Very Easy</span>
                                                    @elseif(($item->difficulty_index ?? 0) >= 61) <span
                                                        class="text-[10px] text-green-500 font-bold uppercase">Easy</span>
                                                    @elseif(($item->difficulty_index ?? 0) >= 41) <span
                                                        class="text-[10px] text-amber-500 font-bold uppercase">Average</span>
                                                    @elseif(($item->difficulty_index ?? 0) >= 21) <span
                                                        class="text-[10px] text-orange-500 font-bold uppercase">Difficult</span>
                                                    @else <span class="text-[10px] text-red-500 font-bold uppercase">Very
                                                        Difficult</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            @if($totalItemAnswers == 0)
                                                <div class="flex items-center gap-2 text-gray-400 text-xs italic">
                                                    Waiting for student responses...
                                                </div>
                                            @else
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($item->distractor_stats ?? [] as $opt)
                                                        @php
                                                            $isCorrectOpt = $opt->is_correct ?? false;
                                                            $optPct = $opt->pct ?? 0;
                                                            $optText = $opt->text ?? '';
                                                            $isDeadDistractor = (!$isCorrectOpt && $optPct == 0);
                                                        @endphp
                                                        <span
                                                            class="px-2 py-1 text-[10px] rounded border {{ $isCorrectOpt ? 'bg-green-50 border-green-200 text-green-700 font-bold shadow-sm' : ($isDeadDistractor ? 'bg-gray-100 border-dashed border-gray-300 text-gray-400 opacity-70' : 'bg-red-50 border-red-100 text-red-600') }}"
                                                            title="{{ $isDeadDistractor ? 'Unused Answer: No student selected this.' : '' }}">
                                                            {!! \Illuminate\Support\Str::limit(strip_tags($optText), 40) !!}:
                                                            {{ $optPct }}%
                                                            @if($isCorrectOpt) <i class="fas fa-check ml-1"></i> @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

    </div>
</div>

{{-- =========================================================================
EXPORT MODAL WITH CHECKBOXES
========================================================================= --}}
<div id="exportModal"
    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300"
        id="exportModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Export Report</h3>
            <button onclick="toggleExportModal()"
                class="text-gray-400 hover:text-gray-600 border-0 bg-transparent cursor-pointer"><i
                    class="fas fa-times text-lg"></i></button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Select the sections to include in the report:</p>
        <form action="{{ route('dashboard.assessments.export', $assessment->id) }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_overview" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Executive Summary & Scores</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_category" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Competency & Mastery Levels</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_item_analysis" checked
                        class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Full Item Analysis Data</span>
                </label>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="toggleExportModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 rounded-xl font-medium border-0 transition-colors cursor-pointer">Cancel</button>
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleExportModal, 500)"
                    class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-2.5 rounded-xl font-medium border-0 transition-colors flex items-center justify-center gap-2 cursor-pointer"><i
                        class="fas fa-print"></i> Print</button>
                <button type="submit" name="action" value="pdf" onclick="setTimeout(toggleExportModal, 500)"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-medium border-0 transition-colors flex items-center justify-center gap-2 cursor-pointer"><i
                        class="fas fa-file-pdf"></i> Download</button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #e5e7eb;
        border-radius: 10px;
    }
</style>

<script>
    // ==========================================
    // Navigation & Modal Logic
    // ==========================================
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
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

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

    // ==========================================
    // Chart Initialization
    // ==========================================
    window.assessmentCharts = window.assessmentCharts || {};

    function initAssessmentCharts() {
        // Destroy old instances to prevent overlaps on navigation
        if (window.assessmentCharts.combined) window.assessmentCharts.combined.destroy();
        if (window.assessmentCharts.time) window.assessmentCharts.time.destroy();

        // Combined Proficiency & Score Distribution Chart
        @if(!empty($combinedDistribution))
            const ctxDist = document.getElementById('combinedDistributionChart');
            if (ctxDist) {
                const combinedData = @json($combinedDistribution);

                window.assessmentCharts.combined = new Chart(ctxDist.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: combinedData.map(d => [d.raw, d.range]),
                        datasets: [{
                            label: 'Number of Students',
                            data: combinedData.map(d => d.count),
                            backgroundColor: combinedData.map(d => d.color),
                            borderRadius: 6,
                            barPercentage: 0.9,
                            categoryPercentage: 1.0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'nearest',
                            intersect: true,
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 12 },
                                padding: 12,
                                callbacks: {
                                    title: function (context) {
                                        return context[0].raw + ' Student(s)';
                                    },
                                    label: function (context) {
                                        const idx = context.dataIndex;
                                        return combinedData[idx].level + ' (' + combinedData[idx].range + ')';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 12 } },
                                title: { display: true, text: 'Score Range (%)', font: { size: 13, weight: 'bold' }, color: '#4b5563' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 12 } },
                                title: { display: true, text: 'Number of Students', font: { size: 13, weight: 'bold' }, color: '#4b5563' },
                                grid: { color: 'rgba(0,0,0,0.04)' }
                            }
                        }
                    }
                });
            }
        @endif

        // Category Time Allocation vs Limit Chart
        @if(!empty($categoryTimeStats) && count($categoryTimeStats) > 0)
            const ctxTime = document.getElementById('categoryTimeChart');
            if (ctxTime) {
                const timeData = @json($categoryTimeStats);

                window.assessmentCharts.time = new Chart(ctxTime.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: timeData.map(d => d.title.length > 20 ? d.title.substring(0, 20) + '...' : d.title),
                        datasets: [
                            {
                                label: 'Avg Time Spent (mins)',
                                data: timeData.map(d => d.mins),
                                backgroundColor: '#a52a2a',
                                borderRadius: 6,
                                barPercentage: 0.7,
                                categoryPercentage: 0.8
                            },
                            {
                                label: 'Time Limit (mins)',
                                data: timeData.map(d => d.time_limit),
                                backgroundColor: '#d97706',
                                borderRadius: 6,
                                barPercentage: 0.7,
                                categoryPercentage: 0.8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { font: { size: 12, weight: 'bold' }, usePointStyle: true, boxWidth: 8 }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 12 },
                                padding: 12,
                                callbacks: {
                                    title: function (context) {
                                        const idx = context[0].dataIndex;
                                        return timeData[idx].title;
                                    },
                                    label: function (context) {
                                        const dsIdx = context.datasetIndex;
                                        const val = context.raw;
                                        if (dsIdx === 0) {
                                            const format = timeData[context.dataIndex].format;
                                            return 'Avg Time Spent: ' + val + ' mins (' + format + ')';
                                        } else {
                                            return 'Time Limit: ' + (val > 0 ? val + ' mins' : 'No Limit');
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } },
                                title: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { font: { size: 12 } },
                                title: { display: true, text: 'Duration (Minutes)', font: { size: 13, weight: 'bold' }, color: '#4b5563' },
                                grid: { color: 'rgba(0,0,0,0.04)' }
                            }
                        }
                    }
                });
            }
        @endif
    }

    initAssessmentCharts();

    @include('dashboard.partials.shared.analytics-pagination')

    setTimeout(() => {
        setupTablePagination('competencyTable', 'competency-table-wrapper', '.competency-row', null, 5);
        setupTablePagination('schoolTable', 'school-table-wrapper', '.school-row', null, 5);
        setupTablePagination('itemTable', 'item-table-wrapper', '.item-row', null, 5);
    }, 100);
</script>