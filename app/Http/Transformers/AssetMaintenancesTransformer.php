<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;

class AssetMaintenancesTransformer
{
    public function transformAssetMaintenances(Collection $assetmaintenances, $total)
    {
        $array = [];
        foreach ($assetmaintenances as $assetmaintenance) {
            $array[] = self::transformAssetMaintenance($assetmaintenance);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformAssetMaintenance(AssetMaintenance $assetmaintenance)
    {
        // Signature logic
        $signature = null;
        $acceptance_note = null;
        if ($assetmaintenance->asset) {
            $acceptance = $assetmaintenance->maintenanceAcceptances()->first();
            if ($acceptance) {
                if ($acceptance->signature_filename) {
                    $signature = asset('uploads/signatures/' . $acceptance->signature_filename);
                }
                $acceptance_note = $acceptance->note;
            }
        }

        $array = [
            'id'            => (int) $assetmaintenance->id,
            'asset' => ($assetmaintenance->asset) ? [
                'id' => (int) $assetmaintenance->asset->id,
                'name'=> ($assetmaintenance->asset->name) ? e($assetmaintenance->asset->name) : null,
                'asset_tag'=> e($assetmaintenance->asset->asset_tag),
                'serial'=> e($assetmaintenance->asset->serial),
                'deleted_at'=> Helper::getFormattedDateObject($assetmaintenance->asset->deleted_at, 'datetime'),
                'created_at' => Helper::getFormattedDateObject($assetmaintenance->asset->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($assetmaintenance->asset->updated_at, 'datetime'),
            ] : null,
            'model' => (($assetmaintenance->asset) && ($assetmaintenance->asset->model)) ? [
                'id' => (int) $assetmaintenance->asset->model->id,
                'name'=> ($assetmaintenance->asset->model->name) ? e($assetmaintenance->asset->model->name).' '.e($assetmaintenance->asset->model->model_number) : null,
            ] : null,
            'status_label' => (($assetmaintenance->asset) && ($assetmaintenance->asset->assetstatus)) ? [
                'id' => (int) $assetmaintenance->asset->assetstatus->id,
                'name'=> e($assetmaintenance->asset->assetstatus->name),
                'status_type'=> e($assetmaintenance->asset->assetstatus->getStatuslabelType()),
                'status_meta' => e($assetmaintenance->asset->present()->statusMeta),
            ] : null,
            'company' => (($assetmaintenance->asset) && ($assetmaintenance->asset->company)) ? [
                'id' => (int) $assetmaintenance->asset->company->id,
                'name'=> ($assetmaintenance->asset->company->name) ? e($assetmaintenance->asset->company->name) : null,

            ] : null,
            'title'         => ($assetmaintenance->title) ? e($assetmaintenance->title) : null,
            'location' => (($assetmaintenance->asset) && ($assetmaintenance->asset->location)) ? [
                'id' => (int) $assetmaintenance->asset->location->id,
                'name'=> e($assetmaintenance->asset->location->name),

            ] : null,
            'rtd_location' => (($assetmaintenance->asset) && ($assetmaintenance->asset->defaultLoc)) ? [
                'id' => (int) $assetmaintenance->asset->defaultLoc->id,
                'name'=> e($assetmaintenance->asset->defaultLoc->name),
            ] : null,
            'notes'         => ($assetmaintenance->notes) ? Helper::parseEscapedMarkedownInline($assetmaintenance->notes) : null,
            'repair_method' => ($assetmaintenance->repair_method) ? Helper::parseEscapedMarkedownInline($assetmaintenance->repair_method) : null,
            'risk_level' => ($assetmaintenance->risk_level) ? e($assetmaintenance->risk_level) : null,
            'maintenance_status' => self::getMaintenanceStatus($assetmaintenance),
            'supplier'      => ($assetmaintenance->supplier) ?  [
                    'id' => $assetmaintenance->supplier->id,
                    'name'=> e($assetmaintenance->supplier->name)
                    ] : null,
            'cost'          => Helper::formatCurrencyOutput($assetmaintenance->cost),
            'asset_maintenance_type'          => e($assetmaintenance->asset_maintenance_type),
            'start_date'         => Helper::getFormattedDateObject($assetmaintenance->start_date, 'date'),
            'asset_maintenance_time'          => $assetmaintenance->asset_maintenance_time,
            'completion_date'     => Helper::getFormattedDateObject($assetmaintenance->completion_date, 'date'),
            'user_id'    => ($assetmaintenance->adminuser) ? [
                'id' => $assetmaintenance->adminuser->id,
                'name'=> e($assetmaintenance->adminuser->present()->fullName())
            ] : null, // legacy to not change the shape of the API
            'created_by' => ($assetmaintenance->adminuser) ? [
                'id' => (int) $assetmaintenance->adminuser->id,
                'name'=> e($assetmaintenance->adminuser->present()->fullName()),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($assetmaintenance->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($assetmaintenance->updated_at, 'datetime'),
            'is_warranty'=> $assetmaintenance->is_warranty,
            'signature' => $signature,
            'acceptance_note' => $acceptance_note,

        ];

        $permissions_array['available_actions'] = [
            'update' => (Gate::allows('update', Asset::class) && ((($assetmaintenance->asset) && $assetmaintenance->asset->deleted_at==''))) ? true : false,
            'delete' => Gate::allows('delete', Asset::class),
        ];

        $array += $permissions_array;

        return $array;
    }

    /**
     * Get the maintenance status based on completion and acceptance
     *
     * @param AssetMaintenance $assetmaintenance
     * @return string
     */
    public static function getMaintenanceStatus(AssetMaintenance $assetmaintenance)
    {
        // Check if maintenance is declined (highest priority)
        $acceptance = $assetmaintenance->maintenanceAcceptances()->first();
        if ($acceptance && $acceptance->declined_at) {
            return 'declined';
        }

        // Check if maintenance is completed (only if accepted and past completion date)
        if ($assetmaintenance->completion_date && now()->isAfter($assetmaintenance->completion_date)) {
            // If accepted and past completion date, mark as completed
            if ($acceptance && $acceptance->accepted_at) {
                return 'completed';
            }
            // If not accepted and not declined and past completion date, keep as pending
            if (!$acceptance || $acceptance->isPending()) {
                return 'pending';
            }
        }

        // Check if maintenance is accepted
        if ($acceptance && $acceptance->accepted_at) {
            return 'under_maintenance';
        }

        // Check if maintenance is pending acceptance
        if ($acceptance && $acceptance->isPending()) {
            return 'pending';
        }

        // Default: in progress
        return 'in_progress';
    }
}
