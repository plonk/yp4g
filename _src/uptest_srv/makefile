SRC = file.cpp main.cpp sqlite_tools.cpp str.cpp tools.cpp
LIB = -lpthread -lsqlite
OBJ = $(SRC:.cpp=.o)

CC = g++

all: uptest_srv

uptest_srv: $(OBJ)
	$(CC) -o $@ $(LIB) $(OBJ)

%.o:%.cpp
	$(CC) -c $< -o $@

clean:
	rm -f $(OBJ)
