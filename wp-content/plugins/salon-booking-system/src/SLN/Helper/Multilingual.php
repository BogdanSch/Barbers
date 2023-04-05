<?php

class SLN_Helper_Multilingual{

    static $implementation;

    static function getImplementation(){
        if(self::$implementation === null){
            self::setImplementation();
        }
        return self::$implementation;
    }

    static function setImplementation(){
        $implementation = 'wp';
        if(function_exists('pll_current_language')){
            $implementation = 'polylang';
        }elseif(defined('ICL_LANGUAGE_CODE')){
            $implementation = 'wpml';
        }
        self::$implementation = $implementation;
    }

    static function getCurrentLanguage(){
        $i = self::getImplementation();
        $ret;
        switch ($i) {
            case 'wpml':
                $ret = ICL_LANGUAGE_CODE;
                break;
            case 'polylang':
                $ret = pll_current_language();
                break;
            default:
               $ret = strtolower(substr(get_user_locale(), 0, 2));
        }
        return $ret;
    }

	static function getDefaultLanguage(){
        $i = self::getImplementation();
        $ret;
        switch ($i) {
            case 'wpml':
                $ret = apply_filters( 'wpml_default_language', NULL );
                break;
            case 'polylang':
                $ret = pll_default_language ();
                break;
            default:
               $ret = strtolower(substr(get_user_locale(), 0, 2));
        }
        return $ret;
    }
	static function getObjectLanguage( $id ){
        $i = self::getImplementation();
        $ret;
        switch ($i) {
            case 'wpml':
                $ret = apply_filters( 'wpml_element_language_code', NULL, array('element_id' => $id, 'element_type' => get_post_type( $id ) ) );
                break;
            case 'polylang':
                $ret = pll_get_post_language($id);
                break;
            default:
               $ret = strtolower(substr(get_user_locale(), 0, 2));
        }
        return $ret;
    }

	static function translateId( $id , $code = false, $return_original = true ){
        $i = self::getImplementation();
        if(!$code) $code = self::getDefaultLanguage();
        $ret;
        switch ($i) {
            case 'wpml':
                $ret = apply_filters( 'wpml_object_id', $id, get_post_type( $id ), $return_original, $code );;
                break;
            case 'polylang':
                $ret = pll_get_post($id, $code);
                if( empty($ret) && $return_original ) $ret = $id;
                break;
            default:
               $ret = $id;
        }
        return $ret;
    }

    static function getTermLanguage( $id, $taxonomy  ){
        $i = self::getImplementation();
        $ret;
        switch ($i) {
            case 'wpml':
                $term = get_term($id,$taxonomy);
                $ret = apply_filters( 'wpml_element_language_code', NULL, array('element_id' => $term->term_taxonomy_id, 'element_type' => $taxonomy ) );
                break;
            case 'polylang':
                $ret = pll_get_term_language($id);
                break;
            default:
               $ret = strtolower(substr(get_user_locale(), 0, 2));
        }
        return $ret;
    }

    static function translateTermId( $id, $taxonomy  , $code = false, $return_original = true ){
        $i = self::getImplementation();
        if(!$code) $code = self::getDefaultLanguage();
        $ret;
        switch ($i) {
            case 'wpml':
                $ret = apply_filters( 'wpml_object_id', $id, $taxonomy, $return_original, $code );
                break;
            case 'polylang':
                $ret = pll_get_term($id, $code);
                if( empty($ret) && $return_original ) $ret = $id;
                break;
            default:
               $ret = $id;
        }
        return $ret;
    }

    static function getDateLocale(){
        $implementation = self::getImplementation();
        if($implementation === 'wpml'){
            $languages = apply_filters( 'wpml_active_languages', null);

	    $language = isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : ICL_LANGUAGE_CODE;

            if ( isset( $languages[$language] ) )
            $locale = $languages[$language]['default_locale'];
        }elseif($implementation === 'polylang'){
            $locale = pll_current_language('locale');
            if(! $locale ){
                $locale = get_user_locale();
            }
        }else{
            $locale = get_user_locale();
        }

        if( setlocale(LC_TIME,0) !== $locale  ){ setlocale(LC_TIME, $locale ); }

        return $locale;
    }

	static function isMultiLingual(){
        return self::getImplementation() !== 'wp';
    }

    static function registerString($string){
        $implementation = self::getImplementation();
        if($implementation === 'wpml'){
            icl_register_string( 'salon-booking-system', '', $string );
        }elseif($implementation === 'polylang'){
            pll_register_string( 'salon-booking-system', $string);
        }
    }
}