<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Completion</title>
    <style>
        /* Lock the PDF size and margins to prevent any extra pages */
        @page {
            size: A4 landscape;
            margin: 30px; 
        }

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
            border-top: 1px solid #000;
            width: 220px;
            display: inline-block;
            padding-top: 8px;
            font-size: 16px;
            margin-bottom: 10px;
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
    <div class="certificate-container">
        <div class="detailed-header">
            <img src="{{ public_path('images/lms-cert-header.png') }}" class="header-img" alt="Header">
        </div>
        
        <div class="header">Certificate of Completion</div>
        <div class="sub-header">This is proudly presented to</div>

                <div class="student-name">{{ $studentName }}</div>

                <div class="course-label">for successfully completing the learning module</div>

                <div class="course-name">"{{ $courseName }}"</div>

        <table class="footer-table">
            <tr>
                <td>
                    <div class="signature-line">
                        <strong>{{ $instructorName }}</strong><br>
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
                        <strong>{{ $date }}</strong><br>
                        <span style="color: #555; font-size: 14px;">Date of Completion</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="cert-id">Certificate ID: {{ $certificateId }}</div>
    </div>
</body>
</html>