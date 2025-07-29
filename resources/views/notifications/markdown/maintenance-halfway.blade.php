@component('mail::message')
# {{ trans('mail.maintenance_halfway_title') }}

{{ trans('mail.maintenance_halfway_greeting', ['name' => $user->first_name]) }}

{{ trans('mail.maintenance_halfway_message', [
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

{{ trans('mail.maintenance_halfway_footer') }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent 