<?php
	function sqlite_table_exists($db, $mytable)
	{
		$result = sqlite_query($db,"SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='$mytable'");
		$count = intval(sqlite_fetch_single($result));
		return $count > 0;
	}
	
	function sqlite_set_var($db, $name, $value)
	{
		$sql = sprintf("UPDATE var SET value='%s', last_update='%d' WHERE name='%s';",
			sqlite_escape_string($value),
			time(),
			sqlite_escape_string($name));
		$ret = sqlite_query($db, $sql);
		
		if(sqlite_changes($db) <= 0)
		{
			$sql = sprintf("INSERT INTO var (name, value, last_update) VALUES('%s', '%s', '%d');",
				sqlite_escape_string($name),
				sqlite_escape_string($value),
				time());
			$ret = sqlite_query($db, $sql);
		}
		
		if($ret === false)
			return false;
		else
			return true;
	}
	
	function sqlite_get_var($db, $name, &$last_update)
	{
		$sql = sprintf("SELECT value, last_update FROM var WHERE name='%s';",
			sqlite_escape_string($name));
		$res = sqlite_array_query($db, $sql, SQLITE_ASSOC);
		$res = array_shift($res);
		
		if($res)
		{
			$last_update = $res['last_update'];
			return $res['value'];
		}
		else
		{
			return false;
		}
	}
	
	function remove_ctrl($str)
	{
		$str = ereg_replace("[\x01-\x1f\x7f]", '', $str);
		$str = trim($str);
		return $str;
	}
	
	function upto($str)
	{
		return preg_replace('/(.*)\/[^\/]+[\/]?$/', '$1', $str);
	}
	
	function clean_utf8(&$str)
	{
		$len = strlen($str);
		$padding_char = '?';
		
		for($i=0; $i < $len; )
		{
			if((ord($str[$i]) & 0x80) == 0x00)
			{
				if(!($str[$i] >= ' ' && $str[$i] <= '~') && ($str[$i] != "\t") && ($str[$i] != "\n") && ($str[$i] != "\r"))
				{
					$str[$i] = $padding_char;
				}
				$i++;
			}
			else if((ord($str[$i]) & 0xe0) == 0xc0)
			{
				if($i+1 < $len)
				{
					if((ord($str[$i+1]) & 0xc0) != 0x80)
					{
						$str[$i] = $padding_char;
						$i++;
					}
					else
					{
						$i+=2;
					}
				}
				else
				{
					$str[$i] = $padding_char;
					$i++;
				}
			}
			else if((ord($str[$i]) & 0xf0) == 0xe0)
			{
				if($i+2 < $len)
				{
					if(((ord($str[$i+1]) & 0xc0) != 0x80) || ((ord($str[$i+2]) & 0xc0) != 0x80))
					{
						$str[$i] = $padding_char;
						$i++;
					}
					else
					{
						$i+=3;
					}
				}
				else
				{
					$str[$i] = $padding_char;
					$i++;
				}
			}
			else
			{
				$str[$i] = $padding_char;
				$i++;
			}
		}
	}
	
	function _array_combine($key, $value)
	{
		$ret = array();
		$n = count($key);
		
		for($i=0; $i<$n; $i++)
			$ret[$key[$i]] = $value[$i];
		
		return $ret;
	}
	
	function remove_ctrl_walk(&$item, $key)
	{
		$item = remove_ctrl($item);
	}
	
	function add_prefix_walk(&$item, $key, $prefix)
	{
		$item = $prefix.$item;
	}
	
	function sqlite_escape_string_walk(&$item, $key)
	{
		$item = sqlite_escape_string($item);
	}
?>