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
	
	function process_ext_qs()
	{
		$qs = $_SERVER['QUERY_STRING'];
		$pos = strpos($qs, '?');

		if($pos !== false)
		{
			$pos += 1;
			$qs = substr($qs, $pos);
			$params = explode('&', $qs);

			foreach($params as $param)
			{
				$temp = explode('=', $param);

				if(count($temp) == 2)
					$_GET[urldecode(trim($temp[0]))] = urldecode(trim($temp[1]));
			}
		}
	}
	
	function trip($seed)
	{
		$seed = mb_convert_encoding($seed, "SJIS", "UTF-8");
		$salt = substr($seed, 1, 2);
		$salt = preg_replace("/[^\.-z]/", ".", $salt);
		$salt = strtr($salt, "\x3A-\x40\x5B-\x60\x00-\x2D\x7B-\xFF", "A-Ga-f");
		return substr(crypt($seed, $salt),-10);
	}
	
	function mtime()
	{
		$temp = explode(' ', microtime());
		return $temp[1].substr($temp[0], 1);
	}
	
	function port_check($addr, $port, $use_pcp = false, $timeout = 2)
	{
		if($use_pcp)
		{
			$fp = @fsockopen($addr, $port, $errno, $errstr, $timeout);
			if($fp)
			{
				stream_set_timeout($fp, 1);
				
				fwrite($fp, "\x70\x63\x70\x0a\x04\x00\x00\x00\x01\x00\x00\x00\x68\x65\x6c\x6f\x00\x00\x00\x80");
				$id = fread($fp, 4);
				
				fclose($fp);
				
				if($id == "oleh")
					return true;
				else
					return false;
			}else
				return false;
		}
		else
		{
			$fp = @fsockopen($addr, $port, $errno, $errstr, $timeout);
			if($fp){
				fclose($fp);
				return true;
			}else{
				return false;
			}
		}
	}
	
	function htmlencode($strin)
	{
		return htmlspecialchars($strin, ENT_QUOTES, 'UTF-8');
	}
	
	function htmldecode($strin)
	{
		$entry = array_flip(get_html_translation_table(HTML_SPECIALCHARS));
		$entry['&#039;'] = "'";
		return strtr($strin, $entry);
	}
	
	function print_time($time)
	{
		$s = $time % 60;
		$time = ($time - $s) / 60;
		$m = $time % 60;
		$time = ($time - $m) / 60;
		$h = $time;
		
		return sprintf("%d:%02d", $h, $m);
	}
	
	function remove_ctrl($str)
	{
		$str = ereg_replace("[\x01-\x1f\x7f]", '', $str);
		$str = trim($str);
		return $str;
	}
	
	function is_proxy_bbq($addr)
	{
		$temp = array_reverse(preg_split("/\./", $addr));
		$ip = gethostbyname(sprintf("%s.niku.2ch.net", join(".", $temp)));
		return $ip == '127.0.0.2' ? true : false;
	}
?>
