<?php
	require_once './yp4g.cfg.php';
	require_once './util.php';
	
	header('Content-Type: text/html; charset=UTF-8');
	
	$ChannelName = trim(@$_GET['cn']);
	
	$name_md5 = md5($ChannelName);
	$db_name = $ChatDBBase.$name_md5.'.db';
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta name="robots" content="noarchive" />
<meta name="generator" content="YP4G" />
<link href="chat.css" rel="stylesheet" type="text/css" />
<title><?= htmlencode($ChannelName) ?> - Chat - <?= htmlencode($YPName) ?></title>
</head>
<body>

<div class="header">
<h2><?= htmlencode($ChannelName) ?> - Chat <a href="#tail">▼</a></h2>
</div>

<?php
	if(file_exists($db_name))
	{
		$db = sqlite_open($db_name);
		
		if(($rowid = sqlite_get_var($db, 'dat_last_rowid', $t)) === false)
			$rowid=0;
		else
			$rowid--;
		
		$rowid -= $ShowResNum;
		
		print '<p><a href="./">戻る</a>';
		
		if($rowid < 0 || @$_GET['mode'] == 'all')
		{
			if(@$_GET['mode'] == 'all')
				printf(' <a href="?cn=%s">最新%d件</a>', urlencode($ChannelName), $ShowResNum);
			
			$sql = sprintf("SELECT * FROM dat;");
		}
		else
		{
			printf(' <a href="?cn=%s&amp;mode=all">全て表示</a>', urlencode($ChannelName));
			
			$rowid++;
			$sql = sprintf("SELECT * FROM dat LIMIT -1 OFFSET %d;", $rowid);
		}
		print '</p>';
		
		$result = sqlite_unbuffered_query($db, $sql);
		
		while($data = sqlite_fetch_array($result, SQLITE_ASSOC))
		{
			print '<dl id="res'.$data['no'].'">';
			 print '<dt>';
			  print '<span class="no">'.$data['no'].'</span> <span class="na">'.$data['name'].'</span> <span class="da">'.$data['date'].'</span> <span class="id">ID:'.$data['id'].'</span>';
			 print '</dt>';
			 print '<dd>';
			  print $data['message'];
			 print '</dd>';
			print '</dl>';
		}
	}
?>

<div id="tail"></div>

<div class="sendform">
<form method="post" action="chat2.php?<?= htmlencode(@$_SERVER['QUERY_STRING']) ?>">
  <table>
    <tr>
      <td>
        <input type="submit" value=" Send " />
        Name: <input type="text" name="name" size="25" value="<?= htmlencode(@$_COOKIE['name']) ?>" />
      </td>
    </tr>
    <tr>
      <td><textarea name="message" cols="60" rows="5"></textarea></td>
    </tr>
  </table>
</form>
</div>

<p>
<a href="./">戻る</a>
</p>

<div class="powered">Powered by YP4G</div>

</body>
</html>
