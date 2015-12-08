// Includes
#include <stdio.h>
#include <stdlib.h>
#include <limits.h>
#include <math.h>
#include <string.h>
#if defined __WIN32__ || _MSC_VER
   #include "my_global.h"
   #include "mysql.h"
#else
   #include <mysql/mysql.h>
#endif

// Definition of error codes returned by the program (excl. MySQL specific error codes)
#define RETURN_SUCCESS 0
#define PARAMETER_ERROR -1
#define IO_ACCESS_ERROR -2
#define IO_READ_ERROR -3
#define FILE_FORMAT_ERROR -4
#define MEMORY_ERROR -5
#define INTERNAL_ERROR -6
#define MYSQL_QUERY_ERROR -7
#define ACCOUNT_NOT_FOUND -8
#define SENDER_RECEIVER_EQUAL -9
#define TAN_INVALID -10
#define CRITICAL_ACCOUNT_BALANCE -11


// Definition of structs
typedef struct account_struct{
	int id;
	int user;
	int account_number;
	double balance;
	char *date_created;
} account;

typedef struct transaction_struct{
	account *sender_account;
	account *recipient_account;
	double amount;
	char *tan;
} transaction_info;
	
// Global variables
MYSQL *mysql = NULL;
char *db_user = "root";
char *db_password = "samurai";
char *db_name = "bank_db";



//-------------------------------------------------------------------------- Database Operations ------------------------------------------------------------------------


/**
 * Connects to a local MySQL server, using the login information given in global variables
 *
 * @return 0 if successful, a non-zero MySQL error code if not successful
**/

int mysqlConnect(){
	// init
	mysql=mysql_init(mysql);
	
	// check for possible errors
	if (mysql_errno(mysql) != 0) {
		//printf("%s\n", mysql_error(mysql));
		return mysql_errno(mysql);
	}
	
	// connect to a local MySQL server
	mysql_real_connect(mysql, "127.0.0.1", db_user, db_password, NULL, 0, NULL, 0);
	if (mysql_errno(mysql) != 0) {
		//printf("%s\n", mysql_error(mysql));
		return mysql_errno(mysql);
	}
	
	return RETURN_SUCCESS;
}



/**
 * Closes the connection to a local MySQL server
 *
 * @return 0 if successful, a non-zero MySQL error code or -5 if not successful. -5 is returned if no active connection was found
**/

int mysqlCloseConnection(){
	// if mysql has not been initialized, there is no connection to close
	if(mysql != NULL){
		mysql_close(mysql);
		if (mysql_errno(mysql) != 0) {
			//printf("%s\n", mysql_error(mysql));
			return mysql_errno(mysql);
		}
	}
	else{
		return MEMORY_ERROR;
	}
	
	return RETURN_SUCCESS;
}



/**
 * Selects a database on the MySQL server, using information given in global variables
 *
 * @return 0 if successful, a non-zero MySQL error code if not successful
**/

int mysqlSelectDB(){
	mysql_select_db(mysql, db_name);
	if (mysql_errno(mysql) != 0) {
		//printf("%s\n", mysql_error(mysql));
		return mysql_errno(mysql);
	}
	
	return RETURN_SUCCESS;
}



/**
 * Executes a query without returning data from the database
 *
 * @param query Pointer to a NULL-terminated string containing the SQL statement to be executed
 * @return 0 if successful, a non-zero MySQL error code if not successful
**/

int executeQuery(char *query){
	mysql_real_query(mysql, query, strlen(query));
	if(mysql_errno(mysql) != 0) {
		//printf("%s\n", mysql_error(mysql));
		return mysql_errno(mysql);
	}
	
	return RETURN_SUCCESS;
}



/**
 * Executes an SQL query and returns the resulting data from the database
 *
 * @param query Pointer to a NULL-terminated string containing the SQL statement to be executed
 * @return Pointer to the data set(s) provided by the database in response (if successful), NULL if not successful
**/

