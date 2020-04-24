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

#ifndef STRH
#define STRH

#include <istream>
#include <string>

void trim_left(std::string &str, const char *chars2remove = " \t\r\n");
void trim_right(std::string &str, const char *chars2remove = " \t\r\n");
void trim(std::string &str, const char *chars2remove = " \t\r\n");
void strtolower(std::string &str);
int getline2(std::istream &s, std::string &str, int maxlen);

#endif
