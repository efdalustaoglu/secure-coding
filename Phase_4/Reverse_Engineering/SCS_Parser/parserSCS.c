#include <errno.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include "mysql.h"
#include <openssl/sha.h>


int32_t ProceedTransaction(MYSQL *mysql , char *userID, char *accountID, char *iban, double amount, int32_t a7, char *description) {
	bool askApproval = false;
	MYSQL_RES result;
	MYSQL_ROW row;
	
	if (amount <= 10000) {
		askApproval = amount < 10000 | amount != 10000 & amount >= 10000;
	}
	int32_t approval = askApproval;
	
	if(amount < 0.0){
		return -13;
	}
	else{
		char *query = malloc(0x2000);
		sprintf(query, "SELECT balance FROM accounts WHERE account_id = %s", accountID);
		queryLen = strlen(query);
		status = mysql_real_query(mysql, query, queryLen);
		free(query);
		
		if(status == 0){
			result = mysql_store_result(mysql);
			if(result != NULL){
				row = mysql_fetch_row(result);
				double sBalance = strtod(row[0], NULL);
				if(sBalance != 0.0){
					if(sBalance >= amount){
						query = malloc(0x2000);
						sprintf(query, "INSERT INTO transactions (account_id, amount, approved, iban, description,transactioncode_id) VALUES ('%s','%f','%d','%s','%s','%d')", accountID, amount, approval, iban, description, 31846);
						queryLen = strlen(query);
						status = mysql_real_query(mysql, query, queryLen);
						free(query);
						
						if(status == 0){
							if(askApproval){
								query = malloc(0x2000);
								sprintf(query, "UPDATE accounts SET balance = %f WHERE account_id = %s", sBalance - amount, accountID);
								queryLen = strlen(query);
								status = mysql_real_query(mysql, query, queryLen);
								free(query);
								
								if(status == 0){
									query = malloc(0x2000);
									sprintf(query, "SELECT COUNT(*) FROM accounts WHERE iban = '%s'", iban);
									queryLen = strlen(query);
									status = mysql_real_query(mysql, query, queryLen);
									free(query);
									
									if(status == 0){
										result = mysql_store_result(mysql);
										if(result != NULL){
											row = mysql_fetch_row(result);
											if(strcmp(row[0], "0") != 0){
												query = malloc(0x2000);
												sprintf(query, "SELECT balance FROM accounts WHERE iban = '%s'", iban);
												queryLen = strlen(query);
												status = mysql_real_query(mysql, query, queryLen);
												free(query);
												if(status == 0){
													result = mysql_store_result(mysql);
													if(result != NULL){
														row = mysql_fetch_row(result);
														double rBalance = strtod(row[0], NULL);
														if(rBalance != 0){
															query = malloc(0x2000);
															sprintf(query, "UPDATE accounts SET balance = %f WHERE iban = '%s'", rBalance + amount, iban);
															queryLen = strlen(query);
															status = mysql_real_query(mysql, query, queryLen);
															free(query);
															if(status == 0){
																return 0;
															}
															else{
																return -29;
															}
														}
														else{
															return -28;
														}
													}
													else{
														return -28;
													}
												}
												else{
													return -28;
												}
											}
											else{
												return -14;
											}
										}
										else{
											return -27;
										}
									}
									else{
										return -27;
									}
								}
								else{
									return -26;
								}
							}
							else{
								return 0;
							}
						}
						else{
							return -25;
						}
					}
					else{
						return -12;
					}
				}
				else{
					return -22;
				}
			}
			else{
				return -22;
			}
		}
		else{
			return -22;
		}
	}
}


