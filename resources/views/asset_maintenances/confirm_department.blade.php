@extends('layouts/default')

@section('title')
  Confirm Department Maintenance
  @parent
@stop

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title">Select Users and Assets for Maintenance</h3>
      </div>
      <form method="POST" action="{{ route('maintenances.department.finalize') }}">
        @csrf
        @foreach ($fields as $key => $value)
          @if (!is_array($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endif
        @endforeach
        <div class="box-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>User</th>
                <th>Assets</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($department->users as $user)
                <tr>
                  <td style="vertical-align:top;">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" class="user-checkbox" data-user="{{ $user->id }}" checked>
                        <span style="padding:8px;">{{ $user->first_name }} {{ $user->last_name }}</span>
                      </label>
                    </div>
                  </td>
                  <td>
                    @foreach ($user->assets as $asset)
                      <div class="checkbox">
                        <label>
                          <input type="checkbox" name="user_asset[{{ $user->id }}][]" value="{{ $asset->id }}" class="asset-checkbox asset-of-{{ $user->id }}" checked>
                          <span style="padding:8px;">{{ $asset->name }} ({{ $asset->asset_tag }})</span>
                        </label>
                      </div>
                    @endforeach
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="box-footer">
          <a href="{{ route('maintenances.index') }}" class="btn btn-default">Cancel</a>
          <button type="submit" class="btn btn-success">Create Maintenances</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Unchecking a user unchecks all their assets
    document.querySelectorAll('.user-checkbox').forEach(function(userCheckbox) {
      userCheckbox.addEventListener('change', function() {
        var userId = this.getAttribute('data-user');
        document.querySelectorAll('.asset-of-' + userId).forEach(function(assetCheckbox) {
          assetCheckbox.checked = userCheckbox.checked;
        });
      });
    });
    // If all assets for a user are unchecked, uncheck the user
    document.querySelectorAll('.asset-checkbox').forEach(function(assetCheckbox) {
      assetCheckbox.addEventListener('change', function() {
        var userId = this.className.match(/asset-of-(\d+)/)[1];
        var all = Array.from(document.querySelectorAll('.asset-of-' + userId));
        var userCheckbox = document.querySelector('.user-checkbox[data-user="' + userId + '"]');
        if (all.every(function(cb) { return !cb.checked; })) {
          userCheckbox.checked = false;
        } else {
          userCheckbox.checked = true;
        }
      });
    });
  });
</script>
@stop 