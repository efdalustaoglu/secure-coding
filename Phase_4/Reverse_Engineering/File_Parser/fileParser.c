#include <errno.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include "mysql.h"


int32_t ProceedTransaction(MYSQL *mysql , char *userID, char *accountID, char *tan, char *iban, double amount, int32_t a7, char *description) {
	bool askApproval = false;
	MYSQL_RES *result;
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
		sprintf(query, "SELECT COUNT(*) FROM transactioncodes WHERE code = %s and active = 1 and user_id = %s", tan, userID);
		int8_t queryLen = strlen(query);
		int32_t status = mysql_real_query(mysql, query, queryLen);
		free(query);
		
		if(status == 0){
			result = mysql_store_result(mysql);
			if(result == NULL){
				return -21;
			}
			
			row = mysql_fetch_row(result);
			if(strcmp(row[0], "0") != 0){
				query = malloc(0x2000);
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
								sprintf(query, "UPDATE transactioncodes SET active = 0 WHERE code = %s", tan);
								queryLen = strlen(query);
								status = mysql_real_query(mysql, query, queryLen);
								free(query);
								
								if(status == 0){
									query = malloc(0x2000);
									sprintf(query, "SELECT transactioncode_id FROM transactioncodes WHERE code = %s", tan);
									queryLen = strlen(query);
									status = mysql_real_query(mysql, query, queryLen);
									free(query);
									
									if(status == 0){
										result = mysql_store_result(mysql);
										if(result != NULL){
											row = mysql_fetch_row(result);
											char *tanID = malloc(1+strlen(row[0]));
											strcpy(tanID, row[0]);
											
											query = malloc(0x2000);
											sprintf(query, "INSERT INTO transactions (account_id, transactioncode_id,amount, approved, iban, description) VALUES ('%s','%s','%f','%d','%s','%s')", accountID, tanID, amount, approval, iban, description);
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
											return -24;
										}
									}
									else{
										return -24;
									}
								}
								else{
									return -23;
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
			else{
				return -11;
			}
		}
		else{
			return -21;
		}
	}
}


int main(int argc, char ** argv) {
	if(argc > 2){
		char *transactionFile = malloc(strlen(argv[1] + 1));
		strcpy(transactionFile, argv[1]);
		char *arg2 = malloc(strlen(argv[2] + 1));
		strcpy(arg2, argv[2]);
		char *arg3 = malloc(strlen(argv[3]) + 1);
		strcpy(arg3, argv[3]);
		
		int32_t result;
		FILE *file = fopen(transactionFile, "r");
		free(transactionFile);
		if (file == NULL) {
			perror("Error");
			return 2;
		}
		else {
			fseek(file, 0, SEEK_END);
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
					while(true){
						memset(mem1, 0, fileSize);
						memset(mem2, 0, fileSize);
						fgets(mem1, fileOffset, file);
						if(memcmp(mem1, mem2, fileOffset) != 0) {
							memcpy(mem2, mem1, fileSize);
							token = strtok(mem1, ",");
							if(token == NULL) {
								continue;
							}
							
							if(strlen(token) != 15){
								result = -32;
							}
							else if (strspn(token, "0123456789") != 15){
								result = -33;
							}
							else{
								mem3 = malloc(strlen(token) + 1);
								strcpy(mem3, token);
							}
							
							char *token2 = strtok(NULL, ",");
							if(token2 == NULL){
								result = -31;
							}
							else if(strlen(token2) != 34){
								result = -34;
							}
							
							if(strspn(token2, ibanChars) != 34){
								result = -35;
							}
							else{
								iban = malloc(strlen(token2) + 1);
								strcpy(iban, token2);
							}
							
							char *token3 = strtok(NULL, ",");
							if(token3 == NULL){
								result = -31;
							}
							errno = 0;
							double amount = 0.0;
							if(token3 != NULL){
								amount = strtod(token3, NULL);
							}
							if(amount == 0.0 || errno != 0){
								result = -36;
							}
							
							char *token4 = strtok(NULL, ",");
							if(token4 == NULL){
								result = -31;
							}
							else if(strlen(token4) >= 151){
								result = -37;
							}
							
							if(result != 0 || strspn(token4, descriptionChars) != strlen(token4)){
								result = -38;
							}
							else{
								description = malloc(strlen(token4) + 1);
								strcpy(description, token4);
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
									mysql_autocommit(mysql, 0);
									result = ProceedTransaction(mysql, arg2, arg3, mem3, iban, amount, 0, description);
									
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
						
						if(mem3 != NULL){
							free(mem3);
						}
						if(iban != NULL){
							free(iban);
						}
						if(description != NULL){
							free(description);
						}
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
