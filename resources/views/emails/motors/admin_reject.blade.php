<p>Dear {{ $name }},</p>

<p>Your motor insurance {{ $data->ref_no }} has been rejected by AllCars. Please see the rejected motor insurance <a href='{{ env('APP_URL') }}/motors/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
