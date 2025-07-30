@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.activity_report') }} 
@parent
@stop

@section('header_right')
    <form method="POST" action="{{ route('reports.activity.post') }}" accept-charset="UTF-8" class="form-horizontal" style="display:inline-block;">
    {{csrf_field()}}
  
    </form>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#activityReportModal" style="margin-left:10px;">
        <i class="fas fa-file-pdf"></i> Generate Report
    </button>
    <button type="submit" class="btn btn-default">
        <x-icon type="download" />
        {{ trans('general.download_all') }}
    </button>
    <!-- Modal for PDF report generation -->
    <div class="modal fade" id="activityReportModal" tabindex="-1" role="dialog" aria-labelledby="activityReportModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="activityReportModalLabel">Generate Activity Report (PDF)</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form method="GET" action="{{ route('reports.activity.pdf') }}" id="activityReportForm">
            <div class="modal-body">
              <div class="form-group">
                <label for="filterType">Filter By</label>
                <select class="form-control" id="filterType" name="filter_type">
                  <option value="date">By Creation Date</option>
                  <option value="location">By Location (Target)</option>
                  <option value="date_location">By Date and Location</option>
                  <option value="date_department">By Date and Department</option>
                </select>
              </div>
              <div id="dateFields">
                <div class="form-group">
                  <label for="start_date">Start Date</label>
                  <input type="date" class="form-control" name="start_date" id="start_date">
                </div>
                <div class="form-group">
                  <label for="end_date">End Date</label>
                  <input type="date" class="form-control" name="end_date" id="end_date">
                </div>
              </div>
              <div id="locationField" style="display:none;">
                <div class="form-group">
                  <label for="location_id">Location</label>
                  <select class="form-control" name="location_id" id="location_id">
                    <option value="">-- Select Location --</option>
                    @foreach(\App\Models\Location::orderBy('name')->get() as $location)
                      <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div id="departmentField" style="display:none;">
                <div class="form-group">
                  <label for="department_id">Department</label>
                  <select class="form-control" name="department_id" id="department_id">
                    <option value="">-- Select Department --</option>
                    @foreach(\App\Models\Department::orderBy('name')->get() as $department)
                      <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Generate PDF</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var filterType = document.getElementById('filterType');
        var dateFields = document.getElementById('dateFields');
        var locationField = document.getElementById('locationField');
        var departmentField = document.getElementById('departmentField');
        var startDate = document.getElementById('start_date');
        var endDate = document.getElementById('end_date');
        var locationId = document.getElementById('location_id');
        var departmentId = document.getElementById('department_id');
        function updateFields() {
          if (filterType.value === 'date') {
            dateFields.style.display = '';
            locationField.style.display = 'none';
            departmentField.style.display = 'none';
            startDate.required = true;
            endDate.required = true;
            locationId.required = false;
            departmentId.required = false;
            locationId.value = ''; // Clear location when switching to date
            departmentId.value = ''; // Clear department when switching to date
          } else if (filterType.value === 'location') {
            dateFields.style.display = 'none';
            locationField.style.display = '';
            departmentField.style.display = 'none';
            startDate.required = false;
            endDate.required = false;
            locationId.required = true;
            departmentId.required = false;
            startDate.value = ''; // Clear start date when switching to location
            endDate.value = '';   // Clear end date when switching to location
            departmentId.value = ''; // Clear department when switching to location
          } else if (filterType.value === 'date_location') {
            dateFields.style.display = '';
            locationField.style.display = '';
            departmentField.style.display = 'none';
            startDate.required = true;
            endDate.required = true;
            locationId.required = true;
            departmentId.required = false;
            departmentId.value = ''; // Clear department when switching to date_location
          } else if (filterType.value === 'date_department') {
            dateFields.style.display = '';
            locationField.style.display = 'none';
            departmentField.style.display = '';
            startDate.required = true;
            endDate.required = true;
            locationId.required = false;
            departmentId.required = true;
            locationId.value = ''; // Clear location when switching to date_department
          }
        }
        filterType.addEventListener('change', updateFields);
        // Set initial state when modal is shown
        $('#activityReportModal').on('show.bs.modal', function () {
          updateFields();
        });
        // Also set initial state on page load
        updateFields();
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
                        data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                        data-cookie-id-table="activityReport"
                        data-id-table="activityReport"
                        data-side-pagination="server"
                        data-sort-order="desc"
                        data-sort-name="created_at"
                        id="activityReport"
                        data-url="{{ route('api.activity.index') }}"
                        class="table table-striped snipe-table"
                        data-export-options='{
                        "fileName": "activity-report-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                </table>
            </div>
        </div>
    </div>
</div>
@stop


@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'activity-export', 'search' => true])
@stop
