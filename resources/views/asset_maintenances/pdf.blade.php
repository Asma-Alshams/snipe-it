<!DOCTYPE html>
<html  lang="en ">
<head>
    <meta charset="utf-8">
    <title>Asset Maintenance Report</title>
    <style>
        body {  font-weight: 500;}
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>Asset Maintenance Report  <br>
    تقرير صيانة الأصل </h1>
    <h2>Maintenance #{{ $maintenance->id }}</h2>
    <table>
        <tr>
            <th>Title</th>
            <td>{{ $maintenance->title }}</td>
        </tr>
        <tr>
            <th>Asset</th>
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
            <th>Type  النوع</th>
            <td>{{ $maintenance->asset_maintenance_type }}</td>
        </tr>
        <tr>
            <th>Supplier  المورد</th>
            <td>{{ $maintenance->supplier->name }}</td>
        </tr>
        <tr>
            <th>Cost  التكلفة</th>
            <td>{{ $maintenance->cost }}</td>
        </tr>
        <tr>
            <th>Start Date</th>
            <td>{{ $maintenance->start_date }}</td>
        </tr>
        <tr>
            <th>Completion Date</th>
            <td>{{ $maintenance->completion_date }}</td>
        </tr>
        <tr>
            <th>Notes</th>
            <td>{{ $maintenance->notes }}</td>
        </tr>
    </table>
    <p style="margin-top:40px; text-align:center; font-size:12px;">Generated on {{ date('Y-m-d H:i') }}</p>
</body>
</html> 