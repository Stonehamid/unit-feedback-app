<!DOCTYPE html>
<html>
<head>
    <title>Report #{{ $report->id }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            margin: 20px;
            font-size: 12px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 { 
            color: #2c3e50; 
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        .info-section {
            margin: 25px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            margin: 8px 0;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #2c3e50;
        }
        .info-value {
            flex: 1;
        }
        .content-section {
            margin: 30px 0;
        }
        .content-title {
            font-size: 16px;
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .content-body {
            line-height: 1.6;
            text-align: justify;
        }
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #7f8c8d;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge-low { background: #28a745; color: white; }
        .badge-medium { background: #ffc107; color: black; }
        .badge-high { background: #fd7e14; color: white; }
        .badge-critical { background: #dc3545; color: white; }
        .badge-draft { background: #6c757d; color: white; }
        .badge-published { background: #17a2b8; color: white; }
        .badge-archived { background: #6c757d; color: white; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $report->title }}</h1>
        <div class="subtitle">
            Report ID: #{{ $report->id }} | Generated: {{ $generated_at }}
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Unit:</div>
            <div class="info-value">{{ $report->unit->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Type:</div>
            <div class="info-value">{{ $report->type ?? 'General Report' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Priority:</div>
            <div class="info-value">
                {{ ucfirst($report->priority ?? 'medium') }}
                @if($report->priority)
                    <span class="badge badge-{{ $report->priority }}">{{ $report->priority }}</span>
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
                {{ ucfirst($report->status ?? 'draft') }}
                @if($report->status)
                    <span class="badge badge-{{ $report->status }}">{{ $report->status }}</span>
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Created By:</div>
            <div class="info-value">{{ $report->admin->name }} ({{ $report->admin->email }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">Created At:</div>
            <div class="info-value">{{ $report->created_at->format('F j, Y H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Last Updated:</div>
            <div class="info-value">{{ $report->updated_at->format('F j, Y H:i') }}</div>
        </div>
    </div>
    
    <div class="content-section">
        <div class="content-title">Report Content</div>
        <div class="content-body">
            {!! nl2br(e($report->content)) !!}
        </div>
    </div>
    
    @if($report->unit->ratings()->exists())
    <div class="page-break"></div>
    <div class="content-section">
        <div class="content-title">Unit Statistics</div>
        
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Ratings</td>
                    <td>{{ $report->unit->ratings()->count() }}</td>
                </tr>
                <tr>
                    <td>Average Rating</td>
                    <td>{{ number_format($report->unit->avg_rating ?? 0, 2) }} / 5</td>
                </tr>
                <tr>
                    <td>Total Messages</td>
                    <td>{{ $report->unit->messages()->count() }}</td>
                </tr>
                <tr>
                    <td>Total Reports</td>
                    <td>{{ $report->unit->reports()->count() }}</td>
                </tr>
            </tbody>
        </table>
        
        @if($report->unit->ratings()->count() > 0)
        <h4>Recent Ratings (Last 5)</h4>
        <table>
            <thead>
                <tr>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->unit->ratings()->latest()->limit(5)->get() as $rating)
                <tr>
                    <td>{{ $rating->reviewer_name }}</td>
                    <td>{{ $rating->rating }} / 5</td>
                    <td>{{ Str::limit($rating->comment, 50) }}</td>
                    <td>{{ $rating->created_at->format('Y-m-d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
    
    <div class="footer">
        <p>Generated by: {{ $generated_by }}</p>
        <p>Generated at: {{ $generated_at }}</p>
        <p>System: App Rating & Feedback Management System</p>
        <p>Confidential - For Internal Use Only</p>
    </div>
</body>
</html>