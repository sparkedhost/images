#!/bin/bash
cd /home/container

# Print current Java version
JAVA_VER=`java -version 2>&1 | head -1 | cut -d'"' -f2 | sed '/^1\./s///'`
echo "Java version: ${JAVA_VER}"

# Make internal Docker IP address available to processes
export INTERNAL_IP=`ip route get 1 | awk '{print $(NF-2);exit}'`

# Replace startup variables.
MODIFIED_STARTUP=$(echo "${STARTUP}" | sed -e 's/{{/${/g' -e 's/}}/}/g' | eval echo "$(cat -)")

# Check if startup command has -Dterminal.jline=false -Dterminal.ansi=true
JLINE_ARGS=$(echo ${MODIFIED_STARTUP} | grep -o "\-Dterminal.jline=false -Dterminal.ansi=true")
TIMEZONE_INUSE=$(echo ${MODIFIED_STARTUP} | grep -o "\-Duser.timezone=")

# If Forge compatibility is enabled and above variable is empty, add the parameters to the startup command
if [ "${FORGE_COMPATIBILITY}" = 1 ] && [ -z "${JLINE_ARGS}" ] && [ -z "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& -Dterminal.jline=false -Dterminal.ansi=true/')
    echo -e "\033[1;33mNOTE: \033[0mForge compatibility mode is enabled"
fi

# Log4j2 vulnerability workaround
if [ "${LOG4J2_VULN_WORKAROUND}" = 1 ] && [ -z "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& -Dlog4j2.formatMsgNoLookups=true/')
    echo -e "\033[1;33mNOTE: \033[0mThe Log4j2 vulnerability workaround has been enabled. If you're running an unpatched server software build, remember to update ASAP as this workaround may be removed at any time, and is not effective in older versions of the game"
fi

# SIMD operations (https://github.com/sparkedhost/images/issues/4)
if [ "${SIMD_OPERATIONS}" = 1 ] && [ -z "${FORGE_VERSION}" ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& --add-modules=jdk.incubator.vector/')
    echo -e "\033[1;33mNOTE: \033[0mSIMD operations are enabled"
fi

# Forge 1.17.1+
if [ -n "${FORGE_VERSION}" ]; then
    if [ -f "libraries/net/minecraftforge/forge/${FORGE_VERSION}/unix_args.txt" ]; then
        MODIFIED_STARTUP="java -Xms128M -Xmx${SERVER_MEMORY}M -Dterminal.jline=false -Dterminal.ansi=true @libraries/net/minecraftforge/forge/${FORGE_VERSION}/unix_args.txt"
    else 
        echo -e "\033[1;33mNOTE: \033[0mReverting to default startup, unix_args.txt was not found."
    fi
fi

# Aikar flags
if [ "${AIKAR_FLAGS}" = 1 ]; then
    MODIFIED_STARTUP=$(echo "${MODIFIED_STARTUP}" | sed -E 's/-Xmx([0-9]+)[KMG]?/& -XX:+UseG1GC -XX:+ParallelRefProcEnabled -XX:MaxGCPauseMillis=200 -XX:+UnlockExperimentalVMOptions -XX:+DisableExplicitGC -XX:G1NewSizePercent=30 -XX:G1MaxNewSizePercent=40 -XX:G1HeapRegionSize=8M -XX:G1ReservePercent=20 -XX:G1HeapWastePercent=5 -XX:G1MixedGCCountTarget=4 -XX:InitiatingHeapOccupancyPercent=15 -XX:G1MixedGCLiveThresholdPercent=90 -XX:G1RSetUpdatingPauseTimePercent=5 -XX:SurvivorRatio=32 -XX:+PerfDisableSharedMem -XX:MaxTenuringThreshold=1 -Daikars.new.flags=true/')
    echo -e "\033[1;33mNOTE: \033[0mEnabled Aikar's Flags"
fi

# If Lower Xmx is enabled, then replace Xmx with MaxRAMPercentage
if [ "${LOWER_XMX}" = 1 ]; then
    MODIFIED_STARTUP=$(echo "$MODIFIED_STARTUP" | sed 's/-Xmx\([0-9]*\)[KMG]/-XX:MaxRAMPercentage=80.0/g')
    echo -e "\033[1;33mNOTE: \033[0mEnabled Lower Maximum RAM"
fi

# If Auto Update is enabled and Update API URL is set, then update the server
if [ "${AUTO_UPDATE_JAR}" = 1 ] && [ -n "${UPDATE_API_URL}" ]; then
    echo -e "\033[1;33mNOTE: \033[0mAuto Update is enabled"

    # Identify the latest version
    # Check if libraries/net/minecraftforge/forge exists
    if [ -d "libraries/net/minecraftforge/forge" ] && [ -z "${HASH}" ]; then
        # get first folder in libraries/net/minecraftforge/forge
        FORGE_VERSION=$(ls libraries/net/minecraftforge/forge | head -n 1)

        # Check if unix_args.txt exists in libraries/net/minecraftforge/forge/${FORGE_VERSION}
        if [ -f "libraries/net/minecraftforge/forge/${FORGE_VERSION}/unix_args.txt" ]; then
            HASH=$(sha256sum libraries/net/minecraftforge/forge/${FORGE_VERSION}/unix_args.txt | awk '{print $1}')
        fi
    fi

    # Check if libraries/net/neoforged/neoforge folder exists
    if [ -d "libraries/net/neoforged/neoforge" ] && [ -z "${HASH}" ]; then
        # get first folder in libraries/net/neoforged/neoforge
        NEOFORGE_VERSION=$(ls libraries/net/neoforged/neoforge | head -n 1)

        # Check if unix_args.txt exists in libraries/net/neoforged/neoforge/${FORGE_VERSION}
        if [ -f "libraries/net/neoforged/neoforge/${NEOFORGE_VERSION}/unix_args.txt" ]; then
            HASH=$(sha256sum libraries/net/neoforged/neoforge/${NEOFORGE_VERSION}/unix_args.txt | awk '{print $1}')
        fi
    fi

    # Hash server jar file
    if [ -z "${HASH}" ]; then
        HASH=$(sha256sum $SERVER_JARFILE | awk '{print $1}')
    fi

    # Check if hash is set
    if [ -n "${HASH}" ]; then
        API_RESPONSE=$(curl -s "${UPDATE_API_URL}/api/v1/build/${HASH}")

        # Check if .success is true
        if [ "$(echo $API_RESPONSE | jq -r '.success')" = "true" ]; then
            # Check if .build.id is .latest.id
            if [ "$(echo $API_RESPONSE | jq -r '.build.id')" != "$(echo $API_RESPONSE | jq -r '.latest.id')" ]; then
                echo -e "\033[1;33mNOTE: \033[0mNew version available. Updating server jar..."

                JAR_URL=$(echo $API_RESPONSE | jq -r '.latest.jarUrl')
                JAR_LOCATION=$(echo $API_RESPONSE | jq -r '.latest.jarLocation')
                ZIP_URL=$(echo $API_RESPONSE | jq -r '.latest.zipUrl')

                if [ "$JAR_LOCATION" = "null" ]; then
                    JAR_LOCATION=$SERVER_JARFILE
                fi

                if [ "$JAR_URL" != "null" ]; then
                    echo -e "\033[1;33mNOTE: \033[0mDownloading server jar from $JAR_URL"
                    curl -s -o $JAR_LOCATION $JAR_URL
                    echo -e "\033[1;33mNOTE: \033[0mServer jar has been downloaded"
                fi

                if [ "$ZIP_URL" != "null" ]; then
                    echo -e "\033[1;33mNOTE: \033[0mDownloading server zip from $ZIP_URL"
                    curl -s -o server_update_files.zip $ZIP_URL
                    echo -e "\033[1;33mNOTE: \033[0mServer zip has been downloaded"

                    echo -e "\033[1;33mNOTE: \033[0mExtracting server zip"
                    unzip -o server_update_files.zip
                    echo -e "\033[1;33mNOTE: \033[0mServer zip has been extracted"
                fi

                echo -e "\033[1;33mNOTE: \033[0mServer jar has been updated"
            else
                echo -e "\033[1;33mNOTE: \033[0mServer jar is up to date"
            fi
        else
            echo -e "\033[1;33mNOTE: \033[0mInstallation could not be verified. Skipping update check."
        fi
    else
        echo -e "\033[1;33mNOTE: \033[0mInstalled version could not be identified. Skipping update check."
    fi
fi

# Print startup command to console
echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

# Run the server.
exec env ${MODIFIED_STARTUP}
