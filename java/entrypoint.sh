#!/bin/bash
cd /home/container

# Print current Java version
JAVA_VER=`java -version 2>&1 | head -1 | cut -d'"' -f2 | sed '/^1\./s///'`
echo "Java version: ${JAVA_VER}"

# Make internal Docker IP address available to processes
export INTERNAL_IP=`ip route get 1 | awk '{print $NF;exit}'`

# Check if startup command has -Dterminal.jline=false -Dterminal.ansi=true
JLINE_ARGS=$(echo ${MODIFIED_STARTUP} | grep -o "\-Dterminal.jline=false -Dterminal.ansi=true")
TIMEZONE_INUSE=$(echo ${MODIFIED_STARTUP} | grep -o "\-Duser.timezone=")

# If Lower Xmx is enabled and above variable is empty, add the parameters to the startup command
if [ "${LOWER_XMX}" = 1 ]; then
    SERVER_MEMORY=$(expr $SERVER_MEMORY - 1024)
    # If 512MiB server, use 256MiB
    if [ "${SERVER_MEMORY}" == -512 ]; then SERVER_MEMORY=256; fi

    # If 1GiB server, use 512MiB
    if [ "${SERVER_MEMORY}" == 0 ]; then SERVER_MEMORY=512; fi
fi

# Replace startup variables.
MODIFIED_STARTUP=$(echo "${STARTUP}" | sed -e 's/{{/${/g' -e 's/}}/}/g' | eval echo "$(cat -)")

# If Forge compatibility is enabled and above variable is empty, add the parameters to the startup command
if [ "${FORGE_COMPATIBILITY}" = 1 ] && [ -z "${JLINE_ARGS}" ] && [ -z "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& -Dterminal.jline=false -Dterminal.ansi=true/')
    echo -e "\033[1;33mNOTE: \033[0mForge compatibility mode is enabled."
fi

# Log4j2 vulnerability workaround
if [ "${LOG4J2_VULN_WORKAROUND}" = 1 ] && [ -z "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& -Dlog4j2.formatMsgNoLookups=true/')
    echo -e "\033[1;33mNOTE: \033[0mThe Log4j2 vulnerability workaround has been enabled. If you're running an unpatched server software build, remember to update ASAP as this workaround may be removed at any time, and is not effective in older versions of the game."
fi

# SIMD operations (https://github.com/sparkedhost/images/issues/4)
if [ "${SIMD_OPERATIONS}" = 1 ] && [ -z "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& --add-modules=jdk.incubator.vector/')
    echo -e "\033[1;33mNOTE: \033[0mSIMD operations are enabled."
fi

# Forge 1.17.1+
if [ -n "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP="java -Xms128M -Xmx${SERVER_MEMORY}M -Dterminal.jline=false -Dterminal.ansi=true @libraries/net/minecraftforge/forge/${FORGE_VERSION}/unix_args.txt"
fi

# Timezone
if [ ! "${TIMEZONE}" = "Default" ] && [ -z "${TIMEZONE_INUSE}" ] && [ ! -z "${TIMEZONE}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& -Duser.timezone=${TIMEZONE}/')
fi

# Aikar flags
if [ "${AIKAR_FLAGS}" = 1 ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& -XX:+UseG1GC -XX:+ParallelRefProcEnabled -XX:MaxGCPauseMillis=200 -XX:+UnlockExperimentalVMOptions -XX:+DisableExplicitGC -XX:G1NewSizePercent=30 -XX:G1MaxNewSizePercent=40 -XX:G1HeapRegionSize=8M -XX:G1ReservePercent=20 -XX:G1HeapWastePercent=5 -XX:G1MixedGCCountTarget=4 -XX:InitiatingHeapOccupancyPercent=15 -XX:G1MixedGCLiveThresholdPercent=90 -XX:G1RSetUpdatingPauseTimePercent=5 -XX:SurvivorRatio=32 -XX:+PerfDisableSharedMem -XX:MaxTenuringThreshold=1 -Daikars.new.flags=true/')
    echo -e "\033[1;33mNOTE: \033[0mEnabled Aikar's Flags"
fi

# Print startup command to console
echo -e "\033[1;33mcustomer@sparkedhost:~\$\033[0m ${MODIFIED_STARTUP}"

# Run the server
eval ${MODIFIED_STARTUP}
