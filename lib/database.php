<?php
final class database 
{
	private static $instance = false;
	private static $debug = false;
	private $connection = false;
	private $host;
	private $user;
	private $password;

	public static function start($host, $user, $password)
	{
		if(empty(self::$instance)) self::$instance = new self($host, $user, $password);

		return self::$instance;
	}

	public static function get()
	{
		if(!empty(self::$instance)) return self::$instance;

		return false;
	}

	public static function debug($flag)
	{
		if($flag === true)
		{
			self::$debug = true;
		}
		else
		{
			self::$debug = false;	
		}
	}	

	public function __construct($host, $user, $password)
	{
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
	}

	private function connect()
	{
		if($this->connection === false)
		{
			$this->connection = mysql_connect($this->host, $this->user, $this->password) 
				or trigger_error("Couldn't connect to database!", E_USER_ERROR);
		}
	}

	public function query($query, $data = null)
	{
		$this->connect();

		if(!empty($data))
		{
			foreach($data as $k=>$v)
			{
				$data[$k] = mysql_real_escape_string($v);
			}
		}

		$sql = vsprintf($query, $data);
		
		#echo "$sql<br />";
		
		$result = mysql_query($sql, $this->connection);

		if($result == false)
		{
			trigger_error(mysql_error());
		}
		else
		{
			list($first, $rest) = explode(' ', $sql, 2);
			if(strtolower(trim($first)) == 'select')
			{
				return $this->format($result);
			}
			
			return true;
		}

		return false;
	}

	private function format($result)
	{
		if(empty($result) || !is_resource($result)) return false;

		if(mysql_num_rows($result) == 1)
		{
			return mysql_fetch_assoc($result);
		}
		else
		{
			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $row;
			}
		}

		return $return;
	}

	public function escape($var, $mode=NULL)
	{
		if($mode !== NULL)
		{
			switch($mode)
			{
				case 'text':
					$var = (string)$var;
					break;
				case 'integer':
					$var = (integer)$var;
					break;
				case 'boolean':
					$var = (boolean)$var;
					break;
				default:
			}
		}
		
		if(!empty($this->connection))
		{
			return mysql_real_escape_string($var);
		}
		else
		{
			return mysql_escape_string($var);
		}
	}

	public function quote($var,$mode=NULL)
	{
		return "'".$this->escape($var,$mode)."'";
	}
	
	public function insert_id()
	{
		return mysql_insert_id();
	}		
}
?>