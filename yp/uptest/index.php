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

<ul>
<li><a href="uptest_j.php">Java Applet version</a></li>
<li><a href="uptest_js.php">Java Script version</a></li>
<li><a href="uptest_n.php">Normal POST version</a></li>
</ul>

<p>
<a href="../">戻る</a>
</p>

<div class="powered">Powered by YP4G</div>

</body>
</html>
