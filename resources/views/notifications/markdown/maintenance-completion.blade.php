@component('mail::message')
# {{ trans('mail.maintenance_completion_title') }}

{{ trans('mail.maintenance_completion_greeting', ['name' => $user->first_name]) }}

@if(isset($maintenances) && $maintenances->count() > 1)
{{ trans('mail.maintenance_completion_multiple_message', ['count' => $maintenances->count()]) }}

@component('mail::table')
<table width="100%">
<tr><td>&nbsp;</td><td>{{ trans('admin/asset_maintenances/form.title') }}</td><td>{{ trans('admin/asset_maintenances/form.asset_maintenance_type') }}</td><td>{{ trans('admin/asset_maintenances/form.asset') }}</td><td>{{ trans('admin/asset_maintenances/form.completion_date') }}</td><td>{{ trans('mail.view_maintenance') }}</td></tr>
@foreach ($maintenances as $maintenance)
@php
$asset = $maintenance->asset;
$completion_date = \App\Helpers\Helper::getFormattedDateObject($maintenance->completion_date, 'date', false);
@endphp
<tr><td>✅</td><td><a href="{{ route('maintenances.show', $maintenance->id) }}">{{ $maintenance->title }}</a></td><td>{{ $maintenance->asset_maintenance_type }}</td><td>{{ $asset->name ?? $asset->asset_tag }}</td><td>{{ is_array($completion_date) ? $completion_date['formatted'] : $completion_date }}</td><td><a href="{{ route('maintenances.show', $maintenance->id) }}" style="display:inline-block;padding:6px 12px;background:#38c172;color:#fff;text-decoration:none;border-radius:4px;">{{ trans('mail.view_maintenance') }}</a></td></tr>
@endforeach
</table>
@endcomponent
@else
{{ trans('mail.maintenance_completion_message', [
    'maintenance' => $maintenance->title,
    'asset' => $asset->name ?? $asset->asset_tag,
    'start_date' => \App\Helpers\Helper::getFormattedDateObject($maintenance->start_date, 'date', false),
    'completion_date' => \App\Helpers\Helper::getFormattedDateObject($maintenance->completion_date, 'date', false)
]) }}

## {{ trans('mail.maintenance_details') }}

- **{{ trans('admin/asset_maintenances/form.title') }}:** {{ $maintenance->title }}
- **{{ trans('admin/asset_maintenances/form.asset_maintenance_type') }}:** {{ $maintenance->asset_maintenance_type }}
- **{{ trans('admin/asset_maintenances/form.start_date') }}:** {{ \App\Helpers\Helper::getFormattedDateObject($maintenance->start_date, 'date', false) }}
- **{{ trans('admin/asset_maintenances/form.completion_date') }}:** {{ \App\Helpers\Helper::getFormattedDateObject($maintenance->completion_date, 'date', false) }}
@if($maintenance->notes)
- **{{ trans('admin/asset_maintenances/form.notes') }}:** {{ $maintenance->notes }}
@endif
@if($maintenance->cost)
- **{{ trans('admin/asset_maintenances/form.cost') }}:** {{ \App\Helpers\Helper::formatCurrencyOutput($maintenance->cost) }}
@endif

@component('mail::button', ['url' => route('maintenances.show', $maintenance->id)])
{{ trans('mail.view_maintenance') }}
@endcomponent
@endif

{{ trans('mail.maintenance_completion_footer') }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent 