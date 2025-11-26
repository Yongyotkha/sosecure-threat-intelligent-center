<?php 

namespace App\Services;

use Modules\WebDefacement\Entities\WebdefacmentDataCheck;

class WebDefacementService
{
    public function getDiffData($settingId): array
    {
        $row = WebdefacmentDataCheck::where('webdefacment_setting_id', $settingId)
            ->latest()
            ->first();

        if (!$row) {
            return ['success' => false, 'message' => 'Diff not found'];
        }

        return [
            'success'       => true,
            'merkle_old'    => (string) ($row->merkle_old ?? ''),
            'merkle_new'    => (string) ($row->merkle_new ?? ''),
            'simhash_bits'  => (string) ($row->simhash_bits ?? ''),
            'section_diffs' => $row->section_diffs ?? [],
            'assets_add'    => $row->assets_add ?? [],
            'assets_del'    => $row->assets_del ?? [],
            'outbound_new'  => $row->outbound_new_not_whitelisted ?? [],
        ];
    }
}
