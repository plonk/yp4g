<?php
	$YPName = 'YP4G PeerCast Yellow Pages';			// YPの名前 適当に
	
	$MyDomain = 'www.mydomain.com';		// 外から見た時の自分のアドレス (グローバルIPとかDDNSで取得したドメイン名とか)
	
	$ShowChannels = 10;					// １ページに表示するチャンネル数
	
	$URLCushion = 'http://get.nu/';		// 空にすると無効
	
	$PortCheck = true;					// ポートチェックの有無
	$PortCheckDefOff = true;			// モード選択記号がない場合にポートチェックを使うか否か
	$SpeedCheck = true;					// 帯域チェックの有無
	
	$MossoLine = 2000;					// 低速・高速回線の判断基準
	
	$ChannelDB = '/var/yp4g/channel.db';
	$HostDB = '/var/yp4g/host.db';
	$StatsDBBase = '/var/yp4g/stats/';
	$ChatDBBase = '/var/yp4g/chat/';
	
	$SpeedChecker = 'http://'.$MyDomain.'/uptest/';	// 帯域測定ページのURL (フル)
	
	$CheckIntervalPass = 60*11;			// ポートチェックの間隔 Pass時
	$CheckIntervalReject = 15;		// ポートチェックの間隔 Rejected時
	
	
	$UptestLimit = $MossoLine * 1.5;	// アップロード時の帯域制限
	$UptestInterval = 15;				// 帯域測定の最小間隔
	$UptestPort = 443;					// 帯域測定サーバのポート
	$UptestObject = '/uptest.cgi';		// uptest_srv.cfg で指定したものと同じ
	$PostSize = 250;					// 測定に使用するデータサイズ (KB)
	
	$ShowResNum = 25;					// デフォルトで表示するレス数 (chat.php)
	$Seed = 'asklwekjqw';				// テキトウな文字列 (要変更) (chat.php)
	
	$PortCheckDefOff = $PortCheck && $PortCheckDefOff;
	if($ShowChannels <= 0)
		$ShowChannels = 1;
?>