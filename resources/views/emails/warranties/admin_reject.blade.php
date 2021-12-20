<p>Dear {{ $name }},</p>

<p>Your warranty {{ $data->ref_no }} has been rejected by AllCars. Please see the rejected warranty <a href='{{ env('APP_URL') }}/warranties/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
