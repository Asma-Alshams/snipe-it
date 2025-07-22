@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Accept Maintenance
    @parent
@stop

{{-- Page content --}}
@section('content')

<link rel="stylesheet" href="{{ url('css/signature-pad.min.css') }}">

<style>
    .form-horizontal .control-label, .form-horizontal .radio, .form-horizontal .checkbox, .form-horizontal .radio-inline, .form-horizontal .checkbox-inline {
        padding-top: 17px;
        padding-right: 10px;
    }
    #eula_div {
        width: 100%;
        height: auto;
        overflow: auto;
    }
    .m-signature-pad--body {
        border-style: solid;
        border-color: grey;
        border-width: thin;
    }
</style>

<form class="form-horizontal" method="post" action="{{ route('account.accept.maintenance.store', $maintenanceAcceptance) }}" autocomplete="off">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="row">
        <div class="col-sm-12 col-sm-offset-1 col-md-10 col-md-offset-1">
            <div class="panel box box-default">
                <div class="box-body">
                    <div class="col-md-12" style="padding-top: 20px;">
                        {{-- EULA or Maintenance Details --}}
                        <h4>Maintenance Details</h4>
                        <table class="table table-striped">
                            <tr>
                                <td><strong>Title:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->title ?: 'Maintenance #' . $maintenanceAcceptance->maintenance->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Asset Name:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->asset->present()->name() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Maintenance Type:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->asset_maintenance_type ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Start Date:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->start_date ? \App\Helpers\Helper::getFormattedDateObject($maintenanceAcceptance->maintenance->start_date, 'date', false) : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Completion Date:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->completion_date ? \App\Helpers\Helper::getFormattedDateObject($maintenanceAcceptance->maintenance->completion_date, 'date', false) : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cost:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->cost ? \App\Helpers\Helper::formatCurrencyOutput($maintenanceAcceptance->maintenance->cost) : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Repair Method:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->repair_method ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Notes:</strong></td>
                                <td>{{ $maintenanceAcceptance->maintenance->notes ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <label class="form-control">
                            <input type="radio" name="acceptance" id="accepted" value="accepted">
                            {{ trans('general.i_accept') }}
                        </label>
                        <label class="form-control">
                            <input type="radio" name="acceptance" id="declined" value="declined">
                            {{ trans('general.i_decline') }}
                        </label>
                    </div>
                    <div class="col-md-12">
                        <br>
                        <div class="col-md-12" style="display:block;">
                            <label id="note_label" for="note" style="text-align:center;" >{{ trans('admin/settings/general.acceptance_note') }}</label>
                        </div>
                        <div class="col-md-12">
                            <textarea id="note" name="note" rows="4" value="note" class="form-control" style="width:100%"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <h3 style="padding-top: 20px">{{ trans('general.sign_tos') }}</h3>
                        <div id="signature-pad" class="m-signature-pad">
                            <div class="m-signature-pad--body col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                <canvas style="width:100%;"></canvas>
                                <input type="hidden" name="signature_output" id="signature_output">
                            </div>
                            <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                <button type="button" class="btn btn-sm btn-default clear" data-action="clear" id="clear_button">{{ trans('general.clear_signature') }}</button>
                            </div>
                        </div>
                    </div>
                </div> <!-- / box-body -->
                <div class="box-footer text-right" style="display: none;" id="showSubmit">
                    <button type="submit" class="btn btn-success" id="submit-button">
                        <i class="fa fa-check icon-white" aria-hidden="true" id="submitIcon"></i>
                        <span id="buttonText">
                            {{ trans('general.i_accept_item') }}
                        </span>
                    </button>
                    <a href="{{ route('account.accept') }}" class="btn btn-default">{{ trans('button.cancel') }}</a>
                </div><!-- /.box-footer -->
            </div> <!-- / box-default -->
        </div> <!-- / col -->
    </div> <!-- / row -->
</form>

@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    var wrapper = document.getElementById("signature-pad"),
        clearButton = wrapper.querySelector("[data-action=clear]"),
        canvas = wrapper.querySelector("canvas"),
        signaturePad;

    signaturePad = new SignaturePad(canvas);

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }
    window.onresize = resizeCanvas;
    resizeCanvas();

    $('#clear_button').on("click", function (event) {
        signaturePad.clear();
    });

    $('#submit-button').on("click", function (event) {
        if (signaturePad.isEmpty()) {
            alert("Please provide signature first.");
            return false;
        } else {
            $('#signature_output').val(signaturePad.toDataURL());
        }
    });
    
    $('[name="acceptance"]').on('change', function() {
        if ($(this).is(':checked') && $(this).attr('id') == 'declined') {
            $("#showSubmit").show();
            $("#submit-button").removeClass("btn-success").addClass("btn-danger").show();
            $("#submitIcon").removeClass("fa-check").addClass("fa-times");
            $("#buttonText").text('{{ trans('general.i_decline_item') }}');
            $("#note").prop('required', true);
        } else if ($(this).is(':checked') && $(this).attr('id') == 'accepted') {
            $("#showSubmit").show();
            $("#submit-button").removeClass("btn-danger").addClass("btn-success").show();
            $("#submitIcon").removeClass("fa-check").addClass("fa-check");
            $("#buttonText").text('{{ trans('general.i_accept_item') }}');
            $("#note").prop('required', false);
        }
    });
</script>
@stop 