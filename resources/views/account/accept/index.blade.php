@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.accept_assets', array('name' => empty($user) ? '' : $user->present()->full_name)) }}
@parent
@stop

{{-- Account page content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">

      <div class="box-body">
        <!-- checked out Accessories table -->

        <div class="table-responsive">
          <h3>Accept assets </h3>
          <table
                  data-cookie-id-table="pendingAcceptances"
                  data-id-table="pendingAcceptances"
                  data-side-pagination="client"
                  data-show-refresh="false"
                  data-sort-order="asc"
                  id="pendingAcceptances"
                  class="table table-striped snipe-table"
                  data-export-options='{
                  "fileName": "my-pending-acceptances-{{ date('Y-m-d') }}",
                  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                  }'>
            <thead>
              <tr>
                <th>{{ trans('general.name')}}</th>
                <th>{{ trans('table.actions')}}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($acceptances as $acceptance)
              <tr>
                @if ($acceptance->checkoutable)
                <td>{{ ($acceptance->checkoutable) ? $acceptance->checkoutable->present()->name : '' }}</td>
                <td><a href="{{ route('account.accept.item', $acceptance) }}" class="btn btn-default btn-sm">{{ trans('general.accept_decline') }}</a></td>
                @else
                <td> ----- </td>
                <td> {{ trans('general.error_user_company_accept_view') }} </td>
                @endif
              </tr>
              @endforeach
            </tbody>
          </table>
<br> <br> <br>
<h3>Accept assets maintenances </h3>
          <table
                  data-cookie-id-table="pendingMaintenanceAcceptances"
                  data-id-table="pendingMaintenanceAcceptances"
                  data-side-pagination="client"
                  data-show-refresh="false"
                  data-sort-order="asc"
                  id="pendingMaintenanceAcceptances"
                  class="table table-striped snipe-table"
                  data-export-options='{
                  "fileName": "my-pending-maintenance-acceptances-{{ date('Y-m-d') }}",
                  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                  }'>
            <thead>
              <tr>
                <th>Title</th>
                <th>Asset name</th>
                <th>Maintenance type</th>
                <th>Start date</th>
                <th>Cost</th>
                <th>{{ trans('table.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($maintenanceAcceptances as $maintenanceAcceptance)
              <tr>
                @if ($maintenanceAcceptance->maintenance && $maintenanceAcceptance->maintenance->asset)
                <td>{{ $maintenanceAcceptance->maintenance->title ?: 'Maintenance #' . $maintenanceAcceptance->maintenance->id }}</td>
                <td>{{ $maintenanceAcceptance->maintenance->asset->present()->name() }}</td>
                <td>{{ $maintenanceAcceptance->maintenance->asset_maintenance_type ?: '-' }}</td>
                <td>{{ $maintenanceAcceptance->maintenance->start_date ? \App\Helpers\Helper::getFormattedDateObject($maintenanceAcceptance->maintenance->start_date, 'date', false) : '-' }}</td>
                <td>{{ $maintenanceAcceptance->maintenance->cost ? \App\Helpers\Helper::formatCurrencyOutput($maintenanceAcceptance->maintenance->cost) : '-' }}</td>
                <td><a href="{{ route('account.accept.maintenance', $maintenanceAcceptance) }}" class="btn btn-default btn-sm">{{ trans('general.accept_decline') }}</a></td>
                @else
                <td> ----- </td>
                <td> ----- </td>
                <td> ----- </td>
                <td> ----- </td>
                <td> ----- </td>
                <td> ----- </td>
                <td> {{ trans('general.error_user_company_accept_view') }} </td>
                @endif
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

       </div> <!-- .box-body-->
    </div><!--.box.box-default-->
  </div> <!-- .col-md-12-->
</div> <!-- .row-->

@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table')
  <script>
    window.maintenanceSignatureFormatter = function(value, row) {
        if (value) {
            return '<img src="' + value + '" alt="Signature" style="max-width:100px;max-height:50px;border:1px solid #ddd;" />';
        } else {
            return '<span class="text-muted">No signature</span>';
        }
    };
  </script>
@stop
