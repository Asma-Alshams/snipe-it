<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;
use Illuminate\Http\Request;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;


/**
 * This controller handles all actions related to Asset Maintenance for
 * the Snipe-IT Asset Management application.
 *
 * @version    v2.0
 */
class AssetMaintenancesController extends Controller
{

    /**
    *  Returns a view that invokes the ajax tables which actually contains
    * the content for the asset maintenances listing, which is generated in getDatatable.
    *
    * @todo This should be replaced with middleware and/or policies
    * @see AssetMaintenancesController::getDatatable() method that generates the JSON response
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function index() : View
    {
        $this->authorize('view', Asset::class);
        return view('asset_maintenances/index');
    }

    /**
     *  Returns a form view to create a new asset maintenance.
     *
     * @see AssetMaintenancesController::postCreate() method that stores the data
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     * @since [v1.8]
     * @return mixed
     */
    public function create() : View
    {
        $this->authorize('update', Asset::class);
        $asset = null;

        if ($asset = Asset::find(request('asset_id'))) {
            // We have to set this so that the correct property is set in the select2 ajax dropdown
            $asset->asset_id = $asset->id;
        }
        
        return view('asset_maintenances/edit')
                   ->with('assetMaintenanceType', AssetMaintenance::getImprovementOptions())
                   ->with('asset', $asset)
                   ->with('item', new AssetMaintenance);
    }

