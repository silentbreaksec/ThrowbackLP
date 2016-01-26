#!/bin/bash

TBDIR=./ThrowbackLP
SQLSCRIPT=throwbackcp.sql
DBNAME=throwbackcp
GITURL=https://github.com/silentbreaksec/ThrowbackLP.git
DBUSER=tblp
red=$'\e[1;31m'
green=$'\e[1;32m'
end=$'\e[0m'

if [ "$EUID" -ne 0 ]; then 
	echo "${red}[+] Needs to be run as root. Exiting...${end}"
	exit
fi

echo "${green}[+] Installing a few dependencies (Apache2, PHP 5, Git, etc.) for ThrowbackLP."
echo "[+] This could take a while...${end}"

apt-get update > /dev/null
apt-get -y install zip ntp git apt-utils dialog apache2 php5 php5-mysql > /dev/null

while true; do
	read -p "${red}[+] Would you like to enable SSL? (y/n)${end} " yn
	if [ "${yn}" == 'y' ] || [ "${yn}" == 'Y' ]; then
		setupssl=true
		break
	elif [ "${yn}" == "n" ] || [ "${yn}" == "N" ]; then
		setupssl=false
		break
	fi
done

if [ "$setupssl" = true ] ; then

	a2enmod ssl > /dev/null
	a2ensite default-ssl > /dev/null
	mkdir -p /etc/apache2/ssl

	read -p "${red}[+] Would you like to use a self-signed cert? (y/n)${end} " yn

	if [ "${yn}" == 'y' ] || [ "${yn}" == 'Y' ]; then
		
		hn=`hostname`
		echo "${green}[+] Generating self-signed certificates.${end}"
		openssl req -x509 -nodes -days 365 -subj "/C=US/ST=MD/L=FtMeade/O=NSA/CN=www.'${hn}'.com" -newkey rsa:2048 -keyout /etc/apache2/ssl/ssl.key -out /etc/apache2/ssl/ssl.crt 2> /dev/null
		
		sed -i 's/SSLCertificateFile/#SSLCertificateFile/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/SSLCertificateKeyFile/#SSLCertificateKeyFile/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/SSLEngine on/SSLEngine on\n\t\tSSLCertificateKeyFile \/etc\/apache2\/ssl\/ssl.key/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/SSLEngine on/SSLEngine on\n\t\tSSLCertificateFile \/etc\/apache2\/ssl\/ssl.crt/g' /etc/apache2/sites-available/default-ssl.conf

	elif [ "${yn}" == "n" ] || [ "${yn}" == "N" ]; then
		echo "${red}[+] Be sure to copy your certs to the locations listed below."
		echo "\etc\apache2\ssl\sslchain.pem"
		echo "\etc\apache2\ssl\ssl.key"
		echo "\etc\apache2\ssl\ssl.crt${end}"

		sed -i 's/SSLCertificateFile/#SSLCertificateFile/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/SSLCertificateKeyFile/#SSLCertificateKeyFile/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/tSSLCertificateChainFile/#SSLCertificateChainFile/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/SSLEngine on/SSLEngine on\n\t\tSSLCertificateChainFile \/etc\/apache2\/ssl\/sslchain.pem/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/SSLEngine on/SSLEngine on\n\t\tSSLCertificateKeyFile \/etc\/apache2\/ssl\/ssl.key/g' /etc/apache2/sites-available/default-ssl.conf
		sed -i 's/SSLEngine on/SSLEngine on\n\t\tSSLCertificateFile \/etc\/apache2\/ssl\/ssl.crt/g' /etc/apache2/sites-available/default-ssl.conf
	fi

	echo "${green}[+] Restarting the Apache server.${end}"
	service apache2 restart > /dev/null
fi

