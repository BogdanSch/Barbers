<?php

class SLN_Helper_Availability_AdminRuleLog{

    protected static $instance;
    protected $ruleLog;
    protected $dateLog;
    protected $attendants;

    public static function getInstance(){
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function __construct(){
        $this->ruleLog = array();
        $this->dateLog = array();
        $this->attendants = array();
    }

    public function addLog( String $time, $value, String $key ){
        if ( true == (bool)SLN_Plugin::getInstance()->getSettings()->get('debug') && current_user_can( 'administrator' )  ){
            $this->ruleLog[esc_html($time)][esc_html($key)] = $value;
        }
    }

    public function addDateLog( String $date, $value, $key ){
        if( true == (bool)SLN_Plugin::getInstance()->getSettings()->get('debug') && current_user_can( 'administrator' )  ){
            if( false == $value && ( !isset($this->dateLog[$date]) || $this->dateLog[$date] == esc_html(__('free', 'salon-booking-settings')) ) ){
                $this->dateLog[esc_html($date)] = esc_html($key);
            } else if( !isset($this->dateLog[$date]) ){
                $this->dateLog[$date] = esc_html(__('free', 'salon-booking-settings'));
            }
            // $this->dateLog[$date][$key] = $value;
        }
    }

    public function addAttendant(int $attendantId){
        if($this->isEnabled() && !empty($attendantId)){
            $this->attendants[] = $attendantId;
        }
    }

    public function addArrayErrors( String $time, $errors ){
        if( true == (bool)SLN_Plugin::getInstance()->getSettings()->get('debug') && current_user_can( 'administrator' )  && !empty( $errors ) ){
            foreach( $errors as $e ){
                if( is_array( $e ) ){
                    continue;
                }
                $this->ruleLog[esc_html($time)][esc_html($e)] = false;
            }
        }
    }

    public function replaceKeyRegex($time, $regex, $newKey, $newValue){
        if( true == (bool)SLN_Plugin::getInstance()->getSettings()->get('debug') && current_user_can( 'administrator' )  ){
            foreach( preg_grep( $regex, array_keys($this->ruleLog[esc_html($time)]) ) as $key ){
                unset( $this->ruleLog[esc_html($time)][esc_html($key)] );
            }
            $this->ruleLog[esc_html($time)][esc_html($newKey)] = $newValue;
        } 
    }

    public function getLog(){
        return $this->ruleLog;
    }

    public function getDateLog(){
        return $this->dateLog;
    }

    public function getAttendats(){
        return SLN_Plugin::getInstance()->createAttendant($this->attendants);
    }

    public function clear(){
        $this->ruleLog = array();
        $this->dateLog = array();
    }

    public function isEnabled () {
        return SLN_Plugin::getInstance()->getSettings()->get('debug') && current_user_can( 'administrator' );
    }
}

?>