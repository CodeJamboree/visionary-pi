# Setup Raspberry Pi

This is how I had setup a Raspberry Pi. Ideally, this project could run on any Linux Desktop. The main thing here is that I've set it up on a machine called "kiosk", and my account is "lewis". Change things according to your own preference.

1. Install Ubuntu Linux Desktop
1. Insall latest updates

See [update-packages.sh](./update-packages.sh)

## Multicast DNS

Setup your device so that you can reach it internally via http://kiosk.local
See [setup-multicast-dns.sh](./setup-multicast-dns.sh)

## SSH

Setup your device so that you can SSH into it from another machine.
See [setup-ssh.sh](./setup-ssh.sh)

## cURL

Install program to download web pages on the console

See [setup-curl.sh](./setup-curl.sh)

## Docker

Install program to let you run services within containers.

See [setup-docker.sh](./setup-docker.sh)

## Apache & PHP

Create a dockerfile image with Apache & PHP. Also, setup a few modules and extensions to protect our site, enable CORS headers, read image, audio, and video dimensions/durations.

See: [setup-lamp-folders.sh](./lamp/setup-lamp-folders.sh)
See: [dockerfile](./lamp/dockerfile)

Copy the apache2 configuration from /etc/apache2/apache2.conf locally to ~/lamp/apache2/apache2.conf, and then add the following `<Directory>` setting with the others.

```bash
sudo nano apache2.conf
```
```htaccess
<Directory /var/www/html>
    AllowOverride All
</Directory>
```
If you can't find/access it, here is a sample [apache2.conf](./lamp/apache2.conf)

Ensure the file can be built. Try with no-cache flag if you've made changes/corrections.
See [build-dockerfile.sh](./lamp/build-dockerfile.sh)

## LAMP

Setup a LAMP stack - Linux, Apache, MySQL, PHP. Instead of MySQL, we will use a fork, MariaDB. We've already setup an image for PHP and Apache that we will bring in. Another container to use will be phpmyadmin to manage the database with a web interface.

```bash
cd ~/lamp
sudo nano compose.yaml
```

See [compose.yaml](./lamp/compose.yaml) and replace environment variables
- MYSQL_ROOT_PASSWORD
- MYSQL_USER
- MYSQL_PASSWORD
- MYSQL_DATABASE

See [start-docker-containers.sh](./lamp/start-docker-containers.sh)

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
See [test-db-connection.php](./test-db-connection.php)