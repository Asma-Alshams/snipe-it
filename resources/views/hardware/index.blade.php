@extends('layouts/default')

@section('title0')

  @if ((Request::get('company_id')) && ($company))
    {{ $company->name }}
  @endif



@if (Request::get('status'))
  @if (Request::get('status')=='Pending')
    {{ trans('general.pending') }}
  @elseif (Request::get('status')=='RTD')
    {{ trans('general.ready_to_deploy') }}
  @elseif (Request::get('status')=='Deployed')
    {{ trans('general.deployed') }}
  @elseif (Request::get('status')=='Undeployable')
    {{ trans('general.undeployable') }}
  @elseif (Request::get('status')=='Deployable')
    {{ trans('general.deployed') }}
  @elseif (Request::get('status')=='Requestable')
    {{ trans('admin/hardware/general.requestable') }}
  @elseif (Request::get('status')=='Archived')
    {{ trans('general.archived') }}
  @elseif (Request::get('status')=='Deleted')
    {{ trans('general.deleted') }}
  @elseif (Request::get('status')=='byod')
    {{ trans('general.byod') }}
  @endif
@else
{{ trans('general.all') }}
@endif
{{ trans('general.assets') }}

  @if (Request::has('order_number'))
    : Order #{{ strval(Request::get('order_number')) }}
  @endif
@stop

{{-- Page title --}}
@section('title')
@yield('title0')  @parent
@stop

@section('header_right')
  <a href="{{ route('reports/custom') }}" style="margin-right: 5px;" class="btn btn-default">
    {{ trans('admin/hardware/general.custom_export') }}</a>
  @can('create', \App\Models\Asset::class)
  <a href="{{ route('hardware.create') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "n" : ''}} class="btn btn-primary pull-right"></i> {{ trans('general.create') }}</a>
  @endcan
  
 @can('view', \App\Models\Asset::class)
    <button type="button" class="btn btn-primary pull-right text-white" style="margin-right: 10px;" data-toggle="modal" data-target="#hardwareReportModal">
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
       
          <div class="row">
            <div class="col-md-12">

                @include('partials.asset-bulk-actions', ['status' => Request::get('status')])
                   
              <table
                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                data-cookie-id-table="assetsListingTable"
                data-id-table="assetsListingTable"
                data-search-text="{{ e(Session::get('search')) }}"
                data-side-pagination="server"
                data-show-footer="true"
                data-sort-order="asc"
                data-sort-name="name"
                data-toolbar="#assetsBulkEditToolbar"
                data-bulk-button-id="#bulkAssetEditButton"
                data-bulk-form-id="#assetsBulkForm"
                id="assetsListingTable"
                class="table table-striped snipe-table"
                data-url="{{ route('api.assets.index',
                    array('status' => e(Request::get('status')),
                    'order_number'=>e(strval(Request::get('order_number'))),
                    'company_id'=>e(Request::get('company_id')),
                    'status_id'=>e(Request::get('status_id')))) }}"
                data-export-options='{
                "fileName": "export{{ (Request::has('status')) ? '-'.str_slug(Request::get('status')) : '' }}-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
              </table>

            </div><!-- /.col -->
          </div><!-- /.row -->
        
      </div><!-- ./box-body -->
    </div><!-- /.box -->
  </div>
</div>
@stop

@section('moar_scripts')
@include('partials.bootstrap-table')

<script>
  (function() {
    var initSelects = function() {
      try {
        $('#report_user_id, #report_location_id, #report_category_id').select2({
          width: '100%',
          allowClear: true
        });
      } catch(e) {}
    };
    $('#hardwareReportModal').on('shown.bs.modal', initSelects);
    if ($('#hardwareReportModal').is(':visible')) { initSelects(); }
  })();
</script>

<!-- Hardware Report Modal -->
<div class="modal fade" id="hardwareReportModal" tabindex="-1" role="dialog" aria-labelledby="hardwareReportModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="hardwareReportModalLabel">Generate Assets Report</h4>
      </div>
      <div class="modal-body">
        <form id="hardwareReportForm" method="GET" action="{{ route('hardware.pdf') }}">
          <div class="form-group">
            <label for="report_user_id">Filter by User (optional)</label>
            <select class="form-control select2" name="user_id" id="report_user_id" data-placeholder="-- Any User --">
              <option value="">-- Any User --</option>
              @foreach(($users ?? []) as $u)
                <option value="{{ $u->id }}">{{ trim(($u->first_name ?? '').' '.($u->last_name ?? '')) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="report_location_id">Filter by Location (optional)</label>
            <select class="form-control select2" name="location_id" id="report_location_id" data-placeholder="-- Any Location --">
              <option value="">-- Any Location --</option>
              @foreach(($locations ?? []) as $loc)
                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="report_category_id">Filter by Category (optional)</label>
            <select class="form-control select2" name="category_id" id="report_category_id" data-placeholder="-- Any Category --">
              <option value="">-- Any Category --</option>
              @foreach(($categories ?? []) as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success text-white" style="margin-right:10px;"
                onclick="window.location.href='{{ route('hardware.pdf') }}'">
          <i class="fas fa-print"></i> Print Full Report
        </button>
        <button type="submit" form="hardwareReportForm" class="btn btn-primary">Generate Report</button>
      </div>
    </div>
  </div>
  </div>

@stop
