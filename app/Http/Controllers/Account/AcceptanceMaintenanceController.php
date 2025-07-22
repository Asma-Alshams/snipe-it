<?php

namespace App\Http\Controllers\Account;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AcceptanceMaintenanceController extends \App\Http\Controllers\Controller
{
    /**
     * Shows a form to either accept or decline the maintenance acceptance
     * @param  int  $id
     */
    public function createMaintenance($id) : View | RedirectResponse
    {
        $maintenanceAcceptance = \App\Models\MaintenanceAcceptance::with('maintenance.asset')->find($id);

        if (is_null($maintenanceAcceptance)) {
            return redirect()->route('account.accept')->with('error', 'Maintenance acceptance record not found.');
        }

        if (! $maintenanceAcceptance->isPending()) {
            return redirect()->route('account.accept')->with('error', 'This maintenance has already been processed.');
        }

        if (! $maintenanceAcceptance->isAssignedTo(auth()->user())) {
            return redirect()->route('account.accept')->with('error', 'You are not authorized to accept this maintenance.');
        }

        if (! Company::isCurrentUserHasAccess($maintenanceAcceptance->maintenance->asset)) {
            return redirect()->route('account.accept')->with('error', trans('general.error_user_company'));
        }

        return view('account/accept.create_maintenance', compact('maintenanceAcceptance'));
    }

    /**
     * Store the accept/decline of the maintenance acceptance
     * @param  Request $request
     * @param  int  $id
     */
    public function storeMaintenance(Request $request, $id) : RedirectResponse
    {
        $maintenanceAcceptance = \App\Models\MaintenanceAcceptance::with('maintenance.asset')->find($id);

        if (is_null($maintenanceAcceptance)) {
            return redirect()->route('account.accept')->with('error', 'Maintenance acceptance record not found.');
        }

        if (! $maintenanceAcceptance->isPending()) {
            return redirect()->route('account.accept')->with('error', 'This maintenance has already been processed.');
        }

        if (! $maintenanceAcceptance->isAssignedTo(auth()->user())) {
            return redirect()->route('account.accept')->with('error', 'You are not authorized to accept this maintenance.');
        }

        if (! Company::isCurrentUserHasAccess($maintenanceAcceptance->maintenance->asset)) {
            return redirect()->route('account.accept')->with('error', trans('general.error_user_company'));
        }

        $signature_filename = null;
        $note = $request->input('note');

        // Handle signature file upload
        if ($request->filled('signature_output')) {
            $signature_data = $request->input('signature_output');
            $filename = 'maintenance_acceptance_' . $maintenanceAcceptance->id . '_' . time() . '.png';
            $signatures_path = public_path('uploads/signatures');
            if (!file_exists($signatures_path)) {
                mkdir($signatures_path, 0755, true);
            }
            $signature_file_path = $signatures_path . '/' . $filename;
            $signature_data = str_replace('data:image/png;base64,', '', $signature_data);
            $signature_data = str_replace(' ', '+', $signature_data);
            $signature_data = base64_decode($signature_data);
            if (file_put_contents($signature_file_path, $signature_data)) {
                $signature_filename = $filename;
            }
        }

        if ($request->input('acceptance') == 'accepted') {
            $maintenanceAcceptance->accept($signature_filename, $note);
            $return_msg = 'Maintenance has been accepted.';
            return redirect()->to('account/accept')->with('success', $return_msg);
        } else {
            $maintenanceAcceptance->decline($signature_filename, $note);
            $return_msg = 'Maintenance has been declined.';
            return redirect()->to('account/accept')->with('success', $return_msg);
        }
    }

    /**
     * Display the EULA or PDF for a maintenance acceptance.
     */
    public function showEula($id)
    {
        // You can replace this with actual EULA/PDF logic later
        return view('account.accept.maintenance-eula', ['id' => $id]);
    }

    // Placeholder for EULA/PDF download view (implement as needed)
    public function showMaintenanceEula($id)
    {
        // You can render a view or trigger a PDF download here
        // Example: return view('account.accept.maintenance-eula', ...);
        // Or: return $this->downloadEulaPdf($id);
        return response('EULA/PDF download for maintenance acceptance ID: ' . $id);
    }

    /**
     * Return all maintenance acceptances for the current user (for API or reporting)
     */
    public function allMaintenanceAcceptances(Request $request)
    {
        $user = auth()->user();
        $maintenanceAcceptances = \App\Models\MaintenanceAcceptance::forUser($user)
            ->with(['maintenance.asset'])
            ->get();

        $maintenanceData = $maintenanceAcceptances->map(function($m) {
            $signature = $m->signature_filename ? asset('uploads/signatures/' . $m->signature_filename) : null;
            return [
                'id' => $m->id,
                'type' => 'maintenance',
                'maintenance_id' => $m->maintenance ? $m->maintenance->id : null,
                'maintenance_title' => $m->maintenance ? $m->maintenance->title : null,
                'asset_name' => $m->maintenance && $m->maintenance->asset ? $m->maintenance->asset->present()->name() : null,
                'accepted_at' => $m->accepted_at,
                'declined_at' => $m->declined_at,
                'signature' => $signature,
                'note' => $m->note,
            ];
        });

        return response()->json([
            'maintenances' => $maintenanceData,
        ]);
    }

    public static function getPendingForUser($user)
    {
        return \App\Models\MaintenanceAcceptance::forUser($user)
            ->with(['maintenance.asset'])
            ->pending()
            ->get();
    }
} 