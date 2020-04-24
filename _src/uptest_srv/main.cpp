// --------------------------------------------------------------------
// YP4G Uptest Server
// Author : Åüe5bW6vDOJ.
// --------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// --------------------------------------------------------------------

#include "socket.h"

#ifdef WIN32
 #include <windows.h>
 #include <process.h>
#else
 #include <signal.h>
 #include <unistd.h>
 #include <pthread.h>
 #include <errno.h>
 #include <sys/types.h>
 #include <netinet/tcp.h>
 #include <fcntl.h>
#endif

#include <stdio.h>
#include <time.h>

#include <iostream>
#include <string>

#include <sqlite.h>

#include "str.h"
#include "exception.h"
#include "mutex.h"
#include "file.h"
#include "sqlite_tools.h"
#include "tools.h"

#define BUFLEN 512
#define MAX_HEADER_SIZE (2*1024)
#define MAX_GETLINE_SIZE 1024

struct tagSettings
{
	unsigned short iServerPort;
	int iMinPostSize, iMaxPostSize;
	std::string strUptestObject, strReturnURL;
	int iMaxConnection, iMaxTestConnection;
	std::string strConf, strLogPath;
	int iMossoLine, iUptestInterval;
	double fSpeedAmp;
	std::string strHostDB;
	std::string strBindAddress;
}settings;

struct tagGlobal
{
	bool bAcceptThreadTerminate;
	gimite::server_stream_socket ss;
	int iConnection, iTestConnection;
	CMutex mutexGlobal, mutexLog, mutexSQL;
	CFile fLog;
	sqlite *db;
}g;

typedef struct tagSockInfo
{
	gimite::socket_t s;
	gimite::socket_address dest_addr;
}tSockInfo;

void logf(const char *format, ...)
{
	va_list ap;
	char sztemp[BUFLEN];
	std::string str, path;

	time_t tt= time(NULL);
	tm *t = localtime(&tt);

	sprintf(sztemp, "%d/%02d/%02d %02d:%02d:%02d ", t->tm_year+1900, t->tm_mon+1, t->tm_mday, t->tm_hour, t->tm_min, t->tm_sec);
	str = sztemp;
	
	va_start(ap, format);
	vsprintf(sztemp, format, ap);
	va_end(ap);
	str += sztemp;
	
	sprintf(sztemp, "%d%02d%02d", t->tm_year+1900, t->tm_mon+1, t->tm_mday);
	path = settings.strLogPath + "_" + sztemp + ".log";

	{
		CLock m(&g.mutexLog);
#ifdef WIN32
		printf("%s\r\n", str.c_str());
#endif
		g.fLog.reopen(path);
		g.fLog.write(str.c_str(), (int)str.length());
		g.fLog.write("\n", 1);
		g.fLog.flush();
	}
}

