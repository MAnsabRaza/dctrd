<div class="bg-white p-16 rounded-24">
    <h3 class="font-16">{{ trans('update.product_specifications') }}</h3>

    <div class="">
        @if(!empty($booking->specifications) and count($booking->specifications))
            @foreach($booking->specifications as $spec)
                <div class="product-show__specification-item d-flex align-items-start mt-16 {{ !$loop->first ? 'pt-16 border-top-gray-200' : '' }}">
                    <div class="specification-item-name font-weight-bold text-gray-500">
                        {{ optional($spec->specification)->title }}
                    </div>
                    <div class="specification-item-value flex-grow-1 px-16">
                        @if($spec->type == 'textarea')
                            {!! nl2br($spec->value) !!}
                        @elseif(!empty($spec->selectedMultiValues))
                            @foreach($spec->selectedMultiValues as $specValue)
                                @if(!empty($specValue->multiValue))
                                    <span class="d-block">{{ $specValue->multiValue->title }}</span>
                                @endif
                            @endforeach
                        @else
                            {{ $spec->value }}
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>