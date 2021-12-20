<p>Dear {{ $name }},</p>

<p>A new vehicle motor insurance {{ $data->ref_no }} has been submitted by {{ $data->dealer->name }} and approved by AllCars. You can view the motor insurance <a href='{{ env('APP_URL') }}/motors/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
