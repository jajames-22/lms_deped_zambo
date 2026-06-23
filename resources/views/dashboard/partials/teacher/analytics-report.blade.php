<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="{{ asset('deped_lms_logo.png') }}">
    <title>Teacher Analytics Report</title>
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
        .text-purple { color: #8b5cf6; }
        .text-amber { color: #f59e0b; }

        .empty-state { padding: 15px; background: #f9f9f9; text-align: center; font-style: italic; color: #777; margin-bottom: 20px; }

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
                        
                        <div class="title">Teacher Analytics Report</div>
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
        <div class="section-title">1. Teaching Overview</div>
        <table class="data-table">
            <tr>
                <th>Total Modules</th>
                <td class="text-right">{{ number_format($totalModules) }}</td>
            </tr>
            <tr>
                <th>Published Modules</th>
                <td class="text-right text-green">{{ number_format($publishedModules) }}</td>
            </tr>
            <tr>
                <th>Draft Modules</th>
                <td class="text-right" style="color: #6b7280;">{{ number_format($draftModules) }}</td>
            </tr>
            <tr>
                <th>Unique Learners</th>
                <td class="text-right text-purple">{{ number_format($uniqueLearners) }}</td>
            </tr>
            <tr>
                <th>Total Enrollments</th>
                <td class="text-right text-blue">{{ number_format($totalEnrollments) }}</td>
            </tr>
            <tr>
                <th>Overall Completion Rate</th>
                <td class="text-right text-amber">{{ $overallCompletionRate }}%</td>
            </tr>
        </table>
        @endif

        @if($showOutcomes)
        <div class="section-title">2. Learning Outcomes</div>
        <table class="data-table">
            <tr>
                <th>Quiz MPS</th>
                <td class="text-right text-green">{{ $quizMPS }}%</td>
            </tr>
            <tr>
                <th>Exam MPS</th>
                <td class="text-right text-blue">{{ $examMPS }}%</td>
            </tr>
            <tr>
                <th>Overall Pass Rate</th>
                <td class="text-right text-amber">{{ $overallPassRate }}%</td>
            </tr>
        </table>
        @endif

        @if($showInsights)
        <div class="section-title">3. Module Performance Insights</div>
        
        <table class="data-table" style="margin-bottom: 10px;">
            <tr>
                <th>Best Performing Module</th>
                <td class="text-right text-green" style="font-size: 14px;">{{ $bestPerformingModule ? $bestPerformingModule->title : 'N/A' }}</td>
            </tr>
            @if($bestPerformingModule)
            <tr>
                <td colspan="2" style="padding-top: 0; padding-bottom: 10px; font-size: 12px; color: #666; text-align: right; border-bottom: 1px solid #eee;">
                    Completion: <strong>{{ $bestPerformingModule->completion_rate }}%</strong> | 
                    MPS: <strong>{{ $bestPerformingModule->assessment_mps }}%</strong>
                </td>
            </tr>
            @endif

            <tr>
                <th>Lowest Performing Module</th>
                <td class="text-right text-red" style="font-size: 14px;">{{ $lowestPerformingModule ? $lowestPerformingModule->title : 'N/A' }}</td>
            </tr>
            @if($lowestPerformingModule)
            <tr>
                <td colspan="2" style="padding-top: 0; padding-bottom: 10px; font-size: 12px; color: #666; text-align: right; border-bottom: 1px solid #eee;">
                    Completion: <strong>{{ $lowestPerformingModule->completion_rate }}%</strong> | 
                    MPS: <strong>{{ $lowestPerformingModule->assessment_mps }}%</strong>
                </td>
            </tr>
            @endif

            <tr>
                <th>Most Engaging Module</th>
                <td class="text-right text-purple" style="font-size: 14px;">{{ ($mostEngagingModule && $mostEngagingModule->engagement_score > 0) ? $mostEngagingModule->title : 'N/A' }}</td>
            </tr>
            @if($mostEngagingModule && $mostEngagingModule->engagement_score > 0)
            <tr>
                <td colspan="2" style="padding-top: 0; padding-bottom: 10px; font-size: 12px; color: #666; text-align: right; border-bottom: 1px solid #eee;">
                    Views: <strong>{{ number_format($mostEngagingModule->views) }}</strong> | 
                    Downloads: <strong>{{ number_format($mostEngagingModule->downloads) }}</strong> | 
                    Enrollments: <strong>{{ number_format($mostEngagingModule->total_enrollments) }}</strong>
                </td>
            </tr>
            @endif
        </table>

        @if(count($topModules) > 0)
            <div class="sub-table-title">Top Performing Modules</div>
            <table class="sub-table">
                <tr>
                    <th style="width: 40%; text-align: left;">Module Title</th>
                    <th style="width: 15%; text-align: right;">Views</th>
                    <th style="width: 15%; text-align: right;">Downloads</th>
                    <th style="width: 15%; text-align: right;">Enrollments</th>
                    <th style="width: 15%; text-align: right;">Comp %</th>
                </tr>
                @foreach($topModules as $tm)
                <tr>
                    <td>{{ $tm->title }}</td>
                    <td style="text-align: right; font-weight: bold; color: #3b82f6;">{{ number_format($tm->views) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($tm->downloads) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #8b5cf6;">{{ number_format($tm->total_enrollments) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #f59e0b;">{{ $tm->completion_rate }}%</td>
                </tr>
                @endforeach
            </table>
        @endif
        @endif

        @if($showTrends)
        @php
            $hasTrendData = collect($trendEnrollments)->sum() > 0 || collect($trendCompletions)->sum() > 0 || collect($trendAssessments)->sum() > 0;
        @endphp
        <div class="section-title" style="page-break-before: always;">4. Activity Trends (Last 30 Days)</div>
        @if(!$hasTrendData)
            <div class="empty-state">No activity recorded in the last 30 days.</div>
        @else
        <table class="sub-table">
            <tr>
                <th style="width: 40%; text-align: left;">Date</th>
                <th style="width: 20%; text-align: right;">Enrollments</th>
                <th style="width: 20%; text-align: right;">Completions</th>
                <th style="width: 20%; text-align: right;">Assessments</th>
            </tr>
            @for($i = 0; $i < count($activityDates); $i++)
            <tr>
                <td>{{ $activityDates[$i] }}</td>
                <td style="text-align: right; font-weight: bold; color: #3b82f6;">{{ number_format($trendEnrollments[$i]) }}</td>
                <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($trendCompletions[$i]) }}</td>
                <td style="text-align: right; font-weight: bold; color: #8b5cf6;">{{ number_format($trendAssessments[$i]) }}</td>
            </tr>
            @endfor
        </table>
        @endif
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