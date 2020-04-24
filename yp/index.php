<?php
	require_once 'util.php';
	require_once 'yp4g.cfg.php';
	
	$starttime = mtime();
	
	header('Content-Type: text/html; charset=UTF-8');
	header('Pragma: no-cache');
	header('Cache-Control: no-cache');
	header('Expires: Thu, 01 Dec 1994 16:00:00 GMT');
	
	require 'index_qs.inc';
	
	$Remote = $_SERVER['REMOTE_ADDR'];
	$Host = @$_GET['host'];
	$Mode = @$_GET['mode'];
	
	if(is_numeric(@$_GET['from']) && $_GET['from'] >= 0)
		$From = $_GET['from'];
	else
		$From = 0;
	
	require 'index_init.inc';
	
	if(empty($Host))
		$Host = 'localhost:'.$Port;
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta name="robots" content="noarchive" />
<meta name="generator" content="YP4G" />
<link href="yp.css" rel="stylesheet" type="text/css" />
<title><?= htmlencode($YPName) ?></title>
</head>
<body>

<div class="header">
<h2><?= htmlencode($YPName) ?> <a href="#foot">▼</a></h2>
</div>

<?php
	if($Mode != 'setting')
	{
		// -----------------------------------------------------------------------------
		// 検索フォーム
		
		print '<div><form method="get" action=""><p>';
		 printf('<input type="text" name="find" size="24" value="%s" />', htmlencode(@$_GET['find']));
		 print '<select name="type">';
		  print '<option value="">All</option>';
		  print '<option value="OGG|MP3|WMA">Audio</option>';
		  print '<option value="WMV|NSV|OGM">Video</option>';
		  print '<option value="WMV">WMV</option>';
		  print '<option value="OGG">OGG</option>';
		  print '<option value="MP3">MP3</option>';
		  print '<option value="WMA">WMA</option>';
		  print '<option value="NSV">NSV</option>';
		  print '<option value="OGM">OGM</option>';
		  print '<option value="RAW">RAW</option>';
		  if(!empty($_GET['type']))
		  {
			print '<option value=""></option>';
			
			if($_GET['type'] == 'OGG|MP3|WMA')
				print '<option value="OGG|MP3|WMA" selected="selected">Audio</option>';
			else if($_GET['type'] == 'WMV|NSV|OGM')
				print '<option value="WMV|NSV|OGM" selected="selected">Video</option>';
			else
				printf('<option value="%s" selected="selected">%s</option>', htmlencode($_GET['type']), htmlencode($_GET['type']));
		  }
		 print '</select>';
		 printf('<input type="hidden" name="host" value="%s" />', htmlencode(@$_GET['host']));
		 printf('<input type="hidden" name="port" value="%s" />', htmlencode(@$_GET['port']));
		 printf('<input type="hidden" name="ns" value="%s" />', htmlencode(@$_GET['ns']));
		 print '<input type="submit" value="Search" />';
		print '</p></form></div>';
		
		// -----------------------------------------------------------------------------
		// チャンネル情報取得
		
		$db = sqlite_popen($ChannelDB);
		
		if(!$PortCheckPassOrg && !empty($SearchSQL))
			$SearchSQL .= " AND (limit_type='')";
		
		$sql = "SELECT * FROM allow $SearchSQL ORDER BY age;";
		$result = sqlite_array_query($db, $sql, SQLITE_ASSOC);
		
		print '<div><table class="chlist" summary="Channel list">';
		print '<tr><th>&gt;&gt;</th><th>Channel</th><th>Status</th><th>Type</th></tr>';
		
		$show_count = 0;
		
		for($i=$From, $j=0; $i<count($result) && $j<$ShowChannels; $i++, $j++)	// チャンネルリスト表示
		{
			$data = $result[$i];
			
			require './index_speed.inc';
			if(!$PortCheckPass)
				continue;
			
			// -----------------------------------------------------------------------------
			
			print '<tr>';
			
			// -----------------------------------------------------------------------------
			// Playボタン
			
			print '<td class="cn">';
			
			if($PortCheckPass && $SpeedCheckPass)
			{
				if(!empty($_GET['port']))
					print '<a href="peercast://pls/'.$data['id'].'?tip='.$data['ip'].'"><span class="play">Play</span></a>';
				else
					print '<a href="http://'.$Host.'/pls/'.$data['id'].'?tip='.$data['ip'].'"><span class="play">Play</span></a>';
				
				if($data['direct'])
					print '<br /><a href="http://'.$data['ip'].'/pls/'.$data['id'].'"><span class="direct">Direct</span></a>';
			}
			else
			{
				print '<a href="'.$SpeedChecker.'"><span class="check">Check</span></a>';
			}
			
			print '</td>';
			
			// -----------------------------------------------------------------------------
			// 詳細
			
			print '<td>';
			
			$temp = parse_url($data['url']);
			if(isset($temp['scheme']) && $temp['scheme'] == 'http')
				print '<a href="'.$URLCushion.$data['url'].'"><span class="name">'.$data['name'].'</span></a>';
			else
				print '<span class="name">'.$data['name'].'</span>';
			
			if(!empty($data['ns']))
				print ' &lt;'.$data['ns'].'&gt;';
			
			if(!empty($data['genre']) && !empty($data['description']))
				print '<br />['.$data['genre'].' - '.$data['description'].'] ';
			else if(!empty($data['genre']))
				print '<br />['.$data['genre'].'] ';
			else if(!empty($data['description']))
				print '<br />['.$data['description'].'] ';
			
			if(!empty($data['track_album']))
				print '&lt;'.$data['track_album'].'&gt;';
			
			if(!empty($data['track_title']) || !empty($data['track_artist']))
			{
				print '<br />Playing: ';
				
				$flag = false;
				
				$temp = parse_url($data['track_contact']);
				if(isset($temp['scheme']) && $temp['scheme'] == 'http')
				{
					print '<a href="'.$URLCushion.$data['track_contact'].'">';
					$flag = true;
				}
				
				if(!empty($data['track_artist']) && !empty($data['track_title']))
					print $data['track_artist'].' - '.$data['track_title'];
				else if(!empty($data['track_title']))
					print $data['track_title'];
				else
					print $data['track_artist'];
				
				if($flag)
					print '</a>';
			}
			
			if(!empty($data['comment']))
				print '<br />「'.$data['comment'].'」';
			
			print '</td>';
			
			// -----------------------------------------------------------------------------
			// ステータス
			
			print '<td class="cn">';
			
			$temp = $data['name_url'];
			print $data['listeners'].' / '.$data['relays'].'<br />';
			print '<a href="chat.php?cn='.$temp.'"><span class="board">(...)</span></a> ';
			print '<a href="getgmt.php?cn='.$temp.'"><span class="board">Stats</span></a>';
			print '<br />';
			print print_time($data['age']);
			
			print '</td>';
			
			// -----------------------------------------------------------------------------
			// タイプ
			
			print '<td class="cn">';
			
			print $data['type'];
			print '<br />'.$data['bitrate'].' kbps';
			
			if($SpeedCheck)
				print '<br />'.$LimitMode;
			
			print '</td>';
			
			// -----------------------------------------------------------------------------
			
			print '</tr>';
			
			$show_count++;
		}
		
		if($show_count > 0){
			printf('<tr><td colspan="4">Results: %d - %d of about %d</td></tr>', $From+1, $i, count($result));
		}else{
			print '<tr><td colspan="4" align="center">Channel not found</td></tr>';
		}
		
		print '</table></div>';
		
		// -----------------------------------------------------------------------------
		// ページセレクタ
		
		if(count($result) > $ShowChannels)
		{
			print '<div>';
			
			$crr = $From / $ShowChannels;
			$all = ceil(count($result) / $ShowChannels);
			
			$link_tmp = sprintf('?host=%s&amp;port=%s&amp;find=%s&amp;type=%s&amp;ns=%s',
				urlencode(@$_GET['host']),
				urlencode(@$_GET['port']),
				urlencode(@$_GET['find']),
				urlencode(@$_GET['type']),
				urlencode(@$_GET['ns'])
				);
			
			if($crr > 0)
			{
				printf('<a href="%s&amp;from=%d">&lt;&lt;&lt;</a> ',
					$link_tmp,
					floor($crr-1) * $ShowChannels
					);
			}
			else
			{
				print '&lt;&lt;&lt; ';
			}
			
			for($i=0; $i<$all; $i++)
			{
				if($i != $crr)
				{
					printf('<a href="%s&amp;from=%d">%d</a> ',
						$link_tmp,
						$i * $ShowChannels,
						$i+1
						);
				}
				else
				{
					print ($i+1).' ';
				}
			}
			
			if($crr < $all-1)
			{
				printf('<a href="%s&amp;from=%d">&gt;&gt;&gt;</a> ',
					$link_tmp,
					floor($crr+1) * $ShowChannels
					);
			}
			else
			{
				print '&gt;&gt;&gt;';
			}
			
			print '</div>';
		}
		
		// -----------------------------------------------------------------------------
		// ホスト別情報
		
		print '<div class="setting">';
		
		printf('<a href="?mode=setting&amp;host=%s&amp;ns=%s"><span class="setting">[Settings]</span></a>', urlencode($Host), urlencode(@$_GET['ns']));
		
		if($PortCheck){
			if($PortCheckPassOrg)
				print '<br />Port check : <span class="pass">Pass</span>';
			else
				print '<br />Port check : <span class="rejected">Rejected</span>';
		}
		
		if($SpeedCheck && $PortCheckPassOrg)
		{
			if($Speed <= 0)
			{
				print '<br />Max playable bitrate : <span class="rejected">No data</span>';
				printf('<br /><a href="%s">◆帯域チェック</a>', $SpeedChecker);
			}
			else if($Speed < $MossoLine)
			{
				printf('<br />Max playable bitrate : <span class="slow">%d kbps</span>', $Speed);
				printf('<br /><a href="%s">◆帯域チェック</a>', $SpeedChecker);
			}
			else
			{
				printf('<br />Max playable bitrate : <span class="fast">%d kbps over</span>', $MossoLine);
			}
		}
		
		// -----------------------------------------------------------------------------
		
		$span = (int)((mtime() - $starttime) * 1000);
		print '<br />Generate time : '.$span.' msec';
		
		print '</div>';
	}
	else
	{
		// -----------------------------------------------------------------------------
		// 設定フォーム表示
		
		print '<div class="setting">';
		
		print '<form method="get" action=""><p>';
		print '◆Play のリンクを http://IP:PORT/pls/ID 形式にする場合';
		print '<table summary="Settings"><tr><td class="nb" align="right">';
		 print '名前空間';
		print '</td><td class="nb">';
		 printf('<input type="text" name="ns" size="24" value="%s" />', htmlencode(@$_GET['ns']));
		print '</td><td class="nb">';
		 print '| (パイプ)かスペースで区切る事で複数指定できます';
		print '</td></tr><tr><td class="nb" align="right">';
		 print 'IP:Port';
		print '</td><td class="nb">';
		 printf('<input type="text" name="host" size="24" value="%s" />', htmlencode($Host));
		print '</td><td class="nb">';
		 print '<input type="submit" value="  OK  " />';
		print '</td></tr></table>';
		print '</p></form>';
		
		print '<form method="get" action=""><p>';
		print '◆Play のリンクを peercast://pls/ID 形式にする場合';
		print '<table summary="Settings"><tr><td class="nb" align="right">';
		 print '名前空間';
		print '</td><td class="nb">';
		 printf('<input type="text" name="ns" size="24" value="%s" />', htmlencode(@$_GET['ns']));
		print '</td><td class="nb">';
		 print '| (パイプ)かスペースで区切る事で複数指定できます';
		print '</td></tr><tr><td class="nb" align="right">';
		 print 'Port No.';
		print '</td><td class="nb">';
		 printf('<input type="text" name="port" size="8" value="%d" />', htmlencode($Port));
		 print ' <input type="submit" value="  OK  " />';
		print '</td><td class="nb">';
		 print '&nbsp;';
		print '</td></tr></table>';
		print '</p></form>';
		
		print '</div>';
	}
?>

<div class="powered">Powered by YP4G</div>

<div id="foot"></div>

</body>
</html>
