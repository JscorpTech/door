<?php

namespace App\Utils;


use App\Utils\Helpers;
use App\Models\Currency;

class Convert
{
    public static function usd($amount): float|int|null
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $default = Currency::find(getWebConfig(name: 'system_default_currency'));
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $default['exchange_rate'] / $usd;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function usdPaymentModule($amount, $currency): float|int|null
    {
        $currencyModel = getWebConfig(name: 'currency_model');
        if ($currencyModel == 'multi_currency') {
            if ($currency == 'USD') {
                return floatval($amount);
            }

            $default = Currency::find(getWebConfig(name: 'system_default_currency'));
            $currentCurrency = Currency::where(['code' => $currency])->first()->exchange_rate ?? 1;

            if ($default['code'] == 'USD') {
                return floatval($amount) / $currentCurrency;
            }
            $rate = $default['exchange_rate'] >= $currentCurrency ? ($default['exchange_rate'] / $currentCurrency) : ($currentCurrency / $default['exchange_rate']);
            $defaultAmount = $default['exchange_rate'] <= $currentCurrency ? floatval($amount) / floatval($rate) : floatval($amount) * floatval($rate);
            $usdRate = Currency::where(['code' => 'USD'])->first()->exchange_rate;

            $value = floatval($default['exchange_rate']) >= floatval($usdRate) ? floatval($defaultAmount) * floatval($usdRate) : floatval($defaultAmount) / floatval($usdRate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function default($amount)
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $default = Currency::find(getWebConfig(name: 'system_default_currency'));
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $default['exchange_rate'] / $usd;
            $value = floatval($amount) * floatval($rate);
        } else {
            $value = floatval($amount);
        }
        return round($value, 2);
    }

    public static function bdtTousd($amount)
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $bdt = Currency::where(['code' => 'BDT'])->first()->exchange_rate ?? 1;
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $bdt / $usd;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function usdTobdt($amount)
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $bdt = Currency::where(['code' => 'BDT'])->first()->exchange_rate ?? 1;
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $usd / $bdt;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function usdTomyr($amount)
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $myr = Currency::where(['code' => 'MYR'])->first()->exchange_rate ?? 1;
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $usd / $myr;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function usdTozar($amount)
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $zar = Currency::where(['code' => 'ZAR'])->first()->exchange_rate ?? 1;
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $usd / $zar;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function usdToinr($amount)
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $inr = Currency::where(['code' => 'INR'])->first()->exchange_rate ?? 1;
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $usd / $inr;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function usdToegp($amount)
    {
        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $egp = Currency::where(['code' => 'EGP'])->first()->exchange_rate ?? 1;
            $usd = Currency::where('code', 'USD')->first()->exchange_rate ?? 1;
            $rate = $usd / $egp;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }
}
