<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Assessment Report - {{ $assessment->title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.4;
        }

        header table {
            width: 100%;
            border-collapse: collapse;
        }

        header td {
            border: none;
            padding: 0;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }

        .subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #a52a2a;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            margin-top: 30px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            page-break-after: avoid;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .data-table th,
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 13px;
        }

        .data-table th {
            width: 50%;
            font-weight: bold;
            color: #444;
        }

        .sub-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
            border: 1px solid #ddd;
            page-break-inside: auto;
        }

        .sub-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .sub-table th {
            background-color: #f8f9fa;
            padding: 10px;
            font-size: 12px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
        }

        .sub-table td {
            padding: 8px 10px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
            font-weight: bold;
        }

        .text-red {
            color: #dc2626;
        }

        .text-green {
            color: #16a34a;
        }

        .text-center {
            text-align: center;
        }

        @if(!isset($isPrint) || !$isPrint)
            @page {
                margin: 140px 40px 80px 40px;
            }

            header {
                position: fixed;
                top: -140px;
                left: -40px;
                right: -40px;
                height: 90px;
                background-color: #ffffff;
                padding: 30px 40px 10px 40px;
            }

            footer {
                position: fixed;
                bottom: -80px;
                left: -40px;
                right: -40px;
                height: 40px;
                background-color: #ffffff;
                padding: 15px 40px 0 40px;
                font-size: 10px;
                color: #777;
            }

            .page-number:before {
                content: "Page " counter(page);
            }

        @endif
        @if(isset($isPrint) && $isPrint)
            @media print {
                @page {
                    margin: 0.5in;
                }

                body {
                    padding: 0;
                    margin: 0;
                }

                header {
                    padding-bottom: 15px;
                    margin-bottom: 20px;
                }

                footer {
                    margin-top: 30px;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                    font-size: 10px;
                    color: #777;
                    page-break-inside: avoid;
                }

                .page-number {
                    display: none;
                }
            }

        @endif
    </style>
</head>

