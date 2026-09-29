<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
{
    private function adminOnly(): void
    {
        if (! Auth::user() || Auth::user()->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminOnly();

        $config = SystemConfig::current();
        $configs = SystemConfig::latest()->paginate(20);

        return view('admin.config.index', compact('config', 'configs'));
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $data = $request->validate([
            'usd_rate'       => 'required|numeric|min:0',
            'buy_inr_rate'   => 'required|numeric|gt:0',
            'sell_inr_rate'  => 'required|numeric|gt:0',
            'xynder_fee'     => 'required|numeric|min:0',
            'network_fee'    => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ]);

        /*
         * The old inr_rate column is still filled so older app
         * versions and old screens keep working. The sell rate is
         * used because the Flutter app opens on Sell USD first.
         */
        $data['inr_rate'] = $data['sell_inr_rate'];

        SystemConfig::create($data);

        return back()->with('success', 'Config saved successfully.');
    }
}
