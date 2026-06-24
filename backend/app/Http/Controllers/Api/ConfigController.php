<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;

class ConfigController extends Controller
{
    public function current()
    {
        return response()->json([
            'success' => true,
            'config' => SystemConfig::current(),
        ]);
    }
}