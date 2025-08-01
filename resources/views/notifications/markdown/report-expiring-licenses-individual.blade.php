@component('mail::message')
# {{ trans('mail.Your_License_Expiring_Alert') }}

{{ trans('mail.license_expiring_individual_alert', ['count' => $licenses->count(), 'threshold' => $threshold]) }}

@component('mail::table')

<table width="100%">
<tr><td>&nbsp;</td><td>{{ trans('mail.name') }}</td><td>{{ trans('mail.Days') }}</td><td>{{ trans('mail.expires') }}</td></tr>
@foreach ($licenses as $license)
@php
$expires = Helper::getFormattedDateObject($license->expiration_date, 'date');
$diff = round(abs(strtotime($license->expiration_date->format('Y-m-d')) - strtotime(date('Y-m-d')))/86400);
$icon = ($diff <= ($threshold / 2)) ? '🚨' : (($diff <= $threshold) ? '⚠️' : ' ');
@endphp
<tr><td>{{ $icon }} </td><td> <a href="{{ route('licenses.show', $license->id) }}">{{ $license->name }}</a> </td><td> {{ $diff }} {{ trans('mail.Days') }}  </td><td>{{ $expires['formatted'] }}</td></tr>
@endforeach
</table>
@endcomponent

{{ trans('mail.license_expiring_individual_footer') }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent 