<!DOCTYPE html>
<html  lang="en ">
<head>
    <meta charset="utf-8">
    <title>Asset Maintenance Report</title>
    <style>
        body {  font-weight: 500;}
        h2, h4 { text-align: center; color: #00008B; font-weight: 600;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { color: grey; }
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
            <td>
                @if($maintenance->asset)
                    {{ $maintenance->asset->present()->fullName() }}
                    @if($maintenance->asset->assigned_type == 'App\Models\User' && $maintenance->asset->assigned_to)
                        @php
                            $assignedUser = \App\Models\User::find($maintenance->asset->assigned_to);
                        @endphp
                        @if($assignedUser)
                           -> {{ $assignedUser->present()->fullName() }}
                           
                        @endif
                    @endif
                @else
                    -
                @endif
            </td>
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
   
        <tr>
            <th>Maintenance Status حالة الصيانة</th>
            <td>{{ $maintenanceStatus ?? '-' }}</td>
        </tr>
        <tr>
            <th>Signature التوقيع</th>
            <td>
                @php
                    $acceptance = $maintenance->maintenanceAcceptances()
                        ->where('assigned_to_id', $maintenance->asset->assigned_to ?? null)
                        ->first();
                    $signature = $acceptance && $acceptance->signature_filename
                        ? asset('uploads/signatures/' . $acceptance->signature_filename)
                        : null;
                @endphp
                @if($signature)
                    <img src="{{ $signature }}" alt="Signature" style="max-width:350px;" />
                @else
                    -
                @endif
            </td>
        </tr>
    </table>
    <p style="margin-top:40px; text-align:center;">
        Report Created by {{ $createdByName }}<br>
        Generated on {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 