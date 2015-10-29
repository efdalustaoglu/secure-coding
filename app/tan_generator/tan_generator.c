
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>

#define RETURN_SUCCESS 0
#define PARAMETER_ERROR -1
#define IO_ACCESS_ERROR -2
#define INTERNAL_ERROR -3


/**
 * Sends previously generated TANs to an email address. The function relies on the use of the command line
 * tool 'sendmail'. If not existing on the system, please download and install the package 'ssmtp', e.g. via
 * the Ubuntu Software Center. In addition two configuration files have to be set up correctly, 'ssmtp.conf'
 * and 'revaliases', both located at 'etc/ssmtp/'. This code should be shipped with both of these config files.
 * 
 * @param tans Pointer to a NULL terminated string containing the sequence of TANs
 * @param emailAddress Pointer to a NULL terminated string containing a valid email address
 * @param recipientName Pointer to a NULL terminated string containing the name of the recipient
 * 
 * @return 0 if successful, a value different from 0 indicating the error code if not successful
**/
int sendTANs(char *tans, char *emailAddress, char *recipientName){

	int result = 0;
	char command[100];
	
	if(tans == NULL || emailAddress == NULL || recipientName == NULL){
		return PARAMETER_ERROR;
	}
    
    // create the body of the email
    char body[180] = {0};
    result = sprintf(body, "Subject: Generated TANs\n\n\nDear %s,\n\nPlease find below a list of newly generated TAN numbers.\n\nSincerely yours,\nSecure Coding Group 6\n\n\nTAN numbers:\n\n", recipientName);   
    if(result < 0){
		return INTERNAL_ERROR;
	}
    
    // create a temporary file name
    char tempFile[256];
    if(strcpy(tempFile, tempnam("/tmp","sendmail")) == NULL){
		return INTERNAL_ERROR;
	}
	
	// create a file and open it
	FILE *file = fopen(tempFile,"w");
	if(file == NULL){
		return IO_ACCESS_ERROR;
	}
	
	// write the email content to the file
    result = fprintf(file, "%s%s\n", body, tans);
    if(result < 0){
		fclose(file);
		return IO_ACCESS_ERROR;
	}
	
	// close the file
    fclose(file);

	// create the command
    result = sprintf(command,"sendmail -s \"TANs\" %s < %s", emailAddress, tempFile); // prepare command.
    if(result < 0){
		return INTERNAL_ERROR;
	}
	
	// execute the command and return the exit code of the call
    result = system(command);
    return result;
}


/**
 * Checks if two TANs are equal
 * 
 * @param tan1 Pointer to a NULL terminated string containing a tan
 * @param tan2 Pointer to a NULL terminated string containing a second tan
 * 
 * @return 0 if the two TANs are equal, -1 otherwise
 **/
int tansEqual(char *tan1, char *tan2){
	
	// check if parameters are valid
	if(tan1 == NULL || tan2 == NULL || *tan1 == 0 || *tan2 == 0){
		return -2;
	}
	
	// iterate through both TANs until they differ in a character
	while(*tan1 != 0 && *tan2 != 0 && *tan1 == *tan2){
		tan1++;
		tan2++;
	}
	
	// if all chracters were identical until the end of both TANs, the TANs are equal
	if(*tan1 == 0 && *tan2 == 0){
		return 0;
	}
	else{
		return -1;
	}
}


/**
 * Checks if a TAN is existing in a given sequence of TANs
 * 
 * @param tanBuffer Pointer to a NULL terminated string containing a sequence of TANs. Each TAN is expected
 * to be 15 characters long, followed by a single character for separation (e.g. '\n' or ' ')
 * @param tanCount Maximum number of TANs the sequence can hold
 * @param tan Pointer to a NULL terminated string containing a TAN
 * 
 * @return 0 if the given TAN was found in the sequence, otherwise -1
 **/
int tanExists(char *tanBuffer, int tanCount, char *tan){
	int i = 0;
	int equal = 0;
	
	// loop through all previously created TANs and compare with the single TAN
	for(i = 0; i<tanCount; i++){
		equal = tansEqual(&tanBuffer[i * 16], tan);
		// equal == 0 means an equal TAN has been found
		if(equal == 0){
			return 0;
		}
		// equal == -2 means the end of the sequence of previously created TANs has been reached
		else if(equal == -2){
			return -1;
		}
	}
	
	return -1;
}


/**
 * Generates a sequence of unique TANs
 * 
 * @param tanBuffer Pointer to a char buffer designated to store the sequence
 * @param tanCount Number of TANs the buffer can hold
 * 
 * @return 0 if successful, a negative value indicating an error code if not successful
 **/
int generateTANs(char *tanBuffer, int tanCount){
	
	// the alphabet that is used for characters of the TAN
	static const char alphanum[] = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

	// check parameters
	if(tanBuffer == NULL || tanCount <= 0){
		return PARAMETER_ERROR;
	}
	
	// definition of temporary variables to perform the calculation of the TANs
	int tanID = 0;
	int tanChar = 0;
	char tempTan[16] = {0};
	
	// initialization of random number generator
	srand(time(NULL));
	
	// loop to create tanCount TANs
	while(tanID < tanCount){
		
		// loop to create one tan, consisting of 15 characters and a trailing NULL character
		for(tanChar = 0; tanChar < 15; tanChar++){
			tempTan[tanChar] = alphanum[rand() % (sizeof(alphanum) - 1)];
		}
		tempTan[15] = 0;
		
		// check if TAN has already been created and add it to the sequence
		if(tanExists(tanBuffer, tanCount, tempTan) == -1){
			tempTan[15] = ' ';
			memcpy(&tanBuffer[tanID * 16], tempTan, 16);
			tanID++;
		}
	}
	
	// finish the sequence of TANs with a NULL character
	tanBuffer[tanCount * 16 - 1] = 0;
	
	return RETURN_SUCCESS;
}


/**
 * Function that is called first when the program is executed. The program can be called with no, one or two arguments.
 * If no argument is given, the program generates 100 unique TANs and writes them (each separated by a space) to stdout.
 * The first argument provided has to be a valid email address, to which the TANs are sent in addition to the stdout print.
 * If a second argument is given, this one has to be the name of the recipient of the email.
**/
int main(int argc, char **argv){
	int result = 0;
	char tans[1600] = {0};
	int tanCount = 100;
	
	// generate the new TANs
	result = generateTANs(tans, tanCount);
	if(result < 0){
		return result;
	}
	else{
		// print the TANs to stdout
		printf("%s\n", tans);
	}
	
	// check if an email address and the name of the customer have been provided (as first and second argument) 
	if(argc >= 2 && argc <= 3){
		char *email = argv[1];
		if(argc == 3){
			char *addressee = argv[2];
			result = sendTANs(tans, email, addressee);
		}
		else{
			result = sendTANs(tans, email, "customer");
		}
		
		// check if an error occured; for error codes of the "sendmail" command see usr/include/sysexits.h
		if(result != 0){
			return result;
		}
	}
	else if(argc >= 4){
		// if more than 2 arguments have been entered, we return an error
		return PARAMETER_ERROR;
	}
	
	return RETURN_SUCCESS;
}
