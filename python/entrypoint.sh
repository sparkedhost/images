#!/bin/bash
cd /home/container

# Output Current Software Versions
PY_VER=`python3 --version 2>&1 | cut -d' ' -f2 | sed '/^1\./s///'`
echo "Python3 version: ${PY_VER}"

PIP_VER=`pip3 --version 2>&1 | cut -d' ' -f2 | sed '/^1\./s///'`
echo "PIP3 version: ${PIP_VER}"

GIT_VER=`git --version 2>&1 | cut -d' ' -f3 | sed '/^1\./s///'`
echo "Git version: ${GIT_VER}"

# Make internal Docker IP address available to processes
export INTERNAL_IP=`ip route get 1 | awk '{print $(NF-2);exit}'`

# Replace Startup Variables.
MODIFIED_STARTUP=$(echo -e ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
echo "customer@apollopanel:~# ${MODIFIED_STARTUP}"

# Run the Server.
eval ${MODIFIED_STARTUP}