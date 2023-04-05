<?php

class SLN_Action_Ajax_GenerateOnesignalApp extends SLN_Action_Ajax_Abstract
{
    public function execute()  {

        $url  = 'https://onesignal.com/api/v1/apps';

        $info = parse_url(home_url());

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . SLN_ONESIGNAL_USER_AUTH_KEY,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode(array(
                'name'                  => 'Salon Booking Plugin App ' . $info['host'],
                'chrome_web_origin'     => $info['scheme'] . '://' . $info['host'],
                'site_name'             => $info['host'],
                'safari_site_origin'    => $info['scheme'] . '://' . $info['host'],
            )),
        );

        $response = wp_remote_post($url, $args);
        $body     = json_decode(wp_remote_retrieve_body($response));

        return array(
	    'app_id' => $body->id,
        );
    }

}
