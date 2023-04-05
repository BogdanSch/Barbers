<?php // algolplus

class SLN_Shortcode_SalonServices
{
    const NAME = 'salon_booking_services';
    const BOOK_NOW_REDIRECT_ACTION = 'salon-booking-services-book-now';

    private $plugin;
    private $attrs;

    function __construct(SLN_Plugin $plugin, $attrs)
    {
        $this->plugin = $plugin;
        $this->attrs = $attrs;
    }

    public static function init(SLN_Plugin $plugin)
    {
        add_shortcode(self::NAME, array(__CLASS__, 'create'));
	add_action('wp_loaded', array(__CLASS__, 'listen_book_now_redirect'));
    }

    public static function create($attrs)
    {
        SLN_TimeFunc::startRealTimezone();

        $obj = new self(SLN_Plugin::getInstance(), $attrs);

        $ret = $obj->execute();
        SLN_TimeFunc::endRealTimezone();
        return $ret;
    }

    public function execute()
    {
        $services = false;
        $categories = false;
        $display = false;
        if(!empty($this->attrs['services'])){
            $services = explode(',',$this->attrs['services']);
        }
        if(!empty($this->attrs['category'])){
            $categories = explode(',',$this->attrs['category']);
        }
        if(!empty($this->attrs['display'])){
            $display = explode(',',$this->attrs['display']);
        }
        $repo = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_SERVICE);
        
        $criteria = $services ? array(
            '@wp_query' => array(
                'post__in' => $services,                
            )
        ) : array('@wp_query' => array());
        if($categories) $criteria['@wp_query']['tax_query'] = array(
            array(
                'taxonomy' => SLN_Plugin::TAXONOMY_SERVICE_CATEGORY,
                'field' => 'term_id',
                'terms' => array_map('intval', $categories)
            )
        );

	$criteria = apply_filters('sln_services_shortcode_get_services_criteria', $criteria, $this->attrs);

        $services = $repo->sortByExecAndTitleDESC($repo->get($criteria));
        $data = array('services' => $services);
        $data['styled'] = !empty($this->attrs['styled']) && $this->attrs['styled']=== 'true';
        $data['skip_service_selection'] = !empty($this->attrs['skip_service_selection']) && $this->attrs['skip_service_selection'] === 'true';
        if(!empty($this->attrs['columns']) && intval($this->attrs['columns'])) $data['columns'] =  intval($this->attrs['columns']);
        $data['display'] = $display;
        $data['booking_url'] = apply_filters('sln_services_shortcode_get_services_booking_url', add_query_arg(array('action' => self::BOOK_NOW_REDIRECT_ACTION)), $this->attrs);
        return $this->render($data);
    }

    protected function render($data = array())
    {
        return $this->plugin->loadView('shortcode/salon_booking_services', compact('data'));
    }

    public static function listen_book_now_redirect()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== self::BOOK_NOW_REDIRECT_ACTION) {
            return;
        }

	$plugin = SLN_Plugin::getInstance();

	$bb = $plugin->getBookingBuilder();

	$bb->removeServices();

	$repo	     = $plugin->getRepository(SLN_Plugin::POST_TYPE_SERVICE);
	$services    = $repo->getAll();

	foreach ($services as $service) {
	    if ($service->getId() == $_GET['service']) {
		$bb->addService($service);
	    }
	}

	$bb->save();

	do_action('sln_book_now_services_after', $bb);

    if(!empty($_GET['skip_service_selection']) && '1' == $_GET['skip_service_selection']
        && empty($_GET['secondary'])){
        $repo     = SLN_Plugin::getInstance()->getRepository(SLN_Plugin::POST_TYPE_SERVICE);
        $services = $repo->getAllSecondary();
        $bookingUrl = add_query_arg(
            array(
                'sln_step_page' => !empty($services) ? 'secondary' : 'attendant',
            ),
            get_the_permalink($plugin->getSettings()->getPayPageId())
        );
    }else{
    	$bookingUrl = add_query_arg(array('sln_step_page' => 'services', 'save_selected' => '1'), get_the_permalink($plugin->getSettings()->getPayPageId()));
    }

	wp_safe_redirect($bookingUrl);
	exit;
    }

}
