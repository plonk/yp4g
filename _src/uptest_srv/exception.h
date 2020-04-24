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

#ifndef EXCEPTIONH
#define EXCEPTIONH

#include <stdio.h>
#include <stdarg.h>

#define EXCEPTION_TEMPLATE		\
{								\
	va_list ap;					\
	va_start(ap, format);		\
	vsprintf(msg, format, ap);	\
	va_end(ap);					\
	err = error;				\
}

#define EXCEPTION_BUFLEN 256

class GeneralException
{
public:
	int err;
	char msg[EXCEPTION_BUFLEN];

	GeneralException(){ err = 0; msg[0] = 0; }
	GeneralException(int error, const char *format, ...) { EXCEPTION_TEMPLATE; }
};


class SocketException : public GeneralException
{
public:
	SocketException(int error, const char *format, ...) : GeneralException() { EXCEPTION_TEMPLATE; }
};


class StreamException : public GeneralException
{
public:
	StreamException(int error, const char *format, ...) : GeneralException() { EXCEPTION_TEMPLATE; }
};


class AbortException : public GeneralException
{
public:
	AbortException(int error, const char *format, ...) : GeneralException() { EXCEPTION_TEMPLATE; }
};


#endif
