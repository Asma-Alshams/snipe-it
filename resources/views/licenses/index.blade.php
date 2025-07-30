@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/licenses/general.software_licenses') }}
@parent
@stop


@section('header_right')
@can('create', \App\Models\License::class)
    <a href="{{ route('licenses.create') }}" accesskey="n" class="btn btn-primary pull-right">
      {{ trans('general.create') }}
    </a>
    @endcan
@can('view', \App\Models\License::class)
    <a class="btn btn-default pull-right" href="{{ route('licenses.export') }}" style="margin-right: 5px;">{{ trans('general.export') }}</a>
    <button type="button" class="btn btn-primary pull-right text-white" style="margin-right: 10px;" data-toggle="modal" data-target="#reportModal">
        <i class="fas fa-file-pdf"></i> Generate Report
    </button>
@endcan
@stop

{{-- Page content --}}
@section('content')


<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-body">

          <table
              data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayout() }}"
              data-cookie-id-table="licensesTable"
              data-side-pagination="server"
              data-footer-style="footerStyle"
              data-show-footer="true"
              data-sort-order="asc"
              data-sort-name="name"
              id="licensesTable"
              class="table table-striped snipe-table"
              data-url="{{ route('api.licenses.index') }}"
              data-export-options='{
            "fileName": "export-licenses-{{ date('Y-m-d') }}",
            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
            }'>
          </table>

      </div><!-- /.box-body -->

      <div class="box-footer clearfix">
      </div>
    </div><!-- /.box -->
  </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="reportModalLabel">Generate Licenses Report</h4>
      </div>
      <div class="modal-body">
        <form id="reportForm" method="GET" action="{{ route('licenses.pdf') }}">
          <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" class="form-control" name="start_date" id="start_date" required>
          </div>
          <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control" name="end_date" id="end_date" required>
          </div>
          <div class="form-group">
            <label for="reportType">Report Type</label>
            <select class="form-control" id="reportType" name="filter">
              <option value="all">By Creation Date</option>
              <option value="expiration_date">By Expiration Date</option>
              <option value="purchase_date">By Purchase Date</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success text-white" style="margin-right:10px;"
    onclick="window.location.href='{{ route('licenses.pdf') }}'">
  <i class="fas fa-print"></i> Print Full Report
</button>
        <button type="submit" form="reportForm" class="btn btn-primary">Generate Report</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

@stop
