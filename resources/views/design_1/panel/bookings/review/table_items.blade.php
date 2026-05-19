<tr>

    <td class="text-left">

        {{ optional($review->booking)->title }}

    </td>

    <td class="text-center">

        {{ $review->rating }}/5

    </td>

    <td class="text-center">

        {{ Str::limit($review->comment, 60) }}

    </td>

    <td class="text-center">

     @if($review->status == 'active')

    <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-success-30 font-12 text-success">
        Active
    </span>

@elseif($review->status == 'pending')

    <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-warning-30 font-12 text-warning">
        Pending
    </span>

@else

    <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-danger-30 font-12 text-danger">
        Rejected
    </span>

@endif

    </td>

    <td class="text-center">

        {{ dateTimeFormat($review->created_at,'j M Y | H:i') }}

    </td>

</tr>