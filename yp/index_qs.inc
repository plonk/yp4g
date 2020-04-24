<?php
	// 検索用のSQLを生成
	// Out: $SearchSQL
	
	process_ext_qs();
	
	$SearchSQL = '';
	
	
	$WordSQL = '';
	$TypeSQL = '';
	
	if(!empty($_GET['find']))
	{
		$target = "name||' '||genre||' '||description||' '||comment||' '||track_artist||' '||track_title";
		
		$words = str_replace('　', ' ', @$_GET['find']);
		$words = explode(' ', $words);
		
		$i = 0;
		foreach($words as $word)
		{
			if($i >= 4)
				break;
			
			$len = strlen($word);
			if($len < 2 || $len > 32)
				continue;
			
			$word = remove_ctrl($word);
			
			if(preg_match('/[%_]/', $word))
				continue;
			
			if(!empty($WordSQL))
				$WordSQL .= ' AND ';
			
			$word = htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
			
			if($word[0] == '-')
			{
				$word = substr($word, 1);
				$WordSQL .= sprintf("%s NOT LIKE '%%%s%%'", $target, sqlite_escape_string($word));
			}
			else
			{
				$WordSQL .= sprintf("%s LIKE '%%%s%%'", $target, sqlite_escape_string($word));
			}
			
			$i++;
		}
	}
	
	if(!empty($_GET['type']))
	{
		$_GET['type'] = strtoupper(@$_GET['type']);
		$types = explode('|', $_GET['type']);
		
		$i = 0;
		foreach($types as $type)
		{
			if($i >= 3)
				break;
			
			$len = strlen($type);
			if($len < 3 || $len > 7)
				continue;
			
			$type = remove_ctrl($type);
			
			if(preg_match('/[%_]/', $type))
				continue;
			
			if(!empty($TypeSQL))
				$TypeSQL .= ' OR ';
			
			$TypeSQL .= sprintf("type='%s'", sqlite_escape_string($type));
			
			$i++;
		}
	}
	
	$NSSQL = "ns=''";
	
	if(!empty($_GET['ns']))
	{
		if(strstr($_GET['ns'], '|') !== false)
		{
			$nss = explode('|', $_GET['ns']);
		}
		else
		{
			$nss = str_replace('　', ' ', @$_GET['ns']);
			$nss = explode(' ', $nss);
		}
		
		$i = 0;
		foreach($nss as $ns)
		{
			if($i >= 6)
				break;
			
			$len = strlen($ns);
			if($len < 1 || $len > 32)
				continue;
			
			$ns = remove_ctrl($ns);
			
			if(empty($ns) || preg_match('/[^a-zA-Z0-9]/', $ns))
				continue;
			
			if(!empty($NSSQL))
				$NSSQL .= ' OR ';
			
			$NSSQL .= sprintf("ns='%s'", sqlite_escape_string($ns));
			
			$i++;
		}
	}
	
	if(!empty($WordSQL) && !empty($TypeSQL))
	{
		$SearchSQL = sprintf("%s AND (%s)", $WordSQL, $TypeSQL);
		
		if(!empty($NSSQL))
			$SearchSQL .= sprintf(' AND (%s)', $NSSQL);
	}
	else if(!empty($WordSQL) || !empty($TypeSQL))
	{
		$SearchSQL = '(';
		$SearchSQL .= $WordSQL;
		$SearchSQL .= $TypeSQL;
		$SearchSQL .= ')';
		
		if(!empty($NSSQL))
			$SearchSQL .= sprintf(' AND (%s)', $NSSQL);
	}
	else
	{
		$SearchSQL = $NSSQL;
	}
	
	if(!empty($SearchSQL))
		$SearchSQL = 'WHERE ('.$SearchSQL.')';

?>