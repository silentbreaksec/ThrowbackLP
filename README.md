0.	Prepare the environment
  1.	apt-get install apache2 mysql-server php5 phpmyadmin curl libcurl3 php5-curl build-essential php-pear
1.	Configure Apache to allow SSL connections.
  1.	wget http://librarian.launchpad.net/7477840/apache2-ssl.tar.gz
  2.	tar -zxvf apache2-ssl.tar.gz
  3.	mv ssleay.cnf /usr/share/apache2/ssleay.cnf
  4.	mkdir /etc/apache2/ssl
  5.	./apache2-ssl-certificate -days 3650
  6.	a2enmod ssl
  7.	a2ensite default-ssl
2.	Configure Throwback LP
  1.	Open PHPMyAdmin by going to http://NAME-OF-SITE/phpmyadmin
    1.	Log into the database
    2.	Click on the SQL icon in the upper left corner
    3.	Paste the contents of throwbackcp.sql. This will create an empty database instance.
  2.	Upload TB LP files to server
  3.	Modify cp/includes/conf.php if any default installation instructions have been modified.
  4.	Move index.php, res.php, and the cp folder to all the domains configured in /opt/web/
  5.	You can now access the Throwback LP server at https://NAME-OF-SITE/cp/index.php
  6.	The default username is root and the default password is Throwback!@#
3.	Configure Metasploit for use with Throwback
  1.	Download and install Metasploit Framework
  2. 	See ./cp/includes/msfrpcd.php for detailed instructions
4. Allow remote access to MySQL if necessary (if more than one LP)
  1. vim /etc/mysql/my.cnf and change bind-address to 0.0.0.0
  2. GRANT ALL ON throwbackcp.* TO tblp@'<ip_address>' IDENTIFIED BY 'ThrowbackPwnage!@#';
	iii. FLUSH PRIVILEGES;

