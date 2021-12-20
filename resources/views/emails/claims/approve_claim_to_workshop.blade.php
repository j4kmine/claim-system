<p>Dear {{ $name }},</p>

<p>Claim {{ $data->ref_no }} has been approved. Please see the claim <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>You may proceed with the repairs quoted. Please attach the documents required for claim completion (repair tax invoice and satisfactory note) after the repairs have been completed.</p>

<p>Thank you.</p>
