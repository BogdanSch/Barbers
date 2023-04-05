<?php

class SLN_Action_LinkServicesBooking
{
    const FORM_STEP_DATE      = 'date';
    const FORM_STEP_ATTENDANT = 'attendant';

    /** @var SLN_Plugin */
    private $plugin;

    /** @var SLN_Shortcode_Salon */
    private $booking_form_handler;

    public function __construct(SLN_Plugin $plugin)
    {
	$this->plugin		    = $plugin;
	$this->booking_form_handler = new SLN_Shortcode_Salon($plugin, null);
    }

    public function execute() {

	if ( ! isset($_REQUEST['skip_service_selection']) ) {
	    return;
	}

	$bb = $this->plugin->getBookingBuilder();
	$bb->clear();
	$values = isset($_REQUEST['sln']) && isset($_REQUEST['sln']['services']) && is_array($_REQUEST['sln']['services'])  ? $_REQUEST['sln']['services'] : array();
        foreach ($this->getServices() as $service) {
            if (isset($values) && isset($values[$service->getId()])) {
                $bb->addService($service);
            }
        }
        $bb->save();
        do_action('sln.service_link.skip_selection.after', $bb);

	if ( in_array( self::FORM_STEP_ATTENDANT, $this->booking_form_handler->getSteps() ) ) {
	    $stepPage = self::FORM_STEP_ATTENDANT;
	} else {
	    $stepPage = self::FORM_STEP_DATE;
	}

	wp_redirect(apply_filters('sln.service_link.skip_selection.url', add_query_arg(
	    array('sln_step_page' => $stepPage),
	    get_permalink($this->plugin->getSettings()->getPayPageId() )
	)));

	exit();
    }

    public function getServices()
    {
	$repo = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_SERVICE);

	$services = $repo->getAll();

	$services = array_filter($services, function ($service) {
	    return !$service->isHideOnFrontend();
	});

	$services = $repo->sortByExecAndTitleDESC($services);

	return apply_filters('sln.shortcode.salon.ServicesStep.getServices', $services);
    }

}