#ifdef WIN32
void server_thread(void *arg)
#else
void *server_thread(void *arg)
#endif
{
	std::auto_ptr<tSockInfo> info((tSockInfo *)arg);
	gimite::socket_stream s(info->s);

	std::string str;
	char sztemp[BUFLEN];

	char **result = NULL, *errmsg = NULL;
	int row = 0, col = 0;

	int speed = 0;
	unsigned int start, end;
	int total = 0, http_contentlen = 0;
	std::string http_method, http_object;
	std::string remote;
	gimite::sock_uint32_t addr;

	{
		CLock m(&g.mutexGlobal);
		g.iConnection++;
	}

	start = get_milli_time();

	addr = info->dest_addr.ip.as_int();
	sprintf(sztemp, "%d.%d.%d.%d", ((unsigned char*)&addr)[0], ((unsigned char*)&addr)[1], ((unsigned char*)&addr)[2], ((unsigned char*)&addr)[3]);
	remote = sztemp;

	try{
		if(g.iConnection > settings.iMaxConnection)
			throw AbortException(0, "Max connections.");

		{
			std::string::size_type pos1, pos2;

			getline2(s, str, MAX_GETLINE_SIZE);
			total += (int)str.length();

			pos1 = str.find(" ");
			pos2 = str.find(" ", pos1+1);

			if(pos1 != std::string::npos && pos2 != std::string::npos)
			{
				http_method = str.substr(0, pos1);
				pos1++;
				http_object = str.substr(pos1, pos2-pos1);
			}
			else
			{
				throw AbortException(0, "HTTP header error.");
			}
		}

		do{
			std::string::size_type pos;

			getline2(s, str, MAX_GETLINE_SIZE);
			total += (int)str.length();

			if(total > MAX_HEADER_SIZE)
				throw StreamException(0, "HTTP header is too big.");

			trim_right(str);

			pos = str.find(":");
			if(pos != std::string::npos)
			{
				std::string name, value;

				name = str.substr(0, pos);
				value = str.substr(pos+1);

				trim(name);
				strtolower(name);
				trim(value);

				if(name == "content-length")
				{
					http_contentlen = atoi(value.c_str());
				}
			}
		}while(str.length() > 0);

		if(http_method != "POST" || http_object != settings.strUptestObject)
			throw StreamException(0, "HTTP request error. [%s] %s", http_method.c_str(), http_object.c_str());

		if(http_contentlen < settings.iMinPostSize || http_contentlen > settings.iMaxPostSize)
			throw StreamException(0, "HTTP Content-Length error. (len=%d)", http_contentlen);

		{
			int ret;

			sprintf(sztemp, "SELECT speed, speed_date FROM host WHERE ip='%s';", remote.c_str());
			{
				CLock m(&g.mutexSQL);
				ret = sqlite_get_table(g.db, sztemp, &result, &row, &col, &errmsg);
			}

			if(ret != SQLITE_OK)
			{
				if(errmsg)
				{
					if(strlen(errmsg) > EXCEPTION_BUFLEN-32)
						errmsg[EXCEPTION_BUFLEN-32] = 0;
					throw StreamException(0, "SQLite query error. (%s)", errmsg);
				}
				else
				{
					throw StreamException(0, "SQLite query error.");
				}
			}
		}

		if(row > 0)
		{
			char *p;

			p = sqlite_get_item(result, col, 0, "speed");
			if(p)
			{
				int bitrate = atoi(p);
				if(bitrate >= settings.iMossoLine)
				{
					throw StreamException(0, "It's not necessary to test. %dkbps", bitrate);
				}
			}

			p = sqlite_get_item(result, col, 0, "speed_date");
			if(p)
			{
				int interval = time(NULL) - atoi(p);
				if(interval < settings.iUptestInterval)
				{
					throw StreamException(0, "Test interval is too short. %ds", interval);
				}
			}
		}

		{
			int len;
			int remain = http_contentlen;

			if(g.iTestConnection >= settings.iMaxTestConnection)
				throw StreamException(0, "Max test connections.");

			{
				CLock m(&g.mutexGlobal);
				g.iTestConnection++;
			}

			end = 0;

			do{
				len = s.recv(sztemp, remain<BUFLEN?remain:BUFLEN);

				if(len > 0)
				{
					end = get_milli_time();

					remain -= len;
					total += len;
				}				
			}while(len > 0 && remain > 0);

			{
				CLock m(&g.mutexGlobal);
				g.iTestConnection--;
			}
		}

		{
			int recv_time = end - start;

			if(recv_time>0 && total>=settings.iMinPostSize)
			{
				speed = (int)(((total/1000.0/(recv_time/1000.0))*8.0)*settings.fSpeedAmp);
				
				logf("%s Test succeeded. %dkbps", remote.c_str(), speed);
			}
			else
			{
				speed = 0;

				logf("%s Data receive failed.", remote.c_str());
			}
		}

		{
			int ret;

			if(errmsg)
				sqlite_freemem(errmsg);
			if(result)
				sqlite_free_table(result);

			if(row > 0)
				sprintf(sztemp, "UPDATE host SET speed='%d', speed_date='%d' WHERE ip='%s';", speed, time(NULL), remote.c_str());
			else
				sprintf(sztemp, "INSERT INTO host (ip, speed, speed_date) VALUES('%s', '%d', '%d');", remote.c_str(), speed, time(NULL));

			{
				CLock m(&g.mutexSQL);
				ret = sqlite_get_table(g.db, sztemp, &result, &row, &col, &errmsg);
			}

			if(ret != SQLITE_OK)
			{
				if(errmsg)
				{
					if(strlen(errmsg) > EXCEPTION_BUFLEN-32)
						errmsg[EXCEPTION_BUFLEN-32] = 0;
					throw StreamException(0, "SQLite query error. (%s)", errmsg);
				}
				else
				{
					throw StreamException(0, "SQLite query error.");
				}
			}
		}

		sprintf(sztemp, 
			"HTTP/1.1 302 Found\r\n"
			"Location: %s\r\n"
			"Connection: close\r\n"
			"Content-Type: text/html\r\n"
			"Content-Length: 0\r\n"
			"\r\n",
			settings.strReturnURL.c_str());

		s.send(sztemp, (int)strlen(sztemp));
	}
	catch(StreamException &e)
	{
		sprintf(sztemp, 
			"HTTP/1.1 404 Not Found\r\n"
			"Connection: close\r\n"
			"Content-Type: text/html\r\n"
			"Content-Length: %d\r\n"
			"\r\n"
			"%s",
			strlen(e.msg),
			e.msg);

		s.send(sztemp, (int)strlen(sztemp));

		logf("%s %s", remote.c_str(), e.msg);
	}
	catch(AbortException &e)
	{
		logf("%s %s", remote.c_str(), e.msg);
	}
	catch(GeneralException &e)
	{
		logf("%s %s", remote.c_str(), e.msg);
	}

	if(errmsg)
		sqlite_freemem(errmsg);
	if(result)
		sqlite_free_table(result);

	{
		CLock m(&g.mutexGlobal);
		g.iConnection--;
	}
	
#ifndef WIN32
	return NULL;
#endif
}

