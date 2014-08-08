0.	Prepare the environment
	a.	apt-get install apache2 mysql-server php5 phpmyadmin curl libcurl3 php5-curl build-essential php-pear
1.	Configure Apache to allow SSL connections.
	a.	wget http://librarian.launchpad.net/7477840/apache2-ssl.tar.gz
	b.	tar -zxvf apache2-ssl.tar.gz
	c.	mv ssleay.cnf /usr/share/apache2/ssleay.cnf
	d.	mkdir /etc/apache2/ssl
	e.	./apache2-ssl-certificate -days 3650
	f.	a2enmod ssl
	g.	a2ensite default-ssl
2.	Configure Throwback LP
	a.	Open PHPMyAdmin by going to http://NAME-OF-SITE/phpmyadmin
		i.	Log into the database
		ii.	Click on the SQL icon in the upper left corner
		iii.	Paste the contents of db_schema.sql. This will create an empty database instance.
	b.	Upload TB LP files to server
	c.	Modify cp/includes/conf.php if any default installation instructions have been modified.
	d.	Move index.php, res.php, and the cp folder to all the domains configured in /opt/web/
	e.	You can now access the Throwback LP server at https://NAME-OF-SITE/cp/index.php
	f.	The default username is root and the default password is Throwback!@#
3.	Configure Metasploit for use with Throwback
	a.	Download and install Metasploit Framework
	b. 	See ./cp/includes/msfrpcd.php for detailed instructions
4. Allow remote access to MySQL if necessary (if more than one LP)
	i. vim /etc/mysql/my.cnf and change bind-address to 0.0.0.0
	ii. GRANT ALL ON throwbackcp.* TO tblp@'<ip_address>' IDENTIFIED BY 'ThrowbackPwnage!@#';
	iii. FLUSH PRIVILEGES;

