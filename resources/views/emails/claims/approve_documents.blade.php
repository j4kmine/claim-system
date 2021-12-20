<p>Dear {{ $name }},</p>

<p>Repairs for claim {{ $data->ref_no }} have been completed by {{ $data->workshop->name }}. Please see the claim completion documents <a href='{{ env('APP_URL') }}/claims/details/{{ $data->id }}'>here</a>.</p>

<p>Thank you.</p>