MYSQL_RES  *executeQueryReply(char *query){
	MYSQL_RES  *query_res = NULL;
	mysql_real_query(mysql, query, strlen(query));
	if(mysql_errno(mysql) != 0) {
		//printf("%s\n", mysql_error(mysql));
		return NULL;
	}

	query_res = mysql_store_result(mysql);
	if(mysql_errno(mysql) != 0) {
		//printf("%s\n", mysql_error(mysql));
		return NULL;
	}
	
	return query_res;
}



/**
 * Fetches bank account information from the database
 *
 * @param accountNumber Account number (not ID) of the account for which information should be retrieved
 * @return Pointer to a struct account containing account information if successfull, a NULL pointer if not succesful
**/

account *getAccount(int accountNumber){
	char query[60] = {0};
	MYSQL_ROW  row = NULL;
	MYSQL_RES  *query_res = NULL;
	
	// catch wrong inputs
	if(accountNumber < 0){
		return NULL;
	}
	
	// create the SQL statement
	if(sprintf(query, "SELECT * FROM accounts WHERE ACCOUNT_NUMBER = '%d'", accountNumber) < 0){
		return NULL;
	}
	
	// execute the query
	query_res = executeQueryReply(query);
	if(query_res == NULL || mysql_num_rows(query_res) != 1){
		return NULL;
	}
	
	// get the first row from the data retrieved
	if((row = mysql_fetch_row(query_res)) == NULL){
		return NULL;
	}
	
	// we expect each row to have 5 fields
	//if(mysql_num_fields(query_res) != 5){
	//	return NULL;
	//}
	
	// get the length of each field in the row; this is required to later create a NULL-terminated string for the date
	unsigned long *field_lengths = mysql_fetch_lengths(query_res);
	
	// create a new account structure in memory
	account *acc = (account *) malloc(sizeof(struct account_struct));
	if(acc == NULL){
		return NULL;
	}
	
	// fill user field
	acc->id = atoi(row[0]);				// atoi() can be used here, since the field in the database can only store int => no overflow possible
	acc->user = atoi(row[1]);
	acc->account_number = atoi(row[2]);
	acc->balance = atof(row[3]);			// atof() returns double (not float as the 'f' suggests) => no overflow possible
	
	// We have no idea how format DATE is saved in SQL, NULL-terminated or not, so we go the safe way => create a new NULL-terminated char array containing the DATE data
	char *creation_date = (char *) malloc(field_lengths[4] + 1);
	if(creation_date == NULL){
		mysql_free_result(query_res);
		return NULL;
	}
	memcpy(creation_date, &row[4], field_lengths[4]);
	creation_date[field_lengths[4]] = 0;
	acc->date_created = creation_date;
	mysql_free_result(query_res);
	return acc;
}



/**
 * Checks if a given TAN is valid by checking its existence, status (valid or previously used) and  assignment to a specific bank account
 *
 * @param tan Pointer to a NULL-terminated string containing the 15 character (excl. '\0') long TAN
 * @param acc Pointer to a struct account containing information about the bank account with which the TAN is intended to be used
 * @return 0 if successful, a non-zero error code if unsuccessful
**/

int verifyTAN(char *tan, account *acc){
	char query[77] = {0};
	MYSQL_RES  *query_res = NULL;
	MYSQL_ROW row = NULL;
	
	// catch wrong inputs
	if(acc == NULL || tan == NULL || tan[15] != 0 || strlen(tan) != 15){
		return PARAMETER_ERROR;
	}
	
	// create an SQL statement to get the bank account and the status associated with the TAN
	if(sprintf(query, "SELECT CLIENT_ACCOUNT, STATUS FROM tans WHERE TAN_NUMBER = '%s'", tan) < 0){
		return INTERNAL_ERROR;
	}
	
	// execute the query
	query_res = executeQueryReply(query);
	if(query_res == NULL || mysql_num_rows(query_res) < 1){
		return MYSQL_QUERY_ERROR;
	}
	
	// get the first row (there should be just one)
	if((row = mysql_fetch_row(query_res)) == NULL){
		return MYSQL_QUERY_ERROR;
	}
	
	// save the bank account ID and the status
	int client_account_id = atoi(row[0]);
	char tan_status = (char) *row[1];
	
	// check if the TAN is assigned to the account; if not, the TAN is not verified
	if(client_account_id != acc->id){
		mysql_free_result(query_res);
		return TAN_INVALID;
	}
	
	// check if the TAN has status 'valid'
	if(tan_status != 'V'){
		mysql_free_result(query_res);
		return TAN_INVALID;
	}
	
	mysql_free_result(query_res);
	return RETURN_SUCCESS;
}



