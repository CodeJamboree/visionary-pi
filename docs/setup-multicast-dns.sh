sudo apt install avahi-daemon -y
sudo nano /etc/hostname
# should be one line with the name of your server "kiosk"
sudo nano /etc/hosts
# if present, replace 127.0.0.1 ubuntu with 127.0.0.1 kiosk
# if 127.0.0.1 localhost, add another line 127.0.0.1 kiosk
sudo systemctl restart avahi-daemon
sudo systemctl restart NetworkManager
