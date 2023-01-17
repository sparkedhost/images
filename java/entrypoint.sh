#!/bin/bash
cd /home/container

# Print current Java version
JAVA_VER=$(java -version 2>&1 | head -1 | cut -d'"' -f2 | sed '/^1\./s///')
echo "Java version: ${JAVA_VER}"

# Make internal Docker IP address available to processes
export INTERNAL_IP=$(ip route get 1 | awk '{print $NF;exit}')

# Check if startup command has -Dterminal.jline=false -Dterminal.ansi=true
JLINE_ARGS=$(echo ${MODIFIED_STARTUP} | grep -o "\-Dterminal.jline=false -Dterminal.ansi=true")
TIMEZONE_INUSE=$(echo ${MODIFIED_STARTUP} | grep -o "\-Duser.timezone=")

# If Lower Xmx is enabled, then lower the Xmx value by 20% to account for the JVM overhead.
if [ "${LOWER_XMX}" = 1 ]; then
    SERVER_MEMORY=$(expr $SERVER_MEMORY * 0.8)
fi

# Replace startup variables.
MODIFIED_STARTUP=$(echo "${STARTUP}" | sed -e 's/{{/${/g' -e 's/}}/}/g' | eval echo "$(cat -)")

# If Forge compatibility is enabled and above variable is empty, add the parameters to the startup command
if [ "${FORGE_COMPATIBILITY}" = 1 ] && [ -z "${JLINE_ARGS}" ] && [ -z "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)M/& -Dterminal.jline=false -Dterminal.ansi=true/')
    echo -e "\033[1;33mNOTE: \033[0mForge compatibility mode is enabled."
fi

# Log4j2 vulnerability workaround
if [ ! "${LOG4J2_VULN_WORKAROUND}" = "disabled" ] && [ -n "${LOG4J2_VULN_WORKAROUND}" ]; then

    if [ "${LOG4J2_VULN_WORKAROUND}" = "formatMsgNoLookups" ]; then
        MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)M/& -Dlog4j2.formatMsgNoLookups=true/')
        echo -e "\033[1;33mNOTE: \033[0mThe Log4j2 vulnerability workaround for 1.17 and 1.18 has been enabled. Please consider updating your server to a newer version of Minecraft."
    fi

    if [ "${LOG4J2_VULN_WORKAROUND}" = "log4j2_112-116.xml" ]; then
        if [ ! -f "log4j2_112-116.xml" ]; then
            curl --silent -Lo log4j2_112-116.xml https://launcher.mojang.com/v1/objects/02937d122c86ce73319ef9975b58896fc1b491d1/log4j2_112-116.xml
        fi
        MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)M/& -Dlog4j.configurationFile=log4j2_112-116.xml/')
        echo -e "\033[1;33mNOTE: \033[0mThe Log4j2 vulnerability workaround for 1.12 - 1.16.5 has been enabled. Please consider updating your server to a newer version of Minecraft."
    fi

    if [ "${LOG4J2_VULN_WORKAROUND}" = "log4j2_17-111.xml" ]; then
        if [ ! -f "log4j2_17-111.xml" ]; then
            curl --silent -Lo log4j2_17-111.xml https://launcher.mojang.com/v1/objects/4bb89a97a66f350bc9f73b3ca8509632682aea2e/log4j2_17-111.xml
        fi
        MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)M/& -Dlog4j.configurationFile=log4j2_17-111.xml/')
        echo -e "\033[1;33mNOTE: \033[0mThe Log4j2 vulnerability workaround for 1.7 - 1.11.2 has been enabled. Please consider updating your server to a newer version of Minecraft."
    fi

fi

# SIMD operations (https://github.com/sparkedhost/images/issues/4)
if [ "${SIMD_OPERATIONS}" = 1 ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)M/& --add-modules=jdk.incubator.vector/')
    echo -e "\033[1;33mNOTE: \033[0mSIMD operations are enabled."
fi


# Timezone
if [ ! "${TIMEZONE}" = "Default" ] && [ -z "${TIMEZONE_INUSE}" ] && [ ! -z "${TIMEZONE}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)M/& -Duser.timezone=${TIMEZONE}/')
fi

# Aikar flags
if [ "${AIKAR_FLAGS}" = 1 ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)M/& -XX:+UseG1GC -XX:+ParallelRefProcEnabled -XX:MaxGCPauseMillis=200 -XX:+UnlockExperimentalVMOptions -XX:+DisableExplicitGC -XX:G1NewSizePercent=30 -XX:G1MaxNewSizePercent=40 -XX:G1HeapRegionSize=8M -XX:G1ReservePercent=20 -XX:G1HeapWastePercent=5 -XX:G1MixedGCCountTarget=4 -XX:InitiatingHeapOccupancyPercent=15 -XX:G1MixedGCLiveThresholdPercent=90 -XX:G1RSetUpdatingPauseTimePercent=5 -XX:SurvivorRatio=32 -XX:+PerfDisableSharedMem -XX:MaxTenuringThreshold=1 -Daikars.new.flags=true/')
    echo -e "\033[1;33mNOTE: \033[0mEnabled Aikar's Flags"
fi

# Forge 1.17.1+
if [ -n "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP="java -Xms128M -Xmx${SERVER_MEMORY}M -Dterminal.jline=false -Dterminal.ansi=true @libraries/net/minecraftforge/forge/${FORGE_VERSION}/unix_args.txt"
fi

# Print startup command to console
echo -e "\033[1;33mcustomer@sparkedhost:~\$\033[0m ${MODIFIED_STARTUP}"

# Run the server
eval ${MODIFIED_STARTUP}
