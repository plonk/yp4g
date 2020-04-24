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

#include "sqlite_tools.h"

#include <stdio.h>
#include <string.h>

#define BUFLEN 256

bool sqlite_table_exists(sqlite *db, const char *name)
{
	char **result, sql[BUFLEN];
	int row, col, ret;

	sprintf(sql, "SELECT * FROM sqlite_master WHERE type='table' AND name='%s'", name);
	ret = sqlite_get_table(db, sql, &result, &row, &col, NULL);
	sqlite_free_table(result);

	if(ret == SQLITE_OK && row > 0)
		return true;
	else
		return false;
}

int sqlite_get_col_index(char **result, int ncol, const char *name)
{
	int i;

	if(!result || ncol < 1 || !name)
		return -1;

	for(i=0; i<ncol; i++)
		if(!strcmp(result[i], name))
			return i;

	return -1;
}

char *sqlite_get_item(char **result, int ncol, int row, int col)
{
	if(!result || ncol < 1)
		return NULL;

	return result[ncol*(row+1)+col];
}

char *sqlite_get_item(char **result, int ncol, int row, const char *name)
{
	int col;
	
	col = sqlite_get_col_index(result, ncol, name);
	if(col < 0)
		return NULL;

	return sqlite_get_item(result, ncol, row, col);
}
