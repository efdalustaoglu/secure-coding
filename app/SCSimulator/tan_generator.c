#include <stdlib.h>
#include <time.h>
#include <stdio.h>
#include <string.h>
#include <math.h>
#include <stdint.h>
#include <limits.h>
#include <openssl/md5.h>
#include <mysql/mysql.h>
 
 
// Definition of error codes returned by the program (excl. MySQL specific error codes)
#define RETURN_SUCCESS 0
#define PARAMETER_ERROR -1
#define MEMORY_ERROR -5
#define INTERNAL_ERROR -6
#define MYSQL_QUERY_ERROR -7
#define ACCOUNT_NOT_FOUND -8
#define TAN_INVALID -10


// Global variables
MYSQL *mysql = NULL;
char *db_user = "root";
char *db_password = "samurai";
char *db_name = "bank_db";


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
	//printf("%d   %d   %s\n", mysql, strlen(query), query);
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

MYSQL_RES *executeQueryReply(char *query){
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
 * Gets the last validated TAN related to a given bank account from the database
 *
 * @param accountNumber The bank acount number for which the last TAN should be retrieved
 * @return Pointer to a string containing the TAN
**/

char *getLastTan(int accountNumber){
	char query[70] = {0};
	MYSQL_ROW  row = NULL;
	MYSQL_RES  *query_res = NULL;
	//printf("%d\n", accountNumber);
	
	// catch wrong inputs
	if(accountNumber < 0){
		return NULL;
	}
	
	// create the SQL statement
	if(sprintf(query, "SELECT LAST_TAN FROM accounts WHERE ACCOUNT_NUMBER = '%d'", accountNumber) < 0){
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
	
	// get the field length, it should be 15, as valid TANs are required to consist of 15 characters
	unsigned long *field_lengths = mysql_fetch_lengths(query_res);
	if(field_lengths[0] != 15){
		return NULL;
	}
	
	// allocate memory for the TAN and save it there
	char *lastTan = (char *)malloc(16 * sizeof(char));
	strcpy(lastTan, row[0]);
	lastTan[15] = 0;
	
	mysql_free_result(query_res);
	return lastTan;
}


/**
 * Adds an entry for the given TAN in the database and set its status to 'u' (used)
 *
 * @param accountNumber The bank account number the TAN is related to
 * @param tan Pointer to a string containing the TAN to be saved in the database
 * @return 0 if the function succeeds, a non-zero error code otherwise
**/

int publishTan(int accountNumber, char *tan){
	// catch wrong inputs
	if(accountNumber < 0 || tan == NULL || tan[15] != 0 || strlen(tan) != 15){
		return PARAMETER_ERROR;
	}
	
	char query[130] = {0};
	MYSQL_ROW  row = NULL;
	MYSQL_RES  *query_res = NULL;
	//printf("%d\n", accountNumber);
	
	// create an SQL statement to retrieve the bank acount ID the TAN is related to
	if(sprintf(query, "SELECT ID FROM accounts WHERE ACCOUNT_NUMBER = '%d'", accountNumber) < 0){
		return ACCOUNT_NOT_FOUND;
	}

	// execute the query
	query_res = executeQueryReply(query);
	if(query_res == NULL || mysql_num_rows(query_res) != 1){
		return INTERNAL_ERROR;
	}
	
	// get the first row from the data retrieved
	if((row = mysql_fetch_row(query_res)) == NULL){
		return INTERNAL_ERROR;
	}

	// create an SQL statement to insert the TAN into the 'tans' table
	if(sprintf(query, "INSERT INTO tans (TAN_NUMBER, CLIENT_ACCOUNT, DATE_CREATED, STATUS) VALUES ('%s','%s', CURDATE(), 'u')", tan, row[0]) < 0){
		return INTERNAL_ERROR;
	}

	// execute
	mysql_free_result(query_res);
	int res = executeQuery(query);
	return res;
}




/**
 * Sets the last TAN validated in the database
 *
 * @param accountNumber The bank account number the TAN is related to
 * @param tan Pointer to a string containing the TAN to be saved in the database
 * @return 0 if the function succeeds, a non-zero error code otherwise
**/

int updateLastTan(int accountNumber, char *lastTan){
	
	// catch wrong inputs
	if(lastTan == NULL || lastTan[15] != 0 || strlen(lastTan) != 15){
		return PARAMETER_ERROR;
	}
	
	char *query = (char *) calloc(95, sizeof(char));
	
	// create an SQL statement to update the tan field
	if(sprintf(query, "UPDATE accounts SET LAST_TAN = '%s' WHERE ACCOUNT_NUMBER = '%d'", lastTan, accountNumber) < 0){
		return ACCOUNT_NOT_FOUND;
	}
	
	// execute
	int res = executeQuery(query);
	free(query);
	return res;
}



/**
 * Generate a 15 character long TAN containing letters and digits from a given 16 byte long hash value
 *
 * @param hash Pointer to a byte array containing the hash value to be processed
 * @return A 15 character long TAN containing letters and digits
**/

unsigned char *generateTANfromHash(uint8_t *hash){
	unsigned char alphabet[65] = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01";
	uint8_t output[15];
	
	// An MD5 hash value is 120 bits long, that is a byte array with 16 elements, with each element having a value between 0 and 255. This information cannot be stored in a TAN.
	// A TAN is only allowed to be 15 characters long, containing only letters and digits. Thus each character has one of 62 values (26 capital letters, 26 small letters, 10 digts) 
	// 62 values however can be saved in 6 bits. In order to achieve a maximum amount of security we take the last 90 bits of the 120 bit long hash
	// and divide it into 15 blocks, each containing 6 bits. Each block of 6 bits is interpreted as a one of the 62 different values from the given alphabet.
	// Each element of the array 'output' is set to a 6-bit number (0..63), indicating the index in the given alphabet of digits and small and capital letters.
	output[0] = hash[15] & 63;
	output[1] = (hash[15] & 192) >> 6 | (hash[14] & 15) << 2;
	output[2] = (hash[14] & 240) >> 4 | (hash[13] & 3) << 4;
	output[3] = (hash[13] & 252) >> 2;
	output[4] = hash[12] & 63;
	output[5] = (hash[12] & 192) >> 6 | (hash[11] & 15) << 2;
	output[6] = (hash[11] & 240) >> 4 | (hash[10] & 3) << 4;
	output[7] = (hash[10] & 252) >> 2;
	output[8] = hash[9] & 63;
	output[9] = (hash[9] & 192) >> 6 | (hash[8] & 15) << 2;
	output[10] = (hash[8] & 240) >> 4 | (hash[7] & 3) << 4;
	output[11] = (hash[7] & 252) >> 2;
	output[12] = hash[6] & 63;
	output[13] = (hash[6] & 192) >> 6 | (hash[5] & 15) << 2;
	output[14] = (hash[5] & 240) >> 4 | (hash[4] & 3) << 4;
	
	// assemble the TAN
	unsigned char *tan = (unsigned char *)malloc(16 * sizeof(unsigned char));
	int i = 0;
	for(i=0; i<15; i++){
		tan[i] = alphabet[output[i]];
	}
	tan[15] = 0;
	
	return tan;
}



/**
 * Checks if a given TAN is valid
 *
 * @param lastTan Pointer to a string containing the last valid TAN saved in the database
 * @param currentTan Pointer to a string containing the TAN to be checked
 * @return 0 if the TAN is valid, -10 if not, otherwise a negative error code
**/

int tanValid(unsigned char *lastTan, unsigned char *currentTan){
	uint8_t hash[16];
	MD5_CTX context;
	int i = 0;
	int equal = TAN_INVALID;
	
	// create a copy of the TAN to check
	char *generatedTan = (char *)malloc(16 * sizeof(char));
	strcpy(generatedTan, currentTan);
	generatedTan[15] = 0;
	
	// Explanation:
	// Let f(x) be the function above to generate a TAN from a hash value
	// Let g(x) be f(MD5(x)), with MD5 being the hash function to create a 120-bit hash value from an arbitraty string
	// Let lastTan be g(PIN)^999
	// Then the next valid TAN generated by the SC Simulator is g(PIN)^998, the one afterwards g(PIN)^997 etc.
	// Notice that a new valid TAN cannot be generated with the knowledge of the lastTan alone, as it is impossible to efficiently calculate g^-1
	// To check if a newly generated TAN is valid we check if g(generatedTan) == lastTan, e.g. g(g(PIN)^998) == g(PIN)^999
	// Since the user may have skipped one or more TANs, we need to check if applying g() to the generated TAN multiple times leads to an equality with g(PIN)^1000
	for(i=0; i<1000; i++){
		MD5_Init(&context);
		MD5_Update(&context, generatedTan, 15);
		MD5_Final(hash, &context);
		generatedTan = generateTANfromHash(hash);
		if(strcmp(lastTan, generatedTan) == 0){
			equal = 0;
			break;
		}
	}

	free(generatedTan);
	return equal;
}






/**
 * Generates a new random PIN and saves g(PIN)^999 as initial reference value for further TAN validations, with g(x) being f(MD5(x)),
 * f being the function to generate a 15 character long TAN from a 120 bit hash and MD5 being the MD5 hash function
 *
 * @param accountNumber The bank acount number for which the PIN should be generated
 * @return Pointer to a string containing the PIN number digits
**/

char *generatePIN(int accountNumber){
	
	// generate a random number between 0 and 999999
	unsigned long numRand = (unsigned long) RAND_MAX + 1;
	unsigned long binSize = numRand / 1000000;
	unsigned long defect = numRand % 1000000;
	long r = 0;
	
	srand(time(NULL));
	
	r = rand() % 1000000;

	// allocate memory for the 6 digit PIN and save the generated number
	char *pin = (char*) malloc(7 * sizeof(char));
	sprintf(pin, "%06ld", r);
	pin[6] = 0;
	
	uint8_t hash[16];
	MD5_CTX context;
	int i = 0;
	
	/*for(i=0; i<6; i++){
		pin[i] = '1';
	}*/
	
	// stretch the 6 digit PIN to a 15 digit string with leading zeros
	char *generatedTan = (char *)malloc(16 * sizeof(char));
	for(i=0; i<9; i++){
		generatedTan[i] = '0';
	}
	generatedTan[15] = 0;
	memcpy(generatedTan + 9, pin, 6);
	
	// create g(PIN)^999 as initial reference value for further TAN validations, with g(x) being f(MD5(x)),
	// f being the function to generate a 15 character long TAN from a 120 bit hash and MD5 being the MD5 hash function
	for(i=0; i<999; i++){
		MD5_Init(&context);
		MD5_Update(&context, generatedTan, 15);
		MD5_Final(hash, &context);
		generatedTan = generateTANfromHash(hash);
	}
	
	// save the reference value in the database
	updateLastTan(accountNumber, generatedTan);
	
	return pin;
}



/**
 * Main function, which should be called with one of the following syntaxes (assuming the program is compiled as 'tan_generator'):
 * 
 * tan_generator pin [account_number] [db_user] [db_password] [db_database]
 * tan_generator validate [account_number] [tan] [db_user] [db_password] [db_database]
 *
 * The first call creates a new PIN number for a given bank account and saves its hash as reference value for later TAN validations in the database.
 * The second call checks if a tan is valid and saves the tan as being used to the database if that's the case
**/

int main(int argc, char **argv){
	int result = 0;
	
	// mysql != NULL indicates that the program is already running
	if(mysql != NULL){
		printf("%d\n", INTERNAL_ERROR);
		return INTERNAL_ERROR;
	}
	
	// get the arguments
	char *command1 = argv[1];
	char *command2 = argv[2];
	char *command3 = argv[3];
	if(argc == 6){
		db_user = argv[3];
		db_password = argv[4];
		db_name = argv[5];
	}
	else if(argc == 7){
		db_user = argv[4];
		db_password = argv[5];
		db_name = argv[6];
	}
	
	
	// connect to the database
	result = mysqlConnect();
	if(result != RETURN_SUCCESS){
		printf("%d\n", result);
		return result;
	}
	result = mysqlSelectDB();
	if(result != RETURN_SUCCESS){
		printf("%d\n", result);
		return result;
	}
	
	// convert the given account number from a string to a number
	unsigned long lng_account = strtoul(command2, NULL, 10);
	if(lng_account == 0 || lng_account > INT_MAX){
		mysqlCloseConnection();
		printf("%d\n", PARAMETER_ERROR);
		return PARAMETER_ERROR;
	}
	
	// check if a new PIN should be generated
	if(strcmp(command1, "pin") == 0){
		char *pin = generatePIN((int) lng_account);
		printf("%s\n", pin);
	}
	// check if a given TAN should be validated
	else if(strcmp(command1, "validate") == 0){
		if(strlen(command3) != 15){
			mysqlCloseConnection();
			printf("%d\n", PARAMETER_ERROR);
			return PARAMETER_ERROR;
		}
		char *lastTan = getLastTan((int) lng_account);
		if(tanValid(lastTan, command3) == 0){
			updateLastTan((int) lng_account, command3);
			publishTan((int) lng_account, command3);
			printf("%d\n", RETURN_SUCCESS);
			return RETURN_SUCCESS;
		}
		else{
			printf("%d\n", TAN_INVALID);
			return TAN_INVALID;
		}
	}
	
	// close database connection
	result = mysqlCloseConnection();
	if(result != RETURN_SUCCESS){
		printf("%d\n", result);
		return result;
	}

	
}
