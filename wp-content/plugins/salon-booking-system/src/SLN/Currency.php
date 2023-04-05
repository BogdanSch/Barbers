<?php

class SLN_Currency
{
    private static $currencies = array(
        'AUD' => array('name' => 'Australian Dollar', 'symbol' => 'A$', 'ASCII' => 'A&#36;'),
        'AOA' => array('name' => 'Angolan kwanza', 'symbol' => 'Kz', 'ASCII' => ''),
        'AED' => array('name' => 'United Arab Emirates dirham', 'symbol' => 'د.إ', 'ASCII' => ''),
        'AMD' => array('name' => 'Armenian Dram', 'symbol' => '֏', 'ASCII' => ''),
        'ARS' => array('name' => 'Argentine Peso', 'symbol' => '$', 'ASCII' => 'A&#36;'),
        'AZN' => array('name' => 'Azerbaijan Manat', 'symbol' => 'man', 'ASCII' => ''),
        'BRL' => array('name' => 'Brazilian Real', 'symbol' => 'R$', 'ASCII' => '&#x0052;&#x0024;'),
        'BGN' => array('name' => 'Bulgarian Lev', 'symbol' => 'лв', 'ASCII' => ''),
        'BAM' => array('name' => 'Bosnia and Herzegovina convertible mark', 'symbol' => 'KM', 'ASCII' => ''),
        'BYN' => array('name' => 'Belarusian ruble', 'symbol' => 'Br', 'ASCII' => ''),
        'BD' => array('name' => 'Bahraini dinar', 'symbol' => '.د.ب', 'ASCII' => ''),
        'BOB' => array('name' => 'Bolivian Boliviano', 'symbol' => 'Bs', 'ASCII' => ''),
        'BZD' => array('name' => 'Belize dollar', 'symbol' => 'BZ$', 'ASCII' => ''),
        'CAD' => array('name' => 'Canadian Dollar', 'symbol' => '$', 'ASCII' => '&#36;'),
        'KYD' => array('name' => 'Cayman Islands Dollar', 'symbol' => 'CI$', 'ASCII' => ''),
        'CLP' => array('name' => 'Chilean peso', 'symbol' => '$', 'ASCII' => '&#36;'),
        'CRC' => array('name' => 'Costa Rican colón', 'symbol' => '₡', 'ASCII' => ''),
        'CZK' => array('name' => 'Czech Koruna', 'symbol' => 'Kč', 'ASCII' => ''),
        'CHF' => array('name' => 'Swiss Franc', 'symbol' => 'CHF', 'ASCII' => ''),
        'CFA' => array('name' => 'West African CFA franc', 'symbol' => 'CFA', 'ASCII' => ''),
        'COP' => array('name' => 'Colombian peso', 'symbol' => '$', 'ASCII' => ''),
        'DKK' => array('name' => 'Danish Krone', 'symbol' => 'kr ', 'ASCII' => ''),
        'DOP' => array('name' => 'Dominican peso', 'symbol' => 'RD$', 'ASCII' => ''),
        'DZD' => array('name' => 'Algerian dinar', 'symbol' => 'DA', 'ASCII' => ''),
        'CNY' => array('name' => 'Chinese Yuan Renminbi', 'symbol'=>'¥', 'hex'=>'&#xa5;'),
        'EUR' => array('name' => 'Euro', 'symbol' => '€', 'ASCII' => '&#8364;'),
        'GEL' => array('name' => 'Georgian lari', 'symbol' => 'ლ', 'ASCII' => ''),
        'HRK' => array('name' => 'Croatian kuna', 'symbol' => 'kn', 'ASCII' => ''),
        'HKD' => array('name' => 'Hong Kong Dollar', 'symbol' => '$', 'ASCII' => '&#36;'),
        'HUF' => array('name' => 'Hungarian Forint', 'symbol' => 'Ft', 'ASCII' => ''),
        'HNL' => array('name' => 'Honduran lempira', 'symbol' => 'L', 'ASCII' => ''),
        'ILS' => array('name' => 'Israeli New Sheqel', 'symbol' => '₪', 'ASCII' => '&#8361;'),
        'INR' => array('name' => 'Indian Rupee', 'symbol' => '₹', 'ASCII' => '&#8377;'),
        'IDR' => array('name' => 'Indonesian Rupee', 'symbol' => 'Rp', 'ASCII' => ''),
        'ISK' => array('name' => 'Icelandic króna', 'symbol' => 'kr', 'ASCII' => ''),
        'JPY' => array('name' => 'Japanese Yen', 'symbol' => '¥', 'ASCII' => '&#165;'),
        'JMD' => array('name' => 'Jamaican Dollar', 'symbol' => 'J$', 'ASCII' => ''),
        'KES' => array('name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'ASCII' => ''),
        'KZT' => array('name' => 'Kazakhstani tenge', 'symbol' => '₸', 'ASCII' => ''),
        'MMK' => array('name' => 'Burmese kyat', 'symbol' => 'K', 'ASCII' => ''),
        'MKD' => array('name' => 'Macedonian denar', 'symbol' => 'ден', 'ASCII' => ''),
        'LEU' => array('name' => 'Moldovan leu', 'symbol' => 'Leu', 'ASCII' => ''),
        'LKR' => array('name' => 'Sri Lankan rupee', 'symbol' => 'රු', 'ASCII' => ''),
        'MXN' => array('name' => 'Mexican Peso', 'symbol' => '$', 'ASCII' => '&#36;'),
        'MUR' => array('name' => 'Mauritian Rupee', 'symbol' => '₨', 'ASCII' => ''),
        'MYR' => array('name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'ASCII' => ''),
        'MAD' => array('name' => 'Moroccan dirham', 'symbol' => '.د.م', 'ASCII' => ''),
        'NOK' => array('name' => 'Norwegian Krone', 'symbol' => 'Kr', 'ASCII' => ''),
        'NZD' => array('name' => 'New Zealand Dollar', 'symbol' => '$', 'ASCII' => '&#36;'),
        'NGN' => array('name' => 'Nigerian Naira', 'symbol' => '₦', 'ASCII' => '&#8358;'),
        'OMR' => array('name' => 'Omani rial', 'symbol' => 'R.O.', 'ASCII' => ''),
        'PEN' => array('name' => 'Peruvian Nuevo Sol', 'symbol'=>'S/.', 'hex'=>''),
        'PKR' => array('name' => 'Pakistan Rupee', 'symbol'=>'₨', 'hex'=>'&#x20a8;'),
        'PHP' => array('name' => 'Philippine Peso', 'symbol' => '₱', 'ASCII' => ''),
        'PLN' => array('name' => 'Polish Zloty', 'symbol' => 'zł', 'ASCII' => ''),
        'RON'=>array('name' => 'Romanian New Lei', 'symbol'=>'lei', 'hex'=>'&#x6c;&#x65;&#x69;'),
        'RWF'=>array('name' => 'Rwandan franc', 'symbol'=>'R₣', 'hex'=>''),
        'QAR'=>array('name' => 'Qatari Riyal', 'symbol'=>'﷼', 'unicode-hex'=>'fdfc'),
        'GBP' => array('name' => 'Pound Sterling', 'symbol' => '£', 'ASCII' => '&#163;'),
        'GHS' => array('name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'ASCII' => '&#x20b5;'),
        'GTQ' => array('name' => 'Guatemalan Quetzal', 'symbol' => 'Q', 'ASCII' => ''),
        'ILS' => array('name' => 'Israeli New Shekel', 'symbol' => '₪', 'ASCII' => '&#8362;'),
        'SAR' => array('name' => 'Saudi riyal', 'symbol' => 'ر.س', 'ASCII' => ''),
        'SGD' => array('name' => 'Singapore Dollar', 'symbol' => '$', 'ASCII' => '&#36;'),
        'SRD' => array('name' => 'Surinamese dollar', 'symbol' => '$', 'ASCII' => ''),
        'SEK' => array('name' => 'Swedish Krona', 'symbol' => 'kr', 'ASCII' => ''),
        'CHF' => array('name' => 'Swiss Franc', 'symbol' => 'CHF', 'ASCII' => ''),
        'KRW' => array('name' => 'South Korea Won', 'symbol' => '￦', 'ASCII' => '&#8361;'),
        'KWD' => array('name' => 'Kuwaiti dinar', 'symbol' => 'KD', 'ASCII' => ''),
        'KGS' => array('name' => 'Kyrgyzstan', 'symbol' => 'c', 'ASCII' => ''),
        'TWD' => array('name' => 'Taiwan New Dollar', 'symbol' => 'NT$', 'ASCII' => 'NT&#36;'),
        'TRY' => array('name' => 'Turkish Lira', 'symbol' => 'TL', 'ASCII' => '&#8378;'),
        'THB' => array('name' => 'Thai Baht', 'symbol' => '฿', 'ASCII' => '&#3647;'),
        'TND' => array('name' => 'Tunisinian dinar', 'symbol' => 'DT', 'ASCII' => ''),
        'TJS' => array('name' => 'Tajikistan', 'symbol' => 'смн', 'ASCII' => ''),
        'TMT' => array('name' => 'Turkmenistan New Manat', 'symbol' => 'm', 'ASCII' => ''),
        'TTD' => array('name' => ' Trinidad and Tobago', 'symbol' => '$', 'ASCII' => ''),
        'USD' => array('name' => 'U.S. Dollar', 'symbol' => '$', 'ASCII' => '&#36;'),
        'UZS' => array('name' => 'Uzbekistan Sum', 'symbol' => 'сўм', 'ASCII' => ''),
        'UGS' => array('name' => 'Ugandan shilling', 'symbol' => 'USh', 'ASCII' => ''),
        'UAH' => array('name' => 'Ukrainian Hryvnia', 'symbol' => '₴', 'ASCII' => '&#8372;'),
        'UYU' => array('name' => 'Uruguayan peso', 'symbol' => '$', 'ASCII' => ''),
        'VND' => array('name' => 'Vietnamese Dong', 'symbol' => '₫', 'ASCII' => ''),
        'LKR' => array('name' => 'Sri Lankan Rupee', 'symbol' => 'Rs', 'ASCII' => '&#8377;'),
        'RUB' => array('name' => 'Russian Ruble', 'symbol' => 'РУБ', 'ASCII' => '&#8381;'),
        'RSD' => array('name' => 'Serbian Dinar', 'symbol' => 'din', 'ASCII' => ''),
        'ZAR' => array('name' => 'South African rand', 'symbol' => 'R', 'ASCII' => '&#x52;'),
    );

    public static function getSymbol($code = 'USD')
    {
        if (!empty(self::$currencies[$code]['ASCII'])) {
            return (string)self::$currencies[$code]['ASCII'];
        }

        return (string)self::$currencies[$code]['symbol'];
    }

    public static function getSymbolAsIs($code = 'USD')
    {
        return (string)self::$currencies[$code]['symbol'];
    }

    public static function toArray()
    {
        $ret = array();
        foreach (array_keys(self::$currencies) as $k) {
            $ret[$k] = $k . ' (' . self::getSymbol($k) . ')';
        }

        return $ret;
    }
}
