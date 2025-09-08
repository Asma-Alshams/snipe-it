<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Activity Report</title>
    <style>

        body { 
            font-family: 'dejavu sans';
        }
        .header {
            text-align: center;
        }
        h1 { 
            color: #00008B; 
            font-size: 18px; 
            font-weight: bold;
        }
        .subtitle {
            font-size: 12px;
            color: #333;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            padding: 4px;
            font-size: 9px;
        }
        th { 
            color:grey;
            border: 1px solid #333; 
            text-align: center;
            font-weight: bold;
        }
        td { 
            border: 1px solid #333; 
            padding: 4px; 
            text-align: right;
        }
        .location-change {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
        }
        .footer {
            text-align: center;
            padding: 30px;
            font-size: 10px;
            color: #666;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #999;
        } 
    </style>
</head>
<body>
    <div class="header">
        @if (!empty($logo))
            <img src="{{ $logo }}" alt="Logo" class="logo" style="width: 800px;"/>   
        @endif
        <h1>سجل الأصول</h1>
        
        @if(isset($location) && $location && isset($start_date) && isset($end_date))
            <div class="subtitle">Location: {{ $location->name }}<br>From {{ $start_date }} to {{ $end_date }}</div>
        @elseif(isset($department) && $department && isset($start_date) && isset($end_date))
            <div class="subtitle">Department: {{ $department->name }}<br>From {{ $start_date }} to {{ $end_date }}</div>
        @elseif(isset($location) && $location)
            <div class="subtitle">Location: {{ $location->name }}</div>
        @elseif(isset($department) && $department)
            <div class="subtitle">Department: {{ $department->name }}</div>
        @elseif(isset($start_date) && isset($end_date))
            <div class="subtitle">From {{ $start_date }} to {{ $end_date }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 22%;">ملاحظات</th>
                <th style="width: 12%;">المستلم/ المكان</th>
                <th style="width: 27%;">نوع الأصل</th>
                <th style="width: 15%;">نوع العملية</th>
                <th style="width: 12%;">اعد من قبل</th>
                <th style="width: 12%;">التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr> 
                        <td style="width: 22%;">{{ $row['note'] ?? '' }}</span></td>
                        <td style="width: 12%;">{{ $row['target'] }}</span></td>
                        <td style="width: 27%;">
                        {{ $row['item'] }}</span>
                        @if(isset($row['location_changes']) && $row['location_changes'])
                            <div class="location-change">
                                @if($row['location_changes']['old'])
                                    From: {{ $row['location_changes']['old'] }}
                                @endif
                                @if($row['location_changes']['old'] && $row['location_changes']['new'])
                                    → 
                                @endif
                                @if($row['location_changes']['new'])
                                    To: {{ $row['location_changes']['new'] }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td style="width: 15%;">{{ $row['action'] }}</span></td>
                    <td style="width: 12%;">{{ $row['created_by'] }}</span></td>
                    <td style="width: 12%;">{{ $row['date'] }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-data">No activity found for this period</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <br>
    <div class="footer">
        Generated on {{ date('Y-m-d H:i') }}
    </div>
</body>
</html> 