/**
 * Sets the status of a given TAN to 'used' in the database
 *
 * @param tan Pointer to a NULL-terminated string containing the 15 character (excl. '\0') long TAN
 * @return 0 if successful, a non-zero error code if unsuccessful
**/

int updateTANStatus(char *tan){
	char query[66] = {0};
	
	// catch wrong inputs
	if(tan == NULL || tan[15] != 0 || strlen(tan) != 15){
		return PARAMETER_ERROR;
	}
	
	// create an SQL statement to update the status field for the tan
	if(sprintf(query, "UPDATE tans SET STATUS = 'U' WHERE TAN_NUMBER = '%s'", tan) < 0){
		return INTERNAL_ERROR;
	}
	
	// execute
	return executeQuery(query);

}


/**
 * Saves information about an executed money transaction in the database
 *
 * @param transaction Pointer to a struct transaction_info containing information about the executed transaction
 * @return 0 if successful, a non-zero error code if unsuccessful
**/

int registerTransaction(transaction_info *transaction){
	char query[250] = {0};
	MYSQL_RES  *query_res = NULL;
	MYSQL_ROW  row = NULL;
	int tan_id = 0;
	
	// catch wrong input
	if(transaction == NULL){
		return PARAMETER_ERROR;
	}
	
	// create an SQL statement for retrieving the ID of the TAN; this is required to later safe it as part of the transaction information
	if(sprintf(query, "SELECT ID FROM tans WHERE TAN_NUMBER = '%s'", transaction->tan) < 0){
		return INTERNAL_ERROR;
	}
	
	// execute
	query_res = executeQueryReply(query);
	if(query_res == NULL || mysql_num_rows(query_res) < 1){
		return MYSQL_QUERY_ERROR;
	}
	
	// get the first row; again, there should just be one
	if((row = mysql_fetch_row(query_res)) == NULL){
		return MYSQL_QUERY_ERROR;
	}
	
	// safe the ID in a separate variable
	tan_id = atoi(row[0]);
	
	// if the money transfer is about more than 10000 Euros, it is not executed immidiately, but has to wait for approval
	// so we treat the two cases separately, as the transaction status and the balances on both accounts (sender and recipient) are dependent on the amount of money transfered
	if(transaction->amount > 10000){
		if(sprintf(query, "INSERT INTO transactions (SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, STATUS, TAN_ID, DATE_CREATED) VALUES ('%d', '%d', '%f', 'P', '%d', CURDATE())",
			transaction->sender_account->id, transaction->recipient_account->id, transaction->amount, tan_id) < 0){
			return INTERNAL_ERROR;
		}
	}
	else{

		// the transaction should be approved automatically => we need to find out the user ID of 'system'
		int system_id = 0;
		memset(query, 0, 60);
		mysql_free_result(query_res);
		if(strcpy(query, "SELECT ID FROM users WHERE USER_TYPE = 'S'") == NULL){
			return INTERNAL_ERROR;
		}

		// execute
		query_res = executeQueryReply(query);
		if(query_res == NULL){
			return MYSQL_QUERY_ERROR;
		}

		// get the first row; again, there should just be one
		if((row = mysql_fetch_row(query_res)) == NULL){
			return MYSQL_QUERY_ERROR;
		}

		// safe the ID in a separate variable
		system_id = atoi(row[0]);


		if(sprintf(query, "INSERT INTO transactions (SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, STATUS, TAN_ID, APPROVED_BY, DATE_APPROVED, DATE_CREATED) VALUES ('%d', '%d', '%f', 'A', '%d', '%d', CURDATE(), CURDATE())",
			transaction->sender_account->id, transaction->recipient_account->id, transaction->amount, tan_id, system_id) < 0){
			return INTERNAL_ERROR;
		}
	}
	mysql_free_result(query_res);
	
	// execute
	return executeQuery(query);
}



