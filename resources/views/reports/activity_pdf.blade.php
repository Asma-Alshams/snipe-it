<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Activity Report</title>
    <style>
        @page {
            margin: 2mm 10mm 2mm 10mm; /* top, right, bottom, left */      }
        body { font-size: 16px; font-weight: 400 !important; }
        h2 { text-align: center; color: #00008B; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { color: grey;  }
    </style>
</head>
<body>
    @if (!empty($logo))
        <img src="{{ $logo }}" alt="Logo" width="100%"; />   
    @endif
    <h2>Activity Report <br> تقرير النشاط</h2>
    @if(isset($location) && $location)
        <p style="text-align:center;">For Location: {{ $location->name }}</p>
    @elseif(isset($start_date) && isset($end_date))
        <p style="text-align:center;">From {{ $start_date }} to {{ $end_date }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Created By</th>
                <th>Action</th>
                <th>Item</th>
                <th>Target</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['created_by'] }}</td>
                    <td>{{ $row['action'] }}</td>
                    <td>{{ $row['item'] }}</td>
                    <td>{{ $row['target'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">No activity found for this period.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p style="margin-top:40px; text-align:center;">
        Generated on {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 
