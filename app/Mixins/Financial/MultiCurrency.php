<?php

namespace App\Mixins\Financial;

use App\Models\Currency;
use Illuminate\Support\Collection;

class MultiCurrency
{

    public function getCurrencies(): Collection
    {
        $defaultCurrency = $this->getDefaultCurrency();
        $currencies = Currency::query()->orderBy('order', 'asc')->get();

        if ($currencies->isNotEmpty()) {
            $currencies->prepend($defaultCurrency);

            return $currencies;
        }

        return collect();
    }

    public function getAllCurrencyOptions(): Collection
    {
        $defaultCurrency = $this->getDefaultCurrency();
        $configuredCurrencies = Currency::query()->orderBy('order', 'asc')->get()->keyBy('currency');

        return collect(currenciesLists())->map(function ($currencyName, $currencyCode) use ($configuredCurrencies, $defaultCurrency) {
            if ($configuredCurrencies->has($currencyCode)) {
                return $configuredCurrencies->get($currencyCode);
            }

            $currency = new Currency();
            $currency->currency = $currencyCode;
            $currency->currency_position = $defaultCurrency->currency_position;
            $currency->currency_separator = $defaultCurrency->currency_separator;
            $currency->currency_decimal = $defaultCurrency->currency_decimal;
            $currency->exchange_rate = null;

            return $currency;
        })->values();
    }

    public function getSpecificCurrency($currencySign)
    {
        $specificCurrency = null;
        $currencies = $this->getAllCurrencyOptions();

        foreach ($currencies as $currency) {
            if (strtoupper($currency->currency) == strtoupper($currencySign)) {
                $specificCurrency = $currency;
            }
        }

        return $specificCurrency;
    }

    public function getDefaultCurrency()
    {
        $settings = getFinancialCurrencySettings();

        $defaultCurrency = new Currency();

        $defaultCurrency->currency = $settings['currency'] ?? 'USD';
        $defaultCurrency->currency_position = $settings['currency_position'] ?? 'left';
        $defaultCurrency->currency_separator = $settings['currency_separator'] ?? 'dot';
        $defaultCurrency->currency_decimal = $settings['currency_decimal'] ?? 0;
        $defaultCurrency->exchange_rate = null;

        return $defaultCurrency;
    }
}
