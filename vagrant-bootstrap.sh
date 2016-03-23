#! /usr/bin/env bash
# Based on https://gist.github.com/rrosiek/8190550
# by Ryan Rosiek

# Variables
DBHOST=localhost
DBNAME=zikula
DBUSER=zikula
DBPASSWD=zikula

echo -e "\n--- Mkay, installing now... ---\n"

echo -e "\n--- Updating packages list ---\n"
apt-get -qq update

echo -e "\n--- Install base packages ---\n"
apt-get -y install vim curl build-essential python-software-properties git > /dev/null 2>&1

echo -e "\n--- Add some repos to update our distro ---\n"
add-apt-repository ppa:ondrej/php5 > /dev/null 2>&1
add-apt-repository ppa:chris-lea/node.js > /dev/null 2>&1

echo -e "\n--- Updating packages list ---\n"
apt-get -qq update

echo -e "\n--- Install MySQL specific packages and settings ---\n"
echo "mysql-server mysql-server/root_password password $DBPASSWD" | debconf-set-selections
echo "mysql-server mysql-server/root_password_again password $DBPASSWD" | debconf-set-selections
echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
echo "phpmyadmin phpmyadmin/app-password-confirm password $DBPASSWD" | debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/admin-pass password $DBPASSWD" | debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/app-pass password $DBPASSWD" | debconf-set-selections
echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect none" | debconf-set-selections
apt-get -y install mysql-server-5.6 phpmyadmin > /dev/null 2>&1

echo -e "\n--- Setting up our MySQL user and db ---\n"
mysql -uroot -p$DBPASSWD -e "CREATE DATABASE $DBNAME"
mysql -uroot -p$DBPASSWD -e "grant all privileges on $DBNAME.* to '$DBUSER'@'localhost' identified by '$DBPASSWD'"

echo -e "\n--- Installing PHP-specific packages ---\n"
apt-get -y install php5 apache2 libapache2-mod-php5 php5-curl php5-gd php5-mcrypt php5-mysql php-apc > /dev/null 2>&1

echo -e "\n--- Enabling mod-rewrite ---\n"
a2enmod rewrite > /dev/null 2>&1

echo -e "\n--- Allowing Apache override to all ---\n"
sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf

echo -e "\n--- Setting document root to public directory ---\n"
rm -rf /var/www
ln -fs /vagrant/src /var/www

echo -e "\n--- We definitly need to see the PHP errors, turning them on ---\n"
sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/apache2/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/apache2/php.ini

echo -e "\n--- We also need to set the default timezone. ---\n"
sed -i "s/^;date.timezone =$/date.timezone = \"Europe\/Berlin\"/" /etc/php5/apache2/php.ini

echo -e "\n--- Configure Apache to use phpmyadmin ---\n"
echo -e "\n\nListen 81\n" >> /etc/apache2/ports.conf
cat > /etc/apache2/conf-available/phpmyadmin.conf << "EOF"
<VirtualHost *:81>
    ServerAdmin webmaster@localhost
    DocumentRoot /usr/share/phpmyadmin
    DirectoryIndex index.php
    ErrorLog ${APACHE_LOG_DIR}/phpmyadmin-error.log
    CustomLog ${APACHE_LOG_DIR}/phpmyadmin-access.log combined
</VirtualHost>
EOF
a2enconf phpmyadmin > /dev/null 2>&1

echo -e "\n--- Add environment variables to Apache ---\n"
cat > /etc/apache2/sites-enabled/000-default.conf <<EOF
<VirtualHost *:80>
    DocumentRoot /var/www
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
    SetEnv DB_HOST $DBHOST
    SetEnv DB_NAME $DBNAME
    SetEnv DB_USER $DBUSER
    SetEnv DB_PASS $DBPASSWD
</VirtualHost>
EOF

echo -e "\n--- Restarting Apache ---\n"
service apache2 restart > /dev/null 2>&1

echo -e "\n--- Installing Composer for PHP package management ---\n"
curl --silent https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

echo -e "\n--- Installing PHPUnit ---\n"
curl --silent -L https://phar.phpunit.de/phpunit.phar > phpunit.phar
mv phpunit.phar /usr/local/bin/phpunit

echo -e "\n--- Installing NodeJS and NPM ---\n"
apt-get -y install nodejs > /dev/null 2>&1
curl --silent https://npmjs.org/install.sh | sh > /dev/null 2>&1

echo -e "\n--- Installing javascript components ---\n"
npm install -g gulp bower > /dev/null 2>&1

echo -e "\n--- Installing utilities ---\n"
npm install -g how2 > /dev/null 2>&1

echo -e "\n--- Updating project components and pulling latest versions ---\n"
cd /vagrant
sudo -u vagrant -H sh -c "composer install"

echo -e "\n--- Add environment variables locally ---\n"
cat >> /home/vagrant/.bashrc <<EOF
# Set envvars
export DB_HOST=$DBHOST
export DB_NAME=$DBNAME
export DB_USER=$DBUSER
export DB_PASS=$DBPASSWD
cd /vagrant
EOF
