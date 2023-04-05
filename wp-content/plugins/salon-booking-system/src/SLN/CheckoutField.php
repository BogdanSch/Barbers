<?php

class SLN_CheckoutField implements ArrayAccess {
	
	protected $settings = [];
	
	function __construct($settings){
		$this->settings = $settings;
	}

	public function get($key){
		return $this->offsetGet($key);
	}

	public function getSettings(){
		return $this->settings;
	}
	
	public function key(){
		return $this->get('key');
	}
	
	public function label(){
		return $this->get('label');
	}
	
	public function isDefault(){
		return (bool) !$this->get('additional');
	}
	
	public function isAdditional(){
		return (bool) $this->get('additional');
	}
	
	public function isRequiredByDefault(){
		return apply_filters('sln.checkout.is_required_by_default', in_array($this->key(), ['email']), $this->key());
	}
	
	public function isRequired(){
		return $this->get('required');	
	}
	
	public function isHidden(){
		return $this->get('hidden');
	}
	
	public function isCustomer(){
		return (bool) $this->get('customer_profile');	
	}

	public function isExportCsv(){
		return (bool) $this->get('export_csv');
	}
	
	public function isRequiredNotHidden(){
		return $this->isRequired() && !$this->isHidden();	
	}
	
	public function isHiddenOrNotRequired(){
		return $this->isHidden() || !$this->isRequired();	
	}
	
	public function labelForSettings(){
		return $this->label().($this->isRequiredByDefault() ? ' '.__('(not editable)', 'salon-booking-system') : '');
	}
		
	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->settings[] = $value;
        } else {
            $this->settings[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->settings[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->settings[$offset]);
    }

    public function offsetGet($offset) {
        $value = isset($this->settings[$offset]) ? $this->settings[$offset] : null;
        return $offset === 'label' ? __($value, 'salon-booking-system') : $value ;
    }
    
    public function getUser($user_id = 0){
    	static $user;
    	static $id;
    	if($user_id !== $id || $user === null ){
    	    $id = $user_id;
    		$user = get_userdata($id);
    	}
    	return $user;
    	
    }
    
    public function getValue($id = false){
	    $value = false;
    	if($this->isCustomer() && $id){
    		$key = $this->key();
    		if(in_array($key,['firstname','lastname','email'])){
    			$user = $this->getUser($id);
    			$key = str_replace('name','_name',$key);
    			$obj_key = ($key === 'email' ? 'user_' : '').$key;
    			$value = $user->{$obj_key};
    		}else{
    		    if(metadata_exists( 'user', $id, '_sln_' . $key )){
                    $value = get_user_meta($id, '_sln_' . $key, true);
                }else{
    		        $default_value = $this->getDefaultValue();
    		        if($default_value !== null) $value = $default_value;
                }
    		}

    	}
    	
    	return $value;
    }
    
    function getDefaultValue(){
    	return $this->get('default_value');
    }

	function getFileType(){
		$file_t = $this->get('file_type');
		if(empty($file_t)){
			return array();
		}
		for($iter = 0; count($file_t) > $iter; $iter++){
			$file_t[$iter] = '.'. $file_t[$iter];
		}
		return array('accept' => $file_t);
	}

	function getFiles($user){
		if(empty($user)){
			return array();
		}
		$files = $user->getMeta($this->label());
		return $files;
	}

    function getSelectOptions(){
	    $str = $this->get('options');
	    $arr = explode(PHP_EOL, $str);
	    $arr = array_merge(...array_map(function($v){
	        $v = explode(':', $v,2);
	        return  [$v[0] => count($v) === 2 ? $v[1] : $v[0]];
        }, $arr));
	    return $arr;
    }
}