void accept_thread()
{
	gimite::ip_address bind_addr;

	g.bAcceptThreadTerminate = false;
	g.iConnection = g.iTestConnection = 0;

	bind_addr = settings.strBindAddress.c_str();

	if(!g.ss.bind(settings.iServerPort, bind_addr))
	{
		logf("bind failed.");
		return;
	}

	logf("accept start.");

	while(!g.bAcceptThreadTerminate)
	{
		gimite::socket_t cs;

		cs = g.ss.accept();

		if(cs != -1)
		{
			tSockInfo *info;

			{
#ifdef WIN32
				int millisec = 3*1000;
#else
				struct timeval millisec;
				millisec.tv_sec = 3;
				millisec.tv_usec = 0;
#endif
				if(setsockopt(cs, SOL_SOCKET, SO_RCVTIMEO, (char *)&millisec, sizeof(millisec)))
				{
					logf("setsockopt error. (1)");
					break;
				}

				if(setsockopt(cs, SOL_SOCKET, SO_SNDTIMEO, (char *)&millisec, sizeof(millisec)))
				{
					logf("setsockopt error. (2)");
					break;
				}
			}

			info = new tSockInfo;

			info->s = cs;
			info->dest_addr = g.ss.last_addr;
#ifdef WIN32
			if(_beginthread(server_thread, 0, info) != 1L) {}
#else
			pthread_t thread;
			if(pthread_create(&thread, NULL, server_thread, info) == 0)
			{
				pthread_detach(thread);
			}
#endif
			else
			{
				logf("Create thread error. (%s)", strerror(errno));
				break;
			}
		}
		else if(!g.bAcceptThreadTerminate)
		{
			logf("accept error. (%s)", strerror(errno));
			break;
		}
	}

	logf("accept end.");
}

