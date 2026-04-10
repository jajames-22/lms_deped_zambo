<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Assessment Report - {{ $assessment->title }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 13px; line-height: 1.4; }
        header table { width: 100%; border-collapse: collapse; }
        header td { border: none; padding: 0; }
        .title { font-size: 22px; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 10px; letter-spacing: 0.5px; }
        .subtitle { font-size: 11px; color: #666; margin-top: 5px; }
        .section-title { font-size: 15px; font-weight: bold; color: #1f2937; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; page-break-after: avoid; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; }
        .data-table th, .data-table td { padding: 8px 10px; border-bottom: 1px solid #eee; text-align: left; font-size: 12px; }
        .data-table th { width: 50%; font-weight: bold; color: #444; }
        .sub-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; border: 1px solid #ddd; page-break-inside: auto; }
        .sub-table tr { page-break-inside: avoid; page-break-after: auto; }
        .sub-table th { background-color: #f8f9fa; padding: 10px; font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; border-bottom: 2px solid #ddd; text-align: left; }
        .sub-table td { padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; font-weight: bold; }
        .text-center { text-align: center; }
        .kpi-box { border: 1px solid #ddd; padding: 15px; text-align: center; border-radius: 4px; background-color: #f9fafb; }
        .kpi-value { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .kpi-label { font-size: 10px; text-transform: uppercase; color: #666; letter-spacing: 1px; }

        @if(!isset($isPrint) || !$isPrint)
            @page { margin: 120px 40px 60px 40px; }
            header { position: fixed; top: -120px; left: -40px; right: -40px; height: 80px; background-color: #ffffff; padding: 30px 40px 10px 40px; }
            footer { position: fixed; bottom: -60px; left: -40px; right: -40px; height: 30px; background-color: #ffffff; padding: 15px 40px 0 40px; font-size: 10px; color: #777; }
            .page-number:before { content: "Page " counter(page); }
        @endif
        @if(isset($isPrint) && $isPrint)
            @media print {
                @page { margin: 0.5in; }
                body { padding: 0; margin: 0; }
                header { padding-bottom: 15px; margin-bottom: 20px; }
                footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #777; page-break-inside: avoid; }
                .page-number { display: none; }
            }
        @endif
    </style>
</head>

<body>

    <header>
        <table>
            <tr>
                <td style="width: 60%; vertical-align: bottom; padding-bottom: 5px;">
                    @php
                        // Dynamically switch image path for browser vs DomPDF
                        $logoPath = isset($isPrint) && $isPrint ? asset('storage/images/lms-logo-red.png') : public_path('storage/images/lms-logo-red.png');
                    @endphp
                    <img src="{{ $logoPath }}" height="40" alt="LMS Logo" style="margin-bottom: 5px;">
                    
                    <div class="title">Division Achievement Report</div>
                    <div class="subtitle">Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 8px;">
                    <strong style="font-size: 14px; color: #111;">{{ $assessment->title }}</strong><br>
                    <span style="font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px;">Status: {{ ucfirst($assessment->status) }}</span>
                </td>
            </tr>
        </table>
    </header>

    <main>
        {{-- Section 1: Overview & Scores --}}
        @if(isset($showOverview) && $showOverview)
            <div class="section-title">1. Executive Summary</div>
            
            <table style="width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-bottom: 20px;">
                <tr>
                    <td style="width: 33.3%;" class="kpi-box">
                        <div class="kpi-value">{{ $overallMPS }}%</div>
                        <div class="kpi-label">Overall MPS</div>
                    </td>
                    <td style="width: 33.3%;" class="kpi-box">
                        <div class="kpi-value" style="color: {{ $masteryColor }};">{{ $overallMasteryLevel }}</div>
                        <div class="kpi-label">Descriptive Level</div>
                    </td>
                    <td style="width: 33.3%;" class="kpi-box">
                        <div class="kpi-value">{{ $completionRate }}%</div>
                        <div class="kpi-label">Participation Rate</div>
                    </td>
                </tr>
            </table>

            <table class="data-table">
                <tr>
                    <th>Proficiency Rate (Met 75% Standard)</th>
                    <td class="text-right" style="color: #2563eb;">{{ $proficiencyRate ?? 0 }}%</td>
                </tr>
                <tr>
                    <th>Total Takers Finished</th>
                    <td class="text-right">{{ $completedCount }} / {{ $totalStudents }}</td>
                </tr>
                <tr>
                    <th>Average Completion Time</th>
                    <td class="text-right" style="font-family: monospace;">{{ $avgTimeFormat }}</td>
                </tr>
            </table>

            <div style="margin-top: 10px; margin-bottom: 20px;">
                <strong style="font-size: 12px; color: #555; text-transform: uppercase;">Proficiency Distribution</strong>
                <table class="sub-table" style="margin-top: 5px; margin-bottom: 10px;">
                    <tr>
                        <th class="text-center" style="color: #16a34a;">Highly Proficient<br><span style="font-size:9px; font-weight:normal;">(90-100%)</span></th>
                        <th class="text-center" style="color: #2563eb;">Proficient<br><span style="font-size:9px; font-weight:normal;">(75-89%)</span></th>
                        <th class="text-center" style="color: #d97706;">Nearly Proficient<br><span style="font-size:9px; font-weight:normal;">(50-74%)</span></th>
                        <th class="text-center" style="color: #ea580c;">Low Proficient<br><span style="font-size:9px; font-weight:normal;">(25-49%)</span></th>
                        <th class="text-center" style="color: #dc2626;">Not Proficient<br><span style="font-size:9px; font-weight:normal;">(0-24%)</span></th>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">{{ $proficiencyLevels['Highly Proficient (90-100%)'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $proficiencyLevels['Proficient (75-89%)'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $proficiencyLevels['Nearly Proficient (50-74%)'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $proficiencyLevels['Low Proficient (25-49%)'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $proficiencyLevels['Not Proficient (0-24%)'] ?? 0 }}</td>
                    </tr>
                </table>
            </div>

            @if(isset($notTakenStudents) && $notTakenStudents->count() > 0)
                <div style="margin-top: 15px; page-break-inside: avoid;">
                    <strong style="font-size: 11px; color: #555; text-transform: uppercase;">Students Pending / Did Not Take ({{ $notTakenStudents->count() }})</strong>
                    <div style="padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 5px; font-family: monospace; font-size: 11px; line-height: 1.8;">
                        @foreach($notTakenStudents as $student)
                            <span style="display: inline-block; width: 110px; margin-right: 10px; border-bottom: 1px dashed #ccc; color: #444;">{{ $student->lrn }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- Section 2: Category Mastery --}}
        @if(isset($showCategory) && $showCategory)
            <div class="section-title">2. Competency Breakdown</div>
            
            <table style="width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-bottom: 15px;">
                <tr>
                    <td style="width: 50%; border: 1px solid #bfdbfe; background-color: #eff6ff; padding: 12px; border-radius: 4px;">
                        <div style="font-size: 10px; font-weight: bold; color: #1d4ed8; text-transform: uppercase; margin-bottom: 4px;">Most Mastered Area</div>
                        @if($mostMastered)
                            <div style="font-weight: bold; color: #111;">{{ $mostMastered->title }}</div>
                            <div style="color: #2563eb; font-size: 11px; font-weight: bold;">{{ $mostMastered->mps }}% MPS</div>
                        @else
                            <div style="font-style: italic; color: #666; font-size: 11px;">Data pending</div>
                        @endif
                    </td>
                    <td style="width: 50%; border: 1px solid #fecaca; background-color: #fef2f2; padding: 12px; border-radius: 4px;">
                        <div style="font-size: 10px; font-weight: bold; color: #b91c1c; text-transform: uppercase; margin-bottom: 4px;">Least Mastered Area (Intervention Required)</div>
                        @if($leastMastered)
                            <div style="font-weight: bold; color: #111;">{{ $leastMastered->title }}</div>
                            <div style="color: #dc2626; font-size: 11px; font-weight: bold;">{{ $leastMastered->mps }}% MPS</div>
                        @else
                            <div style="font-style: italic; color: #666; font-size: 11px;">Data pending</div>
                        @endif
                    </td>
                </tr>
            </table>

            @if(count($competencies ?? []) > 0)
                <table class="sub-table">
                    <tr>
                        <th style="text-align: left;">Section / Competency Name</th>
                        <th style="width: 25%; text-align: center;">Mean Percentage Score (MPS)</th>
                        <th style="width: 20%; text-align: center;">Status</th>
                    </tr>
                    @foreach($competencies as $cat)
                        <tr>
                            <td>{{ $cat->title }}</td>
                            <td class="text-center font-bold" style="{{ $cat->mps >= 75 ? 'color: #16a34a;' : ($cat->mps <= 50 ? 'color: #dc2626;' : 'color: #d97706;') }}">
                                {{ $cat->mps }}%
                            </td>
                            <td class="text-center" style="font-size: 10px; font-weight: bold; text-transform: uppercase;">
                                @if($cat->mps >= 75) <span style="color: #16a34a;">Mastered</span>
                                @elseif($cat->mps >= 50) <span style="color: #d97706;">Review</span>
                                @else <span style="color: #dc2626;">Least Learned</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="color: #888; font-style: italic;">No competency data available.</p>
            @endif
        @endif

        {{-- Section 3: Item Analysis --}}
        @if(isset($showItemAnalysis) && $showItemAnalysis)

            <div class="section-title" style="color: #dc2626;">3. Top Misconceptions</div>
            @if(isset($topMisconceptions) && count($topMisconceptions) > 0)
                <table class="sub-table" style="border-color: #fca5a5;">
                    <tr>
                        <th style="width: 40%;">Question Context</th>
                        <th style="width: 40%;">Common Incorrect Answer</th>
                        <th style="width: 20%; text-align: center;">Selected By</th>
                    </tr>
                    @foreach($topMisconceptions as $missed)
                        <tr>
                            <td style="font-size: 11px; color: #444;">{{ strip_tags($missed->question_text) }}</td>
                            <td style="font-size: 11px; font-weight: bold; color: #dc2626;">{{ strip_tags($missed->distractor_text) }}</td>
                            <td class="text-center font-bold text-red" style="color: #dc2626;">{{ $missed->pct }}%</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="color: #888; font-style: italic; font-size: 11px;">No major misconceptions detected yet.</p>
            @endif

            <div class="section-title">4. Complete Item Analysis</div>
            @if(isset($itemAnalysis) && count($itemAnalysis) > 0)
                <table class="sub-table">
                    <tr>
                        <th style="width: 5%;" class="text-center">#</th>
                        <th style="width: 35%; text-align: left;">Question</th>
                        <th style="width: 15%;" class="text-center">Difficulty (p)</th>
                        <th style="width: 45%; text-align: left;">Response Distribution</th>
                    </tr>
                    @foreach($itemAnalysis as $index => $item)
                        <tr>
                            <td class="text-center" style="color: #888; vertical-align: top;">{{ $index + 1 }}</td>
                            <td style="font-size: 11px; vertical-align: top;">
                                {{ strip_tags($item->question_text) }}
                                <div style="font-size: 9px; color: #888; text-transform: uppercase; margin-top: 4px;">{{ $item->category_name }}</div>
                            </td>
                            <td class="text-center" style="vertical-align: top;">
                                <div style="font-weight: bold; font-size: 13px;">{{ $item->difficulty_index }}%</div>
                                <div style="font-size: 9px; color: #666; margin-top: 3px;">
                                    <span style="color: #16a34a;">✔ {{ $item->correct_count }}</span> | 
                                    <span style="color: #dc2626;">✘ {{ $item->wrong_count }}</span>
                                </div>
                            </td>
                            <td style="font-size: 10px; vertical-align: top;">
                                @foreach($item->distractor_stats as $opt)
                                    @php $isDead = (!$opt->is_correct && $opt->pct == 0); @endphp
                                    <div style="margin-bottom: 3px; padding: 2px 4px; border: 1px solid {{ $opt->is_correct ? '#bbf7d0' : ($isDead ? '#f3f4f6' : '#fecaca') }}; background-color: {{ $opt->is_correct ? '#f0fdf4' : ($isDead ? '#fafafa' : '#fef2f2') }}; color: {{ $opt->is_correct ? '#16a34a' : ($isDead ? '#9ca3af' : '#dc2626') }}; {{ $opt->is_correct ? 'font-weight: bold;' : '' }}">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($opt->text), 45) }}: {{ $opt->pct }}%
                                        @if($opt->is_correct) (Correct) @endif
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="text-align: center; color: #888; font-style: italic;">No item data available.</p>
            @endif
        @endif
    </main>

    <footer>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS Platform') }} • Division Assessment Report</td>
                <td style="text-align: right; width: 20%;" class="page-number"></td>
            </tr>
        </table>
    </footer>

    @if(isset($isPrint) && $isPrint)
        <script>window.onload = function () { window.print(); };</script>
    @endif
</body>

</html>