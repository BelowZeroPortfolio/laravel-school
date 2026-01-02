<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #666;
        }
        
        .meta-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        
        .meta-info table {
            width: 100%;
        }
        
        .meta-info td {
            padding: 3px 5px;
        }
        
        .meta-info .label {
            font-weight: bold;
            width: 120px;
        }
        
        .statistics {
            margin-bottom: 20px;
        }
        
        .statistics h2 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
        }
        
        .stat-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .stat-box .number {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-box .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-box .percentage {
            font-size: 9px;
            color: #888;
        }
        
        .stat-box.present .number { color: #22c55e; }
        .stat-box.late .number { color: #f59e0b; }
        .stat-box.absent .number { color: #ef4444; }
        
        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .records-table th,
        .records-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        
        .records-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        
        .records-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .date-header {
            background-color: #e5e5e5;
            font-weight: bold;
            padding: 8px;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-present { background-color: #dcfce7; color: #166534; }
        .status-late { background-color: #fef3c7; color: #92400e; }
        .status-absent { background-color: #fee2e2; color: #991b1b; }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <div class="subtitle">QR Attendance System</div>
    </div>
    
    <div class="meta-info">
        <table>
            <tr>
                <td class="label">School Year:</td>
                <td>{{ $schoolYearName }}</td>
                <td class="label">Class:</td>
                <td>{{ $className }}</td>
            </tr>
            <tr>
                <td class="label">Date Range:</td>
                <td>{{ $dateRange }}</td>
                <td class="label">Generated:</td>
                <td>{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    <div class="statistics">
        <h2>Summary Statistics</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="number">{{ $statistics['total'] }}</div>
                <div class="label">Total Records</div>
            </div>
            <div class="stat-box present">
                <div class="number">{{ $statistics['present']['count'] }}</div>
                <div class="label">Present</div>
                <div class="percentage">{{ $statistics['present']['percentage'] }}%</div>
            </div>
            <div class="stat-box late">
                <div class="number">{{ $statistics['late']['count'] }}</div>
                <div class="label">Late</div>
                <div class="percentage">{{ $statistics['late']['percentage'] }}%</div>
            </div>
            <div class="stat-box absent">
                <div class="number">{{ $statistics['absent']['count'] }}</div>
                <div class="label">Absent</div>
                <div class="percentage">{{ $statistics['absent']['percentage'] }}%</div>
            </div>
        </div>
    </div>
    
    @if($records->isEmpty())
        <p style="text-align: center; padding: 20px; color: #666;">No attendance records found for the selected criteria.</p>
    @else
        @foreach($groupedRecords as $date => $dateRecords)
            <div class="date-header">
                {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                ({{ $dateRecords->count() }} records)
            </div>
            
            <table class="records-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Student ID</th>
                        <th style="width: 15%;">LRN</th>
                        <th style="width: 25%;">Student Name</th>
                        <th style="width: 12%;">Check In</th>
                        <th style="width: 12%;">Check Out</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 11%;">Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dateRecords as $record)
                        <tr>
                            <td>{{ $record->student?->student_id ?? '-' }}</td>
                            <td>{{ $record->student?->lrn ?? '-' }}</td>
                            <td>{{ $record->student?->full_name ?? 'Unknown' }}</td>
                            <td>{{ $record->check_in_time?->format('h:i A') ?? '-' }}</td>
                            <td>{{ $record->check_out_time?->format('h:i A') ?? '-' }}</td>
                            <td>
                                <span class="status-badge status-{{ $record->status }}">
                                    {{ ucfirst($record->status ?? 'unknown') }}
                                </span>
                            </td>
                            <td>{{ $record->recorder?->full_name ?? 'System' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
    
    <div class="footer">
        <p>This report was automatically generated by the QR Attendance System.</p>
        <p>Page generated on {{ $generatedAt }}</p>
    </div>
</body>
</html>
