Notice:

If 'sendmail' is used as command-line tool to send an email, it has to be provided with a config file that contains information about the mail server and login data. 'Sendmail' can be dowloaded as part of the package 'ssmtp' (either via 'apt-get install ssmtp' or via the Ubuntu Software Center).

If 'ssmtp' is installed, the two files 'ssmtp.conf' and 'revaliases' can be placed in the directory 'etc/ssmtp/' to provide required information to 'sendmail'.

The basic account information used here to send automated emails are:

Mail provider:	GMX.net
SMTP server:	mail.gmx.net:587
Email address:	sec-coding@gmx.de
Password:	securecoding