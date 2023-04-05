<?php

class SLN_Action_Ajax_SearchAssistantStaffMember extends SLN_Action_Ajax_Abstract
{
    public function execute()
    {
       if(!current_user_can( 'manage_salon' )) throw new Exception('not allowed');
       $result = array();
       $search = sanitize_text_field(wp_unslash( isset($_GET['s']) ? $_GET['s'] : '' ));
       if(isset($search)){
           $result = $this->getResult($search);

       $emails = array_map(function ($item) {
        return $item['id'];

           }, $result);


           if (!empty($search) && !in_array($search, $emails)) {

        $result = array_merge(array(array(

            'id' => $search,

            'text' => $search,

            'staff_member_id' => 0,

        )), $result);

        }
       }
       if(!$result){
           $ret = array(
               'success' => 0,
               'errors' => array(__('User not found','salon-booking-system'))
           );
       }else{
           $ret = array(
               'success' => 1,
               'result' => $result,
           );
       }
       return $ret;
    }
    private function getResult($search)
    {
        $include = $this->userSearch($search);

    if ( empty( $include ) ) {
        return array();
    }

        $number     = -1;
    $user_query = new WP_User_Query( compact('include', 'number') );

        if(!$user_query->results) return array();
        else $results = $user_query->results;

        $value = array();

    foreach($results as $u){
        $values[] = array(
                'id' => $u->user_email,
                'text' => $u->user_email,
                'staff_member_id' => $u->ID,
            );
        }
        return $values;
    }

    public function userSearch($wp_user_query) {
            global $wpdb;

            $uids=array();
            if(isset($wp_user_query)){
            $users_ids_collector = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DISTINCT ID FROM $wpdb->users u INNER JOIN $wpdb->usermeta um ON u.ID = um.user_id WHERE LOWER(u.user_email) LIKE %s AND meta_key='{$wpdb->prefix}capabilities' AND ( meta_value LIKE %s  OR  meta_value LIKE %s)",
            '%' . $wp_user_query . '%',
            '%' . SLN_Plugin::USER_ROLE_STAFF . '%',
            '%' . SLN_Plugin::USER_ROLE_WORKER . '%'
        )
        );
            foreach($users_ids_collector as $maf) {
                if(!in_array($maf->ID,$uids)) {
                    array_push($uids,$maf->ID);
                }
            }
        }
        return $uids;
    }
}
