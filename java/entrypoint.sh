#!/bin/bash
cd /home/container

# Print current Java version
JAVA_VER=`java -version 2>&1 | head -1 | cut -d'"' -f2 | sed '/^1\./s///'`
echo "Java version: ${JAVA_VER}"

# Make internal Docker IP address available to processes
export INTERNAL_IP=`ip route get 1 | awk '{print $NF;exit}'`

# Print startup command to console
echo -e "\033[1;33mcustomer@sparkedhost:~\$\033[0m ${MODIFIED_STARTUP}"

# Run the server
eval ${MODIFIED_STARTUP}
