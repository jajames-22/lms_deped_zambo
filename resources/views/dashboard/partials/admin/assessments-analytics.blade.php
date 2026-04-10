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
            <p class="text-gray-500 mt-1">Division-Level Performance and Assessment Analysis.</p>
        </div>
        <button onclick="toggleExportModal()" class="bg-[#a52a2a] text-white px-5 py-2.5 rounded-xl shadow-sm hover:bg-red-900 flex items-center gap-2 transition-all text-sm font-bold border-0 whitespace-nowrap">
            <i class="fas fa-file-export text-white/80"></i> Generate Report
        </button>
    </div>

    {{-- =========================================================================
         FLOATING ACTION BUTTON (QUICK NAVIGATION)
         ========================================================================= --}}
    <div class="fixed bottom-8 right-8 z-50 flex flex-col items-end">
        <div id="fabMenu" class="opacity-0 translate-y-4 pointer-events-none transition-all duration-300 ease-in-out mb-4 flex flex-col gap-2 origin-bottom">
            <div class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-3 flex flex-col gap-1 w-64">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 pb-2 mb-1 border-b border-gray-100">Quick Navigation</p>
                
                <button onclick="scrollToSection('ui-executive-summary'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-chart-pie w-4 text-center"></i> Executive Summary
                </button>
                <button onclick="scrollToSection('ui-proficiency-pacing'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-tasks w-4 text-center"></i> Proficiency & Pacing
                </button>
                <button onclick="scrollToSection('ui-competency-breakdown'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-layer-group w-4 text-center"></i> Competency Breakdown
                </button>
                <button onclick="scrollToSection('ui-item-analysis'); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:text-[#a52a2a] hover:bg-red-50 rounded-xl transition-all text-left">
                    <i class="fas fa-microscope w-4 text-center"></i> Item Analysis
                </button>
                
                <button onclick="scrollToSection('assessment-analytics-content', true); toggleFabMenu();" class="flex items-center gap-3 px-3 py-2 mt-1 text-xs font-semibold text-gray-400 hover:text-gray-800 bg-gray-50 rounded-xl transition-all text-left justify-center border border-gray-200">
                    <i class="fas fa-arrow-up"></i> Back to Top
                </button>
            </div>
        </div>

        <button onclick="toggleFabMenu()" class="w-14 h-14 bg-[#111827] text-white rounded-full shadow-lg shadow-gray-900/30 flex items-center justify-center hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300">
            <i id="fabIcon" class="fas fa-list-ul text-xl transition-transform duration-300"></i>
        </button>
    </div>

    <div class="p-6 space-y-6 max-w-7xl mx-auto">

        {{-- Section: Executive Summary KPI --}}
        <section id="ui-executive-summary" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Executive Summary</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                {{-- 1. Division/Cohort MPS --}}
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-5 rounded-2xl shadow-sm text-white flex flex-col justify-center lg:col-span-1">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Overall MPS</p>
                        <i class="fas fa-question-circle text-gray-500 text-[10px] transition-colors group-hover:text-gray-300"></i>
                        <div class="absolute bottom-full left-0 mb-2 w-56 p-3 bg-white text-gray-700 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 border border-gray-100 font-normal tracking-normal pointer-events-none">
                            <strong class="text-gray-900 block mb-1">Mean Percentage Score</strong>
                            The official average score of the entire test-taking cohort. The standard target for Division mastery is generally 75%.
                        </div>
                    </div>
                    <p class="text-4xl font-black text-white">{{ $overallMPS }}<span class="text-xl text-gray-400">%</span></p>
                    <p class="text-[10px] text-gray-400 mt-1">Average cohort score</p>
                </div>

                {{-- 2. Descriptive Overall Mastery Level --}}
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">Descriptive Level</p>
                        <i class="fas fa-question-circle text-gray-300 text-[10px] transition-colors group-hover:text-[#a52a2a]"></i>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Standardized Mastery Category</strong>
                            Classifies the cohort's overall MPS into national standard proficiency brackets (e.g., Highly Proficient, Low Proficient).
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                    <p class="text-lg font-black {{ $masteryColor }} leading-tight mb-1">{{ $overallMasteryLevel }}</p>
                    <p class="text-[10px] text-gray-500 mt-auto">Based on standard scale</p>
                </div>

                

                {{-- 3 Most Mastered Competency (MMC) --}}
                <div class="bg-blue-50/50 p-5 rounded-2xl shadow-sm border border-blue-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-blue-700 text-[10px] font-bold uppercase tracking-wider">Most Mastered</p>
                        <i class="fas fa-check-circle text-blue-400 text-[10px]"></i>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Greatest Academic Strength</strong>
                            The specific subject or competency category where the cohort scored the highest.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                    @if($mostMastered)
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
                        <i class="fas fa-question-circle text-gray-300 text-[10px] transition-colors group-hover:text-[#a52a2a]"></i>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Cohort Engagement</strong>
                            The percentage of enrolled/whitelisted students who successfully finished the assessment.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $completionRate }}<span class="text-lg text-gray-400">%</span></p>
                    <p class="text-[10px] text-gray-500 mt-1 leading-tight">{{ $completedCount }} of {{ $totalStudents }} takers</p>
                </div>
                                
                {{-- 5. Proficiency Rate --}}
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">Proficiency Rate</p>
                        <i class="fas fa-question-circle text-gray-300 text-[10px] transition-colors group-hover:text-[#a52a2a]"></i>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Standard Met</strong>
                            Percentage of test-takers who successfully met the 75% standard proficiency score.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-blue-600">{{ $proficiencyRate ?? 0 }}<span class="text-lg text-gray-400">%</span></p>
                    <p class="text-[10px] text-gray-500 mt-1 leading-tight">Met 75% standard</p>
                </div>


                {{-- 6. Least Mastered Competency (LMC) --}}
                <div class="bg-red-50/50 p-5 rounded-2xl shadow-sm border border-red-100 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-1 relative group w-fit cursor-help">
                        <p class="text-red-700 text-[10px] font-bold uppercase tracking-wider">Least Mastered</p>
                        <i class="fas fa-exclamation-triangle text-red-400 text-[10px]"></i>
                        <div class="absolute bottom-full right-0 sm:left-1/2 sm:-translate-x-1/2 mb-2 w-56 p-3 bg-gray-900 text-gray-300 text-[11px] leading-relaxed rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 font-normal normal-case tracking-normal pointer-events-none">
                            <strong class="text-white block mb-1">Priority Intervention Focus</strong>
                            The specific subject or competency category with the lowest scores. This dictates where reteaching efforts must be focused.
                            <div class="absolute top-full right-4 sm:left-1/2 sm:-translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                    @if($leastMastered)
                        <p class="text-sm font-bold text-gray-900 line-clamp-2 leading-tight">{{ $leastMastered->title }}</p>
                        <p class="text-[10px] font-bold text-red-600 mt-1">{{ $leastMastered->mps }}% Accuracy</p>
                    @else
                        <p class="text-xs text-gray-500 italic">Data pending</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- Section: Proficiency & Pacing --}}
        <section id="ui-proficiency-pacing" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4 border-b border-gray-200 pb-2">Proficiency & Pacing</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                {{-- Proficiency Level Distribution --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="text-gray-700 font-semibold mb-1">Proficiency Level Distribution</h4>
                            <p class="text-[11px] text-gray-500 mb-4">Categorization of test-takers based on standardized mastery tiers.</p>
                        </div>
                        <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg text-xs font-bold border border-blue-100 whitespace-nowrap">
                            {{ number_format($completedCount ?? 0) }} Takers
                        </div>
                    </div>
                    <div class="relative flex-1 w-full min-h-[220px] flex justify-center items-center">
                        @if(($completedCount ?? 0) == 0)
                            <p class="text-gray-400 text-sm">No submissions yet.</p>
                        @else
                            <canvas id="proficiencyChart"></canvas>
                        @endif
                    </div>
                </div>

                {{-- Analytical Insights: Pacing vs Accuracy Scatterplot --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="text-gray-700 font-semibold mb-1">Pacing vs. Accuracy Insight</h4>
                            <p class="text-[11px] text-gray-500 mb-4">Categorization of test-takers based on test duration and final score.</p>
                        </div>
                        <div class="bg-gray-50 text-gray-700 px-3 py-1 rounded-lg text-xs font-bold border border-gray-200 whitespace-nowrap" title="Average Pacing Time">
                            <i class="fas fa-stopwatch mr-1 text-gray-400"></i> Avg: {{ $avgTimeFormat }}
                        </div>
                    </div>
                    
                    <div class="relative flex-1 w-full min-h-[220px] flex justify-center items-center">
                        @if(empty($scatterData))
                            <p class="text-gray-400 text-sm">No pacing data available yet.</p>
                        @else
                            <canvas id="pacingScatterChart"></canvas>
                        @endif
                    </div>

                    {{-- Legend for Quadrants --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4">
                        <div class="flex items-center gap-1.5 bg-green-50/50 px-2 py-1.5 rounded border border-green-100">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span>
                            <span class="text-[10px] font-semibold text-green-700 leading-tight">Fast & Accurate</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-blue-50/50 px-2 py-1.5 rounded border border-blue-100">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span>
                            <span class="text-[10px] font-semibold text-blue-700 leading-tight">Slow & Accurate</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-red-50/50 px-2 py-1.5 rounded border border-red-100">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span>
                            <span class="text-[10px] font-semibold text-red-700 leading-tight">Fast & Inaccurate</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-amber-50/50 px-2 py-1.5 rounded border border-amber-100">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                            <span class="text-[10px] font-semibold text-amber-700 leading-tight">Slow & Inaccurate</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Section: Competency & Benchmarking --}}
        <section id="ui-competency-breakdown" class="scroll-mt-20">
            <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4 border-b border-gray-200 pb-2">Competency Breakdown</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                {{-- Complete Competency Table --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100">
                        <h4 class="text-gray-700 font-semibold mb-1">Full Competency Breakdown</h4>
                        <p class="text-xs text-gray-400">Mean Percentage Score (MPS) across all tested subject areas.</p>
                    </div>
                    <div class="overflow-y-auto max-h-[350px]">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-6 py-4 w-1/2">Competency / Section Name</th>
                                    <th class="px-6 py-4 text-center">MPS (Accuracy)</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($competencies ?? [] as $cat)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $cat->title }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $cat->mps >= 75 ? 'text-green-700 bg-green-100' : ($cat->mps <= 40 ? 'text-red-700 bg-red-100' : 'text-amber-700 bg-amber-100') }}">{{ $cat->mps }}%</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($cat->mps >= 75) <span class="text-xs text-green-600 font-bold"><i class="fas fa-check-circle"></i> Mastered</span>
                                            @elseif($cat->mps >= 50) <span class="text-xs text-amber-600 font-bold"><i class="fas fa-minus-circle"></i> Review</span>
                                            @else <span class="text-xs text-red-600 font-bold"><i class="fas fa-times-circle"></i> Least Learned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">No competency data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- School Leaderboard --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-900 to-gray-800 text-white">
                        <div>
                            <h4 class="font-semibold mb-1">School Performance Benchmarking</h4>
                            <p class="text-xs text-gray-400">Comparative ranking of schools based on their MPS.</p>
                        </div>
                        <i class="fas fa-trophy text-3xl text-yellow-500 opacity-50"></i>
                    </div>
                    <div class="overflow-y-auto max-h-[350px]">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-6 py-4 w-12 text-center">Rank</th>
                                    <th class="px-6 py-4">School Name</th>
                                    <th class="px-6 py-4 text-center">Takers</th>
                                    <th class="px-6 py-4 text-center">MPS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($schoolLeaderboard ?? [] as $index => $school)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 text-center font-bold {{ $index < 3 ? 'text-[#a52a2a]' : 'text-gray-400' }}">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $school->name }}</td>
                                        <td class="px-6 py-4 text-center text-gray-500">{{ $school->student_count }}</td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-800">{{ $school->mps }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No school data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        {{-- Section: Item Analysis --}}
        <section id="ui-item-analysis" class="scroll-mt-20 mt-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Item Analysis</h3>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h4 class="text-gray-700 font-semibold mb-1">Item Statistics</h4>
                    <p class="text-xs text-gray-400">Evaluation of individual test items and response distributions.</p>
                </div>
                <div class="overflow-x-auto max-h-[600px] custom-scrollbar">
                    <table class="w-full text-left text-sm text-gray-600 min-w-[800px]">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-6 py-4 w-12 text-center">Item</th>
                                <th class="px-6 py-4 w-1/3">Question Base</th>
                                <th class="px-6 py-4 text-center">
                                    Difficulty Index (p)
                                    <i class="fas fa-question-circle text-gray-400 ml-1 cursor-help" title="Percentage of students who answered correctly."></i>
                                </th>
                                <th class="px-6 py-4 w-1/2">
                                    Response Distribution (Distractors)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($itemAnalysis ?? [] as $index => $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-900 font-medium line-clamp-2 mb-1">{!! strip_tags($item->question_text) !!}</p>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $item->category_name }}</span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="font-bold text-gray-900 text-base mb-1">{{ $item->difficulty_index }}%</span>
                                            <div class="flex gap-2 text-[10px] font-bold text-gray-400 mb-1">
                                                <span class="text-green-600"><i class="fas fa-check"></i> {{ $item->correct_count }}</span>
                                                <span class="text-red-500"><i class="fas fa-times"></i> {{ $item->wrong_count }}</span>
                                            </div>
                                            @if($item->difficulty_index >= 81) <span class="text-[10px] text-blue-500 font-bold uppercase">Very Easy</span>
                                            @elseif($item->difficulty_index >= 61) <span class="text-[10px] text-green-500 font-bold uppercase">Easy</span>
                                            @elseif($item->difficulty_index >= 41) <span class="text-[10px] text-amber-500 font-bold uppercase">Average</span>
                                            @elseif($item->difficulty_index >= 21) <span class="text-[10px] text-orange-500 font-bold uppercase">Difficult</span>
                                            @else <span class="text-[10px] text-red-500 font-bold uppercase">Very Difficult</span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($item->distractor_stats as $opt)
                                                @php
                                                    $isCorrect = $opt->is_correct;
                                                    $isDeadDistractor = (!$isCorrect && $opt->pct == 0);
                                                @endphp
                                                <span class="px-2 py-1 text-[10px] rounded border {{ $isCorrect ? 'bg-green-50 border-green-200 text-green-700 font-bold shadow-sm' : ($isDeadDistractor ? 'bg-gray-100 border-dashed border-gray-300 text-gray-400 opacity-70' : 'bg-red-50 border-red-100 text-red-600') }}"
                                                      title="{{ $isDeadDistractor ? 'Dead Distractor: No student selected this.' : '' }}">
                                                    {!! \Illuminate\Support\Str::limit(strip_tags($opt->text), 40) !!}: {{ $opt->pct }}%
                                                    @if($isCorrect) <i class="fas fa-check ml-1"></i> @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">No item data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</div>

{{-- =========================================================================
     EXPORT MODAL WITH CHECKBOXES
     ========================================================================= --}}
<div id="exportModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="exportModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Export Report</h3>
            <button onclick="toggleExportModal()" class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i class="fas fa-times text-lg"></i></button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Select the sections to include in the report:</p>
        <form action="{{ route('dashboard.assessments.export', $assessment->id) }}" method="GET" target="_blank">
            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_overview" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Executive Summary & Scores</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_category" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Competency & Mastery Levels</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="check_item_analysis" checked class="w-5 h-5 text-[#a52a2a] rounded border-gray-300 focus:ring-[#a52a2a]">
                    <span class="text-gray-700 font-medium">Full Item Analysis Data</span>
                </label>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="toggleExportModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 rounded-xl font-medium border-0 transition-colors">Cancel</button>
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-2.5 rounded-xl font-medium border-0 transition-colors flex items-center justify-center gap-2"><i class="fas fa-print"></i> Print</button>
                <button type="submit" name="action" value="pdf" onclick="setTimeout(toggleExportModal, 500)" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-medium border-0 transition-colors flex items-center justify-center gap-2"><i class="fas fa-file-pdf"></i> Download</button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 10px; }
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
        if (window.assessmentCharts.proficiency) window.assessmentCharts.proficiency.destroy();
        if (window.assessmentCharts.scatter) window.assessmentCharts.scatter.destroy();

        @if(($completedCount ?? 0) > 0)
            const ctxProf = document.getElementById('proficiencyChart');
            if (ctxProf) {
                window.assessmentCharts.proficiency = new Chart(ctxProf.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: [
                            'Highly Proficient', 
                            'Proficient', 
                            'Nearly Proficient', 
                            'Low Proficient', 
                            'Not Proficient'
                        ],
                        datasets: [{
                            data: [
                                {{ $proficiencyLevels['Highly Proficient (90-100%)'] ?? 0 }}, 
                                {{ $proficiencyLevels['Proficient (75-89%)'] ?? 0 }}, 
                                {{ $proficiencyLevels['Nearly Proficient (50-74%)'] ?? 0 }},
                                {{ $proficiencyLevels['Low Proficient (25-49%)'] ?? 0 }}, 
                                {{ $proficiencyLevels['Not Proficient (0-24%)'] ?? 0 }}
                            ],
                            backgroundColor: [
                                '#10b981', '#3b82f6', '#f59e0b', '#f97316', '#ef4444' 
                            ], 
                            borderRadius: 4, 
                            borderWidth: 0
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { legend: { display: false } }, 
                        scales: { 
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }, 
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } } 
                        } 
                    }
                });
            }
        @endif

        @if(!empty($scatterData))
            const ctxScatter = document.getElementById('pacingScatterChart');
            if (ctxScatter) {
                const scatterData = @json($scatterData);
                const avgTime = {{ $avgTimeMins ?? 0 }};
                const avgScore = {{ $overallMPS ?? 0 }};

                window.assessmentCharts.scatter = new Chart(ctxScatter.getContext('2d'), {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Student Performance',
                            data: scatterData,
                            backgroundColor: function(context) {
                                const point = context.raw;
                                if (!point) return '#9ca3af';
                                if (point.x < avgTime && point.y >= avgScore) return '#10b981'; // Fast & Accurate
                                if (point.x >= avgTime && point.y >= avgScore) return '#3b82f6'; // Slow & Accurate
                                if (point.x < avgTime && point.y < avgScore) return '#ef4444'; // Fast & Inaccurate
                                if (point.x >= avgTime && point.y < avgScore) return '#f59e0b'; // Slow & Inaccurate
                            },
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 1,
                            borderColor: 'white'
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Time: ${context.raw.x}m, Score: ${context.raw.y}%`;
                                    }
                                }
                            }
                        }, 
                        scales: { 
                            x: { 
                                title: { display: true, text: 'Time Taken (Minutes)', font: { size: 10, weight: 'bold' }, color: '#6b7280' },
                                grid: { color: (context) => context.tick.value === Math.round(avgTime) ? 'rgba(0,0,0,0.1)' : 'rgba(0,0,0,0.03)', lineWidth: (context) => context.tick.value === Math.round(avgTime) ? 2 : 1 },
                                ticks: { font: { size: 10 } } 
                            },
                            y: { 
                                title: { display: true, text: 'Score (%)', font: { size: 10, weight: 'bold' }, color: '#6b7280' },
                                beginAtZero: true, 
                                max: 100,
                                grid: { color: (context) => context.tick.value === Math.round(avgScore) ? 'rgba(0,0,0,0.1)' : 'rgba(0,0,0,0.03)', lineWidth: (context) => context.tick.value === Math.round(avgScore) ? 2 : 1 },
                                ticks: { stepSize: 20, font: { size: 10 } } 
                            } 
                        } 
                    }
                });
            }
        @endif
    }
    initAssessmentCharts();
</script>