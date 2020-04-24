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
<script language="JavaScript">
<!--
function init(){
	var data = "riouwerflksdhfzxcvmncvzxksdjafwperyqweroiwerpwqead";
	var data1 = "";
	var data2 = "";
	var len1 = 1000/data.length;
	var len2 = <?= $PostSize ?>;
	
	for(var i=0; i<len1; i++){
		data1 += data;
	}
	for(var i=0; i<len2; i++){
		data2 += data1;
	}
	document.uptest.file.value = data2;
}
// -->
</script>
<body onload="init()">

<h2>Upload bandwidth test</h2>

<p>
ブラウザが落ち着いてから、(読み込み完了して数秒たってから)<br />
「測定開始」を押したほうが速度が出やすいようです<br />
</p>

<ul>
<li>測定するには JavaScript をオンにしてください</li>
<li>回線によっては測定に数十秒かかる場合があります</li>
</ul>

<div>
<form name="uptest" action="<?= 'http://'.$MyDomain.':'.$UptestPort.$UptestObject ?>" method="post">
<input type="submit" value="測定開始" />
<input type="hidden" name="file" value="" />
</form>
</div>

<div class="powered">Powered by YP4G</div>

</body>
</html>
