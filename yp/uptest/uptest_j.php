<?php
	require_once '../yp4g.cfg.php';
	require_once '../util.php';
	
	header('Content-Type: text/html; charset=UTF-8');
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta name="robots" content="noarchive" />
<meta name="generator" content="YP4G" />
<link href="../yp.css" rel="stylesheet" type="text/css" />
<title>Uptest - <?= htmlencode($YPName) ?></title>
</head>
<body>

<h2>Upload bandwidth test</h2>

<p>
ブラウザが落ち着いてから、(読み込み完了して数秒たってから)<br />
「測定開始」を押したほうが速度が出やすいようです<br />
</p>

<div>
<?php
	print <<<EOM
<applet code="uptest.class" width="260" height="60">
<param name="Server" value="$MyDomain" />
<param name="Port" value="$UptestPort" />
<param name="PostCgi" value="$UptestObject" />
<param name="PostSize" value="$PostSize" />
<param name="RefreshInterval" value="250" />
<param name="BandWidthLimit" value="$UptestLimit" />
</applet>
EOM
?>
</div>

<div class="powered">Powered by YP4G</div>

</body>
</html>