/**
 * Executes a money transfer
 *
 * @param transaction Pointer to a struct transaction_info containing information about the transaction to be executed
 * @return 0 if successful, a non-zero error code if unsuccessful
**/

int transferMoney(transaction_info *transaction){
	char query[200] = {0};	//410
	MYSQL_RES *query_res = NULL;
	MYSQL_ROW  row = NULL;
	int result = 0;
	
	// check for wrong inputs
	if(transaction == NULL){
		return PARAMETER_ERROR;
	}
	
	if(transaction->sender_account == NULL || transaction->recipient_account == NULL){
		return PARAMETER_ERROR;
	}
	
	// if sending and receiving account are the same, the transfer will be denied
	if(transaction->sender_account->account_number == transaction->recipient_account->account_number){
		return SENDER_RECEIVER_EQUAL;
	}
	
	// verify the TAN
	result = verifyTAN(transaction->tan, transaction->sender_account);

	if(result != RETURN_SUCCESS){
		char exe[120] = {0};
		char output[40] = {0};
		FILE *fp;

		sprintf(exe, "./SCSimulator/tan_generator validate %d %s %s %s %s", transaction->sender_account->account_number, transaction->tan, db_user, db_password, db_name);
		if((fp = popen(exe, "r")) == NULL){
			printf("%d\n", IO_ACCESS_ERROR);
			return IO_ACCESS_ERROR;
		}

		while(fgets(output, 35, fp) != NULL){
			//printf("Output: %s\n", output);
		}
		pclose(fp);
		result = atoi(output);
		if(result != RETURN_SUCCESS){
			return result;
		}
	}
	
	// check if there's enough money on the sender's account (may lead to a negative balance otherwise)
	if(transaction->sender_account->balance < transaction->amount){
		return CRITICAL_ACCOUNT_BALANCE;
	}
	
	// check if there is too much money on the receiving account (may lead to an overflow otherwise)
	if(transaction->recipient_account->balance > 2147483647 - transaction->amount){
		return CRITICAL_ACCOUNT_BALANCE;
	}
	
	// if more than 10000 Euros are about to be transfered, we must not execute it right now
	if(transaction->amount > 10000){
		return RETURN_SUCCESS;
	}
	
	// create an SQL transaction to withdraw the money from one account and add it on the other in a safe way
	if(sprintf(query, "START TRANSACTION") < 0){ 
		return INTERNAL_ERROR;
	}

	if((result = executeQuery(query)) != RETURN_SUCCESS){
		return MYSQL_QUERY_ERROR;
	}
	
	// create a temporary variable in SQL to store the old balance of the sender's account; that's required, since the money will only be withdrawn if there's enough money available on the account
	if(sprintf(query, "SELECT @old_balance := BALANCE FROM accounts WHERE ACCOUNT_NUMBER = '%d'", transaction->sender_account->account_number) < 0){ 
		return INTERNAL_ERROR;
	}

	if(executeQueryReply(query) == NULL){
		return MYSQL_QUERY_ERROR;
	}
	mysql_free_result(query_res);
	memset(query, 0, 90);
	
	// withdraw if there's enough money, otherwise leave the money untouched
	if(sprintf(query, "UPDATE accounts SET BALANCE = CASE WHEN @old_balance >= '%f' THEN '%f' ELSE @old_balance END WHERE ACCOUNT_NUMBER = '%d'",
		transaction->amount, transaction->sender_account->balance - transaction->amount, transaction->sender_account->account_number) < 0){ 
		return INTERNAL_ERROR;
	}

	if((result = executeQuery(query)) != RETURN_SUCCESS){
		return result;
	}
	memset(query, 0, 150);
	
	// add the money on the receiver account, if and only if it was withdrawn from the other account before (same conditional for both operations, withdraw and add)
	if(sprintf(query, "UPDATE accounts SET BALANCE = CASE WHEN @old_balance >= '%f' THEN '%f' ELSE @old_balance END WHERE ACCOUNT_NUMBER = '%d'",
		transaction->amount, transaction->recipient_account->balance + transaction->amount, transaction->recipient_account->account_number) < 0){ 
		return INTERNAL_ERROR;
	}

	if((result = executeQuery(query)) != RETURN_SUCCESS){
		return result;
	}
	memset(query, 0, 150);
	
	// finish the transaction
	if(sprintf(query, "COMMIT") < 0){ 
		return INTERNAL_ERROR;
	}

	if((result = executeQuery(query)) != RETURN_SUCCESS){
		return result;
	}
	
	return RETURN_SUCCESS;
}






