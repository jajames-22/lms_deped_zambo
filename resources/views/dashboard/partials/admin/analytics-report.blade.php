<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Analytics Report</title>
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
        
        .empty-state { padding: 15px; background-color: #f9fafb; border: 1px dashed #e5e7eb; color: #6b7280; text-align: center; font-style: italic; font-size: 13px; margin-bottom: 20px; }

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
                        
                        <div class="title">Admin Analytics Report</div>
                        <div class="subtitle">Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 3px;">
                        <strong style="font-size: 16px; color: #111;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong><br>
                        <span style="font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px;">Administrator Account</span>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <main>
        @if($showUsers)
        <div class="section-title">1. User & Demographics</div>
        <table class="data-table">
            <tr>
                <th>Total Students</th>
                <td class="text-right">{{ number_format($totalStudents) }}</td>
            </tr>
            <tr>
                <th>Total Teachers</th>
                <td class="text-right">{{ number_format($totalTeachers) }}</td>
            </tr>
            <tr>
                <th>Total Schools</th>
                <td class="text-right">{{ number_format($totalSchools) }}</td>
            </tr>
            <tr>
                <th>Daily Active Users (Last 24h)</th>
                <td class="text-right text-green">{{ number_format($dailyActiveUsers) }}</td>
            </tr>
            <tr>
                <th>Weekly Active Users (Last 7 Days)</th>
                <td class="text-right text-green">{{ number_format($weeklyActiveUsers) }}</td>
            </tr>
        </table>

        @if(count($schoolLabels) > 0)
            <div class="sub-table-title">Top Schools by Student Count</div>
            <table class="sub-table">
                <tr>
                    <th style="width: 75%; text-align: left;">School Name</th>
                    <th style="width: 25%; text-align: right;">Students</th>
                </tr>
                @for($i = 0; $i < count($schoolLabels); $i++)
                <tr>
                    <td>{{ $schoolLabels[$i] }}</td>
                    <td style="text-align: right; font-weight: bold; color: #8b5cf6;">{{ number_format($schoolData[$i]) }}</td>
                </tr>
                @endfor
            </table>
        @endif
        @endif

        @if($showContent)
        <div class="section-title">2. Content, Engagement & Performance</div>
        <table class="data-table">
            <tr>
                <th>Total Materials Published</th>
                <td class="text-right">{{ number_format($totalMaterials) }}</td>
            </tr>
            <tr>
                <th>Total Enrollments</th>
                <td class="text-right">{{ number_format($totalEnrollments) }}</td>
            </tr>
            <tr>
                <th>Global Completion Rate</th>
                <td class="text-right text-green">{{ $completionRate }}%</td>
            </tr>
            <tr>
                <th>Total Assessments Published</th>
                <td class="text-right">{{ number_format($totalAssessments) }}</td>
            </tr>
            <tr>
                <th>Global Assessment Success Rate</th>
                <td class="text-right text-blue">{{ $globalSuccessRate }}%</td>
            </tr>
        </table>

        @if(count($topMaterialsLabels) > 0)
            <div class="sub-table-title">Most Viewed Materials</div>
            <table class="sub-table">
                <tr>
                    <th style="width: 75%; text-align: left;">Material Title</th>
                    <th style="width: 25%; text-align: right;">Total Views</th>
                </tr>
                @for($i = 0; $i < count($topMaterialsLabels); $i++)
                <tr>
                    <td>{{ $topMaterialsLabels[$i] }}</td>
                    <td style="text-align: right; font-weight: bold; color: #8b5cf6;">{{ number_format($topMaterialsData[$i]) }}</td>
                </tr>
                @endfor
            </table>
        @endif
        @endif

        @if($showHealth)
        <div class="section-title">3. System Health & Resources</div>
        <table class="data-table">
            <tr>
                <th>Storage Usage</th>
                <td class="text-right" style="{{ $storagePercentage > 80 ? 'color: #dc2626;' : 'color: #16a34a;' }}">
                    {{ $storagePercentage }}% ({{ $usedGb }} GB / {{ $totalGb }} GB)
                </td>
            </tr>
        </table>
        @endif
    </main>

    <footer>
        <div class="footer-inner">
            <table>
                <tr>
                    <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS Platform') }} • Official Admin Generated Report</td>
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