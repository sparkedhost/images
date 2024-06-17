#!/usr/bin/env bash
cd /home/container

# Make internal Docker IP address available to processes
export INTERNAL_IP=`ip route get 1 | awk '{print $(NF-2);exit}'`

php --version

# Evaluate startup variables.
MODIFIED_STARTUP=$(eval "echo \"$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')\"")
echo "customer@apollopanel:~# ${MODIFIED_STARTUP}"

eval "${MODIFIED_STARTUP}"
