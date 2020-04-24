<?php
	// index.* 共通処理 帯域による制限
	// In: $GenrePattern, $data, $Speed, $MossoLine, $PortCheckPass, $PortCheckPassOrg
	// Out: $SpeedCheckPass, $PortCheckPass
	
	$SpeedCheckPass = false;
	$LimitMode = '';
	$PortCheckPass = $PortCheckPassOrg;
	
	if($data['limit_type'] == '')
	{
		$SpeedCheckPass = true;
		
		if($PortCheckDefOff){
			$PortCheckPass = true;
			$LimitMode = 'Free';
		}
		else
		{
			$LimitMode = 'Open';
		}
	}
	else if($data['limit_type'] == '@')
	{
		$SpeedCheckPass = true;
		$LimitMode = 'Open';
	}
	else if($data['limit_type'] == '@@')
	{
		$SpeedCheckPass = $Speed > $data['bitrate'];
		$LimitMode = 'Over';
	}
	else if($data['limit_type'] == '@@@')
	{
		$SpeedCheckPass = $Speed >= $MossoLine;
		$LimitMode = '2M Over';
	}
?>