<?php

namespace App\Http\Controllers\Mobile\Device;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CustomerDevice;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DeviceController extends Controller
{
    use ApiResponser;

    public function store(Request $request)
    {
        $attr = $request->validate([
            'device_id' => 'required'
        ]);

        $customer = Auth::user();

        $customer->devices()->save(new CustomerDevice([
            'device_id' => $attr['device_id']
        ]));

        return $this->success($customer->devices, Response::HTTP_CREATED);
    }

    public function destroy(Request $request)
    {
        $attr = $request->validate([
            'device_id' => 'required|exists:customer_devices,device_id'
        ]);

        $customer = Auth::user();

        CustomerDevice::where('customer_id', $customer->id)
            ->where('device_id', $attr['device_id'])->delete();

        return $this->success($customer->fresh()->devices);
    }
}
