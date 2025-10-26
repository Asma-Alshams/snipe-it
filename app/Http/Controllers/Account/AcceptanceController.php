<?php

namespace App\Http\Controllers\Account;

use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Events\ItemAccepted;
use App\Events\ItemDeclined;
use App\Http\Controllers\Controller;
use App\Mail\CheckoutAcceptanceResponseMail;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Contracts\Acceptable;
use App\Models\Setting;
use App\Models\User;
use App\Models\AssetModel;
use App\Models\Accessory;
use App\Models\License;
use App\Models\Component;
use App\Models\Consumable;
use App\Notifications\AcceptanceAssetAcceptedNotification;
use App\Notifications\AcceptanceAssetAcceptedToUserNotification;
use App\Notifications\AcceptanceAssetDeclinedNotification;
use App\Services\TcpdfService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\SettingsController;
use Barryvdh\DomPDF\Facade\Pdf;
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;
use Carbon\Carbon;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class AcceptanceController extends Controller
{
    /**
     * Show a listing of pending checkout acceptances for the current user
     */
    public function index() : View
    {
        $acceptances = CheckoutAcceptance::forUser(auth()->user())->pending()->get();
        $maintenanceAcceptances = \App\Http\Controllers\Account\AcceptanceMaintenanceController::getPendingForUser(auth()->user());
        return view('account/accept.index', compact('acceptances', 'maintenanceAcceptances'));
    }

    /**
     * Shows a form to either accept or decline the checkout acceptance
     *
     * @param  int  $id
     */
    public function create($id) : View | RedirectResponse
    {
        $acceptance = CheckoutAcceptance::find($id);


        if (is_null($acceptance)) {
            return redirect()->route('account.accept')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (! $acceptance->isPending()) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
        }

        if (! $acceptance->isCheckedOutTo(auth()->user())) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.incorrect_user_accepted'));
        }

        if (! Company::isCurrentUserHasAccess($acceptance->checkoutable)) {
            return redirect()->route('account.accept')->with('error', trans('general.error_user_company'));
        }

        return view('account/accept.create', compact('acceptance'));
    }

    /**
     * Generate PDF using TcpdfService
     *
     * @param string $viewRoute
     * @param array $data
     * @return string
     */
    private function generatePdfWithTcpdf(string $viewRoute, array $data): string
    {
        // Generate TCPDF content for notes section if notes are present
        if (isset($data['checkin_note']) || isset($data['checkout_note']) || isset($data['acceptance_note'])) {
            $tcpdfService = new \App\Services\TcpdfService();
            try {
                // Try to generate TCPDF as image first
                $data['tcpdf_notes_image'] = $tcpdfService->generateNotesTcpdf(
                    $data['checkin_note'] ?? '',
                    $data['checkout_note'] ?? '',
                    $data['acceptance_note'] ?? ''
                );
            } catch (Exception $e) {
                // Fallback to HTML if TCPDF image generation fails
                $data['tcpdf_notes_html'] = $tcpdfService->generateNotesHtml(
                    $data['checkin_note'] ?? '',
                    $data['checkout_note'] ?? '',
                    $data['acceptance_note'] ?? ''
                );
            }
        }
        
        $html = view($viewRoute, $data)->render();
        
        // Use the new TCPDF service
        $pdfService = TcpdfService::createForReport('Asset EULA Acceptance', 'Asset EULA Acceptance Form');
        
        $pdfService->addPage()
                   ->setFont('notonaskharabicnormal', '', 12)
                   ->writeHtml($html);
        
        return $pdfService->output('asset-eula.pdf', 'S');
    }

    /**
     * Generate PDF from accept-asset-eula template using Gpdf
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateAssetEulaPdf(Request $request)
    {
        // Get table data from request
        $tableData = json_decode($request->input('table_data', '[]'), true);
        $tableParams = json_decode($request->input('table_params', '{}'), true);
        $tableId = $request->input('table_id', 'table');
        $fileName = $request->input('file_name', 'export');
        
        // If no table data provided, use default EULA template
        if (empty($tableData)) {
            return $this->generateDefaultEulaPdf($request);
        }
        
        // Prepare data for the accept-asset-eula template
        $data = $this->prepareEulaTemplateData($tableData, $tableParams, $tableId);
        
        // Generate TCPDF content for notes section if notes are present
        if (isset($data['checkin_note']) || isset($data['checkout_note']) || isset($data['acceptance_note'])) {
            $tcpdfService = new \App\Services\TcpdfService();
            try {
                // Try to generate TCPDF as image first
                $data['tcpdf_notes_image'] = $tcpdfService->generateNotesTcpdf(
                    $data['checkin_note'] ?? '',
                    $data['checkout_note'] ?? '',
                    $data['acceptance_note'] ?? ''
                );
            } catch (Exception $e) {
                // Fallback to HTML if TCPDF image generation fails
                $data['tcpdf_notes_html'] = $tcpdfService->generateNotesHtml(
                    $data['checkin_note'] ?? '',
                    $data['checkout_note'] ?? '',
                    $data['acceptance_note'] ?? ''
                );
            }
        }
        
        // Generate PDF using the accept-asset-eula template with TCPDF
        $html = view('account.accept.accept-asset-eula', $data)->render();
        
        // Use the new TCPDF service
        $pdfService = TcpdfService::createForReport('Asset EULA Acceptance', 'Asset EULA Acceptance Form');
        
        $pdfService->addPage()
                   ->setFont('notonaskharabicnormal', '', 12)
                   ->writeHtml($html);
        
        $filename = $fileName . '-' . date('Y-m-d-his') . '.pdf';
        return $pdfService->response($filename);
    }
    
    /**
     * Prepare data for the accept-asset-eula template from table data
     *
     * @param array $tableData
     * @param array $tableParams
     * @param string $tableId
     * @return array
     */
    private function prepareEulaTemplateData(array $tableData, array $tableParams, string $tableId): array
    {
        // Get branding settings
        $branding_settings = SettingsController::getPDFBranding();
        
        $path_logo = "";
        // Check for the PDF logo path and use that, otherwise use the regular logo path
        if (!is_null($branding_settings->acceptance_pdf_logo)) {
            $path_logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
        } elseif (!is_null($branding_settings->logo)) {
            $path_logo = public_path() . '/uploads/' . $branding_settings->logo;
        }
        
        // Get first row data for template
        $firstRow = $tableData[0] ?? [];
        
        // Create a mock user object for the template
        $user = (object) [
            'notes' => $firstRow['notes'] ?? '',
            'jobtitle' => $firstRow['jobtitle'] ?? 'Employee',
            'employee_num' => $firstRow['employee_num'] ?? 'EMP001',
            'phone' => $firstRow['phone'] ?? '+1234567890',
            'email' => $firstRow['email'] ?? 'employee@company.com',
            'department' => (object) ['name' => $firstRow['department'] ?? 'IT Department'],
            'userloc' => (object) ['name' => $firstRow['location'] ?? 'Main Office'],
        ];
        
        // Create a mock asset object for the template
        $asset = (object) [
            'manufacturer' => (object) ['name' => $firstRow['manufacturer'] ?? 'Sample Manufacturer'],
        ];
        
        return [
            'item_tag' => $firstRow['asset_tag'] ?? $firstRow['id'] ?? 'ASSET-001',
            'item_model' => $firstRow['model'] ?? $firstRow['name'] ?? 'Sample Model',
            'item_serial' => $firstRow['serial'] ?? 'SN123456',
            'item_status' => $firstRow['status'] ?? 'Deployed',
            'eula' => $this->generateEulaFromTableData($tableData, $tableId),
            'note' => $firstRow['notes'] ?? '',
            'check_out_date' => $firstRow['checkout_date'] ?? date('Y-m-d'),
            'accepted_date' => date('Y-m-d'),
            'assigned_to' => $firstRow['assigned_to'] ?? $firstRow['user'] ?? 'John Doe',
            'company_name' => $branding_settings->site_name,
            'signature' => '',
            'logo' => $path_logo,
            'date_settings' => $branding_settings->date_display_format,
            'user' => $user,
            'asset' => $asset,
        ];
    }
    
    /**
     * Generate EULA content from table data
     *
     * @param array $tableData
     * @param string $tableId
     * @return string
     */
    private function generateEulaFromTableData(array $tableData, string $tableId): string
    {
        $eula = '<h4>Asset Inventory Report</h4>';
        $eula .= '<p>This document contains the inventory of assets as of ' . date('Y-m-d H:i:s') . '</p>';
        $eula .= '<p>Total Items: ' . count($tableData) . '</p>';
        
        // Add table data to EULA
        $eula .= '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
        $eula .= '<thead><tr style="background-color: #f2f2f2;">';
        
        // Generate headers
        if (!empty($tableData)) {
            $firstRow = $tableData[0];
            foreach ($firstRow as $key => $value) {
                if (!in_array($key, ['actions', 'image', 'change', 'checkbox', 'checkincheckout', 'icon'])) {
                    $eula .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">' . ucfirst(str_replace('_', ' ', $key)) . '</th>';
                }
            }
        }
        
        $eula .= '</tr></thead><tbody>';
        
        // Generate rows (limit to first 10 for EULA)
        $displayData = array_slice($tableData, 0, 10);
        foreach ($displayData as $row) {
            $eula .= '<tr>';
            foreach ($row as $key => $value) {
                if (!in_array($key, ['actions', 'image', 'change', 'checkbox', 'checkincheckout', 'icon'])) {
                    $cleanValue = strip_tags($value);
                    $eula .= '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($cleanValue) . '</td>';
                }
            }
            $eula .= '</tr>';
        }
        
        $eula .= '</tbody></table>';
        
        if (count($tableData) > 10) {
            $eula .= '<p><em>Note: Only first 10 items shown. Total items: ' . count($tableData) . '</em></p>';
        }
        
        return $eula;
    }
    
    /**
     * Generate default EULA PDF (original functionality)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private function generateDefaultEulaPdf(Request $request)
    {
        // Example data - you can modify this based on your needs
        $data = [
            'item_tag' => $request->input('item_tag', 'ASSET-001'),
            'item_model' => $request->input('item_model', 'Sample Model'),
            'item_serial' => $request->input('item_serial', 'SN123456'),
            'item_status' => $request->input('item_status', 'Deployed'),
            'eula' => $request->input('eula', 'Sample EULA text'),
            'note' => $request->input('note', ''),
            'check_out_date' => $request->input('check_out_date', date('Y-m-d')),
            'accepted_date' => $request->input('accepted_date', date('Y-m-d')),
            'assigned_to' => $request->input('assigned_to', 'John Doe'),
            'company_name' => $request->input('company_name', 'Sample Company'),
            'signature' => $request->input('signature', ''),
            'logo' => $request->input('logo', ''),
            'date_settings' => $request->input('date_settings', 'Y-m-d'),
            'user' => (object) [
                'notes' => $request->input('user_notes', ''),
                'jobtitle' => $request->input('jobtitle', 'Employee'),
                'employee_num' => $request->input('employee_num', 'EMP001'),
                'phone' => $request->input('phone', '+1234567890'),
                'email' => $request->input('email', 'employee@company.com'),
                'department' => (object) ['name' => $request->input('department', 'IT Department')],
                'userloc' => (object) ['name' => $request->input('location', 'Main Office')],
            ],
            'asset' => (object) [
                'manufacturer' => (object) ['name' => $request->input('manufacturer', 'Sample Manufacturer')],
            ],
        ];

        // Add notes data for TCPDF generation
        $data['checkin_note'] = $request->input('checkin_note', '');
        $data['checkout_note'] = $request->input('checkout_note', '');
        $data['acceptance_note'] = $request->input('acceptance_note', '');

        // Generate TCPDF content for notes section if notes are present
        if (isset($data['checkin_note']) || isset($data['checkout_note']) || isset($data['acceptance_note'])) {
            $tcpdfService = new \App\Services\TcpdfService();
            try {
                // Try to generate TCPDF as image first
                $data['tcpdf_notes_image'] = $tcpdfService->generateNotesTcpdf(
                    $data['checkin_note'] ?? '',
                    $data['checkout_note'] ?? '',
                    $data['acceptance_note'] ?? ''
                );
            } catch (Exception $e) {
                // Fallback to HTML if TCPDF image generation fails
                $data['tcpdf_notes_html'] = $tcpdfService->generateNotesHtml(
                    $data['checkin_note'] ?? '',
                    $data['checkout_note'] ?? '',
                    $data['acceptance_note'] ?? ''
                );
            }
        }

        $html = view('account.accept.accept-asset-eula', $data)->render();
        
        // Use the new TCPDF service
        $pdfService = TcpdfService::createForReport('Asset EULA Acceptance', 'Asset EULA Acceptance Form');
        
        $pdfService->addPage()
                   ->setFont('notonaskharabicnormal', '', 12)
                   ->writeHtml($html);
        
        $filename = 'asset-eula-' . date('Y-m-d-his') . '.pdf';
        return $pdfService->response($filename);
    }

    /**
     * Stores the accept/decline of the checkout acceptance
     *
     * @param  Request $request
     * @param  int  $id
     */
    public function store(Request $request, $id) : RedirectResponse
    {
        $acceptance = CheckoutAcceptance::find($id);

        if (is_null($acceptance)) {
            return redirect()->route('account.accept')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (! $acceptance->isPending()) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
        }

        if (! $acceptance->isCheckedOutTo(auth()->user())) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.incorrect_user_accepted'));
        }

        if (! Company::isCurrentUserHasAccess($acceptance->checkoutable)) {
            return redirect()->route('account.accept')->with('error', trans('general.insufficient_permissions'));
        }

        if (! $request->filled('acceptance')) {
            return redirect()->back()->with('error', trans('admin/users/message.error.accept_or_decline'));
        }

        /**
         * Get the signature and save it
         */
        if (! Storage::exists('private_uploads/signatures')) {
            Storage::makeDirectory('private_uploads/signatures', 775);
        }



        $item = $acceptance->checkoutable_type::find($acceptance->checkoutable_id);
        $display_model = '';
        $pdf_view_route = '';
        $pdf_filename = 'accepted-eula-'.date('Y-m-d-h-i-s').'.pdf';
        $sig_filename='';

        if ($request->input('acceptance') == 'accepted') {

            /**
             * Check for the eula-pdfs directory
             */
            if (! Storage::exists('private_uploads/eula-pdfs')) {
                Storage::makeDirectory('private_uploads/eula-pdfs', 775);
            }

            if (Setting::getSettings()->require_accept_signature == '1') {
                
                // Check if the signature directory exists, if not create it
                if (!Storage::exists('private_uploads/signatures')) {
                    Storage::makeDirectory('private_uploads/signatures', 775);
                }

                // The item was accepted, check for a signature
                if ($request->filled('signature_output')) {
                    $sig_filename = 'siglog-' . Str::uuid() . '-' . date('Y-m-d-his') . '.png';
                    $data_uri = $request->input('signature_output');
                    $encoded_image = explode(',', $data_uri);
                    $decoded_image = base64_decode($encoded_image[1]);
                    Storage::put('private_uploads/signatures/' . $sig_filename, (string)$decoded_image);

                    // No image data is present, kick them back.
                    // This mostly only applies to users on super-duper crapola browsers *cough* IE *cough*
                } else {
                    return redirect()->back()->with('error', trans('general.shitty_browser'));
                }
            }


            $assigned_user = User::find($acceptance->assigned_to_id);
            // this is horrible
            switch($acceptance->checkoutable_type){
                case 'App\Models\Asset':
                        $pdf_view_route ='account.accept.accept-asset-eula';
                        $asset_model = AssetModel::find($item->model_id);
                        if (!$asset_model) {
                            return redirect()->back()->with('error', trans('admin/models/message.does_not_exist'));
                        }
                        $display_model = $asset_model->name;
                break;

                case 'App\Models\Accessory':
                        $pdf_view_route ='account.accept.accept-accessory-eula';
                        $accessory = Accessory::find($item->id);
                        $display_model = $accessory->name;
                break;

                case 'App\Models\LicenseSeat':
                        $pdf_view_route ='account.accept.accept-license-eula';
                        $license = License::find($item->license_id);
                        $display_model = $license->name;
                break;

                case 'App\Models\Component':
                        $pdf_view_route ='account.accept.accept-component-eula';
                        $component = Component::find($item->id);
                        $display_model = $component->name;
                break;

                case 'App\Models\Consumable':
                        $pdf_view_route ='account.accept.accept-consumable-eula';
                        $consumable = Consumable::find($item->id);
                        $display_model = $consumable->name;
                break;
            }
//            if ($acceptance->checkoutable_type == 'App\Models\Asset') {
//                $pdf_view_route ='account.accept.accept-asset-eula';
//                $asset_model = AssetModel::find($item->model_id);
//                $display_model = $asset_model->name;
//                $assigned_to = User::find($item->assigned_to)->present()->fullName;
//
//            } elseif ($acceptance->checkoutable_type== 'App\Models\Accessory') {
//                $pdf_view_route ='account.accept.accept-accessory-eula';
//                $accessory = Accessory::find($item->id);
//                $display_model = $accessory->name;
//                $assigned_to = User::find($item->assignedTo);
//
//            }

            /**
             * Gather the data for the PDF. We fire this whether there is a signature required or not,
             * since we want the moment-in-time proof of what the EULA was when they accepted it.
             */
            $branding_settings = SettingsController::getPDFBranding();

            $path_logo = "";

            // Check for the PDF logo path and use that, otherwise use the regular logo path
            if (!is_null($branding_settings->acceptance_pdf_logo)) {
                $path_logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
            } elseif (!is_null($branding_settings->logo)) {
                $path_logo = public_path() . '/uploads/' . $branding_settings->logo;
            }
            
            // Get the last checkout log
            $lastCheckoutLog = $item->log()->where('action_type', 'checkout')->latest()->first();
            $checkout_note = $lastCheckoutLog ? $lastCheckoutLog->note : '';

          // Always get the most recent checkin note for the asset (only if it's the latest action)
$lastCheckinLog = $item->log()
->where('action_type', 'checkin from')
->latest()
->first();

// Use the note only if it's present in the latest log
$checkin_note = ($lastCheckinLog && !empty($lastCheckinLog->note)) ? $lastCheckinLog->note : '';

// Debug logging for checkin note
\Log::debug('EULA PDF: Asset ID and checkin note', [
'asset_id' => $item->id,
'checkin_note' => $checkin_note
]);

            // Acceptance note from the request (not from the model, which may not be updated yet)
            $acceptance_note = $request->input('note');

            $data = [
                'item_tag' => $item->asset_tag,
                'item_model' => $display_model,
                'item_serial' => $item->serial,
                'item_status' => $item->assetstatus?->name,
                'eula' => $item->getEula(),
                'note' => $acceptance_note, // For backward compatibility
                'checkout_note' => $checkout_note, // Last checkout note
                'checkin_note' => $checkin_note,   // Last checkin note
                'acceptance_note' => $acceptance_note, // Acceptance note
                'check_out_date' => Carbon::parse($acceptance->created_at)->format('Y-m-d'),
                'accepted_date' => Carbon::parse($acceptance->accepted_at)->format('Y-m-d'),
                'assigned_to' => $assigned_user->present()->fullName,
                'company_name' => $branding_settings->site_name,
                'signature' => ($sig_filename) ? storage_path() . '/private_uploads/signatures/' . $sig_filename : null,
                'logo' => $path_logo,
                'date_settings' => $branding_settings->date_display_format,
                'user' => $assigned_user,
                'asset' => $item,
            ];

            if ($pdf_view_route!='') {
                Log::debug($pdf_filename.' is the filename, and the route was specified.');
                // Use Gpdf instead of DomPDF
                $pdfContent = $this->generatePdfWithTcpdf($pdf_view_route, $data);
                Storage::put('private_uploads/eula-pdfs/' .$pdf_filename, $pdfContent);
            }

            $acceptance->accept($sig_filename, $item->getEula(), $pdf_filename, $request->input('note'));

            // Send the PDF to the signing user
            if (($request->input('send_copy') == '1') && ($assigned_user->email !='')) {

                // Add the attachment for the signing user into the $data array
                $data['file'] = $pdf_filename;

                try {
                    $assigned_user->notify(new AcceptanceAssetAcceptedToUserNotification($data));
                } catch (\Exception $e) {
                    Log::warning($e);
                }
            }
            try {
                $acceptance->notify(new AcceptanceAssetAcceptedNotification($data));
            } catch (\Exception $e) {
                Log::warning($e);
            }
            event(new CheckoutAccepted($acceptance));

            $return_msg = trans('admin/users/message.accepted');

        } else if ($request->input('acceptance') == 'declined') {

            /**
             * Check for the eula-pdfs directory
             */
            if (! Storage::exists('private_uploads/eula-pdfs')) {
                Storage::makeDirectory('private_uploads/eula-pdfs', 775);
            }

            if (Setting::getSettings()->require_accept_signature == '1') {
                
                // Check if the signature directory exists, if not create it
                if (!Storage::exists('private_uploads/signatures')) {
                    Storage::makeDirectory('private_uploads/signatures', 775);
                }

                // The item was accepted, check for a signature
                if ($request->filled('signature_output')) {
                    $sig_filename = 'siglog-' . Str::uuid() . '-' . date('Y-m-d-his') . '.png';
                    $data_uri = $request->input('signature_output');
                    $encoded_image = explode(',', $data_uri);
                    $decoded_image = base64_decode($encoded_image[1]);
                    Storage::put('private_uploads/signatures/' . $sig_filename, (string)$decoded_image);

                    // No image data is present, kick them back.
                    // This mostly only applies to users on super-duper crapola browsers *cough* IE *cough*
                } else {
                    return redirect()->back()->with('error', trans('general.shitty_browser'));
                }
            }
            
            // Format the data to send the declined notification
            $branding_settings = SettingsController::getPDFBranding();

            // This is the most horriblest
            switch($acceptance->checkoutable_type){
                case 'App\Models\Asset':
                    $asset_model = AssetModel::find($item->model_id);
                    $display_model = $asset_model->name;
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\Accessory':
                    $accessory = Accessory::find($item->id);
                    $display_model = $accessory->name;
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\LicenseSeat':
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\Component':
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\Consumable':
                    $consumable = Consumable::find($item->id);
                    $display_model = $consumable->name;
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;
            }

            $data = [
                'item_tag' => $item->asset_tag,
                'item_model' => $display_model,
                'item_serial' => $item->serial,
                'item_status' => $item->assetstatus?->name,
                'note' => $request->input('note'),
                'declined_date' => Carbon::parse($acceptance->declined_at)->format('Y-m-d'),
                'signature' => ($sig_filename) ? storage_path() . '/private_uploads/signatures/' . $sig_filename : null,
                'assigned_to' => $assigned_to,
                'company_name' => $branding_settings->site_name,
                'date_settings' => $branding_settings->date_display_format,
            ];

            if ($pdf_view_route!='') {
                Log::debug($pdf_filename.' is the filename, and the route was specified.');
                // Use Gpdf instead of DomPDF
                $pdfContent = $this->generatePdfWithTcpdf($pdf_view_route, $data);
                Storage::put('private_uploads/eula-pdfs/' .$pdf_filename, $pdfContent);
            }

            $acceptance->decline($sig_filename, $request->input('note'));
            $acceptance->notify(new AcceptanceAssetDeclinedNotification($data));
            event(new CheckoutDeclined($acceptance));
            $return_msg = trans('admin/users/message.declined');
        }

        if ($acceptance->alert_on_response_id) {
            try {
                $recipient = User::find($acceptance->alert_on_response_id);

                if ($recipient) {
                    Mail::to($recipient)->send(new CheckoutAcceptanceResponseMail(
                        $acceptance,
                        $recipient,
                        $request->input('acceptance') === 'accepted',
                    ));
                }
            } catch (Exception $e) {
                Log::warning($e);
            }
        }

        return redirect()->to('account/accept')->with('success', $return_msg);

    }

}
