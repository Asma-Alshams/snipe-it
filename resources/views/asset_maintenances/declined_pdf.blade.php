<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Periodic Asset Maintenance Checklist - Declined</title>
    <style>
        @page {
            margin: 2mm 6mm 2mm 6mm; /* top, right, bottom, left */ 
            size: landscape; 
        }
        body { 
            font-family: 'aealarabiya', sans-serif;
            font-weight: 500; 
            text-align: center;
        }
       h1 { 
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
    <h1>قائمة الصيانة المرفوضة</h1>
    @if(isset($filter) && $start_date && $end_date)
        @if($filter === 'declined')
            <p style="text-align:center;">من: {{ $start_date }} الى {{ $end_date }}</p>
        @endif
    @endif
    <table>
        <thead>
            <tr>
                <th class="thead">التوقيع</th>
                <th class="thead">ملاحظات  الرفض</th>
                <th class="thead">مستوى الخطر</th>
                <th class="thead">حالة الصيانة</th>
                <th class="thead">نوع الصيانة</th>
                <!-- <th> اعد من قبل</th> -->
                <th class="thead">صاحب الأصل</th>
                <th class="thead">الاصل</th>
                <th class="thead">القسم</th>
                <!-- <th>#</th> -->
               
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $m)
                <tr>
                    <td class="tdata">
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
                    <td class="tdata">
                        @php
                            $acceptance = $m->maintenanceAcceptances->first();
                        @endphp
                        {{ $acceptance->note ?? '-' }}
                    </td>
                    <td class="tdata">{{ ucfirst($m->risk_level ?? '-') }}</td>
                    <td class="tdata">{{ $m->maintenanceStatus ?? '-' }}</td>
                    
                    <td class="tdata">{{ $m->asset_maintenance_type ?? '-' }}</td>
                    <!-- <td>{{ $m->adminuser ? $m->adminuser->present()->name() : '-' }}</td> -->
                   
                    <td class="tdata">
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
                    <td class="tdata">
                        @if($m->asset)
                            {{ $m->asset->name ?? '-' }} <br> ({{ $m->asset->asset_tag ?? '-' }}) {{ $m->asset->model->name ?? '-' }}@if($m->assignedUser)
                                <br> -> {{ method_exists($m->assignedUser, 'present') && $m->assignedUser->present() ? $m->assignedUser->present()->fullName() : trim(($m->assignedUser->first_name ?? '') . ' ' . ($m->assignedUser->last_name ?? '')) }}
                            @endif
                        @else
                            -
                        @endif
                     </td>
                    <td class="tdata">{{ $originalUser && $originalUser->department ? $originalUser->department->name : '-' }}</td>
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