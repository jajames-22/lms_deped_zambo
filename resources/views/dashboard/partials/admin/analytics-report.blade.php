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
        
        header table { width: 100%; border-collapse: collapse; }
        header td { border: none; padding: 0; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 10px; letter-spacing: 0.5px; }
        .subtitle { font-size: 11px; color: #666; margin-top: 5px; }

        /* Section & Table Formatting */
        .section-title { font-size: 16px; font-weight: bold; color: #a52a2a; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; page-break-after: avoid; }
        
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

        /* =======================================================
           2. DOMPDF SPECIFIC STYLES (When Download PDF is clicked)
           ======================================================= */
        @if(!isset($isPrint) || !$isPrint)
            @page { 
                margin: 140px 40px 80px 40px; /* Top margin is exactly 140px to prevent overlap */
            }
            
            header { 
                position: fixed; 
                top: -140px;     /* Match the top margin to stick to the ceiling */
                left: -40px;     /* Stretch into the left margin */
                right: -40px;    /* Stretch into the right margin */
                height: 90px; 
                background-color: #ffffff; 
                padding: 30px 40px 10px 40px; /* Pad content back into the center */
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
            .page-number:before { content: "Page " counter(page); }
        @endif

        /* =======================================================
           3. BROWSER PRINT SPECIFIC STYLES (When Print is clicked)
           ======================================================= */
        @if(isset($isPrint) && $isPrint)
            @media print {
                @page { margin: 0.5in; }
                body { padding: 0; margin: 0; }
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
                .page-number { display: none; } /* Browsers add their own page numbers */
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
                    
                    <div class="title">Admin Analytics Report</div>
                    <div class="subtitle">Generated on: {{ now()->format('F j, Y - g:i A') }}</div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 8px;">
                    <strong style="font-size: 16px; color: #111;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong><br>
                    <span style="font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px;">{{ auth()->user()->role ?? 'Admin' }} Account</span>
                </td>
            </tr>
        </table>
    </header>

    <main>
        @if($showUsers)
        <div class="section-title">1. User Demographics</div>
        <table class="data-table">
            <tr>
                <th>Total Registered Users</th>
                <td class="text-right">{{ number_format($totalStudents + $totalTeachers) }}</td>
            </tr>
            <tr>
                <th>Daily Active Users</th>
                <td class="text-right">{{ number_format($dailyActiveUsers) }}</td>
            </tr>
            <tr>
                <th>Weekly Active Users</th>
                <td class="text-right">{{ number_format($weeklyActiveUsers ?? 0) }}</td>
            </tr>
            <tr>
                <th>Total Participating Schools</th>
                <td class="text-right text-green">{{ number_format($totalSchools) }}</td>
            </tr>
        </table>

        @if(count($topSchools) > 0)
            <div class="sub-table-title">Top Schools by Student Count</div>
            <table class="sub-table">
                <tr>
                    <th style="width: 75%; text-align: left;">School Name</th>
                    <th style="width: 25%; text-align: right;">Total Students</th>
                </tr>
                @foreach($topSchools as $school)
                <tr>
                    <td>{{ $school['name'] }}</td>
                    <td style="text-align: right; font-weight: bold; color: #3b82f6;">{{ number_format($school['count']) }}</td>
                </tr>
                @endforeach
            </table>
        @endif
        @endif

        @if($showContent)
        <div class="section-title">2. Content & Engagement</div>
        <table class="data-table">
            <tr>
                <th>Total Platform Modules</th>
                <td class="text-right">{{ number_format($totalMaterials) }}</td>
            </tr>
            <tr>
                <th>Total Enrollments</th>
                <td class="text-right">{{ number_format($totalEnrollments ?? 0) }}</td>
            </tr>
            <tr>
                <th>Overall Completion Rate</th>
                <td class="text-right text-green">{{ $completionRate ?? 0 }}%</td>
            </tr>
        </table>

        @if(count($topMaterials) > 0)
            <div class="sub-table-title">Most Popular Modules (By Views)</div>
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

        @if($showHealth)
        <div class="section-title">3. System Health & Storage</div>
        <table class="data-table">
            <tr>
                <th>Total Server Capacity</th>
                <td class="text-right">{{ $totalGb }} GB</td>
            </tr>
            <tr>
                <th>Used Storage Space</th>
                <td class="text-right {{ $storagePercentage > 85 ? 'text-red' : '' }}">
                    {{ $usedGb }} GB ({{ $storagePercentage }}%)
                </td>
            </tr>
            <tr>
                <th>Available Storage Space</th>
                <td class="text-right text-green">{{ $totalGb - $usedGb }} GB</td>
            </tr>
        </table>
        @endif
    </main>

    <footer>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; width: 80%;">{{ config('app.name', 'LMS Platform') }} • Official System Generated Report</td>
                <td style="text-align: right; width: 20%;" class="page-number"></td>
            </tr>
        </table>
    </footer>

    @if(isset($isPrint) && $isPrint)
    <script>
        // Automatically open the print dialog when the page loads via the "Print" button
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</body>
</html>