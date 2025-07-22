@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/asset_maintenances/general.asset_maintenances') }}
  @parent
@stop


@section('header_right')
  @can('update', \App\Models\Asset::class)
    <a href="{{ route('maintenances.create') }}" class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
  @endcan
  <button type="button" class="btn btn-primary pull-right text-white" style="margin-right:10px;" data-toggle="modal" data-target="#reportModal">
    <i class="fas fa-file-pdf"></i> Generate Report
  </button>
  <!-- Report Modal -->
  <div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reportModalLabel">Generate Maintenance Report</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="reportForm" method="GET" action="{{ route('maintenances.pdf.recent') }}">
            <div class="form-group">
              <label for="reportType">Report Type</label>
              <select class="form-control" id="reportType" name="filter">
                <option value="all">All Displayed Maintenances</option>
                <option value="created_at">By Created Date</option>
                <option value="maintenance_date">By Maintenance Date</option>
              </select>
            </div>
            <div class="form-group" id="dateRangeFields" style="display:none;">
              <label for="start_date">Start Date</label>
              <input type="date" class="form-control" name="start_date" id="start_date">
              <label for="end_date">End Date</label>
              <input type="date" class="form-control" name="end_date" id="end_date">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" form="reportForm">Generate</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var reportType = document.getElementById('reportType');
      var dateRangeFields = document.getElementById('dateRangeFields');
      var startDate = document.getElementById('start_date');
      var endDate = document.getElementById('end_date');
      reportType.addEventListener('change', function() {
        if (reportType.value === 'created_at' || reportType.value === 'maintenance_date') {
          dateRangeFields.style.display = '';
          startDate.required = true;
          endDate.required = true;
        } else {
          dateRangeFields.style.display = 'none';
          startDate.required = false;
          endDate.required = false;
        }
      });
    });
  </script>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">

          <table
              data-columns="{{ \App\Presenters\AssetMaintenancesPresenter::dataTableLayout() }}"
              data-cookie-id-table="maintenancesTable"




              data-side-pagination="server"


              data-show-footer="true"


              id="maintenancesTable"
              class="table table-striped snipe-table"
              data-url="{{route('api.maintenances.index') }}"
              data-export-options='{
                "fileName": "export-maintenances-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>

        </table>

      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'maintenances-export', 'search' => true])
<script nonce="{{ csrf_token() }}">
window.maintenanceSignatureFormatter = function(value, row) {
    if (value) {
        return '<img src="' + value + '" alt="Signature" style="max-width:200px;" />';
    } else {
        return '<span class="text-muted">-</span>';
    }
};

window.maintenancesActionsFormatter = function(value, row) {
    var actions = '';
    if ((row) && (row.available_actions && row.available_actions.update === true)) {
        actions += '<a href="/maintenances/' + row.id + '/edit" class="btn btn-sm btn-warning" data-tooltip="true" title="Update"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
    }
    // Print PDF button
    actions += '<a href="/maintenances/' + row.id + '/pdf" class="btn btn-sm btn-info" data-tooltip="true" title="Download" target="_blank"><i class="fas fa-file-pdf"></i></a>&nbsp;';
    if ((row) && (row.available_actions && row.available_actions.delete === true)) {
        actions += '<a href="/maintenances/' + row.id + '" '
            + ' class="btn btn-danger btn-sm delete-asset"  data-tooltip="true"  '
            + ' data-toggle="modal" '
            + ' data-content="{{ trans('general.sure_to_delete') }} ' + row.name + '?" '
            + ' data-title="{{  trans('general.delete') }}" onClick="return false;">'
            + '<i class="fas fa-trash"></i></a>';
    }
    return '<nobr>' + actions + '</nobr>';
};
</script>
@stop
