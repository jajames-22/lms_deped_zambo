<!DOCTYPE html>
<html>
<head>
    
    <link rel="icon" type="image/png" href="{{ asset('deped_lms_logo.png') }}">
    <title>Certificate of Completion</title>
    <style>
        /* Lock the PDF size and margins to prevent any extra pages */
        @if(isset($activeTemplate) && !$activeTemplate->is_default)
        @page {
            size: A4 landscape;
            margin: 0px; 
        }
        @else
        @page {
            size: A4 landscape;
            margin: 30px; 
        }
        @endif

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .certificate-container {
            border: 12px solid #a52a2a;
            padding: 10px;
            text-align: center;
            background-color: white;
            position: relative;
            /* Safely constrained height for Landscape A4 (Total ~794px) */
            height: 685px; 
            box-sizing: border-box;
        }

        .header {
            font-size: 42px;
            font-weight: bold;
            color: #a52a2a;
            margin-top: 10px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .sub-header {
            font-size: 20px;
            color: #555;
            margin-top: 10px;
        }

        .student-name {
            font-size: 48px;
            font-weight: bold;
            color: #222;
            border-bottom: 2px solid #a52a2a;
            display: inline-block;
            padding-bottom: 10px;
            margin-bottom: 10px;
            width: 80%;
            margin-top: 10px;
            word-wrap: break-word;
            line-height: 1.2;
        }

        .course-name {
            font-size: 32px;
            color: #a52a2a;
            font-weight: bold;
            margin: 15px 0;
            line-height: 1.3;
        }

        .footer-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: bottom;
            width: 33.33%;
        }

        .signature-line {
            width: 220px;
            display: inline-block;
            padding-top: 8px;
            font-size: 16px;
            margin-bottom: 10px;
            word-wrap: break-word;
        }

        .qr-container {
            display: inline-block;
        }

        .cert-id {
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 11px;
            color: #888;
        }

        .detailed-header {
            width: 100%;
            height: 180px; /* Adjusted slightly so it doesn't push the bottom text off the page */
            text-align: center; /* Safer for PDF generators than Flexbox */
            margin-bottom: 10px;
        }

        .header-img {
            height: 100%; /* Forces image to fit exactly inside the 180px height */
            width: auto;  /* Maintains the correct aspect ratio */
            max-width: 100%; /* Prevents it from spilling out horizontally */
        }
    </style>
</head>
<body>
    @if(isset($activeTemplate) && !$activeTemplate->is_default)
        <div style="position:absolute; left:0; top:0; width:100%; height:100%; background:#ffffff; overflow:hidden; z-index: 1;">
            @if($activeTemplate->background_image)
                <img src="{{ public_path('storage/' . $activeTemplate->background_image) }}" style="position:absolute; left:0; top:0; width:100%; height:100%; z-index:-1;" alt="Background">
            @endif

            @foreach($activeTemplate->elements as $el)
                @php
                    $value = '';
                    $isQr = false;
                    if ($el['id'] === 'student_name') $value = $studentName;
                    elseif ($el['id'] === 'course_name') $value = $courseName;
                    elseif ($el['id'] === 'duration') $value = $duration ? 'Completed in ' . $duration : '';
                    elseif ($el['id'] === 'instructor_name') $value = $instructorName;
                    elseif ($el['id'] === 'date') $value = $date;
                    elseif ($el['id'] === 'certificate_id') $value = $certificateId;
                    elseif ($el['id'] === 'qr_code') $isQr = true;
                    
                    $align = $el['align'] ?? 'left';
                @endphp
                
                @if($isQr)
                    @php
                        $qrSize = $el['size'] ?? 110;
                        $marginLeft = 0;
                        if ($align === 'center') $marginLeft = -($qrSize / 2);
                        elseif ($align === 'right') $marginLeft = -$qrSize;
                    @endphp
                    <div style="position:absolute; left:{{ $el['x'] }}%; top:{{ $el['y'] }}%; margin-left:{{ $marginLeft }}px; width:{{ $qrSize }}px; height:{{ $qrSize }}px; background:#fff; padding:5px; border-radius:8px; box-sizing:border-box;">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code" style="width:100%; height:100%;">
                    </div>
                @elseif($value)
                    @php
                        $cssPos = "left: {$el['x']}%;";
                        $cssWidth = "white-space: nowrap;";
                        $cssAlign = "";
                        if ($align === 'center') {
                            if ($el['x'] <= 50) {
                                $cssPos = "left: 0;";
                                $cssWidth = "width: " . ($el['x'] * 2) . "%;";
                            } else {
                                $cssPos = "right: 0;";
                                $cssWidth = "width: " . ((100 - $el['x']) * 2) . "%;";
                            }
                            $cssAlign = "text-align: center;";
                        } elseif ($align === 'right') {
                            $cssPos = "right: " . (100 - $el['x']) . "%;";
                            $cssAlign = "text-align: right;";
                        }
                    @endphp
                    <div style="position:absolute; top:{{ $el['y'] }}%; {{ $cssPos }} {{ $cssWidth }} {{ $cssAlign }} font-size:{{ $el['fontSize'] ?? 16 }}px; font-weight:{{ $el['fontWeight'] ?? 'normal' }}; color:{{ $el['color'] ?? '#222' }}; line-height: 1;">
                        {{ $value }}
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="certificate-container">
            <div class="detailed-header">
                <img src="{{ public_path('images/lms-cert-header.png') }}" class="header-img" alt="Header">
            </div>
        
        <div class="header">Certificate of Completion</div>
        <div class="sub-header">This is proudly presented to</div>

                <div class="student-name" style="font-size: {{ strlen($studentName) > 40 ? '28px' : (strlen($studentName) > 25 ? '38px' : '48px') }};">{{ $studentName }}</div>

                <div class="course-label">for successfully completing the learning module</div>

                <div class="course-name" style="margin-bottom: 5px;">"{{ $courseName }}"</div>
                @if(isset($duration) && $duration)
                    <div style="color: #555; font-size: 18px; font-style: italic; margin-bottom: 20px;">Completed in {{ $duration }}</div>
                @else
                    <div style="margin-bottom: 30px;"></div>
                @endif

        <table class="footer-table">
            <tr>
                <td>
                    <div class="signature-line">
                        <strong style="font-size: {{ strlen($instructorName) > 25 ? '16px' : (strlen($instructorName) > 15 ? '20px' : '24px') }}; line-height: 1.2;">{{ $instructorName }}</strong><br>
                        <span style="color: #555; font-size: 14px;">Instructor</span>
                    </div>
                </td>
                
                <td>
                    <div class="qr-container">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code" style="width: 110px; height: 110px;">
                        <div style="font-size: 11px; color: #555; margin-top: 5px; font-weight: bold; text-transform: uppercase;">Scan to Verify</div>
                    </div>
                </td>
                
                <td>
                    <div class="signature-line">
                        <strong style="font-size: 24px;">{{ $date }}</strong><br>
                        <span style="color: #555; font-size: 14px;">Date of Completion</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="cert-id">Certificate ID: {{ $certificateId }}</div>
    </div>
    @endif
</body>
</html>