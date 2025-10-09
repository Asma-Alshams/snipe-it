<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Hardware Report</title>
    <style>
        @page {
            margin: 2mm 6mm 2mm 6mm; 
            size: landscape; 
        }
        body { font-weight: 500; font-size: 15px;}
        h2{ text-align: center; color: #00008B; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; text-align:right; font-size: 13px;}
        th, td { border: 1px solid #333; padding: 3px; }
        th { color: grey; }
    </style>
    @if ($logo ?? false)
        <center>
            <img width="80%" src="{{ $logo }}">
        </center>
    @endif
    </head>
<body>
    <h2>تقرير الأصول</h2>

    @if(isset($user_id) && $user_id)
        <p style="text-align:center;">Name: {{ optional(\App\Models\User::find($user_id))->full_name ?? $user_id }}</p>
    @endif
    @if(isset($location_id) && $location_id)
        <p style="text-align:center;">Location: {{ optional(\App\Models\Location::find($location_id))->name ?? $location_id }}</p>
    @endif
    @if(isset($category_id) && $category_id)
        <p style="text-align:center;">Category: {{ optional(\App\Models\Category::find($category_id))->name ?? $category_id }}</p>
    @endif
    <p style="text-align:center;">Total Records: {{ $assets->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>الموقع</th>
                <th>الرقم التسلسلي</th>
                <th>الرقم الثابت</th>
                <th>النوع</th>
                <th>الفئة</th>
                <th>اسم الأصل</th>
                <th>صاحب الأصل </th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
                <tr>
                    <td>{{ $asset->location?->name ?? $asset->defaultLoc?->name ?? '-' }}</td>
                    <td>{{ $asset->serial ?? '-' }}</td>
                    <td>{{ $asset->asset_tag ?? '-' }}</td>
                    <td>{{ $asset->model?->name ?? '-' }}</td>
                    <td>{{ $asset->model?->category?->name ?? '-' }}</td>
                    <td>{{ $asset->name ?? '-' }}</td>
                    <td>
                        @if($asset->assignedType() === 'user' && $asset->assigned_to)
                            @php $user = \App\Models\User::find($asset->assigned_to); @endphp
                            @if($user)
                                @if(method_exists($user, 'present') && $user->present())
                                    {{ $user->present()->fullName() }}
                                @else
                                    {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}
                                @endif
                            @else
                                -
                            @endif
                        @elseif($asset->assignedType() === 'location' && $asset->assigned_to)
                            @php $location = \App\Models\Location::find($asset->assigned_to); @endphp
                            {{ $location ? $location->name : '-' }}
                        @elseif($asset->assignedType() === 'asset' && $asset->assigned_to)
                            @php $assignedAsset = \App\Models\Asset::find($asset->assigned_to); @endphp
                            {{ $assignedAsset ? (($assignedAsset->asset_tag ?? '').($assignedAsset->name ? ' - '.$assignedAsset->name : '')) : '-' }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:40px; text-align:center;">
        تم إنشاء التقرير في {{ date('Y-m-d') }}
    </p>
</body>
</html>


