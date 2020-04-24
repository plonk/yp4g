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

#ifndef MUTEXH
#define MUTEXH

#ifdef WIN32
 #include <windows.h>
#else //WIN32
 #include <pthread.h>
#endif //WIN32

class CMutexBase
{
public:
	virtual void lock() = 0;
	virtual void unlock() = 0;
};

class CLock
{
private:
	CMutexBase *m_mutex;

public:
	CLock(CMutexBase *mutex)
	{
		m_mutex = mutex;
		m_mutex->lock();
	}

	~CLock()
	{
		m_mutex->unlock();
	}
};

class CMutex : public CMutexBase
{
private:

#ifdef WIN32
	CRITICAL_SECTION cs;
#else //WIN32
	pthread_mutex_t mutex;
#endif //WIN32

public:
	CMutex()
	{
#ifdef WIN32
		InitializeCriticalSection(&cs);
#else //WIN32
		pthread_mutex_init(&mutex, NULL);
#endif //WIN32
	}

	~CMutex()
	{
#ifdef WIN32
		DeleteCriticalSection(&cs);
#else //WIN32
		pthread_mutex_destroy(&mutex);
#endif //WIN32
	}

	void lock()
	{
#ifdef WIN32
		EnterCriticalSection(&cs);
#else //WIN32
		pthread_mutex_lock(&mutex);
#endif //WIN32
	}

	void unlock()
	{
#ifdef WIN32
		LeaveCriticalSection(&cs);
#else //WIN32
		pthread_mutex_unlock(&mutex);
#endif //WIN32
	}
};

#endif
