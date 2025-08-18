<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Activity Report</title>
    <style>
        @page {
            margin: 2mm 10mm 2mm 10mm; /* top, right, bottom, left */      }
        body { font-size: 16px; font-weight: 400 !important; direction: rtl; }
        h2 { text-align: center; color: #00008B; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; text-align: right; }
        th { color: grey;  }
    </style>
</head>
<body>
    @if (!empty($logo))
        <img src="{{ $logo }}" alt="Logo" width="100%"; />   
    @endif
    <h2>سجل الأصول </h2>
    @if(isset($location) && $location && isset($start_date) && isset($end_date))
        <p style="text-align:center;">لموقع: {{ $location->name }}<br>من {{ $start_date }} الى {{ $end_date }}</p>
    @elseif(isset($department) && $department && isset($start_date) && isset($end_date))
        <p style="text-align:center;">لقسم: {{ $department->name }}<br>من {{ $start_date }} الى {{ $end_date }}</p>
    @elseif(isset($location) && $location)
        <p style="text-align:center;">لموقع: {{ $location->name }}</p>
    @elseif(isset($department) && $department)
        <p style="text-align:center;">لقسم: {{ $department->name }}</p>
    @elseif(isset($start_date) && isset($end_date))
        <p style="text-align:center;">من {{ $start_date }} الى {{ $end_date }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>ملاحظات</th>
                <th>\ المستلم<br> المكان</th>
                <th>نوع الاصل</th>
                <th>نوع العملية</th>
                <th>اعد <br> من قبل</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr> 
                    <td>{{ $row['note'] ?? '' }}</td>
                    <td>{{ $row['target'] }}</td>
                    <td>{{ $row['item'] }}</td>
                    <td>{{ $row['action'] }}</td>
                    <td>{{ $row['created_by'] }}</td>
                    <td>{{ $row['date'] }}</td>
                  
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">No activity found for this period.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p style="margin-top:40px; text-align:center;">
        التاريخ {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 
