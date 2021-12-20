<p>Dear {{ $name }},</p>

<p>A new vehicle warranty claim {{ $data->ref_no }} has been submitted by {{ $data->workshop->name }}. Please review it <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
