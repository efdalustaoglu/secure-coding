
#include <stdio.h>
#include <stdlib.h>
#include <limits.h>
#include <math.h>

#define RETURN_SUCCESS 0
#define PARAMETER_ERROR -1
#define IO_ERROR -2
#define FILE_FORMAT_ERROR -3


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
		return -1;
	}
	
	// check if path is NULL
	if(path == NULL){
		return PARAMETER_ERROR;
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
		return IO_ERROR;
	}
	else{
		
		// read the file content as string ("%<buffer size>s" to prevent overflows), before converting to numerical values.
		// Reading directly as integer (-> fscanf(..., "%d", ...)) or conversion to integer with atoi() doesn't
		// prevent overflows! Instead, strtoul() is used for secure conversion
		if(!feof(textfile)){
			if(fscanf(textfile, " SENDER_ACCOUNT: %15s RECIPIENT_ACCOUNT: %15s TAN: %15s AMOUNT: %15s ", sender, recipient, tan, amount)){
				sender[15] = 0;
				recipient[15] = 0;
				tan[15] = 0;
				amount[15] = 0;
				
				unsigned long lng_sender = 0;
				unsigned long lng_recipient = 0;
				double dbl_amount = 0;
		
				// strtoul is for unsigned long; however a similar function for int doesn't exist
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
				
				if(tan[0] == 0){
					fclose(textfile);
					return FILE_FORMAT_ERROR;
				}
		
				// read the amount as double, as there's no such function for float
				dbl_amount = strtod(amount, NULL);
				if(dbl_amount == 0.0 || dbl_amount == HUGE_VAL || dbl_amount == -HUGE_VAL){
					fclose(textfile);
					return FILE_FORMAT_ERROR;
				}
		
				// file was read successfully
				printf("%d, %d, %s, %.2f", (int) lng_sender, (int) lng_recipient, tan, (float) dbl_amount);
		
			}
			else{
				fclose(textfile);
				return FILE_FORMAT_ERROR;
			}
		}
		
		fclose(textfile);
		return RETURN_SUCCESS;
	}
	
}


int main(int argc, char **argv){
	if(argc == 2){
		const char *path = argv[1];
		return parseFile(path);
	}
	
	return PARAMETER_ERROR;
}