<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Licenses Report</title>
    <style>
        @page {
            margin: 2mm 6mm 2mm 6mm; /* top, right, bottom, left */ 
            size: landscape; 
        }
        body { font-weight: 500; }
        h2{ text-align: center; color: #00008B; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; text-align:right; }
        th, td { border: 1px solid #333; padding: 5px; }
        th { color: grey; }
    </style>
    @if ($logo ?? false)
        <center>
            <img width="90%" src="{{ $logo }}">
        </center>
    @endif
</head>
<body>
    
    <h2>تقرير التراخيص</h2>
    
    @if(isset($filter) && $start_date && $end_date)
        @if($filter === 'all')
            <p style="text-align:center;">التي تم إعدادها من: {{ $start_date }} إلى {{ $end_date }}</p>
        @elseif($filter === 'expiration_date')
            <p style="text-align:center;">تاريخ الانتهاء من: {{ $start_date }} إلى {{ $end_date }}</p>
        @elseif($filter === 'purchase_date')
            <p style="text-align:center;">التي تم شرائها من: {{ $start_date }} إلى {{ $end_date }}</p>
        @else
            <p style="text-align:center;">من: {{ $start_date }} إلى {{ $end_date }}</p>
        @endif
    @else
        <p style="text-align:center;">تقرير شامل لجميع التراخيص</p>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>اعد  <br> من قبل</th>
                <th>تكلفة <br> الشراء</th>
                <th>تاريخ <br> الإنهاء</th>
                <th>تاريخ <br> الشراء</th>
                <th>المتاح</th>
                <th>الإجمالي</th>
                <th>الحد <br> الأدنى</th>
                <th>المصنع</th>
                <th>التوقيع</th>
                <th>مرخص <br> باسم</th>
                <th>البريد الإلكتروني للمرخص له</th>
                <th>تاريخ  <br> الانتهاء</th>
                <th>الاسم</th>
                <th>#</th>
            </tr>
        </thead>
        <tbody>
            @foreach($licenses as $license)
                <tr>
                    <td>
                        @if($license->adminuser)
                            @if(method_exists($license->adminuser, 'present') && $license->adminuser->present())
                                {{ $license->adminuser->present()->fullName() }}
                            @else
                                {{ trim(($license->adminuser->first_name ?? '') . ' ' . ($license->adminuser->last_name ?? '')) }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $license->purchase_cost ?? '-' }}</td>
                    <td>{{ $license->termination_date ? \Carbon\Carbon::parse($license->termination_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $license->purchase_date ? \Carbon\Carbon::parse($license->purchase_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $license->free_seat_count ?? '-' }}</td>
                    <td>{{ $license->seats ?? '-' }}</td>
                    <td>{{ $license->min_amt ?? '-' }}</td>
                    <td>{{ $license->manufacturer ? $license->manufacturer->name : '-' }}</td>
                    <td> </td>
                    <td>{{ $license->license_name ?? '-' }}</td>
                    <td>{{ $license->license_email ?? '-' }}</td>
                    <td>{{ $license->expiration_date ? \Carbon\Carbon::parse($license->expiration_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $license->name ?? '-' }}</td>
                    <td>{{ $license->id }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <p style="margin-top:40px; text-align:center;">
    تم إنشاء التقرير في {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 