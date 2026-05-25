@if(!empty($currencies) and count($currencies))
    @php
        $userCurrency = currency();
    @endphp

    <div class="js-currency-select custom-dropdown position-relative mr-10">
        <form action="/set-currency" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="currency" value="{{ $userCurrency }}">
            @if(!empty($previousUrl))
                <input type="hidden" name="previous_url" value="{{ $previousUrl }}">
            @endif

            <div class="custom-dropdown-toggle top-nav-icon-dropdown d-flex align-items-center justify-content-center cursor-pointer" title="{{ trans('update.currency') }}">
                <span class="js-lang-title d-none">{{ $userCurrency }}</span>
                <span class="font-16 font-weight-bold">{{ currencySign($userCurrency) }}</span>
            </div>
        </form>

        <div class="custom-dropdown-body py-10">

            @foreach($currencies as $currencyItem)
                <div class="js-currency-dropdown-item custom-dropdown-body__item cursor-pointer {{ ($userCurrency == $currencyItem->currency) ? 'active' : '' }}" data-value="{{ $currencyItem->currency }}" data-title="{{ $currencyItem->currency }} ({{ currencySign($currencyItem->currency) }})">
                    <div class=" d-flex align-items-center w-100 px-15 py-5 text-gray bg-transparent">
                        <div class="size-32 position-relative d-flex-center bg-gray100 rounded-sm">
                            {{ currencySign($currencyItem->currency) }}
                        </div>

                        <span class="ml-5 font-14">{{ currenciesLists($currencyItem->currency) }}</span>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
@endif
