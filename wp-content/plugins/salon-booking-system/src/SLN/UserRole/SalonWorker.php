<?php

class SLN_UserRole_SalonWorker
{
    private $plugin;

    private $role;
    private $displayName;
    private $capabilities = array(
        'manage_salon' => true,
        'manage_salon_settings' => false,
        'edit_posts' => true,
        'export_reservations_csv_sln_calendar' => false,
        'delete_permanently_sln_booking'       => false,
    );

    public function __construct(SLN_Plugin $plugin, $role, $displayName)
    {
        foreach (array(
                     SLN_Plugin::POST_TYPE_ATTENDANT,
                     SLN_Plugin::POST_TYPE_BOOKING,
                 ) as $k) {
            foreach (get_post_type_object($k)->cap as $v) {
                $this->capabilities[$v] = true;
            }
        }
        $this->plugin = $plugin;
        $this->role = $role;
        $this->displayName = $displayName;

        $this->capabilities['delete_sln_booking'] = false;
        $this->capabilities['delete_sln_bookings'] = false;
        $this->capabilities['delete_private_sln_bookings'] = false;
        $this->capabilities['delete_published_sln_bookings'] = false;
        $this->capabilities['delete_others_sln_bookings'] = false;

        $this->capabilities['create_sln_bookings'] = false;

        $this->capabilities['delete_sln_attendant'] = false;
        $this->capabilities['delete_sln_attendants'] = false;
        $this->capabilities['delete_private_sln_attendants'] = false;
        $this->capabilities['delete_published_sln_attendants'] = false;
        $this->capabilities['delete_others_sln_attendants'] = false;

        $this->capabilities['create_sln_attendants'] = false;
    }

    /**
     * @return SLN_Plugin
     */
    protected function getPlugin()
    {
        return $this->plugin;
    }

    protected function getRole()
    {
        return $this->role;
    }

    protected function getDisplayName()
    {
        return $this->displayName;
    }

    protected function getCapabilities()
    {
        return $this->capabilities;
    }

    public static function addRole() {
        $slnWorker = new SLN_UserRole_SalonWorker(SLN_Plugin::getInstance(), SLN_Plugin::USER_ROLE_WORKER, __('Sln worker', 'salon-booking-system'));
        $roles = wp_roles();
        if ($roles->get_role($slnWorker->getRole())) {
            $roles->remove_role($slnWorker->getRole());
        }
        $roles->add_role($slnWorker->getRole(), $slnWorker->getDisplayName(), $slnWorker->getCapabilities());
    }

    public static function removeRole() {
        $slnWorker = new SLN_UserRole_SalonWorker(SLN_Plugin::getInstance(), SLN_Plugin::USER_ROLE_WORKER, __('Sln worker', 'salon-booking-system'));
        $roles = wp_roles();
        if ($roles->get_role($slnWorker->getRole())) {
            $roles->remove_role($slnWorker->getRole());
        }
    }
}
