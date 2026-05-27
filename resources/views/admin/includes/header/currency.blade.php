@php
    $userCurrency = currency();
    $currencyItems = (new \App\Mixins\Financial\MultiCurrency())->getAllCurrencyOptions();

    if ($currencyItems->isEmpty()) {
        $fallbackCurrency = new \stdClass();
        $fallbackCurrency->currency = $userCurrency;
        $currencyItems = collect([$fallbackCurrency]);
    }

    if (!$currencyItems->pluck('currency')->map(function ($currency) { return strtoupper($currency); })->contains(strtoupper($userCurrency))) {
        $currencyItems->prepend((new \App\Mixins\Financial\MultiCurrency())->getDefaultCurrency());
    }
@endphp

    <div class="js-currency-select language-select position-relative cursor-pointer {{ !empty($currencyClassName) ? $currencyClassName : '' }}">
        <form action="/set-currency" method="post" class="m-0">
            {{ csrf_field() }}
            <input type="hidden" name="currency" value="{{ $userCurrency }}">

            @if(!empty($previousUrl))
                <input type="hidden" name="previous_url" value="{{ $previousUrl }}">
            @endif


            @foreach($currencyItems as $currencyItem)
                @if(strtoupper($userCurrency) == strtoupper($currencyItem->currency))
                    <div class="size-32 d-flex-center rounded-8 p-4 text-white font-12 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.2);">{{ $currencyItem->currency }}</div>
                @endif
            @endforeach
        </form>

        <div class="language-dropdown py-8 ">
            <div class="py-8 px-16 font-12 text-gray-500">{{ trans('update.select_a_currency') }}</div>

            @foreach($currencyItems as $currencyItem)
                @php
                    $currencyName = currenciesLists()[$currencyItem->currency] ?? $currencyItem->currency;
                @endphp

                <div class="js-currency-dropdown-item language-dropdown__item cursor-pointer {{ (strtoupper($userCurrency) == strtoupper($currencyItem->currency)) ? 'active' : '' }}" data-value="{{ $currencyItem->currency }}" data-title="{{ $currencyItem->currency }}">
                    <div class=" d-flex align-items-center justify-content-between w-100 px-16 py-8 text-dark">
                        <span class="ml-8 font-14">{{ $currencyName }}</span>

                        <div class="language-dropdown__item-sign-box position-relative d-flex-center rounded-8">
                            {{ currencySign($currencyItem->currency) }}
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
