CREATE USER 'client'@'localhost' IDENTIFIED BY 'client';
GRANT SELECT ON bank_db.* TO 'client'@'localhost';
GRANT INSERT ON bank_db.transactions TO 'client'@'localhost';
GRANT UPDATE ON bank_db.tans TO 'client'@'localhost';
GRANT UPDATE ON bank_db.accounts TO 'client'@'localhost';

CREATE USER 'login'@'localhost' IDENTIFIED BY 'login';
GRANT SELECT ON bank_db.users TO 'login'@'localhost';

CREATE USER 'register'@'localhost' IDENTIFIED BY 'register';
GRANT INSERT ON bank_db.users TO 'register'@'localhost';

CREATE USER 'employee'@'localhost' IDENTIFIED BY 'employee';
GRANT SELECT, INSERT, UPDATE ON bank_db.* TO 'employee'@'localhost';
GRANT DELETE ON bank_db.users TO 'employee'@'localhost';

