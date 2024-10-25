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

Let's build a docker file that sets up PHP & Apache. We mainly want to install/enable the Apache rewrite module, and the PHP mysqli extension.

This is going to be part of our LAMP stack, so we'll setup a lamp directory, and add a sub folder for the php-image.

```bash
mkdir ~/lamp
mkdir ~/lamp/php-image
cd ~/lamp/php-image
sudo nano dockerfile
```
contents of dockerfile:
```dockerfile
FROM php:8.3-rc-apache

RUN apt-get update
RUN a2enmod rewrite

RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

COPY ./ /var/www/html/

EXPOSE 80 443

CMD ["apache2ctl", "-D", "FOREGROUND"]
```
Ensure the file can be built
```bash
docker build -t php-image .
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