<p>Dear {{ $name }},</p>

<p>A new vehicle warranty {{ $data->ref_no }} price has been proposed by AllCars. Please review it <a href='{{ env('APP_URL') }}/warranties/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>