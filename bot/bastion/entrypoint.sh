#!/bin/bash
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Show versions
echo -e "${YELLOW}MongoDB Version:${NC} " && mongod --version
echo -e "${YELLOW}NodeJS Version:${NC} " && node -v
echo -e "${YELLOW}Python Version:${NC} " && python3 --version

cd /home/container

# Set environment variable that holds the Internal Docker IP
INTERNAL_IP=$(ip route get 1 | awk '{print $(NF-2);exit}')
export INTERNAL_IP

# Replace Startup Variables
MODIFIED_STARTUP=$(echo -e $(echo -e ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g'))
echo -e "\033[1;33mcustomer@sparkedhost:~\$\033[0m ${MODIFIED_STARTUP}"

# Start MongoDB
echo -e "${YELLOW}Starting MongoDB...${NC}"
mongod --fork --dbpath /home/container/mongodb/ --port 27017 --logpath /home/container/mongod.log --logRotate reopen --logappend && until nc -z -v -w5 127.0.0.1 27017; do echo 'Waiting for mongodb connection...'; sleep 5; done

# Run the Server
echo -e "${YELLOW}BastionBot starting...${NC}"
eval ${MODIFIED_STARTUP}

# Stop MongoDB
mongo --eval "db.getSiblingDB('admin').shutdownServer()"