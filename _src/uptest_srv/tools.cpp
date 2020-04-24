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

#ifdef WIN32
 #include <windows.h>
#else
 #include <stdio.h>
 #include <stdlib.h>
 #include <string.h>
 #include <sys/time.h>
 #include <unistd.h>
#endif

#include <fstream>

#include "tools.h"
#include "str.h"

unsigned int get_milli_time()
{
#ifdef WIN32
	return GetTickCount();
#else
	static timeval start = {0, 0};
	timeval tv;

	gettimeofday(&tv, NULL);
	if(!start.tv_sec)
		start = tv;

	return (unsigned int)((tv.tv_sec-start.tv_sec)*1000.0+(tv.tv_usec-start.tv_usec)/1000.0);
#endif
}

void msleep(unsigned int millisec)
{
#ifdef WIN32
	Sleep(millisec);;
#else
	usleep(millisec*1000);
#endif
}

std::string get_profile_string(std::string app, std::string key, std::string def, std::string filename)
{
	std::fstream fs;
	std::string line;
	bool in_app = false;

	fs.open(filename.c_str(), std::ios::in);
	if(!fs.is_open())
		return def;

	app = "[" + app + "]";

	do{
		std::getline(fs, line);

		if(line.length() >= 3)
		{
			if(line[0] == '#' || line[0] == ';')
			{
				continue;
			}
			else if(line[0] == '[')
			{
				std::string str;
				str = line;
				trim(str);

				if(str == app)
					in_app = true;
				else
					in_app = false;
			}
			else if(in_app)
			{
				std::string::size_type pos;

				pos = line.find("=");
				if(pos != std::string::npos)
				{
					std::string name, value;

					name = line.substr(0, pos);
					value = line.substr(pos+1);

					trim(name);
					trim(value);

					if(name == key)
						return value;
				}
			}
		}
	}while(line.length() > 0);

	return def;
}

int get_profile_int(std::string app, std::string key, int def, std::string filename)
{
	std::string ret;

	ret = get_profile_string(app, key, "null", filename);

	if(ret != "null")
		return atoi(ret.c_str());
	else
		return def;
}
