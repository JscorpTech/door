<?php

namespace App\Traits;

trait PaymentGatewayTrait
{
    public function getPaymentGatewaySupportedCurrencies($key = null): array
    {
        $paymentGateway = [
            "amazon_pay" => [
                "USD" => "United States Dollar",
                "GBP" => "Pound Sterling",
                "EUR" => "Euro",
                "JPY" => "Japanese Yen",
                "AUD" => "Australian Dollar",
                "NZD" => "New Zealand Dollar",
                "CAD" => "Canadian Dollar"
            ],
            "bkash" => [
                "BDT" => "Bangladeshi Taka"
            ],
            "cashfree" => [
                "INR" => "Indian Rupee"
            ],
            "ccavenue" => [
                "INR" => "Indian Rupee"
            ],
            "esewa" => [
                "NPR" => "Nepalese Rupee"
            ],
            "fatoorah" => [
                "KWD" => "Kuwaiti Dinar",
                "SAR" => "Saudi Riyal"
            ],
            "flutterwave" => [
                "NGN" => "Nigerian Naira",
                "GHS" => "Ghanaian Cedi",
                "KES" => "Kenyan Shilling",
                "ZAR" => "South African Rand",
                "USD" => "United States Dollar",
                "EUR" => "Euro",
                "GBP" => "British Pound Sterling",
                "CAD" => "Canadian Dollar",
                "XAF" => "Central African CFA Franc",
                "CLP" => "Chilean Peso",
                "COP" => "Colombian Peso",
                "EGP" => "Egyptian Pound",
                "GNF" => "Guinean Franc",
                "MWK" => "Malawian Kwacha",
                "MAD" => "Moroccan Dirham",
                "RWF" => "Rwandan Franc",
                "SLL" => "Sierra Leonean Leone",
                "STD" => "São Tomé and Príncipe Dobra",
                "TZS" => "Tanzanian Shilling",
                "UGX" => "Ugandan Shilling",
                "XOF" => "West African CFA Franc BCEAO",
                "ZMW" => "Zambian Kwacha"
            ],
            "foloosi" => [
                "AED" => "United Arab Emirates Dirham"
            ],
            "hubtel" => [
                "GHS" => "Ghanaian Cedi"
            ],
            "hyper_pay" => [
                "AED" => "United Arab Emirates Dirham",
                "SAR" => "Saudi Riyal",
                "EGP" => "Egyptian Pound",
                "BHD" => "Bahraini Dinar",
                "KWD" => "Kuwaiti Dinar",
                "OMR" => "Omani Rial",
                "QAR" => "Qatari Riyal",
                "USD" => "United States Dollar"
            ],
            "instamojo" => [
                "INR" => "Indian Rupee"
            ],
            "iyzi_pay" => [
                "TRY" => "Turkish Lira"
            ],
            "liqpay" => [
                "UAH" => "Ukrainian Hryvnia",
                "USD" => "United States Dollar",
                "EUR" => "Euro"
            ],
            "maxicash" => [
                "PHP" => "Philippine Peso"
            ],
            "mercadopago" => [
                "ARS" => "Argentine Peso",
                "BRL" => "Brazilian Real",
                "CLP" => "Chilean Peso",
                "COP" => "Colombian Peso",
                "MXN" => "Mexican Peso",
                "PEN" => "Peruvian Sol",
                "UYU" => "Uruguayan Peso",
                "USD" => "United States Dollar"
            ],
            "momo" => [
                "VND" => "Vietnamese Dong"
            ],
            "moncash" => [
                "HTG" => "Haitian Gourde"
            ],
            "payfast" => [
                "ZAR" => "South African Rand"
            ],
            "paymob_accept" => [
                "EGP" => "Egyptian Pound",
                "USD" => "US Dollar",
                "EUR" => "Euro",
                "GBP" => "British Pound",
                "SAR" => "Saudi Riyal",
                "AED" => "UAE Dirham",
            ],
            "paypal" => [
                // Africa
                "DZD" => "Algerian Dinar",
                "AOA" => "Angolan Kwanza",
                "XOF" => "West African CFA Franc", // Benin, Burkina Faso, Côte d'Ivoire, Guinea-Bissau, Mali, Niger, Senegal, Togo
                "BWP" => "Botswana Pula",
                "BIF" => "Burundian Franc",
                "XAF" => "Central African CFA Franc", // Cameroon, Chad, Republic of the Congo, Equatorial Guinea, Gabon
                "CVE" => "Cape Verdean Escudo",
                "KMF" => "Comorian Franc",
                "EGP" => "Egyptian Pound",
                "ERN" => "Eritrean Nakfa",
                "ETB" => "Ethiopian Birr",
                "GMD" => "Gambian Dalasi",
                "GNF" => "Guinean Franc",
                "KES" => "Kenyan Shilling",
                "LSL" => "Lesotho Loti",
                "MGA" => "Malagasy Ariary",
                "MWK" => "Malawian Kwacha",
                "MRO" => "Mauritanian Ouguiya",
                "MUR" => "Mauritian Rupee",
                "MAD" => "Moroccan Dirham",
                "MZN" => "Mozambican Metical",
                "NAD" => "Namibian Dollar",
                "NGN" => "Nigerian Naira",
                "RWF" => "Rwandan Franc",
                "SCR" => "Seychellois Rupee",
                "SLL" => "Sierra Leonean Leone",
                "SOS" => "Somali Shilling",
                "ZAR" => "South African Rand",
                "SSP" => "South Sudanese Pound",
                "SZL" => "Swazi Lilangeni",
                "TZS" => "Tanzanian Shilling",
                "UGX" => "Ugandan Shilling",
                "XAF" => "Central African CFA Franc", // Sao Tome and Principe, Gabon Republic
                "ZMW" => "Zambian Kwacha",
                "ZWL" => "Zimbabwean Dollar",

                // Americas
                "ARS" => "Argentine Peso",
                "BOB" => "Bolivian Boliviano",
                "BRL" => "Brazilian Real",
                "CAD" => "Canadian Dollar",
                "CLP" => "Chilean Peso",
                "COP" => "Colombian Peso",
                "CRC" => "Costa Rican Colón",
                "DOP" => "Dominican Peso",
                "USD" => "United States Dollar", // Used in many territories like Bahamas, Bermuda, Cayman Islands
                "GTQ" => "Guatemalan Quetzal",
                "HNL" => "Honduran Lempira",
                "JMD" => "Jamaican Dollar",
                "MXN" => "Mexican Peso",
                "NIO" => "Nicaraguan Córdoba",
                "PAB" => "Panamanian Balboa",
                "PYG" => "Paraguayan Guarani",
                "PEN" => "Peruvian Sol",
                "TTD" => "Trinidad and Tobago Dollar",
                "UYU" => "Uruguayan Peso",
                "VEF" => "Venezuelan Bolívar",

                // Asia Pacific
                "AMD" => "Armenian Dram",
                "AUD" => "Australian Dollar",
                "BHD" => "Bahraini Dinar",
                "BTN" => "Bhutanese Ngultrum",
                "BND" => "Brunei Dollar",
                "KHR" => "Cambodian Riel",
                "CNY" => "Chinese Yuan",
                "NZD" => "New Zealand Dollar", // Cook Islands, Niue, Norfolk Island
                "FJD" => "Fijian Dollar",
                "XPF" => "CFP Franc", // French Polynesia, Wallis and Futuna, New Caledonia
                "HKD" => "Hong Kong Dollar",
                "INR" => "Indian Rupee",
                "IDR" => "Indonesian Rupiah",
                "ILS" => "Israeli New Shekel",
                "JPY" => "Japanese Yen",
                "JOD" => "Jordanian Dinar",
                "KZT" => "Kazakhstani Tenge",
                "KWD" => "Kuwaiti Dinar",
                "KGS" => "Kyrgyzstani Som",
                "LAK" => "Lao Kip",
                "MYR" => "Malaysian Ringgit",
                "MVR" => "Maldivian Rufiyaa",
                "MNT" => "Mongolian Tugrik",
                "OMR" => "Omani Rial",
                "PGK" => "Papua New Guinean Kina",
                "PHP" => "Philippine Peso",
                "QAR" => "Qatari Riyal",
                "WST" => "Samoan Tala",
                "SAR" => "Saudi Riyal",
                "SGD" => "Singapore Dollar",
                "SBD" => "Solomon Islands Dollar",
                "KRW" => "South Korean Won",
                "LKR" => "Sri Lankan Rupee",
                "TWD" => "New Taiwan Dollar",
                "TJS" => "Tajikistani Somoni",
                "THB" => "Thai Baht",
                "TOP" => "Tongan Paʻanga",
                "TMT" => "Turkmenistani Manat",
                "TVD" => "Tuvaluan Dollar",
                "AED" => "United Arab Emirates Dirham",
                "VUV" => "Vanuatu Vatu",
                "VND" => "Vietnamese Dong",
                "YER" => "Yemeni Rial",

                // Europe
                "ALL" => "Albanian Lek",
                "EUR" => "Euro", // Covers many European countries like Austria, Belgium, France, etc.
                "AZN" => "Azerbaijani Manat",
                "BYN" => "Belarusian Ruble",
                "GBP" => "Pound Sterling",
                "HUF" => "Hungarian Forint",
                "PLN" => "Polish Zloty",
                "RON" => "Romanian Leu",
                "RUB" => "Russian Ruble",
                "SEK" => "Swedish Krona",
                "CHF" => "Swiss Franc",
                "UAH" => "Ukrainian Hryvnia"
            ],
            "paystack" => [
                "NGN" => "Nigerian Naira"
            ],
            "paytabs" => [
                "AED" => "United Arab Emirates Dirham",
                "SAR" => "Saudi Riyal",
                "BHD" => "Bahraini Dinar",
                "KWD" => "Kuwaiti Dinar",
                "OMR" => "Omani Rial",
                "QAR" => "Qatari Riyal",
                "EGP" => "Egyptian Pound",
                "USD" => "United States Dollar"
            ],
            "paytm" => [
                "INR" => "Indian Rupee"
            ],
            "phonepe" => [
                "INR" => "Indian Rupee"
            ],
            "pvit" => [
                "NGN" => "Nigerian Naira"
            ],
            "razor_pay" => [
                "INR" => "Indian Rupee"
            ],
            "senang_pay" => [
                "MYR" => "Malaysian Ringgit"
            ],
            "sixcash" => [
                "BDT" => "Bangladeshi Taka"
            ],
            "ssl_commerz" => [
                "BDT" => "Bangladeshi Taka"
            ],
            "stripe" => [
                "USD" => "United States Dollar",
                "AUD" => "Australian Dollar",
                "CAD" => "Canadian Dollar",
                "EUR" => "Euro",
                "GBP" => "Pound Sterling",
                "JPY" => "Japanese Yen",
                "NZD" => "New Zealand Dollar",
                "CHF" => "Swiss Franc",
                "DKK" => "Danish Krone",
                "NOK" => "Norwegian Krone",
                "SEK" => "Swedish Krona",
                "SGD" => "Singapore Dollar",
                "HKD" => "Hong Kong Dollar"
            ],
            "swish" => [
                "SEK" => "Swedish Krona"
            ],
            "tap" => [
                "AED" => "United Arab Emirates Dirham",
                "SAR" => "Saudi Riyal",
                "BHD" => "Bahraini Dinar",
                "KWD" => "Kuwaiti Dinar",
                "OMR" => "Omani Rial",
                "QAR" => "Qatari Riyal"
            ],
            "thawani" => [
                "OMR" => "Omani Rial"
            ],
            "viva_wallet" => [
                "EUR" => "Euro"
            ],
            "worldpay" => [
                "GBP" => "Pound Sterling",
                "USD" => "United States Dollar",
                "EUR" => "Euro",
                "JPY" => "Japanese Yen"
            ],
            "xendit" => [
                "IDR" => "Indonesian Rupiah",
                "PHP" => "Philippine Peso",
                "VND" => "Vietnamese Dong",
                "THB" => "Thai Baht",
                "MYR" => "Malaysian Ringgit",
                "SGD" => "Singapore Dollar"
            ],
            "cinetpay" => [
                "XOF" => "West African CFA franc",
                "XAF" => "Central African CFA franc",
                "GNF" => "Guinean franc",
                "CDF" => "Congolese franc",
                "USD" => "United States Dollar"
            ],
        ];

        if ($key) {
            return $paymentGateway[$key] ?? [];
        }
        return $paymentGateway;
    }

}
