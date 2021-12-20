<p>Dear {{ $name }},</p>

<p>TThere are amendments required for claim {{ $data->ref_no }} before submission to the Insurer. Please see the recommendation given <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
