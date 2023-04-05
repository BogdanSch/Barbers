<?php

namespace SLB_API_Mobile\Listener;

use SLB_API_Mobile\Listener\Events\BookingEventsListener;

class NotificationListener
{
    public function __construct()
    {
	new BookingEventsListener();
    }


}