<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Report: {{ $material->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans text-gray-900 min-h-screen p-4 sm:p-8 flex flex-col items-center selection:bg-red-900 selection:text-white">

    {{-- TOP BACK BUTTON --}}
    <div class="w-full max-w-4xl mb-4 print:hidden">
        {{-- Linking to /dashboard automatically re-opens the SPA on the last active tab (Manage Tab) --}}
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center text-gray-600 hover:text-red-900 font-bold transition-colors bg-white px-5 py-2.5 rounded-xl shadow-sm border border-gray-200 hover:bg-red-50">
            <i class="fas fa-arrow-left mr-2"></i> Back to Manage
        </a>
    </div>

    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden animate-[fadeIn_0.5s_ease-out]">
        
        {{-- HEADER --}}
        @php
            $isPublished = $material->status === 'published';
            $headerBg = $isPublished ? 'bg-green-600' : 'bg-red-900';
            $statusText = $isPublished ? 'Approved & Published' : 'Returned to Draft (Needs Revision)';
            $statusIcon = $isPublished ? 'fa-check-circle' : 'fa-undo';
        @endphp

        <div class="{{ $headerBg }} text-white p-8 md:p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-bl-full pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-lg text-xs font-black uppercase tracking-widest mb-4 backdrop-blur-sm shadow-sm">
                        <i class="fas {{ $statusIcon }}"></i> {{ $statusText }}
                    </span>
                    <h1 class="text-3xl md:text-4xl font-black mb-2">{{ $material->title }}</h1>
                    <p class="text-white/80 font-medium flex items-center gap-2">
                        <i class="fas fa-chalkboard-teacher"></i> Instructor: {{ $material->instructor->first_name ?? 'N/A' }} {{ $material->instructor->last_name ?? '' }}
                    </p>
                </div>

                <div class="text-center bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl shrink-0 w-40">
                    <p class="text-xs font-bold uppercase tracking-widest text-white/70 mb-1">Final Score</p>
                    <p class="text-4xl font-black">{{ $evaluationData['score_percentage'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="p-8 md:p-10 space-y-10">
            
            {{-- ADMIN REMARKS --}}
            <section>
                <h3 class="text-lg font-black text-gray-900 mb-4 flex items-center gap-2 border-b border-gray-100 pb-2">
                    <i class="fas fa-comment-dots text-red-900"></i> Evaluator Remarks & Feedback
                </h3>
                @if($material->admin_remarks)
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-gray-700 leading-relaxed italic relative">
                        <i class="fas fa-quote-left absolute top-6 left-6 text-gray-200 text-3xl"></i>
                        <p class="relative z-10 pl-10">{{ $material->admin_remarks }}</p>
                    </div>
                @else
                    <p class="text-gray-500 italic bg-gray-50 p-4 rounded-xl text-sm">No remarks provided by the evaluator.</p>
                @endif
            </section>

            {{-- CATEGORIZED RUBRIC BREAKDOWN --}}
            <section>
                <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-2">
                    <i class="fas fa-list-check text-red-900"></i> Detailed Score Breakdown
                </h3>
                
                @php
                    // Use Laravel's Collection to easily group the flat JSON array by 'category'
                    $details = collect($evaluationData['details'] ?? []);
                    $groupedDetails = $details->groupBy(function($item) {
                        return $item['category'] ?? 'General Criteria';
                    });
                @endphp

                @if($groupedDetails->isNotEmpty())
                    <div class="space-y-8">
                        @foreach($groupedDetails as $category => $items)
                            <div>
                                {{-- CATEGORY HEADER --}}
                                <h4 class="text-xs font-black text-red-900 uppercase tracking-widest mb-3 bg-red-50 border border-red-100 inline-block px-3 py-1.5 rounded-lg shadow-sm">{{ $category }}</h4>
                                
                                <div class="space-y-3">
                                    @foreach($items as $index => $item)
                                        <div class="flex items-center justify-between p-4 rounded-xl border {{ $item['score'] < 3 ? 'bg-red-50 border-red-200' : 'bg-white border-gray-200 hover:border-gray-300' }} transition-colors">
                                            <div class="flex gap-4 items-start pr-6">
                                                
                                                {{-- STRICT BLADE LOGIC FOR ICONS --}}
                                                @if($item['score'] >= 3)
                                                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                                                @else
                                                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                                                @endif

                                                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $item['criteria'] }}</p>
                                            </div>
                                            <div class="shrink-0 flex items-baseline gap-1">
                                                <span class="text-xl font-black {{ $item['score'] < 3 ? 'text-red-600' : 'text-green-600' }}">{{ $item['score'] }}</span>
                                                <span class="text-xs text-gray-400 font-bold">/ 5</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">Detailed rubric breakdown is not available for this evaluation.</p>
                @endif
            </section>

        </div>

        {{-- ACTION FOOTER --}}
        <div class="bg-gray-50 border-t border-gray-200 p-6 flex flex-col sm:flex-row justify-between items-center gap-4 print:hidden">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                Evaluated on: {{ $material->updated_at->format('M d, Y h:i A') }}
            </p>
            {{-- Print Button Removed --}}
        </div>

    </div>

</body>
</html>