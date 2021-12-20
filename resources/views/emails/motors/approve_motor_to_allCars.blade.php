<p>Dear {{ $name }},</p>

<p>A new vehicle motor insurance {{ $data->ref_no }} has been approved by {{ $data->dealer->name }}. You can view the motor insurance <a href='{{ env('APP_URL') }}/motors/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
