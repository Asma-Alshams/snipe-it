@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/asset_maintenances/general.asset_maintenances') }}
  @parent
@stop


@section('header_right')
  @can('update', \App\Models\Asset::class)
    <a href="{{ route('maintenances.create') }}" class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
    <button type="button" class="btn btn-success pull-right" style="margin-right:10px;" data-toggle="modal" data-target="#departmentMaintenanceModal">
      <i class="fas fa-cogs"></i> Create Periodic Maintenance
    </button>
  @endcan
  <button type="button" class="btn btn-primary pull-right text-white" style="margin-right:10px;" data-toggle="modal" data-target="#reportModal">
    <i class="fas fa-file-pdf"></i> Generate Report
  </button>
  <!-- Report Modal -->
  <div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header"> 
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="reportModalLabel">Generate Maintenance Report</h4>
        </div>
        <div class="modal-body">
          <form id="reportForm" method="GET" action="{{ route('maintenances.pdf.recent') }}">
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
                <option value="all">All Displayed Maintenances</option>
                <option value="created_at">By Created Date</option>
                <option value="declined">By Declined Maintenance</option>
                <option value="department">By Periodic Maintenance</option>
              </select>
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
  <!-- Department Maintenance Modal -->
  <div class="modal fade" id="departmentMaintenanceModal" tabindex="-1" role="dialog" aria-labelledby="departmentMaintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="departmentMaintenanceModalLabel">Create Periodic Maintenance for Department</h4>
        </div>
        <form id="departmentMaintenanceForm" method="POST" action="{{ route('maintenances.department.confirm') }}">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label for="department_id">Department</label>
              <select class="form-control" id="department_id" name="department_id" required>
                <option value="">Select Department</option>
                @foreach (\App\Models\Department::all() as $department)
                  <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" class="form-control" name="title" id="title" required />
            </div>
            <div class="form-group">
              <label for="asset_maintenance_type">Maintenance Type</label>
              <select class="form-control" name="asset_maintenance_type" id="asset_maintenance_type" required>
                @foreach (\App\Models\AssetMaintenance::getImprovementOptions() as $key => $value)
                  <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="risk_level">Risk Level</label>
              <select class="form-control" name="risk_level" id="risk_level">
                <option value="">Select Risk Level</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div class="form-group">
              <label for="start_date">Start Date</label>
              <input type="date" class="form-control" name="start_date" id="start_date" required />
            </div>
            <div class="form-group">
              <label for="completion_date">Completion Date</label>
              <input type="date" class="form-control" name="completion_date" id="completion_date" />
            </div>
            <div class="form-group">
              <label for="supplier_id">Supplier (optional)</label>
              <select class="form-control" name="supplier_id" id="supplier_id">
                <option value="">Select Supplier</option>
                @foreach (\App\Models\Supplier::all() as $supplier)
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="cost">Cost (optional)</label>
              <input type="number" step="0.01" class="form-control" name="cost" id="cost" />
            </div>
            <div class="form-group">
              <label for="notes">Notes (optional)</label>
              <textarea class="form-control" name="notes" id="notes"></textarea>
            </div>
            <div class="form-group">
              <label for="repair_method">Repair Method (optional)</label>
              <input type="text" class="form-control" name="repair_method" id="repair_method" />
            </div>
            <div class="form-group">
              <label for="is_warranty">Warranty</label>
              <select class="form-control" name="is_warranty" id="is_warranty">
                <option value="0">No</option>
                <option value="1">Yes</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Next: Select Users/Assets</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var reportType = document.getElementById('reportType');
      var startDate = document.getElementById('start_date');
      var endDate = document.getElementById('end_date');
      var reportForm = document.getElementById('reportForm');
      var originalAction = reportForm.getAttribute('action');
      
      reportType.addEventListener('change', function() {
        updateFormAction();
      });
      
      startDate.addEventListener('change', function() {
        updateFormAction();
      });
      
      endDate.addEventListener('change', function() {
        updateFormAction();
      });
      
      function updateFormAction() {
        var selectedType = reportType.value;
        var start = startDate.value;
        var end = endDate.value;
        
        // Set the appropriate action based on report type
        if (selectedType === 'declined') {
          reportForm.setAttribute('action', '{{ route('maintenances.pdf.declined') }}');
        } else {
          // Use main route for all other types including department
          reportForm.setAttribute('action', originalAction);
        }
        
        // Ensure date parameters are always included in the form
        var startDateInput = reportForm.querySelector('input[name="start_date"]');
        var endDateInput = reportForm.querySelector('input[name="end_date"]');
        var filterInput = reportForm.querySelector('select[name="filter"]');
        
        if (startDateInput) {
          startDateInput.value = start;
        }
        if (endDateInput) {
          endDateInput.value = end;
        }
        if (filterInput) {
          filterInput.value = selectedType;
        }
      }
    });
  </script>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active">
              <a href="#all_maintenances" data-toggle="tab">{{ trans('admin/asset_maintenances/general.asset_maintenances') }}</a>
            </li>
            <li>
              <a href="#declined_maintenances" data-toggle="tab">Declined Maintenances</a>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="all_maintenances">
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
            <div class="tab-pane" id="declined_maintenances">
              <table
                  data-columns='@php
                    $columns = json_decode(\App\Presenters\AssetMaintenancesPresenter::dataTableLayout(), true);
                    foreach ($columns as &$col) {
                      if ($col["field"] === "acceptance_note") {
                        $col["title"] = "Decline Notes";
                      }
                    }
                    echo json_encode($columns);
                  @endphp'
                  data-cookie-id-table="declinedMaintenancesTable"
                  data-side-pagination="server"
                  data-show-footer="true"
                  id="declinedMaintenancesTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.maintenances.index', ['declined' => 1]) }}"
                  data-export-options='{
                    "fileName": "export-declined-maintenances-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                  }'>
              </table>
            </div>
          </div>
        </div>
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

