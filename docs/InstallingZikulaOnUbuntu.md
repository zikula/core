---
currentMenu: install
---
# Installation of Zikula on Ubuntu Server

I found installing Zikula on a Ubuntu Server to be quite the process. This had nothing to do with Zikula, but more with getting all the settings for a new Ubuntu server set up correctly so that Zikula would behave. I therefore decided to write a complete tutorial on how to install Zikula on Ubuntu. This tutorial assumes you have a clean install of an Ubuntu Server and was developed with ubuntu 16.04 LTS.

This guide borrows heavily from [the guide at Host Presto](https://hostpresto.com/community/tutorials/how-to-install-zikula-on-ubuntu-16-04/), but there were enough little glitches, I thought I would expand on that and make a more bullet-proof guide. I hope this helps folks with their efforts.

## Requirements

- A new install of [Ubuntu Server](https://www.ubuntu.com/download/server).
- A normal user with sudo priviledges. (If that makes no sense [check this out](https://www.linux.com/learn/linux-101-introduction-sudo%20)).

First install your Ubuntu system and once it boots into your desktop, you need to fire up the terminal program. You can find it by clicking on the search function in the launch bar and typing terminal. Click on the program to launch it.

## Update the System

You will be using the apt program to download many of the software programs. This is a package manager and is a convenient way to install and update software packages. To update apt type these commands into your terminal program
 
```shell
sudo apt-get update -y
sudo apt-get upgrade -y
```
Next restart your system to install these changes

```shell
sudo reboot
```

We will now download and add the LAMP stack which stands for Linux, Apache, Mysql, and Php. These software programs work together to run your web server and are all needed by Zikula. To download and install them all in one fell swoop, type the following command.

```shell
sudo apt-get install apache2 mariadb-server php7.0 php7.0-mysql php7.0-gd php7.0-mcrypt php7.0-xml php7.0-mbstring php7.0-xmlrpc php7.0-curl libapache2-mod-php7.0 wget -y
```

Some explanations about what these do.

* **apache2** is the webserver
* **mariadb-server** is a forked version of mysql. This is the database server that stores content in the database
* **php7.0** installs php 7.0
* **php7.0-mysql php7.0-gd php7.0-mcrypt php7.0-xml php7.0-mbstring php7.0-xmlrpc php7.0-curl libapache2-mod-php7.0** are complimentary php extensions that add functionality that Zikula and its vendor technologies utilize.
* **wget** is a nice program for fetching the latest Zikula release

This may take a while for these packages to download. Once they are all downloaded and installed, then start the apache and mariadb..

```shell
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql
```

Calling `systemctl start` does what you would expect and starts the service. The `systemctl enable` sets up the service so that it is launch at start up of the operating system. When you enable the services you will get a notice that the command was redirected to systemd-sysv-install. That is normal

## Set up mariadb using mysql commands

You now want to test to make sure you can launch mysql. Type the following command.

```shell
sudo mysql -u root
```

You have to call this using sudo because the dbserver is protected from root access unless you are the super user. The mysql program should launch and you can create your database and a user for that database using these commands.
 
```sql
CREATE DATABASE zikuladb;
GRANT ALL PRIVILEGES on zikuladb.* TO 'usernamehere'@'localhost' IDENTIFIED BY 'chooseapassword';
FLUSH PRIVILEGES;
```

You can name your database anything you want, but remember it for use when you install Zikula. The usernamehere and chooseapassword should be substituted with your own user and password that you want to use. FLUSH PRIVILEGES writes the changes to disk. Quit mysql (type quit) and make sure you can log in using your newly created user and password by typing this.

```shell
mysql -u usernamehere -p
```
Enter your password when prompted then issue the mysql command

```sql
USE zikuladb;
```

You should see the text, Database changed. This shows you have access to the database. Finally, increase the security on your mysql installation by running this mysql script:

```shell
sudo mysql_secure_installation
```

Answer the questions as follows.

```plaintext
Set root password? [Y/n] n 
Remove anonymous users? [Y/n] y 
Disallow root login remotely? [Y/n] y 
Remove test database and access to it? [Y/n] y 
Reload privilege tables now? [Y/n] y
```
You do not need to add a root password because mariadb will only allow the root user access to the root mysql account.

## Configure Apache for Zikula

It is useful to create a virtual host file for Zikula. This allows you to set `AllowOverride All` which is required to activate `.htaccess` files that Zikula uses. If this is not configured correctly, routes don't work right in Zikula. You can do this by creating `snipeit.conf` file inside `/etc/apache2/sites-available/` directory:

```shell
sudo nano /etc/apache2/sites-available/zikula.conf
```

Add the following lines to the file.

```apache
<VirtualHost *:80>
    ServerAdmin youremail@domain.com
    DocumentRoot "/var/www/html"
    ServerName yourdomain.com
    <Directory "/var/www/html">
        Options FollowSymLinks
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>
    ErrorLog /var/log/apache2/zikula-error_log
    CustomLog /var/log/apache2/zikula-access_log common
</VirtualHost>
```

Save and close the file when you are finished, then enable the site with the following command:

```shell
sudo a2ensite zikula.conf
```

Next, enable the rewrite module with the following command:

```shell
sudo a2enmod rewrite
```

Now test to make sure your configuration is correct and check for any errors in your `VirtualHost` file:

```shell
apachectl configtest
```

If you get any errors, fix them, otherwise restart Apache web server so that the changes are loaded:

```shell
sudo systemctl restart apache2
```

## Download and Install Zikula and prepare the web root

Download the tar.gz file for the latest release. Right now this is the command to use

```shell
wget https://github.com/zikula/core/releases/download/2.0.2/2.0.tar.gz
```

A stock install of Apache will place all the files under root control. This doesn't work for a site that needs to modify the files. It's also a pain to always be doing sudo to make any little change to the website. We can fix this my doing two things.

- Change the group and owner of the html folder (the apache webserver root) to www-data
- Adding your user to the www-data group so you can make modifications

These commands will make these changes

```shell
sudo chown -R www-data /var/www/html
sudo chgrp -R www-data /var/www/html
sudo chmod -R g+w /var/www/html
sudo usermod -a -G www-data yourusername
```

You will need to log out and back in to get the changes to be updated. You can check to make sure you are in the group by this command:

```shell
groups
```

It should list all the groups in which you are a member and www-data should be one of those groups. 

Once the file is downloaded issue the following command to move it to the webserver root.

```shell
mv 2.0.tar.gz /var/www/html/
```

Now we can expand the tar archive, which will inflate all the files and put them in a folder of our root directory. We use the `-p` flag which perserves all the permissions so that the directories that need to be writable, (e.g. `/var/cache`) are in fact writable. Once it is expanded we will also change the group and owner to `www-data` so the web server has access to all the files.

```shell
cd /var/www/html/
tar -xvpf 2.0.tar.gz
sudo chown -R www-data 2.0
sudo chgrp -R www-data 2.0
sudo chmod -R g+w 2.0
mv 2.0 zikula
```

The last line changes the name of the root Zikula folder to `zikula`, a better name than `2.0`. Once the folder is expanded it is time to test our webserver. First go to the page http://yourdomain.com to make sure the apache web server works. A default ubuntu page should come up. One last modification and we are ready. Open up the root `.htaccess` file.

```shell
nano zikula/.htaccess
```

Uncomment the `RewriteBase` command and change it to:

```apache
RewriteBase /zikula/
```

We are now ready to install Zikula. Browse the the root folder of your Zikukla site in Firefox (http://yourdomain.com/zikula/) An install page should open up. Enter in the database (zikuladb), database user and database password when asked for and then follow the screens. Zikula should install and you should be taken to the home page when finished. 

One final tip. If when you try to enter the Zikula site, if it fails with a 404 now found error, it means your `.htaccess` file is not being read. A nice trick is to put Test in the top of the `.htaccess` file at the root of the zikula directory and it should break your site with a Internal Server Error. If this does not happen, then `.htaccess` is not being read and something is wrong with your configuration. Look for errors in the virtual host file you set up. This is the most likely place.
