<?php
abstract class base_record 
{	
	protected $writeback = false;

	protected $valid_attributes = false;
	protected $attributes = false;
	
	public function __construct($data, $new = false)
	{
		if(!empty($data) && is_array($data))
		{
			foreach($data as $key=>$val)
			{
				if($this->is_attribute($key))
				{
					call_user_func_array(array($this,"set_{$key}"),array($val));
				}
			}
		}
		
		// If we are just loading an already saved record, reset the writeback flag.
		if($new) $this->writeback = true;
	}
	
	public function __call($name,$args)
    {
    	$method_name = '_'.$name;

        if(method_exists($this,$method_name))
        {
        	return call_user_func_array(array($this,$method_name),$args);
        }
        else {
        	// Auto-magic setters and getters...
        	list($action, $varname) = explode('_', $name, 2);
        	if($action == 'set')
        	{
        		if($this->is_attribute($varname) && !empty($args[0]))
        		{
					$this->attributes[$varname] = $args[0];
					
					$this->writeback = true;
					
					return true;
				}
				
				return false;
			}
			else if($action == 'get')
			{
				if($this->is_attribute($varname))
				{
					if(empty($this->attributes[$varname]))
					{
						return null;
					}
					else 
					{
						return $this->attributes[$varname];
					}
				}
				
				return false;
			}
				
			trigger_error("Member function: '$name' not found",E_USER_ERROR);
        }
	}
	
	public function is_empty()
	{
		if(!empty($this->attributes))
		{
			return false;
		}
		
		if(!empty($this->rqd_attributes))
		{
			foreach($this->rqd_attributes as $rqd)
			{
				if(empty($this->attributes[$rqd])) 
				{
					return false;
				}
			}
		}
		
		return true;
	}	
	
	protected function is_attribute($key)
	{
		if(empty($this->valid_attributes)) return false;
				
		if(in_array($key, $this->valid_attributes))
		{
			return true;
		}
		
		return false;
	}
	
	public function _to_array()
	{
		return $this->attributes;
	}
	
	public function _to_string()
	{
		return serialize($this->to_array());
	}
	
	// Abstract
	
	abstract public function _save();
}
?>