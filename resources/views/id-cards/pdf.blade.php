<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Cards - {{ $schoolYear }}</title>
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
        }

        .page {
            page-break-after: always;
            padding: 10mm;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .id-cards-grid {
            display: table;
            width: 100%;
        }

        .id-card-row {
            display: table-row;
        }

        .id-card-cell {
            display: table-cell;
            width: 50%;
            padding: 5mm;
            vertical-align: top;
        }

        .id-card {
            border: 2px solid #1a365d;
            border-radius: 8px;
            width: 85.6mm;
            height: 53.98mm;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f7fafc 100%);
        }

        .id-card-header {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            color: white;
            padding: 3mm;
            text-align: center;
        }

        .school-name {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-title {
            font-size: 8px;
            margin-top: 1mm;
            opacity: 0.9;
        }

        .id-card-body {
            display: table;
            width: 100%;
            padding: 3mm;
        }

        .photo-section {
            display: table-cell;
            width: 25mm;
            vertical-align: top;
        }

        .photo-placeholder {
            width: 22mm;
            height: 28mm;
            border: 1px solid #cbd5e0;
            background: #edf2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #718096;
            text-align: center;
        }

        .photo-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-section {
            display: table-cell;
            vertical-align: top;
            padding-left: 3mm;
        }

        .student-name {
            font-size: 12px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 2mm;
        }

        .info-row {
            margin-bottom: 1mm;
        }

        .info-label {
            font-size: 7px;
            color: #718096;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 9px;
            color: #2d3748;
            font-weight: 500;
        }

        .qr-section {
            display: table-cell;
            width: 20mm;
            vertical-align: top;
            text-align: right;
        }

        .qr-code {
            width: 18mm;
            height: 18mm;
            border: 1px solid #e2e8f0;
            background: white;
            padding: 1mm;
        }

        .qr-code img {
            width: 100%;
            height: 100%;
        }

        .id-card-footer {
            background: #edf2f7;
            padding: 2mm 3mm;
            font-size: 7px;
            color: #4a5568;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer-text {
            margin-top: 10mm;
            text-align: center;
            font-size: 8px;
            color: #718096;
        }
    </style>
</head>
<body>
    @php $chunkedCards = $idCards->chunk(6); @endphp
    
    @foreach($chunkedCards as $pageCards)
    <div class="page">
        <div class="id-cards-grid">
            @foreach($pageCards->chunk(2) as $rowCards)
            <div class="id-card-row">
                @foreach($rowCards as $card)
                <div class="id-card-cell">
                    <div class="id-card">
                        <div class="id-card-header">
                            <div class="school-name">QR Attendance School</div>
                            <div class="card-title">Student Identification Card</div>
                        </div>
                        
                        <div class="id-card-body">
                            <div class="photo-section">
                                <div class="photo-placeholder">
                                    @if($card['photo_path'])
                                        <img src="{{ storage_path('app/public/' . $card['photo_path']) }}" alt="Photo">
                                    @else
                                        No Photo
                                    @endif
                                </div>
                            </div>
                            
                            <div class="info-section">
                                <div class="student-name">{{ $card['full_name'] }}</div>
                                
                                @if($card['lrn'])
                                <div class="info-row">
                                    <div class="info-label">LRN</div>
                                    <div class="info-value">{{ $card['lrn'] }}</div>
                                </div>
                                @endif
                                
                                <div class="info-row">
                                    <div class="info-label">Student ID</div>
                                    <div class="info-value">{{ $card['student_id'] }}</div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-label">Grade & Section</div>
                                    <div class="info-value">{{ $card['grade_level'] ?? 'N/A' }} - {{ $card['section'] ?? 'N/A' }}</div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-label">School Year</div>
                                    <div class="info-value">{{ $card['school_year'] ?? 'N/A' }}</div>
                                </div>
                            </div>
                            
                            <div class="qr-section">
                                <div class="qr-code">
                                    @if($card['qrcode_path'])
                                        <img src="{{ storage_path('app/public/' . $card['qrcode_path']) }}" alt="QR Code">
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="id-card-footer">
                            S.Y. {{ $card['school_year'] ?? $schoolYear }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        
        <div class="footer-text">
            Generated on {{ $generatedAt }}
        </div>
    </div>
    @endforeach
</body>
</html>
