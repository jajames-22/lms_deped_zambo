<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Material Analytics Report</title>
    <style>
        /* =======================================================
           1. COMMON STYLES (Applies to both Print and PDF)
           ======================================================= */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #333; 
            font-size: 14px; 
            line-height: 1.4;
        }
        
        table { width: 100%; border-collapse: collapse; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 10px; letter-spacing: 0.5px; }
        .subtitle { font-size: 11px; color: #666; margin-top: 5px; }

        /* Section & Table Formatting */
        .section-title { font-size: 16px; font-weight: bold; color: #a52a2a; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; page-break-after: avoid; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; }
        .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #eee; text-align: left; }
        .data-table th { width: 70%; font-weight: bold; color: #444; }
        
        .sub-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; border: 1px solid #ddd; page-break-inside: avoid; }
        .sub-table th { background-color: #f8f9fa; padding: 10px; font-size: 12px; font-weight: bold; color: #555; text-transform: uppercase; border-bottom: 2px solid #ddd; }
        .sub-table td { padding: 10px; font-size: 13px; border-bottom: 1px solid #eee; }
        .sub-table-title { font-weight: bold; font-size: 13px; color: #555; margin-bottom: 5px; margin-top: 20px; page-break-after: avoid; }

        .text-right { text-align: right; font-weight: bold; font-size: 15px; }
        .text-red { color: #dc2626; }
        .text-green { color: #16a34a; }
        .text-blue { color: #3b82f6; }

        /* =======================================================
           2. DOMPDF SPECIFIC STYLES
           ======================================================= */
        @if(!isset($isPrint) || !$isPrint)
            @page { 
                margin: 130px 40px 80px 40px; 
            }
            
            header { 
                position: fixed; 
                top: -130px;     
                left: -40px;     
                right: -40px;    
                background-color: #ffffff; 
            }
            .header-inner { padding: 30px 40px 15px 40px; }
            
            footer { 
                position: fixed; 
                bottom: -80px; 
                left: -40px; 
                right: -40px; 
                background-color: #ffffff; 
            }
            .footer-inner { padding: 15px 40px 0 40px; font-size: 10px; color: #777; }
            .page-number:before { content: "Page " counter(page); }
        @endif

        /* =======================================================
           3. BROWSER PRINT SPECIFIC STYLES
           ======================================================= */
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
                        @php
                            $logoPath = isset($isPrint) && $isPrint ? asset('storage/images/lms-logo-red.png') : public_path('storage/images/lms-logo-red.png');
                        @endphp
                        <img src="{{ $logoPath }}" height="40" alt="LMS Logo" style="margin-bottom: 5px;">
                        
                        <div class="title">Material Analytics Report</div>
                        <div class="subtitle">Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 3px;">
                        <strong style="font-size: 16px; color: #111;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong><br>
                        <span style="font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px;">Instructor Account</span>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <main>
        @if($showOverview)
        <div class="section-title">1. Material Overview</div>
        <table class="data-table">
            <tr>
                <th>Total Learners Enrolled</th>
                <td class="text-right">{{ number_format($totalLearners) }}</td>
            </tr>
            <tr>
                <th>Active Learners (Last 7 Days)</th>
                <td class="text-right text-green">{{ number_format($activeLearners) }}</td>
            </tr>
            <tr>
                <th>Pending Enrollment Invites</th>
                <td class="text-right text-red">{{ number_format($pendingRequests) }}</td>
            </tr>
        </table>
        @endif

        @if($showEngagement)
        <div class="section-title">2. Material Engagement</div>
        <table class="data-table">
            <tr>
                <th>Total Modules Created</th>
                <td class="text-right">{{ number_format($totalMaterials) }}</td>
            </tr>
            <tr>
                <th>Total Views Across All Modules</th>
                <td class="text-right">{{ number_format($totalViews) }}</td>
            </tr>
            <tr>
                <th>Completed Enrollments</th>
                <td class="text-right text-green">{{ number_format($completedCount) }}</td>
            </tr>
            <tr>
                <th>In Progress Enrollments</th>
                <td class="text-right" style="color: #f59e0b;">{{ number_format($inProgressCount) }}</td>
            </tr>
        </table>

        @if(count($topMaterials) > 0)
            <div class="sub-table-title">Most Viewed Modules</div>
            <table class="sub-table">
                <tr>
                    <th style="width: 75%; text-align: left;">Module Title</th>
                    <th style="width: 25%; text-align: right;">Total Views</th>
                </tr>
                @foreach($topMaterials as $material)
                <tr>
                    <td>{{ $material->title }}</td>
                    <td style="text-align: right; font-weight: bold; color: #8b5cf6;">{{ number_format($material->views) }}</td>
                </tr>
                @endforeach
            </table>
        @endif
        @endif

        @if($showPerformance)
        <div class="section-title">3. Assessment Performance</div>
        <table class="data-table">
            <tr>
                <th>Global Material Average Score</th>
                <td class="text-right text-green">{{ $averageScore }}%</td>
            </tr>
            <tr>
                <th>Total Correct Answers</th>
                <td class="text-right text-blue">{{ number_format($correctAnswers) }}</td>
            </tr>
            <tr>
                <th>Total Incorrect Answers</th>
                <td class="text-right text-red">{{ number_format($incorrectAnswers) }}</td>
            </tr>
        </table>
        @endif

        @if($showTrends)
        <div class="section-title">4. Activity Trends (Last 7 Days)</div>
        <table class="sub-table">
            <tr>
                <th style="width: 75%; text-align: left;">Date</th>
                <th style="width: 25%; text-align: right;">New Enrollments</th>
            </tr>
            @foreach($activityTrends as $trend)
            <tr>
                <td>{{ $trend['date'] }}</td>
                <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($trend['count']) }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    </main>

    
    <footer>
        <div class="footer-inner">
            <table>
                <tr>
                    <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS Platform') }} • Official Instructor Generated Report</td>
                    <td style="text-align: right; width: 20%;" class="page-number"></td>
                </tr>
            </table>
        </div>
    </footer>

    @if(isset($isPrint) && $isPrint)
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</body>
</html>