<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Periodic Asset Maintenance Checklist</title>
    <style>
           @page {
  margin: 2mm 6mm 2mm 6mm; /* top, right, bottom, left */ size: landscape; }
        body { font-weight: 500; }
        h2 { text-align: center; color: #00008B; font-weight: 600; }
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
    @if(isset($filter) && $filter === 'all')
        <h2>قائمة جميع الصيانات </h2>
    @else
        <h2>قائمة الصيانة الدورية للأصول </h2>
    @endif
    @if(isset($filter) && $start_date && $end_date)
        @if($filter === 'created_at')
            <p style="text-align:center;">التي تم اعدادها من: {{ $start_date }} الى {{ $end_date }}</p>
        @elseif($filter === 'maintenance_date')
            <p style="text-align:center;">تاريخ الصيانة من: {{ $start_date }} الى {{ $end_date }}</p>
        @elseif($filter === 'all')
            <p style="text-align:center;">من: {{ $start_date }} الى {{ $end_date }}</p>
       
        @elseif($filter === 'department')
            <p style="text-align:center;">من: {{ $start_date }} الى {{ $end_date }}</p>
        @else
            <p style="text-align:center;">من: {{ $start_date }} الى {{ $end_date }}</p>
        @endif
    @endif
    <table>
        <thead>
            <tr>
                <th>التوقيع</th>
        <th> الانتهاء تاريخ</th>
                <th>البدء تاريخ</th>
                <th>الصيانة طريقة</th>
                <th>مستوى <br>الخطر</th>
                <th>نوع الصيانة</th>
                <th> اعد
                    <br>من قبل</th>
                <th>حالة الصيانة</th>
                <th>ل مخصص</th>
                <th>الأصل</th>
                <th>القسم</th>
                <th>#</th>
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $m)
                <tr>
                    <td>
                        @php
                            $acceptance = $m->maintenanceAcceptances->first();
                            $signature = $acceptance && $acceptance->signature_filename
                                ? asset('uploads/signatures/' . $acceptance->signature_filename)
                                : null;
                        @endphp
                        @if($signature)
                            <img src="{{ $signature }}" alt="Signature" style="max-width:150px;" />
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $m->completion_date ?? '-' }}</td>
                    <td>{{ $m->start_date ?? '-' }}</td>
                    <td>{{ $m->repair_method ?? '-' }}</td>
                    <td>{{ ucfirst($m->risk_level ?? '-') }}</td>
                    <td>{{ $m->asset_maintenance_type ?? '-' }}</td>
                    <td>{{ $m->adminuser ? $m->adminuser->present()->name() : '-' }}</td>
                    <td>{{ $m->maintenanceStatus ?? '-' }}</td>
                    <td>
                        @php
                            $acceptance = $m->maintenanceAcceptances->first();
                            $originalUser = $acceptance ? \App\Models\User::find($acceptance->assigned_to_id) : null;
                        @endphp
                        @if($originalUser)
                            @if(method_exists($originalUser, 'present') && $originalUser->present())
                                {{ $originalUser->present()->fullName() }}
                            @else
                                {{ trim(($originalUser->first_name ?? '') . ' ' . ($originalUser->last_name ?? '')) }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($m->asset)
                            {{ $m->asset->name ?? '-' }} <br> ({{ $m->asset->asset_tag ?? '-' }}) {{ $m->asset->model->name ?? '-' }}@if($m->assignedUser)
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $originalUser && $originalUser->department ? $originalUser->department->name : '-' }}</td>
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