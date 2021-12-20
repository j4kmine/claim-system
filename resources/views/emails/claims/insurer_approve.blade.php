<p>Dear {{ $name }},</p>

<p>Claim {{ $data->ref_no }} has been approved by {{ $data->insurer->name }}. Please see the claim <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