window.riskLevelFormatter = function(value, row) {
    if (value) {
        var badgeClass = '';
        switch(value.toLowerCase()) {
            case 'high':
                badgeClass = 'badge-danger';
                break;
            case 'medium':
                badgeClass = 'badge-warning';
                break;
            case 'low':
                badgeClass = 'badge-success';
                break;
            default:
                badgeClass = 'badge-secondary';
        }
        console.log('Risk level:', value, 'Badge class:', badgeClass);
        return '<span class="badge ' + badgeClass + '" style="background-color: ' + (badgeClass === 'badge-danger' ? '#d9534f' : badgeClass === 'badge-warning' ? '#f0ad4e' : badgeClass === 'badge-success' ? '#5cb85c' : '#777') + ';">' + value.charAt(0).toUpperCase() + value.slice(1) + '</span>';
    } else {
        return '<span class="text-muted">-</span>';
    }
};

window.maintenanceStatusFormatter = function(value, row) {
    if (value) {
        var badgeClass = '';
        var statusText = '';
        var backgroundColor = '';
        switch(value.toLowerCase()) {
            case 'completed':
                badgeClass = 'badge-primary';
                statusText = 'Completed';
                backgroundColor = '#337ab7'; // Blue
                break;
            case 'under_maintenance':
                badgeClass = 'badge-warning';
                statusText = 'Under Maintenance';
                backgroundColor = '#f0ad4e'; // Orange
                break;
            case 'pending':
                badgeClass = 'badge-secondary';
                statusText = 'Pending';
                backgroundColor = '#777'; // Grey
                break;
            case 'declined':
                badgeClass = 'badge-secondary';
                statusText = 'Declined';
                backgroundColor = '#333'; // Black
                break;
            case 'in_progress':
                badgeClass = 'badge-primary';
                statusText = 'In Progress';
                backgroundColor = '#337ab7'; // Blue
                break;
            default:
                badgeClass = 'badge-secondary';
                statusText = value.charAt(0).toUpperCase() + value.slice(1);
                backgroundColor = '#777'; // Gray
        }
        return '<span class="badge ' + badgeClass + '" style="background-color: ' + backgroundColor + ';">' + statusText + '</span>';
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