<body>

    <header>
        <table>
            <tr>
                <td style="width: 60%; vertical-align: bottom; padding-bottom: 5px;">
                    @php $logoPath = isset($isPrint) && $isPrint ? asset('storage/images/lms-logo-red.png') : public_path('storage/images/lms-logo-red.png'); @endphp
                    <img src="{{ $logoPath }}" height="40" alt="LMS Logo" style="margin-bottom: 5px;">
                    <div class="title">Assessment Analytics</div>
                    <div class="subtitle">Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 8px;">
                    <strong style="font-size: 16px; color: #111;">{{ $assessment->title }}</strong><br>
                    <span style="font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px;">Status:
                        {{ ucfirst($assessment->status) }}</span>
                </td>
            </tr>
        </table>
    </header>

    <main>
        {{-- Section 1: Checkbox 1 (Overview & Scores) --}}
        @if(isset($showOverview) && $showOverview)
            <div class="section-title">1. Overview & Scores</div>
            <table class="data-table">
                <tr>
                    <th>Participation Rate ({{ $completedCount }} / {{ $totalStudents }})</th>
                    <td class="text-right">{{ $completionRate }}% Completed</td>
                </tr>
                <tr>
                    <th>Class Average Score</th>
                    <td class="text-right" style="color: #3b82f6;">{{ $averageScoreRaw }} / {{ $totalQuestions }}
                        ({{ $averageScorePct }}%)</td>
                </tr>
                <tr>
                    <th>Overall Average Time Spent</th>
                    <td class="text-right" style="font-family: monospace;">{{ $overallAvgTime }}</td>
                </tr>
                <tr>
                    <th>Highest Score</th>
                    <td class="text-right text-green">{{ $highestScoreRaw }} / {{ $totalQuestions }}
                        ({{ $highestScorePct }}%)</td>
                </tr>
                <tr>
                    <th>Lowest Score</th>
                    <td class="text-right text-red">{{ $lowestScoreRaw }} / {{ $totalQuestions }} ({{ $lowestScorePct }}%)
                    </td>
                </tr>
            </table>

            <div style="margin-top: 10px; margin-bottom: 20px;">
                <strong style="font-size: 12px; color: #555; text-transform: uppercase;">Score Distribution</strong>
                <table class="sub-table" style="margin-top: 5px; margin-bottom: 10px;">
                    <tr>
                        <th class="text-center">90-100%</th>
                        <th class="text-center">80-89%</th>
                        <th class="text-center">70-79%</th>
                        <th class="text-center">60-69%</th>
                        <th class="text-center">Below 60%</th>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">{{ $scoreDistribution['90-100%'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $scoreDistribution['80-89%'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $scoreDistribution['70-79%'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $scoreDistribution['60-69%'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $scoreDistribution['Below 60%'] ?? 0 }}</td>
                    </tr>
                </table>
            </div>

            @if(isset($notTakenStudents) && $notTakenStudents->count() > 0)
                <div style="margin-top: 15px; page-break-inside: avoid;">
                    <strong style="font-size: 12px; color: #555; text-transform: uppercase;">Students Who Did Not Take
                        ({{ $notTakenStudents->count() }})</strong>
                    <div
                        style="padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 5px; font-family: monospace; font-size: 12px; line-height: 1.8;">
                        @foreach($notTakenStudents as $student)
                            <span
                                style="display: inline-block; width: 120px; margin-right: 10px; border-bottom: 1px dashed #ccc; color: #444;">
                                {{ $student->lrn }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- Section 2: Checkbox 2 (Category Time & Mastery) --}}
        @if(isset($showCategory) && $showCategory)
            <div class="section-title">2. Category Time & Mastery</div>
            @if(count($categoryData ?? []) > 0)
                <table class="sub-table">
                    <tr>
                        <th style="text-align: left;">Section Title</th>
                        <th style="width: 25%; text-align: center;">Average Score</th>
                        <th style="width: 25%; text-align: center;">Average Time</th>
                    </tr>
                    @foreach($categoryData as $cat)
                        <tr>
                            <td>{{ $cat['title'] }}</td>
                            <td
                                style="text-align: center; font-weight: bold; {{ $cat['score_pct'] >= 75 ? 'color: #16a34a;' : ($cat['score_pct'] <= 50 ? 'color: #dc2626;' : 'color: #d97706;') }}">
                                {{ $cat['score_pct'] }}%</td>
                            <td style="text-align: center; font-family: monospace; color: #555;">{{ $cat['avg_time'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="color: #888; font-style: italic;">No category data available.</p>
            @endif
        @endif

        {{-- Sections 3 & 4 & 5: Checkbox 3 (Item Analysis) --}}
        @if(isset($showItemAnalysis) && $showItemAnalysis)

            <div class="section-title" style="color: #dc2626;">3. Most Missed Answers</div>
            @if(isset($mostMissed) && count($mostMissed) > 0)
                <table class="sub-table" style="border-color: #fca5a5;">
                    @foreach($mostMissed as $missed)
                        <tr>
                            <td style="width: 5%; text-align: center; font-weight: bold; color: #dc2626;">{{ $loop->iteration }}
                            </td>
                            <td style="width: 55%; font-size: 12px;">{{ strip_tags($missed->question_text) }}</td>
                            <td style="width: 20%; text-align: center; font-size: 11px;">
                                <span style="color: #16a34a;">✔ {{ $missed->correct_count }}</span> |
                                <span style="color: #dc2626;">✘ {{ $missed->wrong_count }}</span>
                            </td>
                            <td style="width: 20%; text-align: right; color: #dc2626; font-weight: bold;">Accuracy:
                                {{ $missed->accuracy }}%</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="color: #888; font-style: italic;">No missed questions found.</p>
            @endif

            <div class="section-title" style="color: #16a34a;">4. Perfectly Answered (100% Accuracy)</div>
            @if(isset($perfectQuestions) && count($perfectQuestions) > 0)
                <table class="sub-table" style="border-color: #bbf7d0;">
                    @foreach($perfectQuestions as $perfect)
                        <tr>
                            <td style="width: 5%; text-align: center; font-weight: bold; color: #16a34a;"><img
                                    src="https://img.icons8.com/color/16/000000/star--v1.png" style="vertical-align: middle;" />
                            </td>
                            <td style="width: 75%; font-size: 12px;">{{ strip_tags($perfect->question_text) }}</td>
                            <td style="width: 20%; text-align: right; color: #16a34a; font-weight: bold;">Mastered</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="color: #888; font-style: italic;">No perfect scores achieved yet.</p>
            @endif

            <div class="section-title">5. Complete Item Analysis</div>
            @if(isset($itemAnalysis) && count($itemAnalysis) > 0)
                <table class="sub-table">
                    <tr>
                        <th style="width: 5%;" class="text-center">#</th>
                        <th style="width: 55%; text-align: left;">Question</th>
                        <th style="width: 15%;" class="text-center">Correct</th>
                        <th style="width: 15%;" class="text-center">Wrong</th>
                        <th style="width: 10%;" class="text-center">Accuracy</th>
                    </tr>
                    @foreach($itemAnalysis as $index => $item)
                        <tr>
                            <td class="text-center" style="color: #888;">{{ $index + 1 }}</td>
                            <td style="font-size: 11px;">{{ strip_tags($item->question_text) }}</td>
                            <td class="text-center text-green">{{ $item->correct_count }}</td>
                            <td class="text-center text-red">{{ $item->wrong_count }}</td>
                            <td class="text-center"
                                style="font-weight: bold; {{ $item->accuracy >= 75 ? 'color: #16a34a;' : ($item->accuracy <= 40 ? 'color: #dc2626;' : 'color: #d97706;') }}">
                                {{ $item->accuracy }}%
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="text-align: center; color: #888; font-style: italic;">No question data available.</p>
            @endif
        @endif
    </main>

    <footer>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS Platform') }} • Official Assessment
                    Report</td>
                <td style="text-align: right; width: 20%;" class="page-number"></td>
            </tr>
        </table>
    </footer>

    @if(isset($isPrint) && $isPrint)
        <script>window.onload = function () { window.print(); };</script>
    @endif
</body>

</html>