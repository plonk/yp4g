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

#include <algorithm>

#include "str.h"

void trim_left(std::string &str, const char *chars2remove)
{
	if (!str.empty())
	{
		std::string::size_type pos = str.find_first_not_of(chars2remove);

		if (pos != std::string::npos)
			str.erase(str.begin(), str.begin()+pos);
		else
			str.erase(str.begin() , str.end());
	}
}

void trim_right(std::string &str, const char *chars2remove)
{
	if (!str.empty())
	{
		std::string::size_type pos = str.find_last_not_of(chars2remove);

		if (pos != std::string::npos)
			str.erase(str.begin()+pos+1, str.end());
		else
			str.erase(str.begin() , str.end());
	}
}

void trim(std::string &str, const char *chars2remove)
{
	trim_left(str, chars2remove);
	trim_right(str, chars2remove);
}

void strtolower(std::string &str)
{
	std::transform(str.begin(), str.end(), str.begin(), tolower);
}

int getline2(std::istream &s, std::string &str, int maxlen)
{
	int i;
	char c;
	
	str.erase(str.begin() , str.end());
	
	for(i=0; i<maxlen; i++)
	{
		c = 0;
		s.read(&c, 1);
		
		if(c == '\r')
			continue;
		else if(c == '\n')
			break;
		else if(c == 0)
			break;
		
		str.append(1, c);
	}
	
	return str.length();
}
