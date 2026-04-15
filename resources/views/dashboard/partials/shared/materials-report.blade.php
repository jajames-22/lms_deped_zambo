<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $material->title }} - Analytics Report</title>
    <style>
        /* Common Styles */
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 14px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .title { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 10px; letter-spacing: 0.5px; }
        .subtitle { font-size: 11px; color: #666; margin-top: 5px; }
        
        /* Section & Table Formatting */
        .section-title { font-size: 14px; font-weight: bold; color: #a52a2a; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; page-break-after: avoid; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; }
        .data-table th, .data-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; font-size: 13px; }
        .data-table th { width: 60%; font-weight: bold; color: #444; }
        
        .sub-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; border: 1px solid #ddd; }
        .sub-table th { background-color: #f8f9fa; padding: 10px; font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; border-bottom: 2px solid #ddd; }
        .sub-table td { padding: 8px 10px; font-size: 12px; border-bottom: 1px solid #eee; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-red { color: #dc2626; }
        .text-green { color: #16a34a; }
        .text-blue { color: #3b82f6; }
        .text-amber { color: #d97706; }

        .difficulty-badge { font-size: 10px; text-transform: uppercase; font-weight: bold; }

        /* DOMPDF SPECIFIC */
        @if(!isset($isPrint) || !$isPrint)
            @page { margin: 130px 40px 80px 40px; }
            header { position: fixed; top: -130px; left: -40px; right: -40px; background-color: #ffffff; }
            .header-inner { padding: 30px 40px 15px 40px; }
            footer { position: fixed; bottom: -80px; left: -40px; right: -40px; background-color: #ffffff; }
            .footer-inner { padding: 15px 40px 0 40px; font-size: 10px; color: #777; }
            .page-number:before { content: "Page " counter(page); }
        @endif

        /* BROWSER PRINT SPECIFIC */
        @if(isset($isPrint) && $isPrint)
            @media print {
                @page { margin: 0.5in; }
                body { padding: 0; margin: 0; }
                header { margin-bottom: 20px; }
                .header-inner { padding-bottom: 15px; }
                footer { margin-top: 30px; page-break-inside: avoid; }
                .footer-inner { padding-top: 10px; font-size: 10px; color: #777; }
                .page-number { display: none; }
            }
        @endif
    </style>
</head>
<body>

    <header>
        <div class="header-inner">
            <table>
                <tr>
                    <td style="width: 60%; vertical-align: bottom;">
                        @php $logoPath = isset($isPrint) && $isPrint ? asset('storage/images/lms-logo-red.png') : public_path('storage/images/lms-logo-red.png'); @endphp
                        <img src="{{ $logoPath }}" height="35" alt="Logo" style="margin-bottom: 5px;">
                        <div class="title">{{ $material->title }}</div>
                        <div class="subtitle">Material Analytics Report • Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 3px;">
                        <strong style="font-size: 14px; color: #111;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong><br>
                        <span style="font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px;">Instructor Account</span>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <main>
        @if($showMetrics)
        <div class="section-title">1. Class Overview & Progress</div>
        <table class="data-table">
            <tr>
                <th>Total Enrolled Learners</th>
                <td class="text-right font-bold">{{ number_format($totalLearners) }}</td>
            </tr>
            <tr>
                <th>Pending Enrollment Requests</th>
                <td class="text-right text-amber font-bold">{{ number_format($pendingRequests) }}</td>
            </tr>
            <tr>
                <th>Total Dropped Students</th>
                <td class="text-right text-red font-bold">{{ number_format($totalDropped) }}</td>
            </tr>
            <tr>
                <th>Completed Modules</th>
                <td class="text-right text-green font-bold">{{ number_format($completedCount) }}</td>
            </tr>
            <tr>
                <th>In Progress Modules</th>
                <td class="text-right font-bold text-blue">{{ number_format($inProgressCount) }}</td>
            </tr>
            <tr>
                <th>Overall Class Average Score</th>
                <td class="text-right font-bold">{{ $hasQuizzes || $hasExams ? $overallAverage . '%' : 'N/A' }}</td>
            </tr>
        </table>
        @endif

        @if($showCompetency)
        <div class="section-title">2. Competency Breakdown (MPS)</div>
        <table class="sub-table">
            <thead>
                <tr>
                    <th style="width: 60%; text-align: left;">Section / Topic Name</th>
                    <th style="width: 20%; text-align: center;">MPS</th>
                    <th style="width: 20%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($competencies as $cat)
                <tr>
                    <td>{{ $cat->title }}</td>
                    @if($cat->has_quiz)
                        <td class="text-center font-bold {{ $cat->mps >= 75 ? 'text-green' : ($cat->mps <= 40 ? 'text-red' : 'text-amber') }}">{{ $cat->mps }}%</td>
                        <td class="text-center">
                            @if($cat->mps >= 75) Mastered
                            @elseif($cat->mps >= 50) Review
                            @else Needs Focus @endif
                        </td>
                    @else
                        <td colspan="2" class="text-center" style="color:#999; font-style:italic;">No quiz items</td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="3" class="text-center">No competency data available.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title" style="margin-top: 20px;">Top Performing Students</div>
        <table class="sub-table">
            <thead>
                <tr>
                    <th style="width: 10%; text-align: center;">Rank</th>
                    <th style="width: 40%; text-align: left;">Student Name</th>
                    <th style="width: 15%; text-align: center;">Progress</th>
                    <th style="width: 35%; text-align: center;">Overall Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach(collect($studentLeaderboard)->take(10) as $index => $student)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>{{ $student->name }}</td>
                    <td class="text-center">{{ $student->progress }}%</td>
                    <td class="text-center font-bold">{{ $hasQuizzes || $hasExams ? $student->score . '%' : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($showItemAnalysis && ($hasQuizzes || $hasExams))
        <div style="page-break-before: always;"></div>
        <div class="section-title">3. Item Analysis</div>
        
        @if($hasQuizzes)
        <div style="font-weight: bold; margin-bottom: 5px; color:#555;">Quiz Items (Avg: {{ $avgQuizScore }}%)</div>
        <table class="sub-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">#</th>
                    <th style="width: 60%; text-align: left;">Question Base</th>
                    <th style="width: 15%; text-align: center;">Difficulty (p)</th>
                    <th style="width: 17%; text-align: center;">Classification</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quizItemAnalysis as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ strip_tags($item->question_text) }}</td>
                    <td class="text-center font-bold">{{ $item->difficulty_index ?? 0 }}%</td>
                    <td class="text-center difficulty-badge">
                        @if(($item->difficulty_index ?? 0) >= 81) <span class="text-blue">Very Easy</span>
                        @elseif(($item->difficulty_index ?? 0) >= 61) <span class="text-green">Easy</span>
                        @elseif(($item->difficulty_index ?? 0) >= 41) <span class="text-amber">Average</span>
                        @elseif(($item->difficulty_index ?? 0) >= 21) <span style="color: #f97316;">Difficult</span>
                        @else <span class="text-red">Very Difficult</span> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($hasExams)
        <div style="font-weight: bold; margin-bottom: 5px; margin-top:15px; color:#555;">Exam Items (Avg: {{ $avgExamScore }}%)</div>
        <table class="sub-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">#</th>
                    <th style="width: 60%; text-align: left;">Question Base</th>
                    <th style="width: 15%; text-align: center;">Difficulty (p)</th>
                    <th style="width: 17%; text-align: center;">Classification</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examItemAnalysis as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ strip_tags($item->question_text) }}</td>
                    <td class="text-center font-bold">{{ $item->difficulty_index ?? 0 }}%</td>
                    <td class="text-center difficulty-badge">
                        @if(($item->difficulty_index ?? 0) >= 81) <span class="text-blue">Very Easy</span>
                        @elseif(($item->difficulty_index ?? 0) >= 61) <span class="text-green">Easy</span>
                        @elseif(($item->difficulty_index ?? 0) >= 41) <span class="text-amber">Average</span>
                        @elseif(($item->difficulty_index ?? 0) >= 21) <span style="color: #f97316;">Difficult</span>
                        @else <span class="text-red">Very Difficult</span> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endif

    </main>

    <footer>
        <div class="footer-inner">
            <table>
                <tr>
                    <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS Platform') }} • Material Analytics Data Export</td>
                    <td style="text-align: right; width: 20%;" class="page-number"></td>
                </tr>
            </table>
        </div>
    </footer>

    @if(isset($isPrint) && $isPrint)
    <script>window.onload = function() { window.print(); };</script>
    @endif
</body>
</html>