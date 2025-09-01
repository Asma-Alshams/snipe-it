<!DOCTYPE html>
<html  lang="en ">
<head>
    <meta charset="utf-8">
    <title>Asset Maintenance Report</title>
    <style>
        body {  font-weight: 500;}
        h2, h4 { text-align: center; color: #00008B; font-weight: 600;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: right; }
        td { color: grey; }
        @page {
            margin: 2mm 12mm 2mm 12mm;}
    </style>
     
@if ($logo)
    <center>
        <img  width="100%" src="{{ $logo }}">
    </center>
@endif

</head>
<body>
    <h2>تقرير صيانة قسم الدعم التقني </h2>
    <h4>تقرير #{{ $maintenance->id }}</h4>
    <table>
        <tr>
            <th>{{ $maintenance->title }} </th>
            <td> عنوان الصيانة </td>
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
            <td>   الأصل</td>
        </tr>
        <tr>
            <th> {{ $maintenance->asset_maintenance_type ?? '-' }}</th>
            <td>النوع</td>
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
            <td>التكلفة</td>
        </tr>
        <tr>
            <th>{{ $maintenance->start_date ?? '-' }} </th>
            <td>تاريخ البدأ</td>
        </tr>
        <tr>
            <th> {{ $maintenance->completion_date ?? '-' }}</th>
            <td>تاريخ الأنتهاء</td>
        </tr>
        <tr>
            <th> {{ $maintenance->repair_method ?? '-' }}</th>
            <td>طريقة التصليح</td>
        </tr>
        <tr>
            <th> {{ ucfirst($maintenance->risk_level ?? '-') }}</th>
            <td>مستوى الخطر</td>
        </tr>
        <tr>
            <th> {{ $maintenanceStatus ?? '-' }}</th>
            <td>حالة الصيانة</td>
        </tr>
        <tr>
            <th> @php
                            $acceptance = $maintenance->maintenanceAcceptances->first();
                        @endphp
                        {{ $acceptance->note ?? '-' }}</th>
            <td>الملاحظات</td>
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
            <td>توقيع صاحب الأصل</td>
        </tr>
    </table>
    <p style="margin-top:40px; text-align:center;">
        التقرير اعد من قبل {{ $createdByName }}<br>
        بتاريخ {{ date('Y-m-d ') }}
    </p>
</body>
</html> 