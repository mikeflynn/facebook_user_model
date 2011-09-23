<?php
class user_record extends base_record
{
	public function __construct($data)
	{	
		$this->valid_attributes = array(
			'id',
			'first_name',
			'last_name',
			'email',
			'gender',
			'location',
			'birthday',
			'points',
			'photo',
			'fb_id',
			'profile_url',
			'location'
		);
		
		return parent::__construct($data);
	}

	public function add_points($points = 0)
	{
		$this->writeback = true;
		$this->set_points($this->get_points()+$points);

		return $this->save();
	}

	public function _save()
	{
		if(!$this->writeback) return true;

		if(!$this->is_empty())
		{	
			if(!empty($this->attributes['id']))
			{
				$fields = $this->attributes;
				unset($fields['id']);
				
				$gateway = new user_gateway();
				return $gateway->update($this->attributes['id'], $fields);
			}
			else
			{
				$gateway = new user_gateway();
				$id = $gateway->insert($this->attributes);
				if($id) $this->attributes['id'] = $id;
			}
		}
		
		return false;
	}	
}
?>