//-------------------------------------------------------------------------- File Parser ------------------------------------------------------------------------


/**
 * Copies a string from a source to a destination, skipping all leading and trailing whitespaces.
 *
 * @param  source Pointer to a valid string (terminated by NULL character) that contains the characters to be copied
 * @param  destination Pointer to a string to which the characters are copied
 * @param  destinationMaxSize Number of bytes that have been allocated for the destination string. A maximum of
 *               destinationMaxSize - 1 bytes will be copied, followed by a NULL terminal
 * @return Length of the destination string, including the trailing NULL terminal
**/
int copyTrimmedString(char *source, char *destination, short destinationMaxSize){
	// variable to count how many bytes we extract from the line in the file
	short bytesCopied = 0;
	
	if(source == NULL || destination == NULL || destinationMaxSize < 1){
		return PARAMETER_ERROR;
	}
	
	// skip all leading whitespaces
	while(*source == ' ' || *source == '\t'){
		source++;
	}
	
	// copy byte by byte from the source string to the destination string, until we reach a NULL character,
	// a whitespace character (including ' ', '\t', '\n', '\r', '\f', 'v') or the maximum size - 1 of the destination string
	while(bytesCopied < destinationMaxSize - 1 && !isspace(*source) && *source != 0){
		if(*source == '\'' || *source == '\"' || *source == '#' || *source == '\\' || *source == '-' || *source == '\%' || *source == '\_' || *source == '+' || *source == '*' || *source == '?' || *source == '=' || *source == '$' || *source == '&' || *source == '/'){
			return PARAMETER_ERROR;   // these characters have to be caught 
		}
		*destination = *source;
		destination++;
		source++;
		bytesCopied++;
	}
	
	// set a NULL terminal to the end of the destination string
	*destination = 0;
	
	// return the actual length of the destination string (bytesCopied + 1 byte for the NULL terminal)
	return bytesCopied + 1;
}



