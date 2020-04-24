<?php
	require_once './yp4g.cfg.php';
	require_once './util.php';
	
	header('Content-Type: text/html; charset=UTF-8');
	header('Pragma: no-cache');
	header('Cache-Control: no-cache');
	header('Expires: Thu, 01 Dec 1994 16:00:00 GMT');
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta name="robots" content="noarchive" />
<meta name="generator" content="YP4G" />
<link href="yp.css" rel="stylesheet" type="text/css" />
<title>Denied channels - <?= htmlencode($YPName) ?></title>
</head>
<body>

<div class="header">
<h2>Denied channels</h2>
</div>

<?php
	$db = sqlite_open($ChannelDB);
	
	$sql = 'SELECT * FROM deny ORDER BY age;';
	$result = sqlite_unbuffered_query($sql, $db, SQLITE_ASSOC);
	
	print '<div><table class="chlist">';
	print '<tr><th>Channel</th><th>IP:Port</th></tr>';
	
	$count = 0;
	while($data = sqlite_fetch_array($result, SQLITE_ASSOC))		// チャンネルリスト表示
	{
		print '<tr><td>';
		
		printf('<span class="name">%s</span>', $data['name']);
		
		if(!empty($data['genre']))
			printf('<br />Genre: %s', $data['genre']);
		else
			print '<br /> Genre: (Nothing)';
		
		print '</td><td>';
		
		if(!empty($data['ip']))
			print $data['ip'];
		else
			print '&nbsp;';
		
		print '</td></tr>';
		
		$count++;
	}
	
	if($count == 0)
		print '<tr><td colspan="2" align="center">Channel not found</td></tr>';
	
	print '</table></div>';
?>

<p>
<a href="./">戻る</a>
</p>

<div class="powered">Powered by YP4G</div>

</body>
</html>