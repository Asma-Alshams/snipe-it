<?php

namespace App\Http\Controllers\Licenses;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;

/**
 * This controller handles all actions related to Licenses for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class LicensesController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the licenses listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('view', License::class);

        return view('licenses/index');
    }

    /**
     * Returns a form view that allows an admin to create a new licence.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see AccessoriesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', License::class);
        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No',
        ];

        return view('licenses/edit')
            ->with('depreciation_list', Helper::depreciationList())
            ->with('maintained_list', $maintained_list)
            ->with('item', new License);
    }

    /**
     * Validates and stores the license form data submitted from the new
     * license form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getCreate() method that provides the form view
     * @since [v1.0]
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', License::class);
        // create a new model instance
        $license = new License();
        // Save the license data
        $license->company_id        = Company::getIdForCurrentUser($request->input('company_id'));
        $license->depreciation_id   = $request->input('depreciation_id');
        $license->expiration_date   = $request->input('expiration_date');
        $license->license_email     = $request->input('license_email');
        $license->license_name      = $request->input('license_name');
        $license->maintained        = $request->input('maintained', 0);
        $license->manufacturer_id   = $request->input('manufacturer_id');
        $license->name              = $request->input('name');
        $license->notes             = $request->input('notes');
        $license->order_number      = $request->input('order_number');
        $license->purchase_cost     = $request->input('purchase_cost');
        $license->purchase_date     = $request->input('purchase_date');
        $license->purchase_order    = $request->input('purchase_order');
        $license->purchase_order    = $request->input('purchase_order');
        $license->reassignable      = $request->input('reassignable', 0);
        $license->seats             = $request->input('seats');
        $license->serial            = $request->input('serial');
        $license->supplier_id       = $request->input('supplier_id');
        $license->category_id       = $request->input('category_id');
        $license->termination_date  = $request->input('termination_date');
        $license->created_by           = auth()->id();
        $license->min_amt           = $request->input('min_amt');

        session()->put(['redirect_option' => $request->get('redirect_option')]);

        if ($license->save()) {
            return Helper::getRedirectOption($request, $license->id, 'Licenses')
                ->with('success', trans('admin/licenses/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($license->getErrors());
    }

    /**
     * Returns a form with existing license data to allow an admin to
     * update license information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $licenseId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(License $license)
    {

        $this->authorize('update', $license);
        session()->put('back_url', url()->previous());
        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No',
        ];

        return view('licenses/edit')
            ->with('item', $license)
            ->with('depreciation_list', Helper::depreciationList())
            ->with('maintained_list', $maintained_list);
    }


    /**
     * Validates and stores the license form data submitted from the edit
     * license form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getEdit() method that provides the form view
     * @since [v1.0]
     * @param Request $request
     * @param int $licenseId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, License $license)
    {


        $this->authorize('update', $license);

        $license->company_id        = Company::getIdForCurrentUser($request->input('company_id'));
        $license->depreciation_id   = $request->input('depreciation_id');
        $license->expiration_date   = $request->input('expiration_date');
        $license->license_email     = $request->input('license_email');
        $license->license_name      = $request->input('license_name');
        $license->maintained        = $request->input('maintained',0);
        $license->name              = $request->input('name');
        $license->notes             = $request->input('notes');
        $license->order_number      = $request->input('order_number');
        $license->purchase_cost     = $request->input('purchase_cost');
        $license->purchase_date     = $request->input('purchase_date');
        $license->purchase_order    = $request->input('purchase_order');
        $license->reassignable      = $request->input('reassignable', 0);
        $license->serial            = $request->input('serial');
        $license->termination_date  = $request->input('termination_date');
        $license->seats             = e($request->input('seats'));
        $license->manufacturer_id   =  $request->input('manufacturer_id');
        $license->supplier_id       = $request->input('supplier_id');
        $license->category_id       = $request->input('category_id');
        $license->min_amt           = $request->input('min_amt');

        session()->put(['redirect_option' => $request->get('redirect_option')]);

        if ($license->save()) {
            return Helper::getRedirectOption($request, $license->id, 'Licenses')
                ->with('success', trans('admin/licenses/message.update.success'));
        }
        // If we can't adjust the number of seats, the error is flashed to the session by the event handler in License.php
        return redirect()->back()->withInput()->withErrors($license->getErrors());
    }

    /**
     * Checks to see whether the selected license can be deleted, and
     * if it can, marks it as deleted.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $licenseId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(License $license)
    {
        // Check if the license exists
        if (is_null($license = License::find($license->id))) {
            // Redirect to the license management page
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }

        $this->authorize('delete', $license);

        if ($license->assigned_seats_count == 0) {
            // Delete the license and the associated license seats
            DB::table('license_seats')
                ->where('license_id', $license->id)
                ->update(['assigned_to' => null, 'asset_id' => null]);

            $licenseSeats = $license->licenseseats();
            $licenseSeats->delete();
            $license->delete();

            // Redirect to the licenses management page
            return redirect()->route('licenses.index')->with('success', trans('admin/licenses/message.delete.success'));
            // Redirect to the license management page
        }
        // There are still licenses in use.
        return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.assoc_users'));
    }

    /**
     * Makes the license detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $licenseId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(License $license)
    {
        $license = License::with('assignedusers')->find($license->id);

        $users_count = User::where('autoassign_licenses', '1')->count();
        $total_seats_count = $license->totalSeatsByLicenseID();
        $available_seats_count = $license->availCount()->count();
        $checkedout_seats_count = ($total_seats_count - $available_seats_count);

        $this->authorize('view', $license);
        return view('licenses.view', compact('license'))
            ->with('users_count', $users_count)
            ->with('total_seats_count', $total_seats_count)
            ->with('available_seats_count', $available_seats_count)
            ->with('checkedout_seats_count', $checkedout_seats_count);

    }


    /**
     * Returns a view with prepopulated data for clone
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $licenseId
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getClone($licenseId = null) : \Illuminate\Contracts\View\View | \Illuminate\Http\RedirectResponse
    {
        if (is_null($license_to_clone = License::find($licenseId))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.does_not_exist'));
        }

        $this->authorize('create', License::class);

        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No',
        ];
        //clone the orig
        $license = clone $license_to_clone;
        $license->id = null;
        $license->serial = null;

        // Show the page
        return view('licenses/edit')
        ->with('depreciation_list', Helper::depreciationList())
        ->with('item', $license)
        ->with('maintained_list', $maintained_list);
    }

    /**
     * Exports Licenses to CSV
     *
     * @author [G. Martinez]
     * @since [v6.3]
     * @return StreamedResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getExportLicensesCsv()
    {
        $this->authorize('view', License::class);
        \Debugbar::disable();

        $response = new StreamedResponse(function () {
            // Open output stream
            $handle = fopen('php://output', 'w');
            $licenses= License::with('company',
                          'manufacturer',
                          'category',
                          'supplier',
                          'adminuser',
                          'assignedusers')
                          ->orderBy('created_at', 'DESC');
            Company::scopeCompanyables($licenses)
                ->chunk(500, function ($licenses) use ($handle) {
                    $headers = [
                        // strtolower to prevent Excel from trying to open it as a SYLK file
                        strtolower(trans('general.id')),
                        trans('general.company'),
                        trans('general.name'),
                        trans('general.serial_number'),
                        trans('general.purchase_date'),
                        trans('general.purchase_cost'),
                        trans('general.order_number'),
                        trans('general.licenses_available'),
                        trans('admin/licenses/table.seats'),
                        trans('general.created_by'),
                        trans('general.depreciation'),
                        trans('general.updated_at'),
                        trans('admin/licenses/table.deleted_at'),
                        trans('general.email'),
                        trans('admin/hardware/form.fully_depreciated'),
                        trans('general.supplier'),
                        trans('admin/licenses/form.expiration'),
                        trans('admin/licenses/form.purchase_order'),
                        trans('admin/licenses/form.termination_date'),
                        trans('admin/licenses/form.maintained'),
                        trans('general.manufacturer'),
                        trans('general.category'),
                        trans('general.min_amt'),
                        trans('admin/licenses/form.reassignable'),
                        trans('general.notes'),
                        trans('general.created_at'),
                    ];

                    fputcsv($handle, $headers);

                    foreach ($licenses as $license) {
                        // Add a new row with data
                        $values = [
                            $license->id,
                            $license->company ? $license->company->name: '',
                            $license->name,
                            $license->serial,
                            $license->purchase_date,
                            $license->purchase_cost,
                            $license->order_number,
                            $license->free_seat_count,
                            $license->seats,
                            ($license->adminuser ? $license->adminuser->present()->fullName() : trans('admin/reports/general.deleted_user')),
                            $license->depreciation ? $license->depreciation->name: '',
                            $license->updated_at,
                            $license->deleted_at,
                            $license->email,
                            ( $license->depreciate == '1') ? trans('general.yes') : trans('general.no'),
                            ($license->supplier) ? $license->supplier->name: '',
                            $license->expiration_date,
                            $license->purchase_order,
                            $license->termination_date,
                            ( $license->maintained == '1') ? trans('general.yes') : trans('general.no'),
                            $license->manufacturer ? $license->manufacturer->name: '',
                            $license->category ? $license->category->name: '',
                            $license->min_amt,
                            ( $license->reassignable == '1') ? trans('general.yes') : trans('general.no'),
                            $license->notes,
                            $license->created_at,
                        ];

                        fputcsv($handle, $values);
                    }
                });

            // Close the output stream
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="licenses-'.date('Y-m-d-his').'.csv"',
        ]);

        return $response;
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
     * Export all licenses as a PDF report.
     */
    public function exportPdf(Request $request)
    {
        $this->authorize('view', License::class);
        
        $licensesQuery = License::with([
            'company',
            'manufacturer',
            'category',
            'supplier',
            'adminuser',
        ])->orderBy('created_at', 'desc');

        // Apply company scope
        Company::scopeCompanyables($licensesQuery);

        // Filtering logic
        if ($request->filled('filter') && $request->filled('start_date') && $request->filled('end_date')) {
            if ($request->input('filter') === 'all') {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $licensesQuery->whereDate('created_at', '>=', $start)
                              ->whereDate('created_at', '<=', $end);
            } elseif ($request->input('filter') === 'expiration_date') {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $licensesQuery->whereDate('expiration_date', '>=', $start)
                              ->whereDate('expiration_date', '<=', $end);
            } elseif ($request->input('filter') === 'purchase_date') {
                $start = $request->input('start_date');
                $end = $request->input('end_date');
                $licensesQuery->whereDate('purchase_date', '>=', $start)
                              ->whereDate('purchase_date', '<=', $end);
            }
        }
        // If no filters provided, show all licenses (full report)

        $licenses = $licensesQuery->get();
        
        // Get branding settings for logo
        $branding_settings = \App\Http\Controllers\SettingsController::getPDFBranding();
        $logo = '';
        if (!is_null($branding_settings->acceptance_pdf_logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
        } elseif (!is_null($branding_settings->logo)) {
            $logo = public_path() . '/uploads/' . $branding_settings->logo;
        }
        
        $data = [
            'licenses' => $licenses,
            'logo' => $logo,
            'filter' => $request->input('filter'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        
        $pdfContent = $this->generatePdfWithGpdf('licenses.report_pdf', $data);
        $filename = 'licenses-report-' . date('Y-m-d-his') . '.pdf';
        
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Update the expiration date of a license.
     * Allows users who can access the View Assets page to update expiration dates
     * for licenses assigned to them or their subordinates.
     *
     * @param Request $request
     * @param License $license
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateExpiration(Request $request, License $license)
    {
        // Check if user has general license edit permission (admins, etc.)
        if (auth()->user()->hasAccess('licenses.edit')) {
            $this->authorize('update', $license);
        } else {
            // For regular users, check if they can access this license through View Assets page
            $this->authorizeViewAssetsAccess($license);
        }

        $request->validate([
            'expiration_date' => 'nullable|date_format:Y-m-d'
        ]);

        $license->update([
            'expiration_date' => $request->input('expiration_date') ?: null
        ]);

        return redirect()->back()->with('success', trans('admin/licenses/message.update.success'));
    }

    /**
     * Authorize users who can access the View Assets page to update license expiration dates.
     * This allows users to update expiration dates for licenses assigned to them or their subordinates.
     *
     * @param License $license
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizeViewAssetsAccess(License $license)
    {
        $user = auth()->user();
        
        // Super users can always update
        if ($user->isSuperUser()) {
            return;
        }
        
        // Check if the license is assigned to the current user
        if ($license->assignedusers()->where('users.id', $user->id)->exists()) {
            return;
        }
        
        // Check if the license is assigned to any of the user's subordinates (manager view)
        $subordinates = $user->getAllSubordinates();
        if ($subordinates->isNotEmpty() && $license->assignedusers()->whereIn('users.id', $subordinates->pluck('id'))->exists()) {
            return;
        }
        
        // If none of the above conditions are met, deny access
        throw new \Illuminate\Auth\Access\AuthorizationException('You are not authorized to update this license expiration date.');
    }
}
