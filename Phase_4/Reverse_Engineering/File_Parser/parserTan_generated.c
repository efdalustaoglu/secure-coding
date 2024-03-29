//
// This file was generated by the Retargetable Decompiler
// Website: https://retdec.com
// Copyright (c) 2016 Retargetable Decompiler <info@retdec.com>
//

#include <errno.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

// ----------------- Float Types Definitions ------------------

typedef double float64_t;
typedef long double float80_t;

// ------------------------ Structures ------------------------

struct struct__IO_FILE {
    int32_t e0;
    char * e1;
    char * e2;
    char * e3;
    char * e4;
    char * e5;
    char * e6;
    char * e7;
    char * e8;
    char * e9;
    char * e10;
    char * e11;
    struct struct__IO_marker * e12;
    struct struct__IO_FILE * e13;
    int32_t e14;
    int32_t e15;
    int32_t e16;
    int16_t e17;
    char e18;
    char e19[1];
    char * e20;
    int64_t e21;
    char * e22;
    char * e23;
    char * e24;
    char * e25;
    int32_t e26;
    int32_t e27;
    char e28[40];
};

struct struct__IO_marker {
    struct struct__IO_marker * e0;
    struct struct__IO_FILE * e1;
    int32_t e2;
};

// ------------------- Function Prototypes --------------------

int32_t ProceedTransaction(char ** a1, char ** a2, char ** a3, char ** a4, char ** a5, float64_t a6, int32_t a7, char ** a8);

// --------------------- Global Variables ---------------------

float80_t g1 = 0.0; // st7

// ------------------------ Functions -------------------------

