<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServicingSlot;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class CompanySlotController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {

        // $length = $request->input('length');
        // $orderBy = $request->input('column'); //Index
        // $orderByDir = $request->input('dir', 'asc');
        $active = $request->input('active');
        $query = ServicingSlot::where('workshop_id', Auth::user()->company_id);

        if ($active == 1) {
            $query = $query->where('status', 'active');
        }

        // ->where('status', 'active')
        // $query = $query->paginate($length);
        // return new DataTableCollectionResource($query);
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        json_encode($request->query());
        $attr = $request->validate([
            '*.day' => 'required|in:' . implode(',', ServicingSlot::DAYS),
            '*.time_start' => 'required|date_format:H:i',
            '*.time_end' => 'required|date_format:H:i',
            '*.interval' => 'required|integer|in:' . implode(',', ServicingSlot::INTERVALS),
            '*.slots_per_interval' => 'required|integer',
            '*.status' => 'nullable|in:' . implode(',', ServicingSlot::STATUSES)
        ]);

        $company = Auth::user()->company;

        foreach ($attr as $item) {
            // save the data first and get id
            $data = ServicingSlot::updateOrCreate([
                'workshop_id' => $company->id,
                'day' => $item['day']
            ], [
                'time_start' => $item['time_start'],
                'time_end' => $item['time_end'],
                'interval' => $item['interval'],
                'slots_per_interval' => $item['slots_per_interval'],
                'status' => isset($item['status']) ? $item['status'] : 'active'
            ]);

            // check data if there is double day
            $check = ServicingSlot::where('day', $item['day'])->get();
            if (count($check) > 1) {

                // update data at services to be the correct servicing slot id
                Service::whereNotIn('servicing_slot_id', [$data->id])->update(['servicing_slot_id' => $data->id]);

                // delete the rest of double day
                ServicingSlot::whereNotIn('id', [$data->id])->where('day', $item['day'])->delete();
            }
        }

        return $this->success(
            $company->servicing_slots()
                ->where('status', 'active')->get()
        );
    }

    public function destroy(ServicingSlot $slot)
    {
        $slot->update([
            'status' => 'inactive'
        ]);

        return $this->success(null);
    }
}
