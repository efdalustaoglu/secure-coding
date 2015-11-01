file_parser executable

How to compile:
Use the attached Makefile. Just type 'make' to execute the gcc commands inside the file. You may be required to install the MySQL developer package. You can do so by typing 'sudo apt-get install libmysqlclient-dev'

How to run file_parser:
The program takes 4 arguments: ./file_parser <path/to/a/file.txt> <db_user> <db_password> <db_name>
A sample call could be ./filebrowser test_transaction.txt root samurai bank_db
The program returns an integer value, that is also printed to stdout. Possible values are 0 if the file was successfully read, or a non-zero value indicating an error number. Positive error numbers are MySQL errors that can be looked up on the internet. Negative error numbers are custom codes, that can be looked up in the top section of file_parser.c 