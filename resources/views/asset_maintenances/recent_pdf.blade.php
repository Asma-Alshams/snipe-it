<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Recent Asset Maintenances</title>
    <style>
           @page {
  margin: 2mm 6mm 2mm 6mm; /* top, right, bottom, left */ size: landscape; }
        body { font-weight: 500; }
        h2, h4 { text-align: center; color: #00008B; font-weight: 600; }
        table { width: 100%; border-collapse: collapse;  }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { color: grey; }
    </style>
    @if ($logo ?? false)
        <center>
            <img width="90%" src="{{ $logo }}">
        </center>
    @endif
</head>
<body>
    <h2>Periodic Asset Maintenance Checklist<br> قائمة الصيانة الدورية للأصول </h2>
    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>Department</th>
                <th>Assets</th>
                <th>Assigned To</th>
                <th>Maintenance Status</th>
                <th>Created By</th>
                <th>Maintenance Type</th>
                <th>Repair Method</th>
                <th>Start Date</th>
                <th>End Date</th>
               
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td>{{ $m->assignedUser && $m->assignedUser->department ? $m->assignedUser->department->name : '-' }}</td>
                    <td>
                        @if($m->asset && method_exists($m->asset, 'present') && $m->asset->present())
                            {{ $m->asset->present()->fullName() }}
                        @else
                            -
                        @endif
                    </td>
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
                    <td>{{ $m->maintenanceStatus ?? '-' }}</td>
                    <td>{{ $m->adminuser ? $m->adminuser->present()->name() : '-' }}</td>
                    <td>{{ $m->asset_maintenance_type ?? '-' }}</td>
                    <td>{{ $m->repair_method ?? '-' }}</td>
                    <td>{{ $m->start_date ?? '-' }}</td>
                    <td>{{ $m->completion_date ?? '-' }}</td>
                   
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:40px; text-align:center;">
        Generated on {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 