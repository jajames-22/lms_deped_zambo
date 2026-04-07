<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Learning Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 14px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 10px; letter-spacing: 0.5px; }
        .subtitle { font-size: 11px; color: #666; margin-top: 5px; }
        .section-title { font-size: 16px; font-weight: bold; color: #a52a2a; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; page-break-after: avoid; }
        .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #eee; text-align: left; }
        .data-table th { width: 70%; font-weight: bold; color: #444; }
        .sub-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; border: 1px solid #ddd; page-break-inside: avoid; }
        .sub-table th { background-color: #f8f9fa; padding: 10px; font-size: 12px; font-weight: bold; color: #555; text-transform: uppercase; border-bottom: 2px solid #ddd; }
        .sub-table td { padding: 10px; font-size: 13px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; font-weight: bold; font-size: 15px; }
        
        @if(!isset($isPrint) || !$isPrint)
            @page { margin: 130px 40px 80px 40px; }
            header { position: fixed; top: -130px; left: -40px; right: -40px; background-color: #ffffff; }
            .header-inner { padding: 30px 40px 15px 40px; }
            footer { position: fixed; bottom: -80px; left: -40px; right: -40px; background-color: #ffffff; }
            .footer-inner { padding: 15px 40px 0 40px; font-size: 10px; color: #777; }
            .page-number:before { content: "Page " counter(page); }
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
                        <img src="{{ $logoPath }}" height="40" alt="LMS Logo">
                        <div class="title">My Learning Report</div>
                        <div class="subtitle">Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: bottom;">
                        <strong style="font-size: 16px;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong><br>
                        <span style="font-size: 10px; text-transform: uppercase; color: #888;">Student Account</span>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <footer>
        <div class="footer-inner">
            <table><tr>
                <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS') }} • Official Personal Learning Record</td>
                <td style="text-align: right; width: 20%;" class="page-number"></td>
            </tr></table>
        </div>
    </footer>

    <main>
        @if($showAchievements)
        <div class="section-title">1. Achievements & Streak</div>
        <table class="data-table">
            <tr><th>Current Learning Streak</th><td class="text-right" style="color: #f97316;">{{ $streak }} Days 🔥</td></tr>
            <tr><th>Total Modules Completed</th><td class="text-right" style="color: #16a34a;">{{ number_format($completedCount) }}</td></tr>
        </table>
        @endif

        @if($showProgress)
        <div class="section-title">2. Learning Progress</div>
        <table class="data-table">
            <tr><th>Overall Curriculum Progress</th><td class="text-right">{{ $completionRate }}%</td></tr>
            <tr><th>Total Modules Enrolled</th><td class="text-right">{{ number_format($totalEnrollments) }}</td></tr>
            <tr><th>Modules In Progress</th><td class="text-right">{{ number_format($inProgressCount) }}</td></tr>
        </table>
        @endif

        @if($showPerformance)
        <div class="section-title">3. Detailed Performance Stats</div>
        <table class="data-table">
            <tr><th>Total Time Invested</th><td class="text-right" style="color: #3b82f6;">{{ $totalHours }} Hours</td></tr>
            <tr><th>Overall Accuracy</th><td class="text-right">{{ $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100) : 0 }}%</td></tr>
            <tr><th>Total Correct Answers</th><td class="text-right">{{ number_format($correctAnswers) }}</td></tr>
        </table>

        <div style="font-weight: bold; font-size: 13px; color: #555; margin-top: 20px;">Topic Mastery Breakdown</div>
        <table class="sub-table">
            <tr><th style="text-align: left;">Module Title</th><th style="text-align: right;">Mastery Score</th></tr>
            @foreach($masteryData as $m)
            <tr><td>{{ $m->title }}</td><td style="text-align: right; font-weight: bold; color: #8b5cf6;">{{ $m->total_attempts > 0 ? round(($m->correct_attempts / $m->total_attempts) * 100) : 0 }}%</td></tr>
            @endforeach
        </table>
        @endif
    </main>

    @if(isset($isPrint) && $isPrint)
    <script>window.onload = function() { window.print(); };</script>
    @endif
</body>
</html>