// Address range: 0x8048a34 - 0x80491aa
int32_t ProceedTransaction(char ** a1, char ** a2, char ** a3, char ** a4, char ** a5, float64_t a6, int32_t a7, char ** a8) {
    float80_t v1 = a6; // 0x8048a64
    bool v2 = false;
    if (a6 <= 1.0e+4) {
        // if_8048a6f_0_false
        v2 = a6 < 1.0e+4 | a6 != 1.0e+4 & a6 >= 1.0e+4;
        // branch -> after_if_8048a6f_0
    }
    int32_t v3 = v2;
    g1 = v1;
    int32_t result; // bp+150
    if (a6 < 0.0) {
        // after_if_8048a83_0
        result = -13;
        // branch -> 0x80491a4
    } else {
        // if_8048a83_0_false
        if (a6 <= 0.0) {
            // if_8048a83_1_false
            // branch -> after_if_8048a83_0.thread
        }
        char * mem = malloc(0x2000); // 0x8048a9f
        sprintf(mem, "SELECT COUNT(*) FROM transactioncodes WHERE code = %s and active = 1 and user_id = %s", a4, a2);
        strlen(mem);
        int32_t v4 = (int32_t)a1; // 0x8048af9
        mysql_real_query();
        free(mem);
        if (v4 == 0) {
            // 0x8048b22
            mysql_store_result();
            if (v4 == 0) {
                // 0x80491a4
                return -21;
            }
            // 0x8048b40
            mysql_fetch_row();
            int32_t str = *(int32_t *)v4; // 0x8048b51
            bool v5 = false; // 0x8048b65
            if ((0x1000000 * ((((int32_t)(v5 | strncmp((char *)str, (char *)0x8049b36, 2) % 2 == 0) || str & -256) ^ 1) - (0x8049b00 || (int32_t)v5)) || 0xffffff) >= 0x1ffffff) {
                char * mem2 = malloc(0x2000); // 0x8048b89
                sprintf(mem2, "SELECT balance FROM accounts WHERE account_id = %s", a3);
                strlen(mem2);
                mysql_real_query();
                free(mem2);
                if (v4 == 0) {
                    // 0x8048c05
                    mysql_store_result();
                    if (v4 != 0) {
                        // 0x8048c23
                        mysql_fetch_row();
                        int32_t str2 = *(int32_t *)v4; // 0x8048c34
                        float64_t str_as_d = strtod((char *)str2, NULL); // 0x8048c46
                        float80_t v6 = str_as_d; // 0x8048c49
                        int32_t * str3; // 0x8048dbb_0
                        char * mem3; // 0x8048c8d
                        char * mem4; // 0x8048d10
                        char * mem5; // 0x8048ddf
                        char * mem6; // 0x8048e02
                        char * mem7; // 0x8048ec5
                        char * mem8; // 0x8048f52
                        char * mem9; // 0x8049035
                        uint32_t strncmp_rc; // 0x804900f
                        int32_t len; // 0x8048dd0
                        bool v7;
                        bool v8; // 0x8049011
                        int32_t str4; // 0x8048ffd
                        bool v9; // 0x8048c75
                        int32_t v10; // 0x8048d97
                        int32_t v11; // 0x8048fd9
                        int32_t str5; // 0x80490bc
                        if (str_as_d >= 0.0) {
                            // if_8048c4e_0_false
                            if (str_as_d <= 0.0) {
                                // if_8048c4e_1_false
                                if (str_as_d != 0.0) {
                                    // 0x8048c69
                                    g1 = v1;
                                    v7 = false;
                                    v9 = false;
                                    if (str_as_d >= a6) {
                                        // if_8048c71_0_false
                                        if (str_as_d <= a6) {
                                            // if_8048c71_1_false
                                            v7 = str_as_d != a6;
                                            v9 = true;
                                            // branch -> after_if_8048c71_0
                                        } else {
                                            v7 = true;
                                            v9 = false;
                                        }
                                    }
                                    // after_if_8048c71_0
                                    if (((int32_t)(v9 || v7) || str2 & -256) == 1) {
                                        // 0x8048c86
                                        mem3 = malloc(0x2000);
                                        sprintf(mem3, "UPDATE transactioncodes SET active = 0 WHERE code = %s", a4);
                                        strlen(mem3);
                                        mysql_real_query();
                                        free(mem3);
                                        if (v4 == 0) {
                                            // 0x8048d09
                                            mem4 = malloc(0x2000);
                                            sprintf(mem4, "SELECT transactioncode_id FROM transactioncodes WHERE code = %s", a4);
                                            strlen(mem4);
                                            mysql_real_query();
                                            free(mem4);
                                            if (v4 == 0) {
                                                // 0x8048d8c
                                                mysql_store_result();
                                                v10 = v4;
                                                if (v10 != 0) {
                                                    // 0x8048daa
                                                    mysql_fetch_row();
                                                    str3 = (int32_t *)v10;
                                                    len = strlen((char *)*str3);
                                                    mem5 = malloc(1 - -1 * len);
                                                    strcpy(mem5, (char *)*str3);
                                                    mem6 = malloc(0x2000);
                                                    sprintf(mem6, "INSERT INTO transactions (account_id, transactioncode_id,amount, approved, iban, description) VALUES ('%s','%s','%f','%d','%s','%s')", a3, mem5, a6, v3, a5, a8);
                                                    strlen(mem6);
                                                    mysql_real_query();
                                                    free(mem6);
                                                    if (v4 == 0) {
                                                        // 0x8048eae
                                                        if (v2) {
                                                            // 0x8048ebe
                                                            mem7 = malloc(0x2000);
                                                            sprintf(mem7, "UPDATE accounts SET balance = %f WHERE account_id = %s", (float64_t)(v6 - v1), a3);
                                                            strlen(mem7);
                                                            mysql_real_query();
                                                            free(mem7);
                                                            if (v4 == 0) {
                                                                // 0x8048f4b
                                                                mem8 = malloc(0x2000);
                                                                sprintf(mem8, "SELECT COUNT(*) FROM accounts WHERE iban = '%s'", a5);
                                                                strlen(mem8);
                                                                mysql_real_query();
                                                                free(mem8);
                                                                if (v4 == 0) {
                                                                    // 0x8048fce
                                                                    mysql_store_result();
                                                                    v11 = v4;
                                                                    if (v11 != 0) {
                                                                        // 0x8048fec
                                                                        mysql_fetch_row();
                                                                        str4 = *(int32_t *)v11;
                                                                        strncmp_rc = strncmp((char *)str4, (char *)0x8049b36, 2);
                                                                        v8 = false;
                                                                        if ((0x1000000 * ((((int32_t)(v8 || strncmp_rc % 2 == 0) || str4 & -256) ^ 1) - (0x8049b00 || (int32_t)v8)) || 0xffffff) >= 0x1ffffff) {
                                                                            // 0x804902e
                                                                            mem9 = malloc(0x2000);
                                                                            sprintf(mem9, "SELECT balance FROM accounts WHERE iban = '%s'", a5);
                                                                            strlen(mem9);
                                                                            mysql_real_query();
                                                                            free(mem9);
                                                                            if (v4 == 0) {
                                                                                // 0x80490b1
                                                                                mysql_store_result();
                                                                                str5 = v4;
                                                                                if (str5 != 0) {
                                                                                    // 0x80490cf
                                                                                    mysql_fetch_row();
                                                                                    strtod((char *)*(int32_t *)str5, NULL);
                                                                                    result = -28;
                                                                                    // branch -> 0x80491a4
                                                                                } else {
                                                                                    result = -28;
                                                                                }
                                                                            } else {
                                                                                result = -28;
                                                                            }
                                                                        } else {
                                                                            result = -14;
                                                                        }
                                                                    } else {
                                                                        result = -27;
                                                                    }
                                                                } else {
                                                                    result = -27;
                                                                }
                                                            } else {
                                                                result = -26;
                                                            }
                                                        } else {
                                                            result = 0;
                                                        }
                                                    } else {
                                                        result = -25;
                                                    }
                                                } else {
                                                    result = -24;
                                                }
                                            } else {
                                                result = -24;
                                            }
                                        } else {
                                            result = -23;
                                        }
                                    } else {
                                        result = -12;
                                    }
                                } else {
                                    // after_if_8048c59_0
                                    g1 = 0.0;
                                    result = -22;
                                    // branch -> 0x80491a4
                                }
                                // 0x80491a4
                                return result;
                            }
                        }
                        // after_if_8048c59_0.thread
                        // branch -> 0x8048c69
                        // 0x8048c69
                        g1 = v1;
                        v7 = false;
                        v9 = false;
                        if (str_as_d >= a6) {
                            // if_8048c71_0_false
                            if (str_as_d <= a6) {
                                // if_8048c71_1_false
                                v7 = str_as_d != a6;
                                v9 = true;
                                // branch -> after_if_8048c71_0
                            } else {
                                v7 = true;
                                v9 = false;
                            }
                        }
                        // after_if_8048c71_0
                        if (((int32_t)(v9 || v7) || str2 & -256) == 1) {
                            // 0x8048c86
                            mem3 = malloc(0x2000);
                            sprintf(mem3, "UPDATE transactioncodes SET active = 0 WHERE code = %s", a4);
                            strlen(mem3);
                            mysql_real_query();
                            free(mem3);
                            if (v4 == 0) {
                                // 0x8048d09
                                mem4 = malloc(0x2000);
                                sprintf(mem4, "SELECT transactioncode_id FROM transactioncodes WHERE code = %s", a4);
                                strlen(mem4);
                                mysql_real_query();
                                free(mem4);
                                if (v4 == 0) {
                                    // 0x8048d8c
                                    mysql_store_result();
                                    v10 = v4;
                                    if (v10 != 0) {
                                        // 0x8048daa
                                        mysql_fetch_row();
                                        str3 = (int32_t *)v10;
                                        len = strlen((char *)*str3);
                                        mem5 = malloc(1 - -1 * len);
                                        strcpy(mem5, (char *)*str3);
                                        mem6 = malloc(0x2000);
                                        sprintf(mem6, "INSERT INTO transactions (account_id, transactioncode_id,amount, approved, iban, description) VALUES ('%s','%s','%f','%d','%s','%s')", a3, mem5, a6, v3, a5, a8);
                                        strlen(mem6);
                                        mysql_real_query();
                                        free(mem6);
                                        if (v4 == 0) {
                                            // 0x8048eae
                                            if (v2) {
                                                // 0x8048ebe
                                                mem7 = malloc(0x2000);
                                                sprintf(mem7, "UPDATE accounts SET balance = %f WHERE account_id = %s", (float64_t)(v6 - v1), a3);
                                                strlen(mem7);
                                                mysql_real_query();
                                                free(mem7);
                                                if (v4 == 0) {
                                                    // 0x8048f4b
                                                    mem8 = malloc(0x2000);
                                                    sprintf(mem8, "SELECT COUNT(*) FROM accounts WHERE iban = '%s'", a5);
                                                    strlen(mem8);
                                                    mysql_real_query();
                                                    free(mem8);
                                                    if (v4 == 0) {
                                                        // 0x8048fce
                                                        mysql_store_result();
                                                        v11 = v4;
                                                        if (v11 == 0) {
                                                            // 0x80491a4
                                                            return -27;
                                                        }
                                                        // 0x8048fec
                                                        mysql_fetch_row();
                                                        str4 = *(int32_t *)v11;
                                                        strncmp_rc = strncmp((char *)str4, (char *)0x8049b36, 2);
                                                        v8 = false;
                                                        if ((0x1000000 * ((((int32_t)(v8 || strncmp_rc % 2 == 0) || str4 & -256) ^ 1) - (0x8049b00 || (int32_t)v8)) || 0xffffff) >= 0x1ffffff) {
                                                            // 0x804902e
                                                            mem9 = malloc(0x2000);
                                                            sprintf(mem9, "SELECT balance FROM accounts WHERE iban = '%s'", a5);
                                                            strlen(mem9);
                                                            mysql_real_query();
                                                            free(mem9);
                                                            if (v4 == 0) {
                                                                // 0x80490b1
                                                                mysql_store_result();
                                                                str5 = v4;
                                                                if (str5 != 0) {
                                                                    // 0x80490cf
                                                                    mysql_fetch_row();
                                                                    strtod((char *)*(int32_t *)str5, NULL);
                                                                    // branch -> 0x80491a4
                                                                }
                                                                // 0x80491a4
                                                                return -28;
                                                            }
                                                            result = -28;
                                                        } else {
                                                            result = -14;
                                                        }
                                                        // 0x80491a4
                                                        return result;
                                                    }
                                                    result = -27;
                                                } else {
                                                    result = -26;
                                                }
                                            } else {
                                                result = 0;
                                            }
                                            // 0x80491a4
                                            return result;
                                        }
                                        result = -25;
                                    } else {
                                        result = -24;
                                    }
                                    // 0x80491a4
                                    return result;
                                }
                                result = -24;
                            } else {
                                result = -23;
                            }
                        } else {
                            result = -12;
                        }
                    } else {
                        result = -22;
                    }
                    // 0x80491a4
                    return result;
                }
                result = -22;
            } else {
                result = -11;
            }
            // 0x80491a4
            return result;
        }
        result = -21;
    }
    // 0x80491a4
    return result;
}

