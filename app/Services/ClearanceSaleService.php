<?php

namespace App\Services;

use Carbon\Carbon;

class ClearanceSaleService
{
    public function getConfigData(object|array $request, string $setupBy, int|null $vendorId = null, int|null $shopId = null): array|bool
    {
        $dates = explode(' - ', $request['clearance_sale_duration']);
        $durationStartDate = (isset($dates[0]) && $dates[0] != null) ? Carbon::createFromFormat('m/d/Y h:i:s A', $dates[0]) : null;
        $durationEndDate = (isset($dates[1]) && $dates[1] != null) ? Carbon::createFromFormat('m/d/Y h:i:s A', $dates[1]) : null;
        $offerActiveRangeStart = null;
        $offerActiveRangeEnd = null;
        if (!empty($request['offer_active_range'])) {
            $time = explode(' - ', $request['offer_active_range']);
            $offerActiveRangeStart = date("H:i:s", strtotime($time[0]));
            $offerActiveRangeEnd = date("H:i:s", strtotime($time[1]));
        }

        $data = [
            'setup_by' => $setupBy,
            'user_id' => $vendorId,
            'shop_id' => $shopId,
            'discount_type' => $request['discount_type'] ?? null,
            'discount_amount' => $request['discount_amount'],
            'offer_active_time' => $request['offer_active_time'] ?? 'always',
            'offer_active_range_start' => $request['offer_active_time'] == 'specific_time' ? $offerActiveRangeStart : null,
            'offer_active_range_end' => $request['offer_active_time'] == 'specific_time' ? $offerActiveRangeEnd : null,
            'duration_start_date' => $durationStartDate,
            'duration_end_date' => $durationEndDate,
        ];
        if($setupBy == 'admin'){
            $data['show_in_homepage'] = $request['show_in_homepage'] ?? 0;
        }
        return $data;
    }
}
