<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Periodic Asset Maintenance Checklist</title>
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
    @if(isset($filter) && $start_date && $end_date)
        @if($filter === 'created_at')
            <p style="text-align:center;">Created At: from {{ $start_date }} to {{ $end_date }}</p>
        @elseif($filter === 'maintenance_date')
            <p style="text-align:center;">Maintenance date: from {{ $start_date }} to {{ $end_date }}</p>
        @endif
    @endif
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
                <th>Signature</th>
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
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:40px; text-align:center;">
        Generated on {{ date('Y-m-d H:i') }}
    </p>
</body>
</html> 