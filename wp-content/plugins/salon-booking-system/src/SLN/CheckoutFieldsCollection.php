<?php

class SLN_CheckoutFieldsCollection extends ArrayObject
{
	
	public function getField($key){
		return $this->offsetGet($key);
	}
	
	public function keys(){
		return $this->map('key');
	}
	
	public function labels(){
		return $this->map('label');
	}
	
	public function defaults(){
		return $this->filter('additional',false);
	}
	
	public function additional(){
		return $this->filter('additional');
	}
	
	public function required(){
		return $this->filter('required');
	}

	public function exportCsv(){
		return $this->filter('export_csv');
	}
	
	public function labelsForSettings(){
		return $this->map(function($field){
			return $field->labelForSettings();
		});
	}
	
	public function appendPassword(){
		return $this->merge(SLN_Enum_CheckoutFields::passwordField());
	}
    
    public function filter($key,$needle = true,$negate = false){
        $this->exchangeArray( 
        	array_filter($this->getArrayCopy(),function($field) use($key,$needle,$negate){
        	    if(!is_string($key) && is_callable($key)){
        	        return $key($field);
                }else {
                    $value = $field->get($key);
                    $ret = is_array($value) ? in_array($needle, $value) : $value === $needle;
                    return $negate ? !$ret : $ret;
                }
        	})
        );
        
        return $this;
    }	
    
    public function map($key){
    	$this->exchangeArray(
    		array_map(function($field)use($key){
    			return !is_string($key) && is_callable($key) ? $key($field) : $field->get($key);
    		},$this->getArrayCopy())
    	);
    	
    	return $this;
    }
    
    public function intersect($collection){
    	$this->exchangeArray(
    		array_intersect_key($this->getArrayCopy(),$collection->getArrayCopy())
    	);
    	return $this;
    }
    
    public function diff($collection){
    	$this->exchangeArray(
    		array_diff_key($this->getArrayCopy(),$collection->getArrayCopy())
    	);
    	return $this;
    }
    
    public function selfClone(){
    	return clone $this;
    }
    
    public function merge($collection){
    	$this->exchangeArray(
    		array_merge($this->getArrayCopy(),$collection->getArrayCopy())
    	);
    	return $this;
    }
    
    public function prepend($collection){
    	$this->exchangeArray(
    		array_merge($collection->getArrayCopy(),$this->getArrayCopy())
    	);
    	return $this;
    }

    public function implode($glue=''){
        return implode($glue,$this->getArrayCopy());
    }

    public function appendSmsPrefix(){
            return $this->merge(SLN_Enum_CheckoutFields::smsPrefixField());
    }
}