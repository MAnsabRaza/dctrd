<tr>

    <td class="text-left">

        <div class="d-flex align-items-center">

            @if(!empty($favorite->booking->thumbnail))

                <img src="{{ $favorite->booking->thumbnail }}"
                     class="rounded-12 img-cover"
                     width="48"
                     height="48">

            @endif

            <div class="ml-8">

                <a href="#"
                   class="font-weight-500 text-dark">

                    {{ optional($favorite->booking)->title }}

                </a>

            </div>

        </div>

    </td>

    <td class="text-center">

        {{ dateTimeFormat($favorite->created_at,'j M Y | H:i') }}

    </td>

    <td class="text-right">

        <div class="actions-dropdown position-relative d-flex justify-content-end align-items-center">

            <button type="button"
                    class="d-flex-center size-36 bg-gray border-gray-200 rounded-10">

                <x-iconsax-lin-more
                    class="icons text-gray-500"
                    width="18"/>

            </button>

        </div>

    </td>

</tr>