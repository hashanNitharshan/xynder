<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;

class ConfigController extends Controller
{
    public function current()
    {
        $config = SystemConfig::current();

        return response()->json([
            'success' => true,
            'config' => [
                'usd_rate' => (float) $config->usd_rate,
                'inr_rate' => (float) $config->inr_rate,
                'xynder_fee' => (float) $config->xynder_fee,
                'network_fee' => (float) $config->network_fee,
                'effective_date' => optional($config->effective_date)->format('Y-m-d'),
            ],
        ]);
    }
}