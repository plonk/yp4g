<?php
	require_once './yp4g.cfg.php';
	require_once './util.php';
	
	header('Content-Type: text/html; charset=UTF-8');
	
	$ChannelName = trim(@$_GET['cn']);
	
	$name_md5 = md5($ChannelName);
	$db_name = $ChatDBBase.$name_md5.'.db';

	$err = '';
	
	do{
		if(empty($ChannelName))
		{
			$err = 'チャンネル名がありません.';
			break;
		}
		
		$name = @$_POST['name'];
		$message = @$_POST['message'];
		
		if((strlen($name) > 64) || (strlen($message) > 2048))
		{
			$err = '名前か本文が長すぎます.';
			break;
		}
		
		$temp = preg_replace("/[^\n]/", '', $message);
		if(strlen($temp) > 30)
		{
			$err = '改行が多すぎます.';
			break;
		}
		
/*		$db = sqlite_open($ChannelDB);
		$sql = sprintf("SELECT * FROM allow WHERE name='%s';", sqlite_escape_string($ChannelName));
		$data = sqlite_array_query($db, $sql, SQLITE_ASSOC);
		if(count($data) < 1)
		{
			$err = '配信時間外は書き込めません.';
			break;
		}*/
		
		$message = preg_replace('/[ ]+/', ' ', $message);
		$message = preg_replace("/[ ]+[\r\n]+/", "\n", $message);
		$message = str_replace("\r\n", "\n", $message);
		$message = preg_replace("/([\n]){2,}/", "\n\n", $message);
		$message = trim($message);
		
		if(strlen($message) < 2)
		{
			$err = '本文が短すぎます.';
			break;
		}
		
/*		if(!empty($_SERVER['HTTP_CACHE_CONTROL']) || !empty($_SERVER['HTTP_VIA']) || !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$err = 'PROXY規制';
			break;
		}
		
		if(gethostbyaddr($_SERVER['REMOTE_ADDR']) == $_SERVER['REMOTE_ADDR'])
		{
			$err = '逆引き不能ホスト規制';
			break;
		}
		
		if(is_proxy_bbq($_SERVER['REMOTE_ADDR']))
		{
			$err = 'BBQホスト規制';
			break;
		}*/
		
		$message = htmlencode($message);
		$message = str_replace("\n", '<br />', $message);
		$message = remove_ctrl($message);
		$message = preg_replace("|http://[^<>[:space:]]+[[:alnum:]/]|", '<a href="'.$URLCushion.'$0">$0</a>', $message);
		$message = preg_replace("/(&gt;|＞){1,2}([0-9]+)/", '<a href="#res$2">$0</a>', $message);
		
		$name = remove_ctrl($name);
		
		setcookie('name', $name, time()+60*60*24*365*10);
		
		if(empty($name))
		{
			$name = '名無しさん';
		}
		else
		{
			$name = str_replace(array('★', '◆', '●'), array('☆', '◇', '○'), $name);
			
			if(preg_match('/#(.*)$/', $name, $temp))
			{
				$trip = trip($temp[1]);
				$name = preg_replace('/(#.*)$/', '◆'.$trip, $name);
			}
			
			$name = htmlencode($name);
			$name = str_replace('fusianasan', '<b>'.gethostbyaddr($_SERVER['REMOTE_ADDR']).'</b>', $name);
		}
		
		$db = sqlite_open($db_name);
		
		if(!sqlite_table_exists($db, 'dat'))
		{
			chmod($db_name, 0666);
			
			sqlite_query($db, "CREATE TABLE dat (no INTEGER PRIMARY KEY, name TEXT, message TEXT, date TEXT, id TEXT, ip TEXT, time INTEGER);");
			sqlite_query($db, "CREATE INDEX dat_idx ON dat (no, ip);");
			sqlite_query($db, "CREATE TABLE var (name TEXT NOT NULL UNIQUE, value NOT NULL, last_update INTEGER DEFAULT '0');");
			sqlite_query($db, "CREATE INDEX var_idx ON var (name);");
			
			sqlite_set_var($db, 'name', $ChannelName);
		}
		
		sqlite_query($db, "BEGIN;");
		
		$sql = sprintf("INSERT INTO dat VALUES(NULL, '%s', '%s', '%s', '%s', '%s', '%d');",
			$name,
			$message,
			date('Y/m/d(D) H:i:s', time()),
			trip(md5($_SERVER['REMOTE_ADDR'].$Seed.date('Ymd', time()-60*60*6))),
			$_SERVER['REMOTE_ADDR'],
			time());
		sqlite_query($db, $sql);
		
		if(sqlite_changes($db) > 0)
			sqlite_set_var($db, 'dat_last_rowid', sqlite_last_insert_rowid($db));
		
		sqlite_query($db, "COMMIT;");
		
		header('Location: chat.php?cn='.urlencode($ChannelName).'#tail');
		exit;
		
	}while(0);
	
	print $err;
//	header('Location: chat.php?cn='.urlencode($ChannelName).'#tail');
?>