// Address range: 0x80491ab - 0x8049a0f
int main(int argc, char ** argv) {
    int32_t v1 = (int32_t)argv; // 0x80491ab_1
    int32_t v2 = v1;
    char ** str = (char **)&v2; // bp-196
    int32_t v3 = *(int32_t *)20; // 0x80491c2
    if ((char *)argc > (char *)2) {
        char * mem = malloc(-((-1 * strlen((char *)(v1 + 1))))); // 0x804920d
        strcpy(mem, *str);
        char * v4 = *str; // 0x8049236
        char * mem2 = malloc(-((-1 * strlen((char *)((int32_t)v4 + 1))))); // 0x804925c
        strcpy(mem2, *str);
        char * mem3 = malloc(1 - -1 * strlen(*str)); // 0x80492ab
        strcpy(mem3, *str);
        struct struct__IO_FILE * file = fopen(mem, "r"); // 0x80492dd
        free(mem);
        int32_t result; // bp+115
        if (file == NULL) {
            // 0x80492f9
            perror("Error");
            result = 2;
            // branch -> 0x80499ee
        } else {
            // 0x804930f
            fseek(file, 0, SEEK_END);
            int32_t curr_file_offset = ftell(file); // 0x8049332
            fseek(file, 0, SEEK_SET);
            int32_t size = curr_file_offset + 1; // 0x804935b
            char * mem4 = malloc(size); // 0x8049361
            char * mem5 = malloc(size); // 0x8049374
            if (mem4 != NULL) {
                // 0x8049384
                if (mem5 != NULL) {
                    // 0x80493a8
                    // branch -> 0x80493a8
                  lab_0x80493a8_2:;
                    char * next_token; // 0x8049463
                    while (true) {
                        // 0x80493a8
                        memset(mem4, 0, size);
                        memset(mem5, 0, size);
                        fgets(mem4, curr_file_offset, file);
                        if (memcmp(mem4, mem5, curr_file_offset) != 0) {
                            // 0x8049432
                            memcpy(mem5, mem4, size);
                            next_token = strtok(mem4, ",");
                            if (next_token != NULL) {
                                // break -> 0x8049477
                                break;
                            }
                            // continue -> 0x80493a8
                            continue;
                        }
                    }
                    // 0x8049477
                    int32_t * v5; // 0x02937
                    char ** v6; // bp+031
                    if (-((-1 * strlen(next_token))) == 15) {
                        // 0x80494b4
                        if (strspn(next_token, "0123456789") == 15) {
                            char * mem6 = malloc(1 - -1 * strlen(next_token)); // 0x804950c
                            strcpy(mem6, next_token);
                            v6 = (char **)mem6;
                            v5 = NULL;
                            // branch -> 0x8049529
                        } else {
                            v6 = NULL;
                            v5 = (int32_t *)-33;
                        }
                    } else {
                        // 0x8049529
                        v6 = NULL;
                        v5 = (int32_t *)-32;
                        // branch -> 0x8049529
                    }
                    char * next_token2 = strtok(NULL, ","); // 0x8049539
                    int32_t * v7 = next_token2 == NULL ? (int32_t *)-31 : v5; // bp+028
                    int32_t * v8 = v7;
                    int32_t accept;
                    int32_t * v9; // 0x02643
                    char ** v10; // bp+032
                    if (v7 == NULL) {
                        // 0x8049558
                        if (-((-1 * strlen(next_token2))) == 34) {
                            // 0x80495f9
                            accept = 0x33323130;
                            int32_t ini_seg_bytes = strspn(next_token2, (char *)&accept); // 0x804960b
                            v9 = (int32_t *)-35;
                            v10 = NULL;
                            if (ini_seg_bytes == 34) {
                                char * mem7 = malloc(1 - -1 * strlen(next_token2)); // 0x8049654
                                strcpy(mem7, next_token2);
                                v9 = NULL;
                                v10 = (char **)mem7;
                                // branch -> 0x8049671
                            }
                          lab_0x8049671:;
                            char * next_token3 = strtok(NULL, ","); // 0x8049681
                            int32_t * v11 = next_token3 == NULL ? (int32_t *)-31 : v9; // bp+025
                            *__errno_location() = 0;
                            float64_t v12 = 0.0; // 0x804991516
                            if (next_token3 != NULL) {
                                // 0x80496b1
                                strtod(next_token3, NULL);
                                v12 = g1;
                                // branch -> 0x80496c9
                            }
                            int32_t * v13 = v11; // bp+024
                            char * next_token4; // 0x804970b
                            int32_t * v14; // bp+022
                            int32_t * v15; // bp+023
                            if (v11 == NULL) {
                                float80_t v16 = v12; // 0x80496d0
                                g1 = v16;
                                if (v12 >= 0.0) {
                                    // if_80496d6_0_false
                                    if (v12 <= 0.0) {
                                        // if_80496d6_1_false
                                        if (v12 != 0.0) {
                                          lab_0x80496e8:
                                            // 0x80496e8
                                            if (*__errno_location() == 0) {
                                                v13 = NULL;
                                              lab_0x80496fb:
                                                // 0x80496fb
                                                next_token4 = strtok(NULL, ",");
                                                v15 = next_token4 == NULL ? (int32_t *)-31 : v13;
                                                v14 = v15;
                                                if (v15 == NULL) {
                                                  lab_0x804972a:
                                                    // 0x804972a
                                                    if (-((-1 * strlen(next_token4))) >= 151) {
                                                        // 0x8049753
                                                        v14 = (int32_t *)-37;
                                                        // branch -> 0x8049798
                                                    } else {
                                                        v14 = NULL;
                                                    }
                                                }
                                              lab_0x8049798:;
                                                char accept2 = 48;
                                                int32_t v17;
                                                memcpy((char *)&v17, (char *)0x8049d71, 16);
                                                *(int16_t *)&v17 = *(int16_t *)0x8049d71;
                                                *(char *)((int32_t)&v17 | 2) = *(char *)0x8049d73;
                                                int32_t * v18 = v14;
                                                int32_t * v19; // bp+018
                                                char ** v20; // 0x03356
                                                if (v14 == NULL) {
                                                    int32_t ini_seg_bytes2 = strspn(next_token4, &accept2); // 0x80497e4
                                                    if (ini_seg_bytes2 == -((-1 * strlen(next_token4)))) {
                                                        char * mem8 = malloc(1 - -1 * strlen(next_token4)); // 0x8049850
                                                        char * dest_str = strcpy(mem8, next_token4); // 0x8049868
                                                        mysql_init();
                                                        int32_t * v21 = dest_str == NULL ? (int32_t *)-1 : NULL; // bp+020
                                                        mysql_real_connect();
                                                        int32_t v22 = (int32_t)dest_str; // 0x80498db
                                                        int32_t * v23 = v22 == 0 ? (int32_t *)-2 : v21; // bp+019
                                                        v19 = v23;
                                                        if (v23 == NULL) {
                                                            // 0x80498f9
                                                            mysql_autocommit();
                                                            int32_t v24 = ProceedTransaction((char **)v22, (char **)mem2, (char **)mem3, v6, v10, v12, 0, (char **)mem8); // 0x8049944
                                                            if (v24 == 0) {
                                                                // 0x8049954
                                                                mysql_commit();
                                                                // branch -> 0x804996e
                                                            } else {
                                                                // 0x8049962
                                                                mysql_rollback();
                                                                // branch -> 0x804996e
                                                            }
                                                            // 0x804996e
                                                            mysql_autocommit();
                                                            v19 = (int32_t *)v24;
                                                            // branch -> 0x8049982
                                                        }
                                                        // 0x8049982
                                                        mysql_close();
                                                        v20 = (char **)mem8;
                                                        // branch -> 0x804998e
                                                      lab_0x804998e:
                                                        // 0x804998e
                                                        printf("%d \n", (int32_t)v19);
                                                        if (v6 != NULL) {
                                                            // 0x80499aa
                                                            free((char *)v6);
                                                            // branch -> 0x80499b6
                                                        }
                                                        // 0x80499b6
                                                        if (v10 != NULL) {
                                                            // 0x80499bd
                                                            free((char *)v10);
                                                            // branch -> 0x80499c9
                                                        }
                                                        // 0x80499c9
                                                        if (v20 != NULL) {
                                                            // break (via goto) -> 0x80499d4
                                                            goto lab_0x80499d4;
                                                        }
                                                        // continue (via goto) -> 0x80493a8
                                                        goto lab_0x80493a8_2;
                                                    } else {
                                                        v18 = (int32_t *)-38;
                                                    }
                                                }
                                                // 0x804986d
                                                v20 = NULL;
                                                v19 = v18;
                                                // branch -> 0x804998e
                                                goto lab_0x804998e;
                                            }
                                        } else {
                                            // after_if_80496e2_0
                                            g1 = v16;
                                            // branch -> 0x80496f3
                                        }
                                        // 0x80496f3
                                        v13 = (int32_t *)-36;
                                        // branch -> 0x80496fb
                                        goto lab_0x80496fb;
                                    }
                                }
                                // after_if_80496e2_0.thread
                                g1 = v16;
                                // branch -> 0x80496e8
                                goto lab_0x80496e8;
                            }
                            // 0x80496fb
                            next_token4 = strtok(NULL, ",");
                            v15 = next_token4 == NULL ? (int32_t *)-31 : v13;
                            if (v15 == NULL) {
                                goto lab_0x804972a;
                            }
                            v14 = v15;
                            goto lab_0x8049798;
                        } else {
                            v8 = (int32_t *)-34;
                        }
                    }
                    // 0x804961d
                    accept = 0x33323130;
                    v9 = v8;
                    v10 = NULL;
                    // branch -> 0x8049671
                    goto lab_0x8049671;
                } else {
                    result = -1;
                }
            } else {
                result = -1;
            }
        }
        // 0x80499ee
        if (*(int32_t *)20 != v3) {
            // 0x80499fe
            __stack_chk_fail();
            // branch -> 0x8049a03
        }
        // 0x8049a03
        return result;
    }
    // 0x80499ee
    if (*(int32_t *)20 != v3) {
        // 0x80499fe
        __stack_chk_fail();
        // branch -> 0x8049a03
    }
    // 0x8049a03
    return 1;
}

