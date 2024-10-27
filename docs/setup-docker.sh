curl -sSL https://get.docker.com | sh
sudo usermod -aG docker $USER
logout
# ssh back into the pi
ssh kiosk.local
groups
# we see "docker" as one of the groups

# Verify we can run docker containers
docker run hello-world