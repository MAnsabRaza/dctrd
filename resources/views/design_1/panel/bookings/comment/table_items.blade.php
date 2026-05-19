<tr>

    <td class="text-left">

        <a href="#"
           class="font-weight-500 text-dark">

            {{ optional($comment->booking)->title }}

        </a>

    </td>

    <td class="text-center">

        <button type="button"
                class="btn btn-sm bg-gray-200">

            View

        </button>

    </td>

    <td class="text-center">

        @if($comment->is_active)

            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-success-30 font-12 text-success">
                Active
            </span>

        @else

            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-danger-30 font-12 text-danger">
                Inactive
            </span>

        @endif

    </td>

    <td class="text-center">

        {{ dateTimeFormat($comment->created_at,'j M Y | H:i') }}

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