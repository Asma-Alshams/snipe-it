<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Periodic Asset Maintenance Checklist - Declined</title>
    <style>
           @page {
  margin: 2mm 6mm 2mm 6mm; /* top, right, bottom, left */ size: landscape; }
        body { font-weight: 500; }
        h2, h4 { text-align: center; color: #00008B; font-weight: 600; }
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
    <h2>قائمة الصيانةالمرفوضة</h2>
    @if(isset($filter) && $start_date && $end_date)
        @if($filter === 'created_at')
            <p style="text-align:center;">اعد التقرير من: {{ $start_date }} الى {{ $end_date }}</p>
        @elseif($filter === 'maintenance_date')
            <p style="text-align:center;">تاريخ الصيانة من: {{ $start_date }} الى {{ $end_date }}</p>
        @endif
    @endif
    <table>
        <thead>
            <tr>
                <th>التوقيع</th>
                 <th>ملاحظات الرفض</th>
        
                <th>الصيانة نوع</th>
                <th> اعد<br>من قبل</th>
                <th>ل مخصص</th>
                <th>الأصل</th>
                <th>القسم</th>
                <th>الرقم</th>
               
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $m)
                <tr>
                    <td>
                        @php
                            $acceptance = $m->maintenanceAcceptances->where('assigned_to_id', $m->asset->assigned_to ?? null)->first();
                            $signature = $acceptance && $acceptance->signature_filename
                                ? asset('uploads/signatures/' . $acceptance->signature_filename)
                                : null;
                        @endphp
                        @if($signature)
                            <img src="{{ $signature }}" alt="Signature" style="max-width:200px;" />
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $acceptance = $m->maintenanceAcceptances->where('assigned_to_id', $m->asset->assigned_to ?? null)->first();
                        @endphp
                        {{ $acceptance->note ?? '-' }}
                    </td>
                    
                    <td>{{ $m->asset_maintenance_type ?? '-' }}</td>
                    <td>{{ $m->adminuser ? $m->adminuser->present()->name() : '-' }}</td>
                   
                    <td>
                        @if($m->assignedUser)
                            @if(method_exists($m->assignedUser, 'present') && $m->assignedUser->present())
                                {{ $m->assignedUser->present()->fullName() }}
                            @else
                                {{ trim(($m->assignedUser->first_name ?? '') . ' ' . ($m->assignedUser->last_name ?? '')) }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($m->asset && method_exists($m->asset, 'present') && $m->asset->present())
                            {{ $m->asset->present()->fullName() }}
                        @else
                            -
                        @endif
                     </td>
                    <td>{{ $m->assignedUser && $m->assignedUser->department ? $m->assignedUser->department->name : '-' }}</td>
                    <td>{{ $m->id }}</td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:40px; text-align:center;">
       تاريخ {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 