/**
 * Reads the content of a given file and extracts information about the sender's and recipient's accounts, the amount
 * of money to transfer and the TAN. The file content should have the format presented below. Lines that are empty
 * or have arbitrary content are automatically skipped during the parsing process. The four keywords
 * 'SENDER_ACCOUNT:', 'RECIPIENT_ACCOUNT:', 'AMOUNT:' and 'TAN:' have to be present, followed by the
 * respective information. The position and order of the lines in the file are irrelevant.
 * 
 * |-------------------------------------------------------------------------------------|
 * |										|
 * |SENDER_ACCOUNT:	1234567890					|
 * |RECIPIENT_ACCOUNT:	9876543210					|
 * |AMOUNT:			123.45						|
 * |TAN:			abcdefgh1234567				|
 * |										|
 * |										|
 * |-------------------------------------------------------------------------------------|
 *
 *
 * @param  path Pointer to a valid string (terminated by NULL character) that contains the path of the file
 * @return 0 if successful or a negativ error code if unsuccessful. In addition the code is printed to stdout, in case of
 *               success followed by the values of SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, TAN, separated
 *               by a space character
**/
int parseFile(const char *path, transaction_info *transaction) {
	// check if parameter is NULL
	if(path == NULL || transaction == NULL){
		return PARAMETER_ERROR;
	}
	
	// file to read from
	FILE *textfile = NULL;
	int path_length = 0;
	
	// buffers to store the file content
	char sender[16] = {0};
	char recipient[16] = {0};
	char tan[16] = {0};
	char amount[16] = {0};
	
	// check if system had enough memory available
	if(sender == NULL || recipient == NULL || tan == NULL || amount == NULL){
		return MEMORY_ERROR;
	}
	
	// check if path is NULL terminated
	while(path[path_length] != 0 && path_length < FILENAME_MAX){
		path_length++;
	}
	
	// if path is not correctly terminated, calling fopen would result in an overflow
	if(path_length == FILENAME_MAX){
		return PARAMETER_ERROR;
	}
	
	if((textfile = fopen(path, "r")) == NULL){
		return IO_ACCESS_ERROR;
	}
	else{

		// variable to store one line (maximum 64 characters) of the input file
		char line[64] = {0};
		short stringLength = 0;
		
		while(!feof(textfile)){
			if(fgets(line, 64, textfile)){
				if(line[63] != 0){
					line[63] = 0;
				}

				// we check if the line starts with one of the key words "SENDER_ACCOUNT:", "RECIPIENT_ACCOUNT:",
				// "AMOUNT:" or "TAN:". If it does, we extract the information given behind the key word and store it
				// in the designated char buffer. stringLength holds the length of the information stored, including a trailing
				// NULL terminal. A negative value of stringLength indicates an error code. The value 0 indicates that the
				// line doesn't start with one of the given keywords.
				// printf("line: %s\n", line);
				if(strstr(line, "SENDER_ACCOUNT:") == line){
					stringLength = copyTrimmedString(line + 15, sender, 16);
					//printf("sender: %d, %s\n", sender, sender);
				}
				else if(strstr(line, "RECIPIENT_ACCOUNT:") == line){
					stringLength = copyTrimmedString(line + 18, recipient, 16);
					//printf("recipient: %d, %s\n", recipient, recipient);
				}
				else if(strstr(line, "AMOUNT:") == line){
					stringLength = copyTrimmedString(line + 7, amount, 16);
					//printf("amount: %d, %s\n", amount, amount);
				}
				else if(strstr(line, "TAN:") == line){
					stringLength = copyTrimmedString(line + 4, tan, 16);
					//printf("tan: %d, %s\n", tan, tan);
				}
				else{
					// if the line doesn't start with one of the patterns above we don't need to copy anything and
					// stringLength is set to the reserved value 0, indicating that the line can just be skipped
					stringLength = 0;
				}
				
				if(stringLength < 0){
					// an error has occured during the extraction of information from the processed line
					fclose(textfile);
					return IO_READ_ERROR;
				}
				else if(stringLength == 1){
					// only a NULL terminal has been copied => the line doesn't contain any information after a
					// promising keyword (e.g. SENDER_ACCOUNT)
					fclose(textfile);
					return FILE_FORMAT_ERROR;
				}
			}
		}
		
		// check if all four strings have been set to a value 
		if(*sender == 0 || *recipient == 0 || *amount == 0 || *tan == 0){
			fclose(textfile);
			return FILE_FORMAT_ERROR;
		}
		
		// after we have read the data, we have to convert the account numbers to integers and the amount to float
		
		unsigned long lng_sender = 0;
		unsigned long lng_recipient = 0;
		double dbl_amount = 0;
		
		// strtoul is for unsigned long; a similar secure function for int doesn't exist; the similar atoi() function doesn't protect
		// from overflows
		lng_sender = strtoul(sender, NULL, 10);
		if(lng_sender == 0 || lng_sender > INT_MAX){
			fclose(textfile);
			return FILE_FORMAT_ERROR;
		}
		
		lng_recipient = strtoul(recipient, NULL, 10);
		if(lng_recipient == 0 || lng_recipient > INT_MAX){
			fclose(textfile);
			return FILE_FORMAT_ERROR;
		}
		
		// read the amount as double, as there's no such function for float
		dbl_amount = strtod(amount, NULL);
		if(dbl_amount == 0.0 || dbl_amount == HUGE_VAL || dbl_amount == -HUGE_VAL){
			fclose(textfile);
			return FILE_FORMAT_ERROR;
		}
		
		transaction->sender_account = getAccount((int) lng_sender);
		transaction->recipient_account = getAccount((int) lng_recipient);
		transaction->amount = dbl_amount;
		transaction->tan = (char *) malloc(16);
		memcpy(transaction->tan, tan, 16);
	
		//printf("%d %d %d %.2f %s\n", RETURN_SUCCESS, (int) transaction->sender_account->account_number, (int) transaction->recipient_account->account_number, (float) transaction->amount, transaction->tan);
		
		fclose(textfile);
		return RETURN_SUCCESS;
	}
	
}



