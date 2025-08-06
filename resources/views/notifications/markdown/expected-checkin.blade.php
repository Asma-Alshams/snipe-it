@component('mail::message')
# {{ trans('mail.hello') }},

@if(isset($assets) && $assets->count() > 1)
{{ trans('mail.Expected_Checkin_Date', ['date' => $date]) }}

@component('mail::table')
<table width="100%">
<tr><td>{{ trans('mail.asset_name') }}</td><td>{{ trans('mail.asset_tag') }}</td><td>{{ trans('mail.serial') }}</td><td>Expected Checkin Date</td></tr>
@foreach ($assets as $asset)
<tr>
<td>{{ $asset->present()->name() }}</td>
<td>{{ $asset->asset_tag }}</td>
<td>{{ $asset->serial }}</td>
<td>{{ \App\Helpers\Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}</td>
</tr>
@endforeach
</table>
@endcomponent
@else
{{ trans('mail.Expected_Checkin_Date', ['date' => $date]) }}
@if ((isset($asset)) && ($asset!=''))
{{ trans('mail.asset_name') }} {{ $asset }}
@endif
{{ trans('mail.asset_tag') }} {{ $asset_tag }}
@if (isset($serial))
{{ trans('mail.serial') }}: {{ $serial }}
@endif
@endif

**[{{ trans('mail.your_assets') }}](http://localhost/account/view-assets#assets)**

Comment on the asset to check in or to request a check-in date change if additional time is needed.

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}
@endcomponent
