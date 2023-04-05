<?php

class SLN_Action_Ajax_UpdateUser extends SLN_Action_Ajax_Abstract
{
    public function execute()
    {
       if(!current_user_can( 'manage_salon' )) throw new Exception('not allowed');
       $result = $this->getResult(sanitize_text_field(wp_unslash( $_POST['s'] )));
       if(!$result){
           $ret = array(
               'success' => 0,
               'errors' => array(__('User not found','salon-booking-system'))
           );
       }else{
           $ret = array(
               'success' => 1,
               'result' => $result,
               'message' => __('User updated','salon-booking-system')
           );
       }
       return $ret;
    }
    private function getResult($id)
    {
        $number = 1;
        $u = new WP_User($id);
        if(!$u) return;
        $values = [ 'id' => $u->ID, ];
        foreach (SLN_Enum_CheckoutFields::forBookingAndCustomer()->appendSmsPrefix() as $key => $field){
            $values[$key] = $field->getValue($u->ID);
        }
        $values['admin_url'] = add_query_arg(array('page' => 'salon-customers', 'id' => $u->ID), admin_url( "admin.php". $action ));
        return $values;
    }
}
