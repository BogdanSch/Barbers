<?php

namespace SLB_Zapier;

class Plugin {

    private static $instance;

    public static function get_instance() {

        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {
	new Webhook();
	new Store();
    }

}