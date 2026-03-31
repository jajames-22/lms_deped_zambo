<!DOCTYPE html>
<html>

<head>
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .certificate-container {
            border: 15px solid #a52a2a;
            /* Your LMS brand color */
            padding: 40px;
            text-align: center;
            background-color: white;
            height: 90%;
            margin: 20px;
        }

        .header {
            font-size: 40px;
            font-weight: bold;
            color: #a52a2a;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .sub-header {
            font-size: 20px;
            color: #555;
            margin-bottom: 40px;
        }

        .student-name {
            font-size: 45px;
            font-weight: bold;
            color: #222;
            border-bottom: 2px solid #ccc;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 30px;
            width: 70%;
        }

        .course-name {
            font-size: 30px;
            color: #a52a2a;
            font-weight: bold;
            margin: 20px 0;
        }

        .footer {
            margin-top: 60px;
            width: 100%;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            display: inline-block;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 16px;
        }

        .cert-id {
            position: absolute;
            bottom: 30px;
            right: 40px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <div class="header">Certificate of Completion</div>
        <div class="sub-header">This is proudly presented to</div>

        <div class="student-name">{{ $studentName }}</div>

        <div class="sub-header">for successfully completing the learning module</div>

        <div class="course-name">"{{ $courseName }}"</div>

        <div class="footer">
            <table style="width: 100%; text-align: center;">
                <tr>
                    <td>
                        <div class="signature-line">
                            <strong>{{ $instructorName }}</strong><br>
                            Instructor
                        </div>
                    </td>
                    <td>
                        <div class="signature-line">
                            <strong>{{ $date }}</strong><br>
                            Date of Completion
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="cert-id">Certificate ID: {{ $certificateId }}</div>
    </div>

    <div class="footer">
        <table style="width: 100%; text-align: center; vertical-align: bottom;">
            <tr>
                <td style="width: 33%;">
                    <div class="signature-line">
                        <strong>{{ $instructorName }}</strong><br>
                        Instructor
                    </div>
                </td>
                <td style="width: 33%;">
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code" style="width: 80px; height: 80px;">
                    <div style="font-size: 10px; color: #888; margin-top: 5px;">Scan to Verify</div>
                </td>
                <td style="width: 33%;">
                    <div class="signature-line">
                        <strong>{{ $date }}</strong><br>
                        Date of Completion
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>