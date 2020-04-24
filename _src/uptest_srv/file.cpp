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

#include "file.h"

CFile::CFile()
{
}

CFile::~CFile()
{
	close();
}

int CFile::reopen(std::string filename)
{
	if(m_filename != filename)
	{
		close();

		m_fs.open(filename.c_str(), std::ios::out | std::ios::app);
		if(!m_fs.is_open())
			m_fs.open(filename.c_str(), std::ios::out);

		if(m_fs.is_open())
		{
			m_filename = filename;
			return 0;
		}
		else
		{
			return -1;
		}
	}
	return 1;
}

void CFile::flush()
{
	if(is_open())
		m_fs.flush();
}

void CFile::close()
{
	if(is_open())
	{
		m_fs.close();
	}
	m_filename = "";
}

bool CFile::is_open()
{
	return m_fs.is_open();
}

std::string CFile::get_filename()
{
	return m_filename;
}

void CFile::write(const char *buffer, unsigned int len)
{
	if(is_open())
		m_fs.write(buffer, len);
}
void CFile::read(char *buffer, unsigned int len)
{
	if(is_open())
		m_fs.read(buffer, len);
}
