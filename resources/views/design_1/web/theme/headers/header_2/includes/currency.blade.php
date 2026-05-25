@php
    $userCurrency = currency();
    $currencyItems = (new \App\Mixins\Financial\MultiCurrency())->getAllCurrencyOptions();

    if ($currencyItems->isEmpty()) {
        $fallbackCurrency = new \stdClass();
        $fallbackCurrency->currency = $userCurrency;
        $currencyItems = collect([$fallbackCurrency]);
    }
@endphp

<div class="js-currency-select theme-header-2__dropdown position-relative">
    <form action="/set-currency" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="currency" value="{{ $userCurrency }}">

        <div class="d-flex align-items-center gap-8 cursor-pointer">
            <div class="size-32 d-flex-center bg-gray-100 rounded-8">
                <span class="font-14 text-gray-500">{{ currencySign($userCurrency) }}</span>
            </div>
        </div>
    </form>

    <div class="header-2-dropdown-menu py-8">
        <div class="py-8 px-16 font-12 text-gray-500">{{ trans('update.select_a_currency') }}</div>

        @foreach($currencyItems as $currencyItem)
            @php
                $currencyName = currenciesLists()[$currencyItem->currency] ?? $currencyItem->currency;
            @endphp

            <div class="js-currency-dropdown-item header-2-dropdown-menu__item cursor-pointer {{ ($userCurrency == $currencyItem->currency) ? 'active' : '' }}" data-value="{{ $currencyItem->currency }}" data-title="{{ $currencyItem->currency }}">
                <div class="d-flex align-items-center justify-content-between w-100 px-16 py-8 bg-transparent">
                    <span class="text-gray-500 text-dark">{{ $currencyName }}</span>

                    <div class="header-2-dropdown-menu__item-sign-box position-relative d-flex-center rounded-8">
                        {{ currencySign($currencyItem->currency) }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
