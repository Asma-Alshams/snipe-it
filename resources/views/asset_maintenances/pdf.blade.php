<!DOCTYPE html>
<html  lang="en ">
<head>
    <meta charset="utf-8">
    <title>Asset Maintenance Report</title>
    <style>
        body {  font-weight: 500;}
        h2, h4 { text-align: center; color: #00008B; font-weight: 600;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { color: grey; }
    </style>
     
@if ($logo)
    <center>
        <img  width="100%" src="{{ $logo }}">
    </center>
@endif

</head>
<body>
    <h2>Asset Maintenance Report  <br>
    تقرير صيانة الأصل </h2>
    <h4>Maintenance #{{ $maintenance->id }}</h4>
    <table>
        <tr>
            <th>Title عنوان الصيانة</th>
            <td>{{ $maintenance->title }}</td>
        </tr>
        <tr>
            <th>Asset الأصل</th>
            <td>{{ $maintenance->asset ? $maintenance->asset->present()->fullName() : '-' }} ->
                 @php
                    $asset = is_object($maintenance->asset) ? $maintenance->asset : ($maintenance->asset() ? $maintenance->asset()->first() : null);
                @endphp
                @if($asset && $asset->assignedType() === 'user' && $asset->assignedTo)
                    {{ $asset->assignedTo->present()->fullName() }}
                @else
                    -
                @endif</td>
        </tr>
        <tr>
            <th>Serial No. رقم التسلسل  </th>
            <td>{{ $item_serial }}</td>
        </tr>
        <tr>
            <th>Type  النوع</th>
            <td>{{ $maintenance->asset_maintenance_type }}</td>
        </tr>
        <tr>
            <th>Supplier  المورد</th>
            <td>{{ $maintenance->supplier->name }}</td>
        </tr>
        <tr>
            <th>Location الموقع</th>
            <td> {{ $user->userloc->name }} </td>
        </tr>

        <tr>
            <th>Cost  التكلفة</th>
            <td>{{ $maintenance->cost }}</td>
        </tr>
        <tr>
            <th>Start Date تاريخ البدأ</th>
            <td>{{ $maintenance->start_date }}</td>
        </tr>
        <tr>
            <th>Completion Date تاريخ الأنتهاء</th>
            <td>{{ $maintenance->completion_date }}</td>
        </tr>
        <tr>
            <th>Repair Method طريقة التصليح</th>
            <td>{{ $maintenance->repair_method }}</td>
        </tr>
        <tr>
            <th>Notes الملاحظات</th>
            <td>{{ $maintenance->notes }}</td>
        </tr>
    </table>
    <p style="margin-top:40px; text-align:center;">
        Report Created by {{ $createdByName }}<br>
        Generated on {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 