// --------------- Dynamically Linked Functions ---------------

// int * __errno_location(void);
// void __stack_chk_fail(void);
// char * fgets(char *restrict, int, FILE *restrict);
// FILE * fopen(const char *restrict, const char *restrict);
// void free(void *);
// int fseek(FILE *, long, int);
// long ftell(FILE *);
// void * malloc(size_t);
// int memcmp(const void *, const void *, size_t);
// void * memcpy(void *restrict, const void *restrict, size_t);
// void * memset(void *, int, size_t);
// void mysql_autocommit(void);
// void mysql_close(void);
// void mysql_commit(void);
// void mysql_fetch_row(void);
// void mysql_init(void);
// void mysql_real_connect(void);
// void mysql_real_query(void);
// void mysql_rollback(void);
// void mysql_store_result(void);
// void perror(const char *);
// int printf(const char *restrict, ...);
// int sprintf(char *restrict, const char *restrict, ...);
// char * strcpy(char *restrict, const char *restrict);
// size_t strspn(const char *, const char *);
// double strtod(const char *restrict, char **restrict);
// char * strtok(char *restrict, const char *restrict);

// --------------- Instruction-Idiom Functions ----------------

// int32_t strlen(char * a1);
// int32_t strncmp(char * a1, char * a2, int32_t a3);

// --------------------- Meta-Information ---------------------

// Detected compiler/packer: gcc (4.6.3)
// Detected functions: 2
// Decompiler release: v2.1.1.1 (2015-11-18)
// Decompilation date: 2016-01-02 09:56:35
