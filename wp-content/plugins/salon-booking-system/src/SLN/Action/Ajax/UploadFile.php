<?php

class SLN_Action_Ajax_UploadFile extends SLN_Action_Ajax_Abstract
{
    public function execute()
    {
        $errors    = array();
        $file_name = '';

        if(!empty($_FILES) && isset($_FILES['file'])) {
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            $tmp_file  = $_FILES['file'];
            $file_name = $this->unique_filename(null, $tmp_file['name']);
            $overrides = array(
                'test_form' => false,
                'unique_filename_callback' => array($this, 'unique_filename')
            );
            $movefile = wp_handle_upload($tmp_file, $overrides);
            if(isset($movefile['error'])){
                $errors[] = $movefile['error'];
            }
        }

	$ret = array(
            'success' => empty($errors),
            'errors'  => $errors,
            'file'    => $file_name,
        );

        return $ret;
    }

    public function unique_filename($path, $filename){
        return (new DateTime())->getTimestamp(). '_'. $filename;
    }

}
