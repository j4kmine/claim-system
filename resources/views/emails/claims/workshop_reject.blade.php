<p>Dear {{ $name }},</p>

<p>Claim {{ $data->ref_no }} has been cancelled by {{ $data->workshop->name }}. Please see the cancelled claim <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
