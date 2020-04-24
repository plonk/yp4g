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

#ifndef SQLITE_TOOLSH
#define SQLITE_TOOLSH

#include <sqlite.h>

bool sqlite_table_exists(sqlite *db, const char *name);
int sqlite_get_col_index(char **result, int ncol, const char *name);
char *sqlite_get_item(char **result, int ncol, int row, int col);
char *sqlite_get_item(char **result, int ncol, int row, const char *name);

#endif
