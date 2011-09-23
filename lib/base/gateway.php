<?php
abstract class base_gateway 
{
	protected $_db;
	protected $database = DB_NAME;
	protected $table = false;
	
	public function __construct()
	{
		if( defined(DB_HOST) || defined(DB_USER) || defined(DB_PASS) )
		{
  			trigger_error("Db connection information invalid!", E_USER_ERROR);
		}

	    $this->_db = database::start(DB_HOST, DB_USER, DB_PASS);	
	}
	
	protected function parse_where_array($where_array)
	{
		if(empty($where_array)) return false;
		
		if(is_array($where_array))
		{	
			$where = array();
			foreach($where_array as $key=>$value)
			{
				$str = $this->_db->escape($key).' = '.$this->_db->quote($value);
				if(!strpos($key, '.')) $str = $this->table.'.'.$str;

				$where[] = $str;
			}		
			
			return implode(' AND ', $where);
		}
		else
		{
			$where = $where_array;
		}
		
		return $this->_db->escape($where);
	}
	
	public function select($where = null, $limit = null, $order_by = null, $order = null)
	{
		$query = "SELECT * FROM {$this->database}.{$this->table}";
		
		if(!empty($where))
		{
			$where_string = $this->parse_where_array($where);
			
			$query .= "  WHERE {$where_string}";
		}
		
		if(!empty($order_by))
		{
			$query .= " ORDER BY ".$this->_db->escape($order_by);
			
			if(!empty($order))
			{
				$query .= ' '.$this->_db->escape($order);
			}
		}

		if(is_null($limit) || $limit > 1)
		{
			if($limit > 1) $query .= " LIMIT {$limit}";
			
			return $this->_db->query($query);
		}
		else 
		{
			return $this->_db->query($query);
		}
	}
	
	public function insert($data)
	{
		$keys = $values = array();
		
		foreach($data as $key=>$value)
		{
			$keys[] = $this->_db->escape($key);
			$values[] = $this->_db->escape($value);
		}
	
		$query = "
			INSERT INTO {$this->database}.{$this->table} 
			(`".implode("`, `", $keys)."`) 
			VALUES ('".implode("', '", $values)."')
		";
		
		if($this->_db->query($query))
		{
			return $this->_db->insert_id();
		}
		
		return false;
	}
	
	public function update($where, $data)
	{
		$fields = array();
		
		foreach($data as $key=>$value)
		{
			$fields[] = $this->_db->escape($key).'='.$this->_db->quote($value);
		}
		
		$where_string = $this->parse_where_array($where);
	
		$query = "
			UPDATE {$this->database}.{$this->table} 
			SET ".implode(',', $fields)."
			WHERE {$where_string}
		";
		
		return $this->_db->query($query);		
	}

	public function query($query, $data = null)
	{
		return $this->_db->query($query, $data);
	}
}
?>