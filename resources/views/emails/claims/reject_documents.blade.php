<p>Dear {{ $name }},</p>

<p>Please revise the claim completion documents for claim {{ $data->ref_no }} <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
