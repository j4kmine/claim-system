<p>Dear {{ $name }},</p>

<p>The payment for claim {{ $data->ref_no }} has been acknowledged by {{ $data->workshop->name }}. Please see the claim <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
