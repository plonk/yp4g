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

#ifndef FILEH
#define FILEH

#include <fstream>
#include <string>

class CFile
{
private:
	std::fstream m_fs;
	std::string m_filename;

public:
	CFile();
	~CFile();
	int reopen(std::string filename);
	void flush();
	void close();
	bool is_open();
	std::string get_filename();
	void write(const char *buffer, unsigned int len);
	void read(char *buffer, unsigned int len);
};

#endif // FILEH
