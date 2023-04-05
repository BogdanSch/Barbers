<?php

class SLN_Formatter
{
    private $plugin;

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function money($val, $showFree = true, $useDefaultSep = true, $removeDecimals = false, $htmlEntityDecode = false)
    {
	$val = floatval($val);
        $s = $this->plugin->getSettings();
        $isLeft = $s->get('pay_currency_pos') == 'left';
        $rightSymbol = $isLeft ? '' : $s->getCurrencySymbol();
        $rightSymbol = $htmlEntityDecode ? html_entity_decode($rightSymbol) : $rightSymbol;
        $leftSymbol = $isLeft ? $s->getCurrencySymbol() : '';
        $leftSymbol = $htmlEntityDecode ? html_entity_decode($leftSymbol) : $leftSymbol;

        if ($showFree && $val <= 0) {
            $money = '<span class="sln-service-price-free">' . __('free','salon-booking-system') . '</span>';
        }
        else {
            if ($useDefaultSep) {
                $decimalSeparator  = $s->getDecimalSeparatorDefault();
                $thousandSeparator = $s->getThousandSeparatorDefault();
            }
            else {
                $decimalSeparator  = $s->getDecimalSeparator();
                $thousandSeparator = $s->getThousandSeparator();
            }

            $decimals = $removeDecimals && floor($val) === floatval($val) ? 0 : 2;
            $money = ($leftSymbol . ( !empty( $leftSymbol ) ? ' ' : '' ) . number_format((float)$val, $decimals, $decimalSeparator, $thousandSeparator) . ( !empty( $rightSymbol ) ? ' ' : '' ) . $rightSymbol);
        }

        return $money;
    }

    public function moneyFormatted($val, $showFree = true, $htmlEntityDecode = false) {
        return $this->money($val, $showFree, false, true, $htmlEntityDecode);
    }

    public function datetime($val)
    {
        return self::date($val).' '.self::time($val);
    }

    public function date($val)
    {
        $timezone = '';

        if ($val instanceof DateTime || $val instanceof DateTimeImmutable) {
            $timezone = $val->getTimezone();
            $val = $val->getTimestamp();
        }else{
            $val = SLN_TimeFunc::strtotime($val);
        }

        $f = $this->plugin->getSettings()->getDateFormat();
        $phpFormat = SLN_Enum_DateFormat::getPhpFormat($f);
        remove_filter( 'date_i18n', 'wp_maybe_decline_date' );
        $formatted  = ucwords(SLN_TimeFunc::translateDate($phpFormat, $val, $timezone ));
        add_filter( 'date_i18n', 'wp_maybe_decline_date' );
        return $formatted;
    }

    public function time($val, $customFormat = false)
    {
	    $f         = $this->plugin->getSettings()->getTimeFormat();
	    $phpFormat = $customFormat ?: SLN_Enum_TimeFormat::getPhpFormat( $f );
	    $timezone  = '';
	    if ( $val instanceof DateTime || $val instanceof DateTimeImmutable ) {
		    $timezone = $val->getTimezone();
		    $val = $val->getTimestamp();
	    } elseif ( $val instanceof \Salon\Util\Time ) {
		    $val = $val->toDateTime()->getTimestamp();
	    }else{
            $val = SLN_TimeFunc::strtotime( $val );
        }

	    return SLN_TimeFunc::translateDate( $phpFormat, $val, $timezone );
    }

    public function phone($val, $prefix = null, $trunk_prefix = null){

	$s = $this->plugin->getSettings();

	if (empty($prefix)) {
	    $prefix = $s->get('sms_prefix');
	}

	if (!isset($trunk_prefix)) {
	    $trunk_prefix = $s->get('sms_trunk_prefix');
	}

        if($trunk_prefix && strpos($val,'0') === 0){
            $val = substr($val,1);
        }
        $val = str_replace(' ','',$val);
        return substr($val, 0, 1) === '+' ? $val : $prefix . $val;
    }
}
