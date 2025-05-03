<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class MaintenanceController extends Controller
{
    public function maintenanceModeOn(Request $request)
    {
        $validate = Validator::make(request()->all(), [
            'finished_at'     =>      ['required', 'after_or_equal:now']
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message'       => 'Ops! Something went wrong. Please try again.',
                'errors'        => $validate->errors()
            ], 400);
        }

        $maintenanceLogs = MaintenanceLog::create([
            'maintenance_type'       => $request->maintenance_type,
            'finished_at'            => $request->finished_at
        ]);

        if ($maintenanceLogs) {
            Artisan::call('down');
        }

        return response()->json([
            'message'       => 'Maintenance mode on',
        ], 204);
    }

    public function maintenanceModeOff()
    {
        Artisan::call('up');

        $maintenanceLogs = MaintenanceLog::where('is_maintenance', true)
            ->where('is_finished', false)
            ->first();

        $maintenanceLogs->update([
            'is_finished'       => true,
            'is_maintenance'    => false,
        ]);

        return response()->json([
            'message'       => 'Maintenance mode off',
        ], 204);
    }

    public function index()
    {
        $maintenanceLogs = MaintenanceLog::where('is_maintenance', true)
            ->where('is_finished', false)
            ->first();

        return response()->json([
            'maintenance_logs'       => $maintenanceLogs,
        ], 200);
    }
}
