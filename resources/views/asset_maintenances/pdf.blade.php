<!DOCTYPE html>
<html  lang="en ">
<head>
    <meta charset="utf-8">
    <title>Asset Maintenance Report</title>
    <style>
        body { 
            font-weight: 500;
            font-family: 'aealarabiya', sans-serif;
        }
        h2, h4 { 
            text-align: center; 
            color: #00008B; 
            font-weight: 600;
            font-family: 'aealarabiya', sans-serif;
        }
        table { 
            border-collapse: collapse; 
            margin-top: 20px; 
            text-align: right; 
            padding: 7px;
        }
        th {
            font-family: 'notonaskharabicnormal', sans-serif;
            border: 1px solid #333; 
            width: 75%;
        }
        .thead {
            border: 1px solid #333; 
            color: grey;
            font-family: 'aealarabiya', sans-serif;
            color: grey;
            font-size: 14px;
            width: 25%;
        }

        @page {
            margin: 2mm 12mm 2mm 12mm;
        }
    </style>
</head>
<body>
    @if ($logo)
        <center>
            <img src="{{ $logo }}" alt="Logo" class="logo" style="width: 800px;"/>
        </center>
    @endif
    <h2>تقرير صيانة قسم الدعم التقني </h2>
    <!-- <h4>تقرير #{{ $maintenance->id }}</h4> -->
    <table>
        <tr>
            <th>{{ $maintenance->title }} </th>
            <td class="thead"> عنوان الصيانة </td>
        </tr>
        <tr>
            <th>
                @if($maintenance->asset)
                    {{ $maintenance->asset->name ?? '-' }}<br> ({{ $maintenance->asset->asset_tag ?? '-' }}) {{ $maintenance->asset->model->name ?? '-' }}
                    @php
                        $acceptance = $maintenance->maintenanceAcceptances->first();
                        $originalUser = $acceptance ? \App\Models\User::find($acceptance->assigned_to_id) : null;
                    @endphp
                    @if($originalUser)
                      -> {{ $originalUser->present()->fullName() }}
                    @endif
                @else
                    -
                @endif
            </th>
            <td class="thead"> الأصل</td>
        </tr>
        <tr>
            <th> {{ $maintenance->asset_maintenance_type ?? '-' }}</th>
            <td class="thead">النوع</td>
        </tr>
        <!-- <tr>
            <th> {{ $maintenance->supplier && $maintenance->supplier->name ? $maintenance->supplier->name : '-' }}</th>
            <td>المورد</td>
        </tr> -->
        <!-- <tr>
            <th> {{ $user && isset($user->userloc) && $user->userloc ? $user->userloc->name : '-' }}</th>
            <td> الموقع </td>
        </tr> -->

        <tr>
            <th> {{ $maintenance->cost ?? '-' }} </th>
            <td class="thead">التكلفة</td>
        </tr>
        <tr>
            <th>{{ $maintenance->start_date ?? '-' }} </th>
            <td class="thead">تاريخ البدأ</td>
        </tr>
        <tr>
            <th> {{ $maintenance->completion_date ?? '-' }}</th>
            <td class="thead">تاريخ الأنتهاء</td>
        </tr>
        <tr>
            <th> {{ $maintenance->repair_method ?? '-' }}</th>
            <td class="thead">طريقة التصليح</td>
        </tr>
        <tr>
            <th> {{ ucfirst($maintenance->risk_level ?? '-') }}</th>
            <td class="thead">مستوى الخطر</td>
        </tr>
        <tr>
            <th> {{ $maintenanceStatus ?? '-' }}</th>
            <td class="thead">حالة الصيانة</td>
        </tr>
        <tr>
            <th> @php
                            $acceptance = $maintenance->maintenanceAcceptances->first();
                        @endphp
                        {{ $acceptance->note ?? '-' }}</th>
            <td class="thead">الملاحظات</td>
        </tr>
   
       
        <tr>
            <th>@php
                    $acceptance = $maintenance->maintenanceAcceptances->first();
                    $signature = $acceptance && $acceptance->signature_filename
                        ? asset('uploads/signatures/' . $acceptance->signature_filename)
                        : null;
                @endphp
                @if($signature)
                    <img src="{{ $signature }}" alt="Signature" style="max-width:350px;" />
                @else
                    -
                @endif </th>
            <td class="thead">توقيع صاحب الأصل</td>
        </tr>
    </table>
    <p style="margin-top:40px; text-align:center;">
        تاريخ {{ date('Y-m-d ') }}
    </p>
</body>
</html> 