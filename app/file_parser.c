
#include <stdio.h>
#include <stdlib.h>
#include <limits.h>
#include <math.h>
#include <string.h>

#define RETURN_SUCCESS 0
#define PARAMETER_ERROR -1
#define IO_ACCESS_ERROR -2
#define IO_READ_ERROR -3
#define FILE_FORMAT_ERROR -4
#define MEMORY_ERROR -5


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
int parseFile(const char *path) {
	int path_length = 0;
	
	// file to read from
	FILE *textfile = NULL;
	
	// buffers to store the file content
	char sender[16] = {0};
	char recipient[16] = {0};
	char tan[16] = {0};
	char amount[16] = {0};
	
	// check if system had enough memory available
	if(sender == NULL || recipient == NULL || tan == NULL || amount == NULL){
		printf("%d", MEMORY_ERROR);
		return MEMORY_ERROR;
	}
	
	// check if path is NULL
	if(path == NULL){
		printf("%d", PARAMETER_ERROR);
		return PARAMETER_ERROR;
	}
	
	// check if path is NULL terminated
	while(path[path_length] != 0 && path_length < FILENAME_MAX){
		path_length++;
	}
	
	// if path is not correctly terminated, calling fopen would result in an overflow
	if(path_length == FILENAME_MAX){
		printf("%d", PARAMETER_ERROR);
		return PARAMETER_ERROR;
	}
	
	if((textfile = fopen(path, "r")) == NULL){
		printf("%d", IO_ACCESS_ERROR);
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
					printf("%d", IO_READ_ERROR);
					return IO_READ_ERROR;
				}
				else if(stringLength == 1){
					// only a NULL terminal has been copied => the line doesn't contain any information after a
					// promising keyword (e.g. SENDER_ACCOUNT)
					fclose(textfile);
					printf("%d", FILE_FORMAT_ERROR);
					return FILE_FORMAT_ERROR;
				}
			}
		}
		
		// check if all four strings have been set to a value 
		if(*sender == 0 || *recipient == 0 || *amount == 0 || *tan == 0){
			fclose(textfile);
			printf("%d", FILE_FORMAT_ERROR);
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
			printf("%d", FILE_FORMAT_ERROR);
			return FILE_FORMAT_ERROR;
		}
		
		lng_recipient = strtoul(recipient, NULL, 10);
		if(lng_recipient == 0 || lng_recipient > INT_MAX){
			fclose(textfile);
			printf("%d", FILE_FORMAT_ERROR);
			return FILE_FORMAT_ERROR;
		}
		
		// read the amount as double, as there's no such function for float
		dbl_amount = strtod(amount, NULL);
		if(dbl_amount == 0.0 || dbl_amount == HUGE_VAL || dbl_amount == -HUGE_VAL){
			fclose(textfile);
			printf("%d", FILE_FORMAT_ERROR);
			return FILE_FORMAT_ERROR;
		}
	
		printf("%d %d %d %.2f %s\n", RETURN_SUCCESS, (int) lng_sender, (int) lng_recipient, (float) dbl_amount, tan);
		
		fclose(textfile);
		return RETURN_SUCCESS;
	}
	
}


/**
 * Function that is called first when the program is executed. The program expects one argument, that is the path to the file
 * that contains the transaction details. Return value is the value returned from parseFile(...) or -1 if the number of arguments
 * provided is wrong
**/
int main(int argc, char **argv){
	if(argc == 2){
		const char *path = argv[1];
		return parseFile(path);
	}
	else{
		printf("%d", PARAMETER_ERROR);
		return PARAMETER_ERROR;
	}
}
