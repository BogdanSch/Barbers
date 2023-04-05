<?php

class SLN_Action_Ajax_RemoveUploadedFile extends SLN_Action_Ajax_Abstract
{
    public function execute()
    {
        $file_name  = $_POST['file'];
        $file       = wp_upload_dir()['path'].'/'. $file_name;

        if(file_exists($file)){
            unlink($file);
        }

	$ret = array(
            'success'  => 1,
        );

        return $ret;
    }

}
