<?php

namespace App\Http\Controllers\Mobile\Setting;

use Throwable;
use Mockery\Undefined;
use App\Models\Setting;
use App\Models\Customer;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        // Get Customer Settings
        $customer = Auth::user();
        $customerSettings = $customer->settings;

        // Get All Customer and global Settings
        $allSettings = Setting::customerSetting()
            ->globalSetting()
            ->get();

        // Parse customer setting
        foreach ($allSettings as $key => $setting) {
            if (in_array($setting->id, $customerSettings->pluck('id')->toArray())) {
                $s = array_merge($customerSettings->where('id', $setting->id)->toArray(), []);
                $allSettings[$key]['value'] = $s[0]['pivot']['value'];
            } else {
                $allSettings[$key]['value'] = null;
            }
        }

        return $this->success($allSettings);
    }

    public function update(Request $request)
    {
        // TODO: Customer ID will be deleted if authed is done
        $attr = $request->validate([
            'key' => 'required|exists:settings,key',
            'value' => 'required'
        ]);

        try {
            $customer = Auth::user();

            $setting = Setting::where('key', $attr['key'])->first();

            /**
             * If the customer has already the setting,
             * then update the value
             * if hasn't
             * then attach new value
             */
            if ($customer->settings->contains($setting->id)) {
                $customer->settings()->updateExistingPivot($setting, [
                    'value' => $attr['value']
                ]);
            } else {
                $customer->settings()->attach($setting, [
                    'value' => $attr['value']
                ]);
            }

            return $this->success($customer->fresh()->settings);
        } catch (Throwable $e) {
            Log::error([
                'SettingController',
                $request->all(),
                $e->getMessage()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
