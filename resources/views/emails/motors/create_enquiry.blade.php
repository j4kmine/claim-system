<p>Dear {{ $name }},</p>

<p>A new vehicle motor insurance {{ $data->ref_no }} has been submitted by {{ $data->dealer->name }}. Please update the price <a href='{{ env('APP_URL') }}/motors/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
