<?php
	// index.* 共通処理 ホスト別情報読み込み＋ポートチェック
	// In: $Remote, $Host, $SpeedCheck, $PortCheck
	// Out: $Speed, $PortCheckPass, $Port
	
	$DefaultPort = 7144;
	
	$hostdb = sqlite_popen($HostDB);
	
	$sql = "SELECT * FROM host WHERE ip='$Remote';";
	$data = sqlite_array_query($hostdb, $sql, SQLITE_ASSOC);
	$row = count($data);
	
	if($row == 1)
		$data = array_shift($data);
	
	if($SpeedCheck)
	{
		if($row == 1)
			$Speed = $data['speed'];
		else
			$Speed = 0;
	}
	else
	{
		$Speed = 100000;
	}
	
	$Port = $DefaultPort;
	
	if(empty($Host))
	{
		$Port = @$_GET['port'];
		
		if(!is_numeric($Port))
		{
			if($row == 1)
				$Port = $data['port_no'];
			else
				$Port = $DefaultPort;
		}
	}
	else
	{
		if(preg_match('/:([0-9]+)/', $Host, $Temp))
		{
			if(is_numeric($Temp[1]))
				$Port = $Temp[1];
		}
	}
	
	if($PortCheck)
	{
		$PortCheckPass = false;
		
		if($Port > 0 && $Port < 65536)
		{
			if($row == 1)
				$date_diff = time() - $data['port_open_date'];
			else
				$date_diff = 0;
			
			if(($row == 0) ||
			   ($data['port_open'] != 0 && $date_diff > $CheckIntervalPass) ||
			   ($data['port_open'] == 0 && $date_diff > $CheckIntervalReject) ||
			   ($data['port_no'] != $Port && $date_diff > $CheckIntervalReject)
			  )
			{
				$PortCheckPass = port_check($Remote, $Port, true);
				
				if($data)
					$sql = sprintf("UPDATE host SET port_no='%d', port_open='%d', port_open_date='%d' WHERE ip='%s';", $Port, $PortCheckPass, time(), $Remote);
				else
					$sql = sprintf("INSERT INTO host (ip, port_no, port_open, port_open_date) VALUES('%s', '%d', '%d', '%d');", $Remote, $Port, $PortCheckPass, time());
				
				sqlite_query($hostdb, $sql);
			}
			else
			{
				if($data['port_no'] == $Port)
					$PortCheckPass = $data['port_open'];
			}
		}
	}
	else
	{
		$PortCheckPass = true;
	}
	
	$PortCheckPassOrg = $PortCheckPass;
?>