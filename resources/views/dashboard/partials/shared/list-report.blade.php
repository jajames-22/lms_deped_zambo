<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; line-height: 1.4; margin: 0; }
        header table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        header td { border: none; padding: 0; }
        .title { font-size: 20px; font-weight: bold; color: #a52a2a; text-transform: uppercase; margin-top: 5px; }
        .subtitle { font-size: 11px; color: #666; margin-top: 4px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background-color: #f8f9fa; padding: 10px; font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; border-bottom: 2px solid #ddd; text-align: left; }
        .data-table td { padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #eee; vertical-align: top; }
        
        @if(!isset($isPrint) || !$isPrint)
            @page { margin: 40px; }
        @else
            @media print {
                @page { margin: 0.5in; }
                body { -webkit-print-color-adjust: exact; }
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
                        $logoPath = isset($isPrint) && $isPrint ? asset('storage/images/lms-logo-red.png') : public_path('storage/images/lms-logo-red.png'); 
                    @endphp
                    <img src="{{ $logoPath }}" height="35" alt="Logo" style="margin-bottom: 5px;">
                    <div class="title">{{ config('app.name', 'LMS') }} - {{ $title }}</div>
                    <div class="subtitle">Generated on: {{ now()->format('F j, Y - g:i A') }} | Total Records: {{ $records->count() }}</div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 8px;">
                    <strong style="font-size: 14px; color: #111;">{{ auth()->user()->first_name ?? 'Admin' }} {{ auth()->user()->last_name ?? '' }}</strong><br>
                    <span style="font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px;">Generated Report</span>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    @if($type === 'schools')
                        <th style="width: 35%;">School Name</th>
                        <th style="width: 15%;">School ID</th>
                        <th style="width: 15%;">Level</th>
                        <th style="width: 30%;">District</th>
                    @elseif($type === 'students')
                        <th style="width: 25%;">Student Name</th>
                        <th style="width: 15%;">LRN</th>
                        <th style="width: 15%;">Grade Level</th>
                        <th style="width: 25%;">Assigned School</th>
                        <th style="width: 15%;">Status</th>
                    @elseif($type === 'teachers')
                        <th style="width: 25%;">Teacher Name</th>
                        <th style="width: 15%;">Employee ID</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 25%;">Assigned School</th>
                        <th style="width: 15%;">Status</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $record)
                    <tr>
                        <td style="color: #888;">{{ $index + 1 }}</td>
                        
                        @if($type === 'schools')
                            <td><strong>{{ $record->name }}</strong></td>
                            <td>{{ $record->school_id }}</td>
                            <td>{{ ucfirst($record->level) }}</td>
                            <td>{{ $record->district->name ?? 'N/A' }}</td>
                            
                        @elseif($type === 'students')
                            <td><strong>{{ $record->last_name }}, {{ $record->first_name }}</strong></td>
                            <td>{{ $record->lrn ?? 'N/A' }}</td>
                            <td>{{ $record->grade_level ?? 'N/A' }}</td>
                            <td>{{ $record->school->name ?? 'Unassigned' }}</td>
                            <td>{{ ucfirst($record->status ?? 'Pending') }}</td>
                            
                        @elseif($type === 'teachers')
                            <td><strong>{{ $record->last_name }}, {{ $record->first_name }}</strong></td>
                            <td>{{ $record->employee_id ?? 'N/A' }}</td>
                            <td>{{ $record->email }}</td>
                            <td>{{ $record->school->name ?? 'Unassigned' }}</td>
                            <td>{{ ucfirst($record->status ?? 'Pending') }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; font-style: italic; color: #888; padding: 20px;">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>

    @if(isset($isPrint) && $isPrint)
        <script>window.onload = function () { window.print(); };</script>
    @endif
</body>
</html>