echo "${green}[+] Hardening the Apache web server.${end}"
sed -i 's/Options Indexes/Options/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/Options Indexes/Options/g' /etc/apache2/sites-available/default-ssl.conf
sed -i 's/Options Indexes/Options/g' /etc/apache2/apache2.conf
sed -i 's/ServerTokens OS/ServerTokens Prod/g' /etc/apache2/conf-available/security.conf
sed -i 's/ServerSignature On/ServerSignature Off/g' /etc/apache2/conf-available/security.conf

update-rc.d apache2 defaults > /dev/null
update-rc.d apache2 enable > /dev/null

echo "${green}[+] Checking out ThrowbackLP from GitHub.${end}"
cd /tmp/
git clone $GITURL > /dev/null

while true; do
	read -p "${red}[+] Enter the root WWW directory, or leave blank for the default. (i.e. /var/www/html)${end}" wwwrootdir
	if [ "${wwwrootdir}" == "" ]; then
		wwwrootdir=/var/www/html
	fi
	if [ -d "$wwwrootdir" ]; then
		break
	fi
done

echo "${green}[+] The Throwback backdoor calls back to a PHP file hosted on one or more ThrowbackLP servers. These PHP files are used for callback data (from the backdoor) and can have different filenames on the different ThrowbackLP servers. Make note of the filenames, use the mangle Python script to obfuscate them, and then compile them into the Throwback backdoor.${end}"
read -p "${red}[+] What would you like to name the PHP file for the Throwback callback? (e.g. index.php)${end} " callbackfile
if [ "${callbackfile}" == "" ]; then
	callbackfile=index.php
fi

echo "${green}[+] Fyi, this file is placed in ${wwwrootdir}.${end}"

while true; do
	read -p "${red}[+] Is this server the primary ThrowbackLP? (y/n)${end} " yn
	if [ "${yn}" == 'y' ] || [ "${yn}" == 'Y' ]; then
		
		primarylp=true
		mkdir $wwwrootdir/cp
		mkdir $wwwrootdir/scripts

		while true; do
			read -p "${red}[+] Enter the MySQL root password. If you haven't installed MySQL, enter the password you'd like to use:${end} " mysqlpw1
			read -p "${red}[+] Please confirm:${end} " mysqlpw2
			if [ "${mysqlpw1}" == "${mysqlpw2}" ]; then
				break
			fi
		done
		break

	elif [ "${yn}" == "n" ] || [ "${yn}" == "N" ]; then
		primarylp=false

		while true; do
			read -p "${red}[+] Enter the IP address of the primary ThrowbackLP:${end} " tblp1
			read -p "${red}[+] Please confirm:${end} " tblp2
			if [ "${tblp1}" == "${tblp2}" ]; then
				break
			fi
		done

		while true; do
			read -p "${red}[+] Enter the password for the 'tblp' datbase user on the primary MySQL server:${end} " tblppw1
			read -p "${red}[+] Please confirm:${end} " tblppw2
			if [ "${tblppw1}" == "${tblppw2}" ]; then
				break
			fi
		done

		break
	fi
done

