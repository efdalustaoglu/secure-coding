all:
	gcc -c -Iusr/include/mysql tan_generator.c
	gcc -o tan_generator tan_generator.o -L/usr/lib/mysql -lmysqlclient -lcrypto -lssl