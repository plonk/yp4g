<?php
	require_once 'reload.conf';
	require_once 'util.php';
	
	$GenrePattern  = '/^'.$GenreKeyword.'(([a-zA-Z0-9]*):)?(([\?]?)([@]*))(.*)$/';
	
	// -------------------------------------------------------------------------
	
	$path = '';
	$crr_nameid = '';
	$chlist = array();
	$nslist = array();
	
	function startElement($parser, $name, $attr)
	{
		global $chlist, $nslist, $path, $crr_nameid;
		global $GenrePattern, $IPReplaceFrom, $IPReplaceTo;
		
		$attr = array_change_key_case($attr, CASE_LOWER);
		$name = strtolower($name);
		
		$path .= '/'.$name;
		
		if($path == '/peercast/channels_found/channel')
		{
			array_walk($attr, 'remove_ctrl_walk');
			
			$attr['status'] = 'click';
			$attr['genre_org'] = $attr['genre'];
			
			if(preg_match($GenrePattern, $attr['genre'], $temp))
			{
				$attr['genre'] = $temp[6];
				$attr['limit'] = $temp[5];
				$attr['ns'] = $temp[2];
				$attr['allow'] = 1;
				
				$attr['hide_status'] = $temp[4]=='?' ? 0 : 1;
				
				if(!empty($temp[2]))
					@$nslist[$temp[2]]++;
			}
			else
			{	// 他YPからの流出か、ジャンルの書式が間違っている
				$attr['limit'] = '';
				$attr['ns'] = '';
				$attr['allow'] = 0;
			}
			
			$crr_nameid = $attr['id'];
			
			$chlist[$crr_nameid] = $attr;
		}
		else if($path == '/peercast/channels_found/channel/hits')
		{
			$attr['listeners_org'] = $attr['listeners'];
			$attr['relays_org'] = $attr['relays'];
			
			if($chlist[$crr_nameid]['hide_status'])
			{
				$attr['listeners'] = -1;
				$attr['relays'] = -1;
			}
			
			$chlist[$crr_nameid] = array_merge($chlist[$crr_nameid], $attr);
		}
		else if($path == '/peercast/channels_found/channel/hits/host')
		{
			$key = array_keys($attr);
			$value = array_values($attr);
			array_walk($key, 'add_prefix_walk', 'host_');
			$attr = _array_combine($key, $value);
			
			if($attr['host_tracker'] == 1 && $attr['host_push'] == 0)
			{
				if($attr['host_ip'] == $IPReplaceFrom)	// IP置換
					$attr['host_ip'] = $IPReplaceTo;
				
				$chlist[$crr_nameid] = array_merge($chlist[$crr_nameid], $attr);
			}
			else if(!isset($chlist[$crr_nameid]['host_ip']))
			{
				$chlist[$crr_nameid] = array_merge($chlist[$crr_nameid], $attr);
			}
		}
		else if($path == '/peercast/channels_found/channel/track')
		{
			array_walk($attr, 'remove_ctrl_walk');
			
			$key = array_keys($attr);
			$value = array_values($attr);
			array_walk($key, 'add_prefix_walk', 'track_');
			$attr = _array_combine($key, $value);
			
			$chlist[$crr_nameid] = array_merge($chlist[$crr_nameid], $attr);
		}
	}
	
	function endElement($parser, $name)
	{
		global $path;
		$path = upto($path);
	}
	
	// -------------------------------------------------------------------------
	
	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, 'startElement', 'endElement');
	
	$data = file_get_contents($StatusXMLURL) or exit('入力をオープンできませんでした.');
	
	clean_utf8($data);
	
	if (!xml_parse($xml_parser, $data))
	{
		exit(sprintf('XML エラー: %s が %d 行目で発生しました.',
			xml_error_string(xml_get_error_code($xml_parser)),
			xml_get_current_line_number($xml_parser)));
	}
	
	xml_parser_free($xml_parser);
	
	// -------------------------------------------------------------------------
	
	$db = sqlite_open($ChannelDB);
	
	sqlite_query($db, 'BEGIN;');
	
	sqlite_query($db, 'DELETE FROM allow;');
	sqlite_query($db, 'DELETE FROM deny;');
	
	$allow_cnt = 0;
	$deny_cnt = 0;
	
	foreach($chlist as $info)				// データベースにチャンネルリスト書き出し
	{
		if($info['newest'] > $DeadLine)		// 更新の無いチャンネルを飛ばす
		{
			if(isset($nslist[$info['ns']]))
				$nslist[$info['ns']]--;
			
			$chlist[$info['id']]['allow'] = 0;
			continue;
		}
		
		if(!isset($info['host_ip']) || preg_match('/:0$/', $info['host_ip']))
		{
			if(isset($nslist[$info['ns']]))
				$nslist[$info['ns']]--;
			
			$chlist[$info['id']]['allow'] = 0;
			$info['allow'] = 0;				// ポート0は載せない
		}
		
		if($info['allow'])
		{
			$sql = 'INSERT INTO allow VALUES(';
			$allow_cnt++;
		}
		else
		{
			$sql = 'INSERT INTO deny VALUES(';
			$deny_cnt++;
		}
		
		
		$info['name_raw'] = $info['name'];
		$info['name_url'] = urlencode($info['name_raw']);
		$info['name'] = htmlspecialchars($info['name_raw'], ENT_QUOTES, 'UTF-8');
		$info['genre'] = htmlspecialchars($info['genre'], ENT_QUOTES, 'UTF-8');
		$info['desc'] = htmlspecialchars($info['desc'], ENT_QUOTES, 'UTF-8');
		$info['url'] = htmlspecialchars($info['url'], ENT_QUOTES, 'UTF-8');
		$info['comment'] = htmlspecialchars($info['comment'], ENT_QUOTES, 'UTF-8');
		$info['track_artist'] = htmlspecialchars($info['track_artist'], ENT_QUOTES, 'UTF-8');
		$info['track_title'] = htmlspecialchars($info['track_title'], ENT_QUOTES, 'UTF-8');
		$info['track_album'] = htmlspecialchars($info['track_album'], ENT_QUOTES, 'UTF-8');
		$info['track_genre'] = htmlspecialchars($info['track_genre'], ENT_QUOTES, 'UTF-8');
		$info['track_contact'] = htmlspecialchars($info['track_contact'], ENT_QUOTES, 'UTF-8');
		$info['status'] = htmlspecialchars($info['status'], ENT_QUOTES, 'UTF-8');
		
		array_walk($info, 'sqlite_escape_string_walk');
		
		$sql .= sprintf("'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
			$info['id'],
			$info['host_ip'],
			$info['name'],
			$info['name_raw'],
			$info['name_url'],
			$info['bitrate'],
			$info['type'],
			$info['listeners'],
			$info['relays'],
			$info['age'],
			$info['genre'],
			$info['desc'],
			$info['url'],
			$info['comment'],
			$info['track_artist'],
			$info['track_title'],
			$info['track_album'],
			$info['track_genre'],
			$info['track_contact'],
			$info['host_direct'],
			$info['status'],
			$info['limit'],
			$info['ns']);
		
		$sql .= ');';
		
		sqlite_query($db, $sql) or print $sql."\n";
	}
	
	sqlite_set_var($db, 'allow_count', $allow_cnt);
	sqlite_set_var($db, 'deny_count', $deny_cnt);
	
	$temp = '';
	foreach($nslist as $ns => $cnt)
	{
		if($cnt <= 0)
			continue;
		
		if(!empty($temp))
			$temp .= '|';
		$temp .= $ns;
	}
	sqlite_set_var($db, 'available_ns', $temp);
	
	sqlite_query($db, 'COMMIT;');
	
	// -------------------------------------------------------------------------
	
	if(date('H:i') == '06:00')				// 更新のないホスト情報を削除
	{
		$db = sqlite_open($HostDB);
		
		$deltime = time() - 60*60*24*7;
		sqlite_query($db, "DELETE FROM host WHERE port_open_date < $deltime OR speed_date < $deltime AND speed_date > 0;");
	}
	
	// -------------------------------------------------------------------------
	
	$ch_exists = array();
	
	if(substr(date('i'), 1) == '0')			// ログ書き出し 10分ごとに処理
	{
		foreach($chlist as $info)
		{
			if($info['allow'] == 0)
				continue;
			
			$ch_exists[$info['name']]++;
			if($ch_exists[$info['name']] > 1)
				continue;
			
			$name_md5 = md5($info['name']);
			$db_name = $StatsDBBase.$name_md5.'.db';
			
			$db = sqlite_open($db_name);
			
			if(!sqlite_table_exists($db, 'log'))
			{
				chmod($db_name, 0666);
				
				sqlite_query($db, "CREATE TABLE var (name TEXT NOT NULL UNIQUE, value NOT NULL, last_update INTEGER DEFAULT '0');");
				sqlite_query($db, "CREATE INDEX var_idx ON var (name);");
				
				sqlite_query($db, "CREATE TABLE available (date_id INTEGER NOT NULL UNIQUE, year INTEGER NOT NULL, month INTEGER NOT NULL, day INTEGER NOT NULL);");
				sqlite_query($db, "CREATE INDEX available_idx ON available (date_id, year, month, day);");
				
				sqlite_query($db, "CREATE TABLE log (date_id INTEGER NOT NULL, time INTEGER NOT NULL, listeners INTEGER, relays INTEGER, age INTEGER, same INTEGER, id TEXT, ip TEXT, bitrate INTEGER, type TEXT, genre_org TEXT, genre TEXT, ns TEXT, description TEXT, url TEXT, comment TEXT, track_artist TEXT, track_title TEXT, track_album TEXT, track_genre TEXT, track_contact TEXT);");
				sqlite_query($db, "CREATE INDEX log_idx ON log (date_id, time, same, id, ip);");
				
				sqlite_set_var($db, 'name', $info['name']);
				sqlite_set_var($db, 'name_id', $name_md5);
			}
			
			$date_id = date('Ymd');
			
			$sql = sprintf("INSERT OR IGNORE INTO available VALUES('%d', '%d', '%d', '%d');",
				$date_id, date('Y'), date('n'), date('j'));
			sqlite_query($db, $sql);
			
			if(sqlite_changes($db) > 0)
				sqlite_set_var($db, 'available_last_rowid', sqlite_last_insert_rowid($db));
			
			$same = 0;
			
			if(($rowid = sqlite_get_var($db, 'log_last_full_rowid', $t)) !== false)
			{
				$sql = sprintf("SELECT * FROM log WHERE rowid=%d;", $rowid);
				$data = sqlite_array_query($db, $sql, SQLITE_ASSOC);
				$data = array_shift($data);
				
				$same = date('Ymd', $t) == $date_id &&
						$data['id'] == $info['id'] &&
						$data['ip'] == $info['host_ip'] &&
						$data['bitrate'] == $info['bitrate'] &&
						$data['type'] == $info['type'] &&
						$data['genre_org'] == $info['genre_org'] &&
						$data['description'] == $info['desc'] &&
						$data['url'] == $info['url'] &&
						$data['comment'] == $info['comment'] &&
						$data['track_artist'] == $info['track_artist'] &&
						$data['track_title'] == $info['track_title'] &&
						$data['track_album'] == $info['track_album'] &&
						$data['track_genre'] == $info['track_genre'] &&
						$data['track_contact'] == $info['track_contact'];
				
				$same = $same ? 1:0;
			}
			
			array_walk($info, 'sqlite_escape_string_walk');
			
			$changed = 0;
			
			if($same != 0)
			{
				$sql = sprintf("INSERT INTO log (date_id, time, listeners, relays, age, same) VALUES('%d', '%d', '%d', '%d', '%d', '%d');",
					$date_id, time(),
					$info['listeners'],
					$info['relays'],
					$info['age'],
					$same
					);
				sqlite_query($db, $sql);
				
				$rowid = sqlite_last_insert_rowid($db);
				$changed = sqlite_changes($db);
			}
			else
			{
				$sql = sprintf("INSERT INTO log VALUES('%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');",
					$date_id,
					time(),
					$info['listeners'],
					$info['relays'],
					$info['age'],
					$same,
					$info['id'],
					$info['host_ip'],
					$info['bitrate'],
					$info['type'],
					$info['genre_org'],
					$info['genre'],
					$info['ns'],
					$info['desc'],
					$info['url'],
					$info['comment'],
					$info['track_artist'],
					$info['track_title'],
					$info['track_album'],
					$info['track_genre'],
					$info['track_contact']
					);
				sqlite_query($db, $sql);
				
				$rowid = sqlite_last_insert_rowid($db);
				$changed = sqlite_changes($db);
				
				if($changed > 0)
					sqlite_set_var($db, 'log_last_full_rowid', $rowid);
			}
			
			if($changed > 0)
				sqlite_set_var($db, 'log_last_rowid', $rowid);
		}
	}
?>