<?php
	require_once 'util.php';
	require_once 'yp4g.cfg.php';
	
	$chdb = sqlite_open($ChannelDB);
	if(!sqlite_table_exists($chdb, 'allow'))
	{
		chmod($ChannelDB, 0666);
		
		sqlite_query($chdb, "CREATE TABLE allow (id TEXT, ip TEXT, name TEXT, name_raw TEXT, name_url TEXT, bitrate INTEGER, type TEXT, listeners INTEGER, relays INTEGER, age INTEGER, genre TEXT, description TEXT, url TEXT, comment TEXT, track_artist TEXT, track_title TEXT, track_album TEXT, track_genre TEXT, track_contact TEXT, direct INTEGER, status TEXT, limit_type TEXT, ns TEXT);");
		sqlite_query($chdb, "CREATE INDEX allow_idx ON allow (id, name, name_raw, bitrate, type, listeners, relays, age, limit_type, ns);");
		sqlite_query($chdb, "CREATE TABLE deny (id TEXT, ip TEXT, name TEXT, name_raw TEXT, name_url TEXT, bitrate INTEGER, type TEXT, listeners INTEGER, relays INTEGER, age INTEGER, genre TEXT, description TEXT, url TEXT, comment TEXT, track_artist TEXT, track_title TEXT, track_album TEXT, track_genre TEXT, track_contact TEXT, direct INTEGER, status TEXT, limit_type TEXT, ns TEXT);");
		sqlite_query($chdb, "CREATE INDEX deny_idx ON deny (id, name, name_raw, bitrate, type, listeners, relays, age, limit_type, ns);");
		sqlite_query($chdb, "CREATE TABLE var (name TEXT NOT NULL UNIQUE, value NOT NULL, last_update INTEGER DEFAULT '0');");
		sqlite_query($chdb, "CREATE INDEX var_idx ON var (name);");
		
		print "チャンネルDBを作成しました.\n";
	}
	else
	{
		print "チャンネルDBは既に存在しています.\n";
	}
	
	$hostdb = sqlite_open($HostDB);
	if(!sqlite_table_exists($hostdb, 'host'))
	{
		chmod($HostDB, 0666);
		
		sqlite_query($hostdb, "CREATE TABLE host (ip TEXT NOT NULL UNIQUE, uid DEFAULT '', speed INTEGER DEFAULT '0', speed_date INTEGER DEFAULT '0', port_no INTEGER DEFAULT '7144', port_open INTEGER DEFAULT '0', port_open_date INTEGER DEFAULT '0');");
		sqlite_query($hostdb, "CREATE INDEX host_idx ON host (ip, uid);");
		sqlite_query($hostdb, "CREATE TABLE var (name TEXT NOT NULL UNIQUE, value NOT NULL, last_update INTEGER DEFAULT '0');");
		sqlite_query($hostdb, "CREATE INDEX var_idx ON var (name);");
		
		print "ホストDBを作成しました.\n";
	}
	else
	{
		print "ホストDBは既に存在しています.\n";
	}
?>