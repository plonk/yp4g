<?php
	require_once 'util.php';
	require_once 'yp4g.cfg.php';
	
	header('Content-Type: text/html; charset=UTF-8');
	header('Pragma: no-cache');
	header('Cache-Control: no-cache');
	header('Expires: Thu, 01 Dec 1994 16:00:00 GMT');
	
	$Remote = $_SERVER['REMOTE_ADDR'];
	$Host = @$_GET['host'];
	
	require 'index_init.inc';
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta name="robots" content="noarchive" />
<meta name="generator" content="YP4G" />
<link href="yp.css" rel="stylesheet" type="text/css" />
<title>Uptest result - <?= htmlencode($YPName) ?></title>
</head>
<body>

<div class="header">
<h2>Uptest result</h2>
</div>

<?php
	print '<div>';
	
	print '<ul>';
	
	if($Speed <= 0){
		print '<li>Max playable bitrate : <span class="rejected">No data</span></li>';
		printf('<li><a href="%s">◆再測定</a></li>', $SpeedChecker);
	}else if($Speed < $MossoLine){
		printf('<li>Max playable bitrate : <span class="slow">%d kbps</span></li>', $Speed);
		printf('<li><a href="%s">◆再測定</a></li>', $SpeedChecker);
	}else{
		printf('<li>Max playable bitrate : <span class="fast">%d kbps over</span></li>', $MossoLine);
	}
	
	print '</ul>';
	
	if($Speed < $MossoLine)
	{
		print '<ul>';
		print '<li>再測定する場合は、しばらく時間を置いてください</li>';
		print '<li>連続して測定しようとしてもエラーが出ます</li>';
		print '</ul>';
	}
	
	print '</div>';
?>

<p>
<a href="./">戻る</a>
</p>

<div class="powered">Powered by YP4G</div>

</body>
</html>