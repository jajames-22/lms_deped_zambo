<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Completion</title>
    <style>
        /* Force A4 Landscape and kill all default margins */
        @page {
            margin: 0;
            size: A4 landscape;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        /* Using a table as the main wrapper is the most stable way 
           to prevent domPDF from clipping the bottom content. */
        .main-wrapper {
            width: 100%;
            height: 100%;
            padding: 30px;
            box-sizing: border-box;
        }

        .outer-border {
            border: 12px solid #a52a2a; /* LMS Brand Color */
            height: 100%;
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        .inner-border {
            border: 2px solid #d3a625; /* Gold Accent */
            height: 100%;
            width: 100%;
            text-align: center;
            box-sizing: border-box;
            padding: 40px 20px;
            position: relative; /* Allows QR/Footer to anchor */
        }

        .header {
            font-size: 44px;
            font-weight: bold;
            color: #a52a2a;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .sub-header {
            font-size: 20px;
            color: #555;
            font-style: italic;
            margin-bottom: 30px;
        }

        .student-name {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 42px;
            font-weight: bold;
            color: #222;
            border-bottom: 2px solid #a52a2a;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 25px;
            width: 70%;
        }

        .course-label {
            font-size: 18px;
            color: #555;
            margin-bottom: 15px;
        }

        .course-name {
            font-size: 28px;
            color: #a52a2a;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 50px;
        }

        /* The Footer Table */
        .footer-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .footer-td {
            width: 33.3%;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-text {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }

        .qr-code {
            width: 80px;
            height: 80px;
        }

        .cert-id {
            margin-top: 20px;
            font-size: 10px;
            color: #999;
            font-family: 'Helvetica', 'Arial', sans-serif;
            text-align: right;
            padding-right: 20px;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="outer-border">
            <div class="inner-border">
                
                <div class="header">Certificate of Completion</div>
                <div class="sub-header">This is proudly presented to</div>

                <div class="student-name">{{ $studentName }}</div>

                <div class="course-label">for successfully completing the learning module</div>

                <div class="course-name">"{{ $courseName }}"</div>

                <table class="footer-table">
                    <tr>
                        <td class="footer-td">
                            <div class="signature-text">{{ $instructorName }}</div>
                            <div class="signature-line">Instructor</div>
                        </td>
                        <td class="footer-td">
                            <img src="data:image/svg+xml;base64,{{ $qrCode }}" class="qr-code">
                            <div style="font-size: 9px; color: #888; margin-top: 5px;">SCAN TO VERIFY</div>
                        </td>
                        <td class="footer-td">
                            <div class="signature-text">{{ $date }}</div>
                            <div class="signature-line">Date of Completion</div>
                        </td>
                    </tr>
                </table>

                <div class="cert-id">ID: {{ $certificateId }}</div>
            </div>
        </div>
    </div>
</body>
</html>