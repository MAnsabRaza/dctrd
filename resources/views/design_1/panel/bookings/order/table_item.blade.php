<tr>

    <td class="text-left">

        <span class="font-weight-bold">
            {{ $order->order_number }}
        </span>

    </td>

    <td class="text-left">

        @foreach($order->items as $item)

            <div class="mb-10">

                @if($item->booking)

                    <div class="font-weight-bold">
                        {{ $item->booking->title }}
                    </div>

                @endif

                @if($item->bundle)

                    <div class="text-gray-500 font-12">
                        Bundle:
                        {{ $item->bundle->title }}
                    </div>

                @endif

            </div>

        @endforeach

    </td>

    <td class="text-center">

        {{ handlePrice($order->total) }}

    </td>

    <td class="text-center">

        @if($order->status == 'pending')

            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-warning-30 font-12 text-warning">
                Pending
            </span>

        @elseif($order->status == 'confirmed')

            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-primary-30 font-12 text-primary">
                Confirmed
            </span>

        @elseif($order->status == 'completed')

            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-success-30 font-12 text-success">
                Completed
            </span>

        @else

            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-danger-30 font-12 text-danger">
                Cancelled
            </span>

        @endif

    </td>

    <td class="text-center">

        @if($order->payment_status == 'paid')

            <span class="badge badge-success">
                Paid
            </span>

        @else

            <span class="badge badge-warning">
                {{ ucfirst($order->payment_status) }}
            </span>

        @endif

    </td>

    <td class="text-center">

        {{ dateTimeFormat($order->created_at, 'j M Y H:i') }}

    </td>

</tr>