int main(int argc, char ** argv) {
	if(argc > 6){
		char *transactionFile = malloc(strlen(argv[1] + 1));
		strcpy(transactionFile, argv[1]);
		char *arg2 = malloc(strlen(argv[2] + 1));
		strcpy(arg2, argv[2]);
		char *arg3 = malloc(strlen(argv[3]) + 1);
		strcpy(arg3, argv[3]);
		char *arg4 = malloc(strlen(argv[4]) + 1);
		strcpy(arg4, argv[4]);
		char *arg5 = malloc(strlen(argv[5]) + 1);
		strcpy(arg5, argv[5]);
		char *arg6 = malloc(strlen(argv[6]) + 1);
		strcpy(arg6, argv[6]);
		
		int32_t result;
		FILE *file = fopen(transactionFile, "r");
		free(transactionFile);
		if (file == NULL) {
			perror("Error");
			return 2;
		}
		else {
			seek(file, 0, SEEK_END);
			int32_t fileOffset = ftell(file);
			fseek(file, 0, SEEK_SET);
			
			int32_t fileSize = fileOffset + 1;
			char *mem1 = malloc(fileSize);
			char *mem2 = malloc(fileSize);
			char *mem3 = NULL;
			char *iban = NULL;
			char *description = NULL;
			char *ibanChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			char *descriptionChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.?!: ";
			
			if(mem1 != NULL){
				if(mem2 != NULL){
					char *token = NULL;
					int32_t result;
					
					unsigned char hash[32] = "";
					int32_t i = 0;
					while(i < 21){
						hash[i] = 0;
						i++;
					}
					
					while(true){
						memset(mem1, 0, size);
						memset(mem2, 0, size);
						fgets(mem1, fileOffset, file);
						if(memcmp(mem1, mem2, fileOffset) != 0) {
							memcpy(mem2, mem1, fileSize);
							token = strtok(mem1, ",");
							if(token == NULL) {
								continue;
							}
							
							if(strlen(token) != 34){
								result = -34;
							}
							
							if(strspn(token, ibanChars) != 34){
								result = -35;
							}
							else{
								iban = malloc(strlen(token) + 1);
								strcpy(iban, token);
							}
							
							char *token2 = strtok(NULL, ",");
							if(token2 == NULL){
								result = -31;
							}
							errno = 0;
							double amount = 0.0;
							char *amountStr = NULL;
							if(token2 != NULL){
								amount = strtod(token2, NULL);
							}
							if(amount == 0.0 || errno != 0){
								result = -36;
							}
							else{
								amountStr = malloc(strlen(token2) + 1);
								strcpy(amountStr, token2);
							}
							
							
							char *token3 = strtok(NULL, ",");
							if(token3 == NULL){
								result = -31;
							}
							else if(strlen(token3) >= 151){
								result = -37;
							}
							
							if(strspn(token3, descriptionChars) != strlen(token3) - 1){
								result = -38;
							}
							else{
								description = malloc(strlen(token3) + 1);
								strcpy(description, token3);
							}
							
							if(result == 0){
								int32_t size = 0;
								int32_t len1 = strlen(iban);
								int32_t len2 = strlen(amountStr);
								int32_t len3 = strlen(arg4);
								int32_t len4 = strlen(arg5);
								int32_t len = len1 + len2 + len3 + len4 + 1;
								
								char infoString[len];
								memset(infoString, 0, len);
								
								char temp[32] = "";
								memset(temp, 0, 33);
								
								char temp2[64] = "";
								memset(temp2, 0, 1 + strlen(infoString) + strLen(temp));
								
								strcat(infoString, iban);
								strcat(infoString, amountStr);
								strcat(infoString, arg4);
								strcat(infoString, arg5);
								
								SHA256(infoString, strlen(infoString), temp);
								memcpy(temp2, hash, 32); 
								memcpy((temp2 + 32), temp, 32);
								
								char temp3[64] = "";
								char temp4[2] = "";
								int32_t i = 0;
								for(i=0; i<64; i++){
									sprintf(temp4, "%02x", *(temp2+i));
									strcat(temp3, temp4);
								}
								
								SHA256(temp3, strlen(temp3), hash);
								
							}
							
							if(amountStr != NULL){
								free(amountStr);
							}
							if(iban != NULL){
								free(iban);
							}
							if(description != NULL){
								free(description);
							}
						}
						else{
							break;
						}
					}
					
					char temp5[33] = "";
					char temp6[2] = "";
					int32_t i = 0;
					for(i=0; i<32; i++){
						sprintf(temp6, "%02x", *(hash+i));
						strcat(temp5, temp6);
					}
					
					fseek(file, 0, SEEK_SET);
					if (strcmp(temp5, arg6) == 0){
						while(true){
							result = 0;
							memset(mem1, 0, size);
							memset(mem2, 0, size);
							fgets(mem1, fileOffset, file);
							if(memcmp(mem1, mem2, fileOffset) != 0) {
								memcpy(mem2, mem1, fileSize);
								token = strtok(mem1, ",");
								if(token == NULL) {
									continue;
								}
							
								if(strlen(token) != 34){
									result = -34;
								}
							
								if(strspn(token, ibanChars) != 34){
									result = -35;
								}
								else{
									iban = malloc(strlen(token) + 1);
									strcpy(iban, token);
								}
							
								char *token2 = strtok(NULL, ",");
								if(token2 == NULL){
									result = -31;
								}
								errno = 0;
								double amount = 0.0;
								char *amountStr = NULL;
								if(token2 != NULL){
									amount = strtod(token2, NULL);
								}
								if(amount == 0.0 || errno != 0){
									result = -36;
								}
								else{
									amountStr = malloc(strlen(token2) + 1);
									strcpy(amountStr, token2);
								}
							
								char *token3 = strtok(NULL, ",");
								if(token3 == NULL){
									result = -31;
								}
								else if(strlen(token3) >= 151){
									result = -37;
								}
							
								if(strspn(token3, descriptionChars) != strlen(token3) - 1){
									result = -38;
								}
								else{
									description = malloc(strlen(token3) + 1);
									strcpy(description, token3);
								}
								
								
								if(result == 0){
									MYSQL *mysql;
								
									mysql = mysql_init(NULL);
									if(mysql == NULL){
										result = -1;
									}
								
									mysql = mysql_real_connect(mysql, "127.0.0.1", "samurai", "", "scbank", 0, NULL, 0);
									if(mysql == NULL){
										result = -2;
									}
								
									if(result == 0){
										mysql_autocommit(my, 0);
										result = ProceedTransaction(mysql, arg2, arg3, iban, amount, description);
									
										if (result == 0){
											mysql_commit(mysql);
										}
										else{
											mysql_rollback(mysql);
										}

										mysql_autocommit(mysql, 1);
									}
								
									mysql_close(mysql);
								}	
							}
							else{
								break;
							}
							
							printf("%d \n", result);
						}

					}
					else{
						printf("-15");	
						return 0;
					}
				}
				else{
					return -1;
				}
			}
			else{
				return -1;
			}
		}	
	}
	else{
		return 1;
	}
	
}