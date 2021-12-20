<p>Dear {{ $name }},</p>

<p>A new vehicle motor insurance {{ $data->ref_no }} price has been proposed by AllCars. Please review it <a href='{{ env('APP_URL') }}/motors/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>