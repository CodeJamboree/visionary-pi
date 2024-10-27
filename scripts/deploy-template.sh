#!/usr/bin/expect -f

# make sure your user account owns the html folder
# docker usually creates it as the root user with root as the owner

set timeout -1

set source "dist/"
set destination "USER_NAME@REMOTE_HOST:~/lamp/html"

spawn rsync -avz $source $destination
expect "*?assword:*"
send "YOUR_PASSWORD\r"
expect eof
