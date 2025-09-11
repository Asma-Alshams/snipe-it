<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Periodic Asset Maintenance Checklist</title>
    <style>
        @page {
            margin: 2mm 6mm 2mm 6mm; /* top, right, bottom, left */ 
            size: landscape; 
        }
        body { 

            font-weight: 500; 
            text-align: center;
        }
        h1{ 
            text-align: center; 
            color: #00008B; 
            font-weight: 600;
            font-family: 'aealarabiya', sans-serif;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align:right;
            padding: 3px;
        }
        .tdata {
            font-family: 'notonaskharabicnormal', sans-serif;
            border: 1px solid #333; 
            font-size: 9px;
        }
        .thead {
            font-family: 'aealarabiya', sans-serif;
            border: 1px solid #333; 
            color: grey;
            font-size: 13px;
            padding: 5px; 
        }
    </style>
</head>
<body>
    @if ($logo ?? false)
        <center>
            <img src="{{ $logo }}" alt="Logo" class="logo" style="width: 700px;"/>
        </center>
    @endif
    @if(isset($filter) && ($filter === 'all' || $filter === 'created_at'))
        <h1 style='font-size: 16px;'>قائمة جميع الصيانات </h1>
    @else
        <h2>قائمة الصيانة الدورية للأجهزة المحمولة واللوحية 
            <br>
            لموظفين الامانة العامة
        </h2>
    @endif
    @if(isset($filter) && $start_date && $end_date)
        @if($filter === 'created_at')
            <p style="text-align:center; font-family: 'aealarabiya', sans-serif;">التي تم اعدادها من: {{ $start_date }} الى {{ $end_date }}</p>     
        @else
        <p style="text-align:center; font-family: 'aealarabiya', sans-serif;">تاريخ الصيانة من: {{ $start_date }} الى {{ $end_date }}</p>
        @endif
    @endif
    <table>
        <thead>
            <tr>
                <th style="width: 16%;" class="thead">توقيع صاحب الأصل</th>
                <th style="width: 7%;" class="thead"> تاريخ الانتهاء </th>
                <th style="width: 7%;" class="thead"> تاريخ البدء </th>
                <th style="width: 13%;" class="thead"> طريقة الصيانة </th>
                <th style="width: 7%;" class="thead">مستوى الخطر</th>
                <th style="width: 9%;" class="thead">نوع الصيانة</th>
                <!-- <th> اعد من قبل</th> -->
                <th style="width: 9%;" class="thead">حالة الصيانة</th>
                <th style="width: 9%;" class="thead">صاحب الأصل</th>
                <th style="width: 15%;" class="thead">الأصل</th>
                <th style="width: 8%;" class="thead">القسم</th>
                <!-- <th>#</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $m)
                <tr>
                    <td style="width: 16%;" class="tdata">
                        @php
                            $acceptance = $m->maintenanceAcceptances->first();
                            $signature = $acceptance && $acceptance->signature_filename
                                ? asset('uploads/signatures/' . $acceptance->signature_filename)
                                : null;
                        @endphp
                        @if($signature)
                            <img src="{{ $signature }}" alt="Signature" style="width:200px;" />
                        @else
                            -
                        @endif
                    </td>
                    <td style="width: 7%;" class="tdata">{{ $m->completion_date ?? '-' }}</td>
                    <td style="width: 7%;" class="tdata">{{ $m->start_date ?? '-' }}</td>
                    <td style="width: 13%;" class="tdata">{{ $m->repair_method ?? '-' }}</td>
                    <td style="width: 7%;" class="tdata">{{ ucfirst($m->risk_level ?? '-') }}</td>
                    <td style="width: 9%;" class="tdata">{{ $m->asset_maintenance_type ?? '-' }}</td>
                    <!-- <td>{{ $m->adminuser ? $m->adminuser->present()->name() : '-' }}</td> -->
                    <td style="width: 9%;" class="tdata">{{ $m->maintenanceStatus ?? '-' }}</td>
                    <td style="width: 9%;" class="tdata">
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
                    <td style="width: 15%;" class="tdata">
                        @if($m->asset)
                            {{ $m->asset->name ?? '-' }} <br> ({{ $m->asset->asset_tag ?? '-' }}) {{ $m->asset->model->name ?? '-' }}@if($m->assignedUser)
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="width: 8%;" class="tdata">{{ $originalUser && $originalUser->department ? $originalUser->department->name : '-' }}</td>
                    <!-- <td>{{ $m->id }}</td> -->
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:40px; text-align:center;">
       تاريخ {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 