if [ "$primarylp" = true ] ; then
	
	echo "${green}[+] Installing MySQL server.${end}"
	apt-get --reinstall install bsdutils > /dev/null
	debconf-set-selections <<< 'mysql-server mysql-server/root_password password '$mysqlpw1
	debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password '$mysqlpw1
	apt-get -y install mysql-server > /dev/null

	echo "${green}[+] Installing SQL database and tables.${end}"
	mysql -u root -p$mysqlpw1 -e "create database "$DBNAME";"
	mysql -u root -p$mysqlpw1 $DBNAME < $TBDIR/$SQLSCRIPT
	
	echo '${green}[+] Generating random password for the ThrowbackLP database user.'
	echo "[+] Note that you'll need these credentials if connecting other ThrowbackLPs to this primary LP.${end}"
	tblppw=`head /dev/urandom | tr -dc A-Za-z0-9 | head -c 15`
	echo "${green}[+] For reference, the credentials are below."
	echo "Username: ${end}${red}tblp${end}"
	echo "${green}Password: ${end}${red}${tblppw}${end}"

	sed -i 's/public static \$host.*/public static \$host = "127.0.0.1";/g' $TBDIR/cp/includes/mysql.php
	sed -i 's/public static \$password.*/public static \$password = "'$tblppw'";/g' $TBDIR/cp/includes/mysql.php
	sed -i 's/public static \$user.*/public static \$user = "'$DBUSER'";/g' $TBDIR/cp/includes/mysql.php
	sed -i 's/public static \$dbName.*/public static \$dbName = "'$DBNAME'";/g' $TBDIR/cp/includes/mysql.php

	sed -i 's/public static \$host.*/public static \$host = "127.0.0.1";/g' $TBDIR/index.php
	sed -i 's/public static \$password.*/public static \$password = "'$tblppw'";/g' $TBDIR/index.php
	sed -i 's/public static \$user.*/public static \$user = "'$DBUSER'";/g' $TBDIR/index.php
	sed -i 's/public static \$dbName.*/public static \$dbName = "'$DBNAME'";/g' $TBDIR/index.php

	mysql -uroot -p$mysqlpw1 $DBNAME -e "GRANT ALL ON throwbackcp.* to tblp@localhost IDENTIFIED BY '${tblppw}'";
	mysql -uroot -p$mysqlpw1 $DBNAME -e "GRANT ALL ON throwbackcp.* to tblp@'%' IDENTIFIED BY '${tblppw}'"; 
	mysql -uroot -p$mysqlpw1 $DBNAME -e "FLUSH PRIVILEGES;"

	while true; do
		read -p "${red}[+] Would you like to create a user for the Throwback Control Panel? (y/n)${end} " yn
		if [ "${yn}" == 'y' ] || [ "${yn}" == 'Y' ]; then
			while true; do
				read -p "${red}[+] Enter the new username:${end} " user
				read -p "${red}[+] You entered ${user}. Is that correct? (y/n)${end} " yn1
				if [ "${yn1}" == 'y' ] || [ "${yn1}" == 'Y' ]; then
					break
				fi
			done
			
			while true; do
				read -p "${red}[+] Enter ${user}'s password:${end} " pass
				read -p "${red}[+] You entered ${pass}. Is that correct? (y/n)${end} " yn2
				if [ "${yn2}" == 'y' ] || [ "${yn2}" == 'Y' ]; then
					break
				fi
			done
			
			mysql -uroot -p$mysqlpw1 $DBNAME -e "INSERT INTO \`users\` (\`id\`, \`username\`, \`password\`, \`lastlogin\`) VALUES (0, '${user}', SHA1('${pass}'), '0');"

		elif [ "${yn}" == "n" ] || [ "${yn}" == "N" ]; then
			break
		fi
	done

	echo "${green}[+] Copying ThrowbackLP files to ${wwwrootdir}.${end}"
	echo "${green}[+] Fyi, these files are copied to ${wwwrootdir}/cp/.${end}"
	
	mv $TBDIR/index.php $wwwrootdir/$callbackfile
	cp -r $TBDIR/cp/* $wwwrootdir/cp/
	
	sed -i 's/bind-address.*/bind-address = 0.0.0.0/g' /etc/mysql/my.cnf
	service mysql restart > /dev/null

	update-rc.d mysql defaults > /dev/null
	update-rc.d mysql enable > /dev/null

else
	
	sed -i 's/public static \$password.*/public static \$password = "'$tblppw1'";/g' $TBDIR/index.php
	sed -i 's/public static \$host.*/public static \$host = "'$tblp1'";/g' $TBDIR/index.php
	sed -i 's/public static \$user.*/public static \$user = "'$DBUSER'";/g' $TBDIR/index.php
	sed -i 's/public static \$dbName.*/public static \$dbName = "'$DBNAME'";/g' $TBDIR/index.php

	echo "${green}[+] Copying ThrowbackLP files to ${wwwrootdir}.${end}"
	mv $TBDIR/index.php $wwwrootdir/$callbackfile
	echo "${green}[+] Success! We're done here.${end}"