/**
 * Frees memory occupied on the heap
 * 
 * @param transaction Pointer to a struct transaction, that may occupy memory on the heap
**/

void releaseMemory(transaction_info *transaction){
	if(transaction != NULL){
		if(transaction->sender_account != NULL){
			if(transaction->sender_account->date_created != NULL){
				free(transaction->sender_account->date_created);
			}
			free(transaction->sender_account);
		}
		if(transaction->recipient_account != NULL){
			if(transaction->recipient_account->date_created != NULL){
				free(transaction->recipient_account->date_created);
			}
			free(transaction->recipient_account);
		}
		if(transaction->tan != NULL){
			free(transaction->tan);
		}
		free(transaction);
		transaction = NULL;
	}
}



/**
 * Function that is called first when the program is executed. The program expects 4 arguments:
 *
 * ./file_parser <some/path/to/the/file.txt> <db_user> <db_password> <db_name> 
 *
**/
int main(int argc, char **argv){
	int result = 0;
	
	if(argc != 5){
		printf("%d\n", PARAMETER_ERROR);
		return PARAMETER_ERROR;
	}
	
	// mysql != NULL indicates that the program is already running
	if(mysql != NULL){
		printf("%d\n", INTERNAL_ERROR);
		return INTERNAL_ERROR;
	}
	
	// get the arguments
	const char *path = argv[1];
	db_user = argv[2];
	db_password = argv[3];
	db_name = argv[4];
	
	// reserve memory for the new transaction
	transaction_info *transaction = (transaction_info *) malloc(sizeof(transaction_info));
	if(transaction == NULL){
		printf("%d\n", MEMORY_ERROR);
		return MEMORY_ERROR;
	}
	transaction->sender_account = NULL;
	transaction->recipient_account = NULL;
	transaction->tan = NULL;
	
	// connect to the database
	result = mysqlConnect();
	if(result != RETURN_SUCCESS){
		releaseMemory(transaction);
		printf("%d\n", result);
		return result;
	}
	result = mysqlSelectDB();
	if(result != RETURN_SUCCESS){
		releaseMemory(transaction);
		printf("%d\n", result);
		return result;
	}
	
	// read the file
	result = parseFile(path, transaction);
	if(result != RETURN_SUCCESS){
		releaseMemory(transaction);
		printf("%d\n", result);
		return result;
	}
	
	// check for obvious errors
	if(transaction->sender_account == NULL || transaction->recipient_account == NULL){
		releaseMemory(transaction);
		printf("%d\n", ACCOUNT_NOT_FOUND);
		return ACCOUNT_NOT_FOUND;
	}
	
	// perform the transaction
	result = transferMoney(transaction);
	if(result != RETURN_SUCCESS){
		releaseMemory(transaction);
		printf("%d\n", result);
		return result;
	}
	
	// update the TAN status in the database
	result = updateTANStatus(transaction->tan);
	if(result != RETURN_SUCCESS){
		releaseMemory(transaction);
		printf("%d\n", result);
		return result;
	}
	
	// safe the transaction
	result = registerTransaction(transaction);
	if(result != RETURN_SUCCESS){
		releaseMemory(transaction);
		printf("%d\n", result);
		return result;
	}
	
	// close database connection
	result = mysqlCloseConnection();
	if(result != RETURN_SUCCESS){
		releaseMemory(transaction);
		printf("%d\n", result);
		return result;
	}

	releaseMemory(transaction);
	
	// exit
	printf("%d\n", RETURN_SUCCESS);
	return RETURN_SUCCESS;
}