void _main()
{
	{
		std::string str;

		settings.strBindAddress = get_profile_string("Settings", "BindAddress", "0.0.0.0", settings.strConf);
		settings.iServerPort = get_profile_int("Settings", "ServerPort", 443, settings.strConf);
		
		settings.iMaxConnection = get_profile_int("Settings", "MaxConnection", 32, settings.strConf);
		settings.iMaxTestConnection = get_profile_int("Settings", "MaxTestConnection", 8, settings.strConf);
		
		settings.strUptestObject = get_profile_string("Settings", "UptestObject", "/uptest.cgi", settings.strConf);
		settings.strReturnURL = get_profile_string("Settings", "ReturnURL", "", settings.strConf);
		
		settings.iMinPostSize = get_profile_int("Settings", "MinPostSize", 250*1000, settings.strConf);
		settings.iMaxPostSize = get_profile_int("Settings", "MaxPostSize", 251*1000, settings.strConf);
		
		settings.strHostDB = get_profile_string("Settings", "HostDB", "host.db", settings.strConf);
		
		settings.iUptestInterval = get_profile_int("Settings", "UptestInterval", 60, settings.strConf);
		settings.iMossoLine = get_profile_int("Settings", "MossoLine", 2000, settings.strConf);
		str = get_profile_string("Settings", "SpeedAmp", "0.8", settings.strConf);
		settings.fSpeedAmp = atof(str.c_str());
		
		settings.strLogPath = get_profile_string("Settings", "LogPath", "uptest_srv", settings.strConf);
	}

	logf("YP4G Uptest Server startup.");

	gimite::startup_socket();

	logf("SQLite initializing...");

	g.db = sqlite_open(settings.strHostDB.c_str(), 0666, NULL);
	if(g.db)
	{
		logf("SQLite DB open succeeded.");

		accept_thread();

		sqlite_close(g.db);

		logf("SQLite DB closed.");
	}
	else
	{
		logf("SQLite DB open failed.");
	}

	gimite::cleanup_socket();

	logf("YP4G Uptest Server terminated.");
}

#ifdef WIN32

BOOL WINAPI signal_handler(DWORD dwCtrlType)
{
	g.bAcceptThreadTerminate = true;
	g.ss.close();

	return TRUE;
}

int main(int argc, const char **argv)
{
	SetConsoleCtrlHandler(signal_handler, TRUE);

	if(argc != 2)
	{
		printf("Usage: uptest_srv config-file\nSQLite version: %s\n", sqlite_libversion());
		return 0;
	}

	settings.strConf = argv[1];

	_main();

	return 0;
}

#else

void signal_handler(int sig)
{
	switch(sig)
	{
	case SIGINT:
	case SIGTERM:
		g.bAcceptThreadTerminate = true;
		g.ss.close();
		break;
	}
}

void print_usage()
{
	printf(
		"Usage: uptest_srv [Options] config-file\n"
		"Options:\n"
		"  -d              Daemon mode\n"
		"  -p <pidfile>    Write PID file\n"
		"SQLite version: %s\n",
		sqlite_libversion());
	
	exit(0);
}

int main(int argc, char **argv)
{
	int i;
	bool daemon_mode = false;
	std::string pidfile;
	
	signal(SIGINT, signal_handler);
	signal(SIGTERM, signal_handler);
	signal(SIGPIPE, SIG_IGN);
	signal(SIGHUP , SIG_IGN);
	
	for(i=1; i<argc; i++)
	{
		if(!strcmp("-d", argv[i]))
		{
			daemon_mode = true;
		}
		else if(!strcmp("-p", argv[i]))
		{
			if(argc <= i+1)
				print_usage();
			
			pidfile = argv[i+1];
			
			i++;
		}
		else
		{
			settings.strConf = argv[i];
		}
	}
	
	if(settings.strConf == "")
		print_usage();
	
	if(daemon_mode)
	{
		daemon(1, 0);
	}
	
	umask(0);
	
	if(pidfile != "")
	{
		char sztemp[BUFLEN];
		sprintf(sztemp, "%d\n", (int)getpid());
		
		CFile fpid;
		fpid.reopen(pidfile);
		fpid.write(sztemp, strlen(sztemp));
	}
	
	_main();

	return 0;
}

#endif