fi

chmod -R 755 $wwwrootdir > /dev/null
rm -rf $TBDIR

msfpw=`head /dev/urandom | tr -dc A-Za-z0-9 | head -c 10`
echo load msgrpc Pass=${msfpw} > /root/msgrpc.rc


if [ "$primarylp" = true ] ; then
	if [ "$setupssl" = true ] ; then
		echo "${green}[+] Success! Login to ThrowbackLP at https://[IP_OF_HOST]/cp/index.php ${end}"
	else
		echo "${green}[+] Success! Login to ThrowbackLP at http://[IP_OF_HOST]/cp/index.php ${end}"
	fi
fi


while true; do
	read -p "${red}[+] ThrowbackLP can also interface with Metasploit to create various payloads to perform interactive operations. Would you like to install Metasploit? This is experimental! (y/n)${end} " yn
	if [ "${yn}" == 'y' ] || [ "${yn}" == 'Y' ]; then
		installmsf=true
		break
	elif [ "${yn}" == "n" ] || [ "${yn}" == "N" ]; then
		installmsf=false
		break
	fi
done

if [ "$installmsf" == true ]; then
	
	echo "${red}[+] Ok, but don't say we didn't warn you!${end}"
	
	echo "${green}[+] Installing kernel headers.${end}"
	apt-get -y install gcc make linux-headers-$(uname -r) > /dev/null
	ln -s /usr/src/linux-headers-$(uname -r)/include/generated/uapi/linux/version.h /usr/src/linux-headers-$(uname -r)/include/linux/

	echo "${green}[+] Installing additional dependencies.${end}"
	apt-get -y install php5-dev php-pear build-essential > /dev/null

	echo "${green}[+] Installing and configuring MsgPack for PHP. Watch for any errors!${end}"
	pecl install channel://pecl.php.net/msgpack-0.5.5 
	echo "extension=msgpack.so" >> /etc/php5/apache2/php.ini
	service apache2 restart > /dev/null
	
	echo "${green}[+] Installing the last few dependencies.${end}"
	apt-get -y install curl libcurl3 libcurl3-dev php5-curl > /dev/null

	machinetype=`uname -m`
	cd /tmp/

	if [ ${machinetype} == 'x86_64' ]; then
		echo "${green}[+] Downloading the x64 version of Metasploit. This may take a while.${end}"
		wget http://downloads.metasploit.com/data/releases/metasploit-latest-linux-x64-installer.run > /dev/null
	else
		echo "${green}[+] Downloading the x86 version of Metasploit. This may take a while.${end}"
		wget  http://downloads.metasploit.com/data/releases/metasploit-latest-linux-installer.run > /dev/null
	fi
	
	echo "${green}[+] Download complete! Starting the Metasploit installer.${end}"

	chmod +x /tmp/metasploit-latest-linux*.run
	
	/tmp/metasploit-latest-linux*.run
	
	echo "${red}[+] Don't forget to register Metasploit! Go to https://localhost:3790 to create a user account and obtain/activate a license key.${end}"

	echo "${green}[+] Configuring ThrowbackLP for Metasploit.${end}"		
	sed -i 's/\$MSFPASSWORD.*/\$MSFPASSWORD = "'$msfpw'";/g' $wwwrootdir/cp/includes/conf.php
	sed -i 's/\$METASPLOIT.*/\$METASPLOIT  = "127.0.0.1";/g' $wwwrootdir/cp/includes/conf.php
	sed -i 's/\$MSFUSERNAME.*/\$MSFUSERNAME  = "msf";/g' $wwwrootdir/cp/includes/conf.php

	echo "${green}[+] Start Metasploit via 'msfconsole /root/msgrpc.rc', and you **should** be able to generate payload in the ThrowbackLP interface.${end}"
fi

echo "${green}[+] All done. Thanks for playing!${end}"	
