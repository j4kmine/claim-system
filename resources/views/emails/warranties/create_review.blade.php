<p>Dear {{ $name }},</p>

<p>A new vehicle warranty {{ $data->ref_no }} has been submitted by {{ $data->dealer->name }}. Please review it <a href='{{ env('APP_URL') }}/warranties/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
