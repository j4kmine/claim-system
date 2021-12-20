<p>Dear {{ $name }},</p>

<p>A new vehicle warranty {{ $data->ref_no }} has been approved by {{ $data->dealer->name }}. You can view the warranty <a href='{{ env('APP_URL') }}/warranties/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
