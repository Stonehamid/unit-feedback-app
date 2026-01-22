<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Unit Statistics Report - {{ date('Y-m-d') }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 5px; color: #1a1a1a; }
        .subtitle { font-size: 14px; color: #666; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .summary-card { background-color: #f8f9fa; border-radius: 8px; padding: 15px; text-align: center; border: 1px solid #e9ecef; }
        .summary-value { font-size: 24px; font-weight: bold; color: #3b82f6; margin-bottom: 5px; }
        .summary-label { font-size: 12px; color: #6c757d; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { background-color: #3b82f6; color: white; border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold; }
        .table td { border: 1px solid #ddd; padding: 10px; }
        .table tr:nth-child(even) { background-color: #f8f9fa; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; display: inline-block; }
        .badge-open { background-color: #d4edda; color: #155724; }
        .badge-closed { background-color: #f8d7da; color: #721c24; }
        .badge-full { background-color: #fff3cd; color: #856404; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
        .chart-container { margin: 20px 0; }
        .chart-bar { display: flex; align-items: center; margin-bottom: 8px; }
        .chart-label { width: 100px; font-size: 11px; }
        .chart-bar-inner { flex: 1; height: 20px; background-color: #e9ecef; border-radius: 10px; overflow: hidden; }
        .chart-fill { height: 100%; background-color: #3b82f6; }
        .chart-value { width: 60px; text-align: right; font-size: 11px; font-weight: bold; }
        .status-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }
        .status-open { background-color: #28a745; }
        .status-closed { background-color: #dc3545; }
        .status-full { background-color: #ffc107; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Unit Statistics Report</div>
        <div class="subtitle">Comprehensive Analysis of Unit Performance</div>
        <div style="margin-top: 10px; font-size: 12px; color: #666;">
            Generated on: {{ $exportDate }}
        </div>
    </div>
    
    <!-- Summary Section -->
    <div class="section">
        <div class="section-title">📊 Executive Summary</div>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-value">{{ $report['summary']['total_units'] }}</div>
                <div class="summary-label">Total Units</div>
            </div>
            <div class="summary-card">
                <div class="summary-value">{{ $report['summary']['active_units'] }}</div>
                <div class="summary-label">Active Units</div>
            </div>
            <div class="summary-card">
                <div class="summary-value">{{ $report['summary']['featured_units'] }}</div>
                <div class="summary-label">Featured Units</div>
            </div>
            <div class="summary-card">
                <div class="summary-value">{{ $report['summary']['inactive_units'] }}</div>
                <div class="summary-label">Inactive Units</div>
            </div>
        </div>
    </div>
    
    <!-- Status Distribution -->
    <div class="section">
        <div class="section-title">📈 Status Distribution</div>
        <div class="chart-container">
            @foreach($report['status_distribution'] as $status => $count)
            <div class="chart-bar">
                <div class="chart-label">
                    <span class="status-indicator status-{{ strtolower($status) }}"></span>
                    {{ ucfirst(strtolower($status)) }}
                </div>
                <div class="chart-bar-inner">
                    @php
                        $total = array_sum($report['status_distribution']->toArray());
                        $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                    @endphp
                    <div class="chart-fill" style="width: {{ $percentage }}%"></div>
                </div>
                <div class="chart-value">{{ $count }} ({{ round($percentage, 1) }}%)</div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Breakdown by Type -->
    <div class="section">
        <div class="section-title">🏷️ Breakdown by Type</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Count</th>
                    <th>Percentage</th>
                    <th>Avg Rating</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['breakdown']['by_type'] as $type)
                <tr>
                    <td>{{ $type->type }}</td>
                    <td>{{ $type->count }}</td>
                    <td>
                        @php
                            $percentage = $report['summary']['total_units'] > 0 ? ($type->count / $report['summary']['total_units']) * 100 : 0;
                        @endphp
                        {{ round($percentage, 1) }}%
                    </td>
                    <td>{{ number_format($type->avg_rating ?? 0, 1) }} ★</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Top Rated Units -->
    <div class="section">
        <div class="section-title">⭐ Top Rated Units</div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Unit Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Total Ratings</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['top_performers']['top_rated'] as $index => $unit)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $unit->name }}</td>
                    <td>{{ $unit->type }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($unit->status) }}">
                            {{ ucfirst(strtolower($unit->status)) }}
                        </span>
                    </td>
                    <td>{{ number_format($unit->avg_rating, 1) }} ★</td>
                    <td>{{ $unit->ratings_count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Most Active Units -->
    <div class="section">
        <div class="section-title">🔥 Most Active Units (by Ratings)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Unit Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Total Ratings</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['top_performers']['most_active'] as $index => $unit)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $unit->name }}</td>
                    <td>{{ $unit->type }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($unit->status) }}">
                            {{ ucfirst(strtolower($unit->status)) }}
                        </span>
                    </td>
                    <td>{{ $unit->ratings_count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        This report was automatically generated by the Unit Management System<br>
        Report ID: UNIT-STATS-{{ date('YmdHis') }}
    </div>
</body>
</html>