# Setup Raspberry Pi

This is how I had setup a Raspberry Pi. Ideally, this project could run on any Linux Desktop.

1. Install Ubuntu Linux Desktop
1. Insall latest updates

```bash
sudo apt update
sudo apt upgrade
```

## Multicast DNS

Setup your device so that you can reach it internally via http://kiosk.local

```bash
sudo apt install avahi-daemon -y
sudo nano /etc/hostname
# should be one line with the name of your server "kiosk"
sudo nano /etc/hosts
# if present, replace 127.0.0.1 ubuntu with 127.0.0.1 kiosk
# if 127.0.0.1 localhost, add another line 127.0.0.1 kiosk
sudo systemctl restart avahi-daemon
sudo systemctl restart NetworkManager
```

## SSH

Setup your device so that you can SSH into it from another machine.

```bash
sudo apt install openssh-server -y
sudo systemctl enable ssh
sudo systemctl start ssh
sudo systemctl status ssh
```

Go to another computer
```bash
ssh your_user_name@kiosk.local
# Enter password
```

## cURL

Install program to download web pages on the console

```bash
sudo apt install curl
```

## Docker

Install program to let you run services within containers.

```bash
curl -sSL https://get.docker.com | sh
sudo usermod -aG docker $USER
logout
# ssh back into the pi
ssh kiosk.local
groups
# we see "docker" as one of the groups

# Verify we can run docker containers
docker run hello-world
```

## Apache & PHP

Let's build a docker file that sets up PHP & Apache.

- Enable Apache Modules
  - rewrite - allow us to expose urls without php extension
  - headers - allow other devices on the network to talk to us (CORS)
- PHP Extensions
  - mysqli - connection to MariaDB/MySQL

This is going to be part of our LAMP stack, so we'll setup a lamp directory, and add a sub folder for the php-image.

```bash
mkdir ~/lamp
mkdir ~/lamp/php-image
cd ~/lamp/php-image
sudo nano dockerfile
```
contents of dockerfile:
```dockerfile
FROM php:8.3-apache

RUN apt-get update
RUN a2enmod rewrite headers authz_core access_compat

RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

COPY ./ /var/www/html/
WORKDIR /var/www/html

RUN chmod 777 /var/www/html

COPY apache2.conf /etc/apache2/apache2.conf

EXPOSE 80 443

RUN mkdir -p /usr/local/apache2/conf
RUN echo "LoadModule headers_module /usr/lib/apache2/modules/mod_headers.so" >> /usr/local/apache2/conf/httpd.conf

RUN apachectl configtest
# RUN service apache2 restart

CMD ["apache2ctl", "-D", "FOREGROUND"]
```
Copy the apache2 configuration from /etc/apache2/apache2.conf locally to ~/lamp/apache2/apache2.conf, and then add the following `<Directory>` setting with the others.

```bash
sudo nano apache2.conf
```
```htaccess
<Directory /var/www/html>
    AllowOverride All
</Directory>
```
If you can't find/access it, here is a sample
```htaccess
ServerName localhost
DefaultRuntimeDir ${APACHE_RUN_DIR}
PidFile ${APACHE_PID_FILE}
Timeout 300
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5
User ${APACHE_RUN_USER}
Group ${APACHE_RUN_GROUP}
HostnameLookups Off
ErrorLog ${APACHE_LOG_DIR}/error.log
LogLevel warn
IncludeOptional mods-enabled/*.load
IncludeOptional mods-enabled/*.conf
Include ports.conf
<Directory />
        Options FollowSymLinks
        AllowOverride None
        Require all denied
</Directory>
<Directory /usr/share>
        AllowOverride None
        Require all granted
</Directory>
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>
<Directory /var/www/html/>
    AllowOverride All
</Directory>
AccessFileName .htaccess
<FilesMatch "^\.ht">
        Require all denied
</FilesMatch>
LogFormat "%v:%p %h %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\"" vhost_combined
LogFormat "%h %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\"" combined
LogFormat "%h %l %u %t \"%r\" %>s %O" common
LogFormat "%{Referer}i -> %U" referer
LogFormat "%{User-agent}i" agent
IncludeOptional conf-enabled/*.conf
IncludeOptional sites-enabled/*.conf
```

Ensure the file can be built
```bash
docker build --no-cache -t php-image .
```

## LAMP

Setup a LAMP stack - Linux, Apache, MySQL, PHP. Instead of MySQL, we will use a fork, MariaDB. We've already setup an image for PHP and Apache that we will bring in. 

Another container to use will be phpmyadmin to manage the database with a web interface.

```bash
cd ~/lamp
sudo nano compose.yaml
```

compose.yaml

*Enter the credentials that you would like to use in the MYSQL_* environment variables.*

```yaml
services:
  php: 
    build: ./php-image
    container_name: php
    restart: unless-stopped
    ports:
      - 80:80
      - 443:443
    volumes:
      - ./html:/var/www/html:rw
      - ./php/php.ini:/usr/local/etc/php/php.ini
      - ./apache2:/etc/apache2
    environment:
      TZ: "America/New_York"
  mariadb:
    image: mariadb:latest
    container_name: mariadb
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: '{{root password}}'
      MYSQL_USER: '{{ user name }}'
      MYSQL_PASSWORD: '{{ user password }}'
      MYSQL_DATABASE: '{{ user db }}'
    volumes:
      - ./mysqldata:/var/lib/mysql
    ports:
      - 3306:3306
phpmyadmin:
    image: phpmyadmin:latest
    container_name: phpmyadmin
    restart: unless-stopped
    ports:
      - 8080:80
    environment:
      - PMA_ARBITRARY=1
      - PMA_HOST=mariadb
    depends_on:
      - mariadb
    volumes:
      - ./php/php.ini:/usr/local/etc/php/php.ini
```
Start up docker
```bash
docker compose up -d
```

We now have a few services running.

http://kiosk.local - serves content from ~/lamp/html
https://kiosk.local - serves content from ~/lamp/html over https
http://kiosk.local:8080 - phpmyadmin to manage database
3306 - port for mysql/MariaDB clients (mysqli)

## Integration testing

### phpmyadmin

First, login to php with your database credentials specified in `compose.yaml` under `MYSQL_USER` and `MYSQL_PASSWORD`. You should see a new database created for you, with the name provided in `MYSQL_DATABASE`.

http://kiosk.local:8080

### PHP mysqli test

A new folder should have been created for `html`. Let's create a test file to write out what version of MariaDB is running.

```bash
cd ./html
sudo nano index.php
```
```php
<?php
error_reporting(E_ALL);
ini_set('display_error', 1);

echo "<p>Hello World!</p>";

$conn = new mysqli(
  "mariadb", 
  "{{db username}}", 
  "{{db password}", 
  "{{db name}}"
);

if ($conn->connect_error) {
    die("Connect Error: " . $conn->connect_error);
}

$version = $conn->server_info;

echo "<p>DB Version: $version</p>";

$conn->close();
```