    /**
    *  Validates and stores the new asset maintenance
    *
    * @see AssetMaintenancesController::getCreate() method for the form
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function store(Request $request) : RedirectResponse
    {
        $this->authorize('update', Asset::class);

        $assets = Asset::whereIn('id', $request->input('selected_assets'))->get();

        // Loop through the selected assets
        foreach ($assets as $asset) {

            $assetMaintenance = new AssetMaintenance();
            $assetMaintenance->supplier_id = $request->input('supplier_id');
            $assetMaintenance->is_warranty = $request->input('is_warranty');
            $assetMaintenance->cost = $request->input('cost');
            $assetMaintenance->notes = $request->input('notes');
            $assetMaintenance->repair_method = $request->input('repair_method');
            $assetMaintenance->risk_level = $request->input('risk_level');

            // Save the asset maintenance data
            $assetMaintenance->asset_id = $asset->id;
            $assetMaintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
            $assetMaintenance->title = $request->input('title');
            $assetMaintenance->start_date = $request->input('start_date');
            $assetMaintenance->completion_date = $request->input('completion_date');
            $assetMaintenance->created_by = auth()->id();

            if (($assetMaintenance->completion_date !== null)
                && ($assetMaintenance->start_date !== '')
                && ($assetMaintenance->start_date !== '0000-00-00')
            ) {
                $startDate = Carbon::parse($assetMaintenance->start_date);
                $completionDate = Carbon::parse($assetMaintenance->completion_date);
                $assetMaintenance->asset_maintenance_time = (int) $completionDate->diffInDays($startDate, true);
            }


            // Was the asset maintenance created?
            if (!$assetMaintenance->save()) {
                return redirect()->back()->withInput()->withErrors($assetMaintenance->getErrors());
            }

            // Create acceptance record for the assigned user
            $assetMaintenance->createAcceptanceRecord();
        }

        return redirect()->route('maintenances.index')
            ->with('success', trans('admin/asset_maintenances/message.create.success'));

    }

    /**
    *  Returns a form view to edit a selected asset maintenance.
    *
    * @see AssetMaintenancesController::postEdit() method that stores the data
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function edit(AssetMaintenance $maintenance) : View | RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);

        return view('asset_maintenances/edit')
            ->with('selected_assets', $maintenance->asset->pluck('id')->toArray())
            ->with('asset_ids', request()->input('asset_ids', []))
            ->with('assetMaintenanceType', AssetMaintenance::getImprovementOptions())
            ->with('item', $maintenance);
    }

    /**
     *  Validates and stores an update to an asset maintenance
     *
     * @see AssetMaintenancesController::postEdit() method that stores the data
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @param Request $request
     * @param int $assetMaintenanceId
     * @version v1.0
     * @since [v1.8]
     */
    public function update(Request $request, AssetMaintenance $maintenance) : View | RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);

        $maintenance->supplier_id = $request->input('supplier_id');
        $maintenance->is_warranty = $request->input('is_warranty', 0);
        $maintenance->cost =  $request->input('cost');
        $maintenance->notes = $request->input('notes');
        $maintenance->repair_method = $request->input('repair_method');
        $maintenance->risk_level = $request->input('risk_level');
        $maintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
        $maintenance->title = $request->input('title');
        $maintenance->start_date = $request->input('start_date');
        $maintenance->completion_date = $request->input('completion_date');


        // Todo - put this in a getter/setter?
        if (($maintenance->completion_date == null))
        {
            if (($maintenance->asset_maintenance_time !== 0)
              || (! is_null($maintenance->asset_maintenance_time))
            ) {
                $maintenance->asset_maintenance_time = null;
            }
        }

        if (($maintenance->completion_date !== null)
          && ($maintenance->start_date !== '')
          && ($maintenance->start_date !== '0000-00-00')
        ) {
            $startDate = Carbon::parse($maintenance->start_date);
            $completionDate = Carbon::parse($maintenance->completion_date);
            $maintenance->asset_maintenance_time = (int) $completionDate->diffInDays($startDate, true);
        }

        if ($maintenance->save()) {
            return redirect()->route('maintenances.index')
                            ->with('success', trans('admin/asset_maintenances/message.edit.success'));
        }

        return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
    }

    /**
    *  Delete an asset maintenance
    *
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @param int $assetMaintenanceId
    * @version v1.0
    * @since [v1.8]
    */
    public function destroy(AssetMaintenance $maintenance) : RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);
        // Delete the asset maintenance
        $maintenance->delete();
        // Redirect to the asset_maintenance management page
        return redirect()->route('maintenances.index')
                       ->with('success', trans('admin/asset_maintenances/message.delete.success'));
    }

    /**
    *  View an asset maintenance
    *
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @param int $assetMaintenanceId
    * @version v1.0
    * @since [v1.8]
    */
    public function show(AssetMaintenance $maintenance) : View | RedirectResponse
    {
        return view('asset_maintenances/view')->with('assetMaintenance', $maintenance);
    }

    /**
     * Helper to generate PDF using Gpdf with proper options for Arabic/RTL support.
     *
     * @param string $viewRoute
     * @param array $data
     * @return string
     */
    private function generatePdfWithGpdf(string $viewRoute, array $data): string
    {
        $html = view($viewRoute, $data)->render();
        return GpdfFacade::generate($html, [
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
        ]);
    }

    /**
     * Generate a PDF report for a specific asset maintenance.
     */
    public function pdf(AssetMaintenance $maintenance)
    {
        $this->authorize('view', Asset::class);
        $maintenance->load(['asset', 'asset.assignedTo', 'asset.assignedTo.department']);
        $branding_settings = \App\Http\Controllers\SettingsController::getPDFBranding();
        $logo = '';
        if (!is_null($branding_settings->acceptance_pdf_logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
        } elseif (!is_null($branding_settings->logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->logo;
        }
        $item_serial = $maintenance->asset ? $maintenance->asset->serial : '';
        // Get user location (assigned user or asset location)
        $user = null;
        if ($maintenance->asset && $maintenance->asset->assignedTo) {
            $user = $maintenance->asset->assignedTo;
        }
        if ($user && isset($user->userloc)) {
            // user->userloc is available
        } elseif ($maintenance->asset && isset($maintenance->asset->location)) {
            // fallback to asset location
            $user = (object)[ 'userloc' => (object)['name' => $maintenance->asset->location->name ?? '-'] ];
        } else {
            $user = (object)[ 'userloc' => (object)['name' => '-'] ];
        }
        $userName = $maintenance->created_by
            ? \App\Models\User::withTrashed()->find($maintenance->created_by)?->name
            : 'Unknown';
        
        // Get custom field values
        $macAddress = $maintenance->asset ? $maintenance->asset->getAttribute('_snipeit_mac_address_1') : null;
        $maintenanceStatus = self::getMaintenanceStatus($maintenance);
        
        $pdfContent = $this->generatePdfWithGpdf('asset_maintenances.pdf', [
'maintenance' => $maintenance,
            'assetMaintenance' => $maintenance,
            'createdByName' => $userName,
            'logo' => $logo,
            'item_serial' => $item_serial,
            'user' => $user,
            'macAddress' => $macAddress,
            'maintenanceStatus' => $maintenanceStatus
        ]);
        $filename = 'maintenance-report-' . $maintenance->id . '.pdf';
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Export asset maintenances as PDF from table data (server-side, Gpdf, Arabic/RTL support)
     */
    public function exportPdf(\Illuminate\Http\Request $request)
    {
        $tableData = json_decode($request->input('table_data', '[]'), true);
        $tableParams = json_decode($request->input('table_params', '{}'), true);
        $tableId = $request->input('table_id', 'maintenances');
        $fileName = $request->input('file_name', 'maintenances-export');

        $branding_settings = \App\Http\Controllers\SettingsController::getPDFBranding();
        $logo = '';
        if (!is_null($branding_settings->acceptance_pdf_logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
        } elseif (!is_null($branding_settings->logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->logo;
        }
        $data = [
            'maintenances' => $tableData,
            'logo' => $logo,
            'date_settings' => config('app.date_format', 'Y-m-d'),
        ];
        $pdfContent = $this->generatePdfWithGpdf('asset_maintenances.pdf', $data);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '-' . date('Y-m-d-his') . '.pdf"'
        ]);
    }

    /**
     * Generate a sample PDF for asset maintenance using Gpdf.
     */
    public function generatePdf()
    {
        $maintenance = AssetMaintenance::with('asset')->first();
        if (!$maintenance) {
            abort(404, 'No asset maintenance record found.');
        }
        $branding_settings = \App\Http\Controllers\SettingsController::getPDFBranding();
        $logo = '';
        if (!is_null($branding_settings->acceptance_pdf_logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
        } elseif (!is_null($branding_settings->logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->logo;
        }
        $item_serial = $maintenance->asset ? $maintenance->asset->serial : '';
        // Get user location (assigned user or asset location)
        $user = null;
        if ($maintenance->asset && $maintenance->asset->assignedTo) {
            $user = $maintenance->asset->assignedTo;
        }
        if ($user && isset($user->userloc)) {
            // user->userloc is available
        } elseif ($maintenance->asset && isset($maintenance->asset->location)) {
            // fallback to asset location
            $user = (object)[ 'userloc' => (object)['name' => $maintenance->asset->location->name ?? '-'] ];
        } else {
            $user = (object)[ 'userloc' => (object)['name' => '-'] ];
        }
        $pdfContent = $this->generatePdfWithGpdf('asset_maintenances.pdf', [
            'maintenance' => $maintenance,
            'logo' => $logo,
            'item_serial' => $item_serial,
            'user' => $user
        ]);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="maintenance-sample.pdf"'
        ]);
    }

    /**
     * Export all asset maintenances as a PDF using the recent_pdf view.
     */
    public function exportAllRecentPdf(Request $request)
    {
        $this->authorize('view', Asset::class);
        $maintenancesQuery = \App\Models\AssetMaintenance::with([
            'asset',
            'asset.assignedTo',
            'asset.assetstatus',
            'adminuser',
        ])->whereDoesntHave('maintenanceAcceptances', function($query) {
            $query->where('assigned_to_id', auth()->id())
                  ->whereNotNull('declined_at');
        });

        // Filtering logic
        if ($request->filled('filter')) {
            if ($request->input('filter') === 'all' && $request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $maintenancesQuery->whereDate('start_date', '>=', $start)
                                  ->whereDate('completion_date', '<=', $end);
            } elseif ($request->input('filter') === 'department') {
                $maintenancesQuery->where('periodic', true);
                // Add date filtering for department maintenances
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = $request->input('start_date');
                    $end = $request->input('end_date');
                    $maintenancesQuery->whereDate('start_date', '>=', $start)
                                      ->whereDate('completion_date', '<=', $end);
                }
            } elseif ($request->input('filter') === 'created_at' && $request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $maintenancesQuery->whereDate('created_at', '>=', $start)
                                  ->whereDate('created_at', '<=', $end);
            } elseif ($request->input('filter') === 'maintenance_date' && $request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $maintenancesQuery->whereDate('start_date', '>=', $start)
                                  ->whereDate('completion_date', '<=', $end);
            }
        }
        $maintenances = $maintenancesQuery->orderBy('created_at', 'desc')->get();
        
        // Add custom field values to each maintenance
        foreach ($maintenances as $maintenance) {
            if ($maintenance->asset) {
                $maintenance->macAddress = $maintenance->asset->getAttribute('_snipeit_mac_address_1');
                $maintenance->maintenanceStatus = $maintenance->asset->getAttribute('_snipeit_maintenance_status_2');
                
                // Get original assigned user from acceptance record
                $acceptance = $maintenance->maintenanceAcceptances->first();
                if ($acceptance && $acceptance->assigned_to_id) {
                    $assignedUser = \App\Models\User::with('department')->find($acceptance->assigned_to_id);
                    $maintenance->assignedUser = $assignedUser;
                } else {
                    $maintenance->assignedUser = null;
                }
            } else {
                $maintenance->macAddress = null;
                $maintenance->maintenanceStatus = null;
                $maintenance->assignedUser = null;
            }
        }
        
        $branding_settings = \App\Http\Controllers\SettingsController::getPDFBranding();
        $logo = '';
        if (!is_null($branding_settings->acceptance_pdf_logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
        } elseif (!is_null($branding_settings->logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->logo;
        }
        $data = [
            'maintenances' => $maintenances,
            'logo' => $logo,
            'filter' => $request->input('filter'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        $html = view('asset_maintenances.recent_pdf', $data)->render();
        $pdfContent = \Omaralalwi\Gpdf\Facade\Gpdf::generate($html, [
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
        ]);
        $filename = 'all-maintenance-report-' . date('Y-m-d-his') . '.pdf';
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Get the maintenance status based on completion and acceptance
     *
     * @param AssetMaintenance $maintenance
     * @return string
     */
    private static function getMaintenanceStatus(AssetMaintenance $maintenance)
    {
        // Check if maintenance is declined (highest priority)
        $acceptance = $maintenance->maintenanceAcceptances()->first();
        if ($acceptance && $acceptance->declined_at) {
            return 'Declined';
        }

        // Check if maintenance is completed (only if accepted and past completion date)
        if ($maintenance->completion_date && now()->isAfter($maintenance->completion_date)) {
            // If accepted and past completion date, mark as completed
            if ($acceptance && $acceptance->accepted_at) {
                return 'Completed';
            }
            // If not accepted and not declined and past completion date, keep as pending
            if (!$acceptance || $acceptance->isPending()) {
                return 'Pending';
            }
        }

        // Check if maintenance is accepted
        if ($acceptance && $acceptance->accepted_at) {
            return 'Under Maintenance';
        }

        // Check if maintenance is pending acceptance
        if ($acceptance && $acceptance->isPending()) {
            return 'Pending';
        }

        // Default: in progress
        return 'In Progress';
    }

    /**
     * Export declined asset maintenances as a PDF using the declined_pdf view.
     */
    public function exportDeclinedPdf(Request $request)
    {
        $this->authorize('view', Asset::class);
        $maintenancesQuery = \App\Models\AssetMaintenance::with([
            'asset',
            'asset.assignedTo',
            'asset.assetstatus',
            'adminuser',
        ])->whereHas('maintenanceAcceptances', function($query) {
            $query->where('assigned_to_id', auth()->id())
                  ->whereNotNull('declined_at');
        });

        // Filtering logic (optional: add date range if needed)
        if ($request->filled('filter')) {
            if ($request->input('filter') === 'all' && $request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $maintenancesQuery->whereDate('start_date', '>=', $start)
                                  ->whereDate('completion_date', '<=', $end);
            } elseif ($request->input('filter') === 'department') {
                $maintenancesQuery->where('periodic', true);
                // Add date filtering for department maintenances
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = $request->input('start_date');
                    $end = $request->input('end_date');
                    $maintenancesQuery->whereDate('start_date', '>=', $start)
                                      ->whereDate('completion_date', '<=', $end);
                }
            } elseif ($request->input('filter') === 'declined' && $request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $maintenancesQuery->whereDate('start_date', '>=', $start)
                                  ->whereDate('completion_date', '<=', $end);
            } elseif ($request->input('filter') === 'created_at' && $request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $maintenancesQuery->whereDate('created_at', '>=', $start)
                                  ->whereDate('created_at', '<=', $end);
            } elseif ($request->input('filter') === 'maintenance_date' && $request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $maintenancesQuery->whereDate('start_date', '>=', $start)
                                  ->whereDate('completion_date', '<=', $end);
            }
        }
        $maintenances = $maintenancesQuery->orderBy('created_at', 'desc')->get();
        // Add custom field values to each maintenance
        foreach ($maintenances as $maintenance) {
            if ($maintenance->asset) {
                $maintenance->macAddress = $maintenance->asset->getAttribute('_snipeit_mac_address_1');
                $maintenance->maintenanceStatus = self::getMaintenanceStatus($maintenance);
                // Get original assigned user from acceptance record
                $acceptance = $maintenance->maintenanceAcceptances->first();
                if ($acceptance && $acceptance->assigned_to_id) {
                    $assignedUser = \App\Models\User::with('department')->find($acceptance->assigned_to_id);
                    $maintenance->assignedUser = $assignedUser;
                } else {
                    $maintenance->assignedUser = null;
                }
            } else {
                $maintenance->macAddress = null;
                $maintenance->maintenanceStatus = null;
                $maintenance->assignedUser = null;
            }
        }
        $branding_settings = \App\Http\Controllers\SettingsController::getPDFBranding();
        $logo = '';
        if (!is_null($branding_settings->acceptance_pdf_logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
        } elseif (!is_null($branding_settings->logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->logo;
        }
        $data = [
            'maintenances' => $maintenances,
            'logo' => $logo,
            'filter' => $request->input('filter'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        $html = view('asset_maintenances.declined_pdf', $data)->render();
        $pdfContent = \Omaralalwi\Gpdf\Facade\Gpdf::generate($html, [
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
        ]);
        $filename = 'declined-maintenance-report-' . date('Y-m-d-his') . '.pdf';
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Create maintenances for all assets of all users in a department
     */
    public function createForDepartment(Request $request) : RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:100',
            'asset_maintenance_type' => 'required|string',
            'start_date' => 'required|date',
            'completion_date' => 'nullable|date|after_or_equal:start_date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'repair_method' => 'nullable|string',
            'is_warranty' => 'nullable|boolean',
        ]);

        $department = \App\Models\Department::with('users.assets')->findOrFail($validated['department_id']);
        $createdCount = 0;
        foreach ($department->users as $user) {
            foreach ($user->assets as $asset) {
                $assetMaintenance = new AssetMaintenance();
                $assetMaintenance->supplier_id = $validated['supplier_id'] ?? null;
                $assetMaintenance->is_warranty = $validated['is_warranty'] ?? 0;
                $assetMaintenance->cost = $validated['cost'] ?? null;
                $assetMaintenance->notes = $validated['notes'] ?? null;
                $assetMaintenance->repair_method = $validated['repair_method'] ?? null;
                $assetMaintenance->risk_level = $validated['risk_level'] ?? null;
                $assetMaintenance->asset_id = $asset->id;
                $assetMaintenance->asset_maintenance_type = $validated['asset_maintenance_type'];
                $assetMaintenance->title = $validated['title'];
                $assetMaintenance->start_date = $validated['start_date'];
                $assetMaintenance->completion_date = $validated['completion_date'] ?? null;
                $assetMaintenance->created_by = auth()->id();
                // Set periodic to true to identify this as a department maintenance
                $assetMaintenance->periodic = true;
                if (($assetMaintenance->completion_date !== null)
                    && ($assetMaintenance->start_date !== '')
                    && ($assetMaintenance->start_date !== '0000-00-00')
                ) {
                    $startDate = Carbon::parse($assetMaintenance->start_date);
                    $completionDate = Carbon::parse($assetMaintenance->completion_date);
                    $assetMaintenance->asset_maintenance_time = (int) $completionDate->diffInDays($startDate, true);
                }
                if ($assetMaintenance->save()) {
                    $assetMaintenance->createAcceptanceRecord();
                    $createdCount++;
                }
            }
        }
        return redirect()->route('maintenances.index')
            ->with('success', $createdCount . ' maintenances created for department.');
    }

    /**
     * Step 1: Show confirmation page for department maintenance creation
     */
    public function confirmDepartmentMaintenances(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:100',
            'asset_maintenance_type' => 'required|string',
            'start_date' => 'required|date',
            'completion_date' => 'nullable|date|after_or_equal:start_date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'repair_method' => 'nullable|string',
            'is_warranty' => 'nullable|boolean',
            'risk_level' => 'nullable|in:high,medium,low',
        ]);
        $department = \App\Models\Department::with('users.assets')->findOrFail($validated['department_id']);
        return view('asset_maintenances.confirm_department', [
            'department' => $department,
            'fields' => $validated,
        ]);
    }

    /**
     * Step 2: Finalize and create maintenances for selected users/assets
     */
    public function finalizeDepartmentMaintenances(Request $request)
    {
        $validated = $request->validate([
            'user_asset' => 'required|array', // user_asset[user_id][] = asset_id
            'title' => 'required|string|max:100',
            'asset_maintenance_type' => 'required|string',
            'start_date' => 'required|date',
            'completion_date' => 'nullable|date|after_or_equal:start_date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'repair_method' => 'nullable|string',
            'is_warranty' => 'nullable|boolean',
            'risk_level' => 'nullable|in:high,medium,low',
        ]);
        $createdCount = 0;
        // Generate a unique identifier for this department maintenance batch
        $batchId = time() . '_' . auth()->id();
        
        foreach ($validated['user_asset'] as $user_id => $asset_ids) {
            foreach ($asset_ids as $asset_id) {
                $assetMaintenance = new \App\Models\AssetMaintenance();
                $assetMaintenance->supplier_id = $validated['supplier_id'] ?? null;
                $assetMaintenance->is_warranty = $validated['is_warranty'] ?? 0;
                $assetMaintenance->cost = $validated['cost'] ?? null;
                $assetMaintenance->notes = $validated['notes'] ?? null;
                $assetMaintenance->repair_method = $validated['repair_method'] ?? null;
                $assetMaintenance->risk_level = $validated['risk_level'] ?? null;
                $assetMaintenance->asset_id = $asset_id;
                $assetMaintenance->asset_maintenance_type = $validated['asset_maintenance_type'];
                $assetMaintenance->title = $validated['title'];
                $assetMaintenance->start_date = $validated['start_date'];
                $assetMaintenance->completion_date = $validated['completion_date'] ?? null;
                $assetMaintenance->created_by = auth()->id();
                // Set periodic to true to identify this as a department maintenance
                $assetMaintenance->periodic = true;
                if (($assetMaintenance->completion_date !== null)
                    && ($assetMaintenance->start_date !== '')
                    && ($assetMaintenance->start_date !== '0000-00-00')
                ) {
                    $startDate = \Carbon\Carbon::parse($assetMaintenance->start_date);
                    $completionDate = \Carbon\Carbon::parse($assetMaintenance->completion_date);
                    $assetMaintenance->asset_maintenance_time = (int) $completionDate->diffInDays($startDate, true);
                }
                if ($assetMaintenance->save()) {
                    $assetMaintenance->createAcceptanceRecord();
                    $createdCount++;
                }
            }
        }
        return redirect()->route('maintenances.index')
            ->with('success', $createdCount . ' maintenances created for department.');
    }
}