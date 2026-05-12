<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $assessment->title }} - Analytics Report</title>
    <style>
        /* Common Styles */
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 14px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .title { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 10px; letter-spacing: 0.5px; }
        .subtitle { font-size: 11px; color: #666; margin-top: 5px; }
        
        /* Section & Table Formatting */
        .section-title { font-size: 14px; font-weight: bold; color: #a52a2a; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; page-break-after: avoid; border-bottom: 1px solid #ddd; }
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
        .text-blue { color: #2563eb; }
        .text-amber { color: #d97706; }
        .text-orange { color: #ea580c; }

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
                        <div class="title">{{ $assessment->title }}</div>
                        <div class="subtitle">Assessment Analytics Report • Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
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
        @if($showOverview)
        <div class="section-title">1. Executive Summary & Scores</div>
        <table class="data-table">
            <tr>
                <th>Total Enrolled Takers</th>
                <td class="text-right">{{ number_format($totalStudents) }}</td>
            </tr>
            <tr>
                <th>Finished Assessments</th>
                <td class="text-right text-blue font-bold">{{ number_format($completedCount) }} ({{ $completionRate }}%)</td>
            </tr>
            <tr>
                <th>Overall Class MPS (Mean Percentage Score)</th>
                <td class="text-right font-bold" style="color: {{ $masteryColor }}">{{ $overallMPS }}%</td>
            </tr>
            <tr>
                <th>Descriptive Mastery Level</th>
                <td class="text-right font-bold" style="color: {{ $masteryColor }}">{{ $overallMasteryLevel }}</td>
            </tr>
            <tr>
                <th>Most Mastered Topic/Competency</th>
                <td class="text-right font-bold text-green">{{ $mostMastered ? $mostMastered->title : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Least Mastered Topic/Competency</th>
                <td class="text-right font-bold text-red">{{ $leastMastered ? $leastMastered->title : 'N/A' }}</td>
            </tr>
        </table>

        <div style="font-weight: bold; margin-bottom: 5px; color:#555; margin-top:20px; font-size:12px; text-transform:uppercase;">Score Distribution & Proficiency Curve</div>
        <table class="sub-table">
            <thead>
                <tr>
                    <th style="width: 40%; text-align: left;">Proficiency Level</th>
                    <th style="width: 20%; text-align: center;">Score Range</th>
                    <th style="width: 20%; text-align: center;">Raw Items</th>
                    <th style="width: 20%; text-align: center;">Student Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_reverse($combinedDistribution) as $dist)
                <tr>
                    <td class="font-bold" style="color: {{ $dist['color'] }}">{{ $dist['level'] }}</td>
                    <td class="text-center font-bold" style="color: #666;">{{ $dist['range'] }}</td>
                    <td class="text-center">{{ $dist['raw'] }}</td>
                    <td class="text-center font-bold">{{ $dist['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($showCategory)
        <div class="section-title">2. Competency & School Benchmarking</div>
        <table class="sub-table">
            <thead>
                <tr>
                    <th style="width: 60%; text-align: left;">Section / Topic Name</th>
                    <th style="width: 20%; text-align: center;">Score (Accuracy)</th>
                    <th style="width: 20%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($competencies as $cat)
                <tr>
                    <td>{{ $cat->title }}</td>
                    @if(($cat->total_answers ?? 0) == 0)
                        <td class="text-center font-bold" style="color: #999;">--</td>
                        <td class="text-center" style="font-size: 10px; color: #999; text-transform: uppercase;">No Data</td>
                    @else
                        <td class="text-center font-bold {{ $cat->mps >= 75 ? 'text-green' : ($cat->mps < 40 ? 'text-red' : 'text-amber') }}">{{ $cat->mps }}%</td>
                        <td class="text-center difficulty-badge">
                            @if($cat->mps >= 90) <span class="text-green">Advanced</span>
                            @elseif($cat->mps >= 75) <span class="text-green">Upper Intermediate</span>
                            @elseif($cat->mps >= 60) <span class="text-blue">Intermediate</span>
                            @elseif($cat->mps >= 40) <span class="text-amber">Basic</span>
                            @else <span class="text-red">Beginner</span> @endif
                        </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="3" class="text-center">No competency data available.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="font-weight: bold; margin-bottom: 5px; color:#555; margin-top:20px; font-size:12px; text-transform:uppercase;">School Benchmarking</div>
        <table class="sub-table" style="margin-top: 5px;">
            <thead>
                <tr>
                    <th style="width: 10%; text-align: center;">Rank</th>
                    <th style="width: 50%; text-align: left;">School Name</th>
                    <th style="width: 20%; text-align: center;">Takers</th>
                    <th style="width: 20%; text-align: center;">Overall Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse(collect($schoolLeaderboard)->take(10) as $index => $school)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>{{ $school->name }}</td>
                    <td class="text-center">{{ $school->student_count }}</td>
                    <td class="text-center font-bold">{{ $school->mps }}%</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">No school data available.</td></tr>
                @endforelse
            </tbody>
        </table>
        @endif

        @if($showItemAnalysis && count($itemAnalysis) > 0)
        <div style="page-break-before: always;"></div>
        <div class="section-title">3. Full Item Analysis Data</div>
        <p style="font-size: 11px; color: #666; margin-bottom: 10px;">Item difficulty index and classification are based strictly on submitted tests. Pending students are omitted.</p>
        
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
                @foreach($itemAnalysis as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ strip_tags($item->question_text) }}</td>
                    <td class="text-center font-bold">{{ $item->difficulty_index ?? 0 }}%</td>
                    <td class="text-center difficulty-badge">
                        @if(($item->difficulty_index ?? 0) >= 81) <span class="text-blue">Very Easy</span>
                        @elseif(($item->difficulty_index ?? 0) >= 61) <span class="text-green">Easy</span>
                        @elseif(($item->difficulty_index ?? 0) >= 41) <span class="text-amber">Average</span>
                        @elseif(($item->difficulty_index ?? 0) >= 21) <span class="text-orange">Difficult</span>
                        @else <span class="text-red">Very Difficult</span> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    </main>

    <footer>
        <div class="footer-inner">
            <table>
                <tr>
                    <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS Platform') }} • Assessment Analytics Export</td>
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