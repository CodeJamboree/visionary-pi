# make sure your user account owns the html folder
# docker usually creates it as the root user

# scp -r dist/ui/* lewis@kiosk.local:~/lamp/html
rsync -avz dist/ lewis@kiosk.local:~/lamp/html