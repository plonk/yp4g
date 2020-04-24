<?php
	require_once './yp4g.cfg.php';
	require_once './util.php';
	
	header('Content-Type: text/html; charset=UTF-8');
	
	$ChannelName = trim(@$_GET['cn']);
	
	if(is_numeric(@$_GET['date']))
		$Date = $_GET['date'];
	else
		$Date = date('Ymd');
	
	$name_md5 = md5($ChannelName);
	$db_name = $StatsDBBase.$name_md5.'.db';
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta name="robots" content="noarchive" />
<meta name="generator" content="YP4G" />
<link href="getgmt.css" rel="stylesheet" type="text/css" />
<title><?= htmlencode($ChannelName) ?> - Statistics - <?= htmlencode($YPName) ?></title>
</head>
<body>

<?php
	if(file_exists($db_name))
	{
		print '<div class="main">';
		print '<div>';
		
		print '<h2>'.htmlencode($ChannelName).' - Statistics</h2>';
		
		if(strlen($Date) == 8)
		{
			print '<h4>';
			print substr($Date, 0, 4).'/'.substr($Date, 4, 2).'/'.substr($Date, 6, 2);
			print '</h4>';
		}
		
		print '<table class="log">';
		print '<tr><th>Time</th><th>Age</th><th>Status</th><th align="left">Description</th></tr>';
		
		$db = sqlite_open($db_name);
		
		$sql = sprintf("SELECT * FROM log WHERE date_id='%d';", $Date);
		$datas = sqlite_array_query($db, $sql, SQLITE_ASSOC);
		
		$before = 0;
		
		foreach($datas as $data)
		{
			if($before == 0)
				$before = $data['time'];
			
			if($data['time'] - $before > 60*11)
				print '<tr><td colspan="4">&nbsp;</td></tr>';
			
			print '<tr>';
			
			print '<td class="rn">';
			print date('H:i', $data['time']);
			print '</td>';
			
			print '<td class="rn">';
			print print_time($data['age']);
			print '</td>';
			
			print '<td class="cn">';
			print $data['listeners'].' / '.$data['relays'];
			print '</td>';
			
			if($data['same'])
			{
				print '<td class="n">';
				print "''";
				print '</td>';
			}
			else
			{
				$data['genre'] = htmlspecialchars($data['genre'], ENT_QUOTES, 'UTF-8');
				$data['description'] = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
				$data['track_artist'] = htmlspecialchars($data['track_artist'], ENT_QUOTES, 'UTF-8');
				$data['track_title'] = htmlspecialchars($data['track_title'], ENT_QUOTES, 'UTF-8');
				$data['comment'] = htmlspecialchars($data['comment'], ENT_QUOTES, 'UTF-8');
				
				print '<td class="n">';
				
				if(!empty($data['genre']) && !empty($data['description'])){
					print '['.$data['genre'].' - '.$data['description'].'] ';
				}else if(!empty($data['genre'])){
					print '['.$data['genre'].'] ';
				}else if(!empty($data['description'])){
					print '['.$data['description'].'] ';
				}
				
				if(!empty($data['track_artist']) && !empty($data['track_title'])){
					print 'Playing: '.$data['track_artist'].' - '.$data['track_title'].' ';
				}else if(!empty($data['track_artist'])){
					print 'Playing: '.$data['track_artist'].' ';
				}else if(!empty($data['track_title'])){
					print 'Playing: '.$data['track_title'].' ';
				}
				
				if(!empty($data['comment'])){
					print '「'.$data['comment'].'」';
				}
				
				print '</td>';
			}
			
			print '</tr>';
			
			$before = $data['time'];
		}
		
		print '</table>';
		print '</div>';
		
		print '<p><a href="./">戻る</a></p>';
		
		print '<div class="powered">Powered by YP4G</div>';
		
		print '</div>';
		
		
		$sql = "SELECT * FROM available;";
		$dates = sqlite_array_query($db, $sql, SQLITE_ASSOC);
		
		$available = array();
		
		foreach($dates as $date)
		{
			$available[$date['year']][$date['month']][$date['day']] = 1;
		}
		
		$month_cnt = ($dates[count($dates)-1]['year'] - $dates[0]['year'])*12 + $dates[count($dates)-1]['month'] - $dates[0]['month'] + 1;
		$year = $dates[0]['year'];
		$month = $dates[0]['month'];
		
		print '<div class="side">';
		
		for($i=0; $i<$month_cnt; $i++)
		{
			if(isset($available[$year][$month]))
			{
				print '<table class="calendar">';
				
				$t = mktime(0, 0, 0, $month, 1, $year);
				$first = date('w', $t);
				$last = date('t', $t);
				
				printf('<tr><td class="idx" colspan="7">%d/%02d</td></tr>', $year, $month);
				
				for($j=0; $j<$first; $j++)
				{
					if($j == 0)
						print '<tr>';
					print '<td>&nbsp;</td>';
				}
				
				for($day=1; $day<=$last; $day++, $j++)
				{
					if($j%7 == 0)
						print '<tr>';
					
					if(isset($available[$year][$month][$day]))
						print '<td class="a"><a href="?cn='.urlencode($ChannelName).'&amp;date='.sprintf('%d%02d%02d', $year, $month, $day).'">'.$day.'</a></td>';
					else
						print '<td class="b">'.$day.'</td>';
					
					if($j%7 == 6)
						print '</tr>';
				}
				
				$j %= 7;
				for(; $j>0 && $j<7; $j++)
				{
					print '<td>&nbsp;</td>';
					if($j == 6)
						print '</tr>';
				}
				
				print '</table>';
			}
			
			$month++;
			if($month > 12)
			{
				$year++;
				$month = 1;
			}
		}
		
		print '</div>';
	}
?>

</body>
</html>