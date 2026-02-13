#!/usr/bin/env bash
# UPDATE_SERVER AUTO_UPDATE_CHOICE TXADMIN_ENABLE TXADMIN_PORT STEAM_WEBAPIKEY DOWNLOAD_URL FIVEM_VERSION SERVER_HOSTNAME FIVEM_LICENSE MAX_PLAYERS 

# Global Variables and configuration
TXADMIN_DIR="/home/container/txData"
TXADMIN_CONFIG_FILE="/home/container/txData/default/config.json"
SERVER_PORT="${SERVER_PORT:-30123}"
VERSION_FILE="/home/container/fivem_version.txt" 
TXADMIN_INSTALLED=false

if [[ -f "$VERSION_FILE" ]]; then
    LAST_VERSION=$(cat "$VERSION_FILE")
else
    LAST_VERSION=""
fi

if [[ -f $TXADMIN_CONFIG_FILE ]]; then
    TXADMIN_INSTALLED=true
    TXADMIN_VERSION=$(jq '.version' "$TXADMIN_CONFIG_FILE" -r)
    if [[ $TXADMIN_VERSION == "2" ]];then
        TXADMIN_SERVER_PATH=$(jq -r '.server.dataPath' $TXADMIN_CONFIG_FILE)
    else
        TXADMIN_SERVER_PATH=$(jq -r '.fxRunner.serverDataPath' $TXADMIN_CONFIG_FILE)
    fi
fi
[[ $TXADMIN_SERVER_PATH ]] && SERVER_CONFIG_FILE=$(find "$TXADMIN_SERVER_PATH" -type f -name "server.cfg" | head -n 1)


# Functions
add_logo(){
    # Needs imagemagick
    local  size new_image
    new_image=$(find ./ -maxdepth 1 -name "*.png"  | head -n 1)
    [[ -z $new_image ]] && new_image=$(find ./ -maxdepth 1 -name "*.jpg"  | head -n 1)
    [[ -z $new_image ]] && new_image=$(find ./ -maxdepth 1 -name "*.jpeg"  | head -n 1)
    [[ -z $new_image ]] && return 1
    if [[ -f $new_image ]];  then
    if [[ $(file -b --mime-type "$new_image") != "image/png" ]]; then
        convert "$new_image" "${new_image%.*}.png"
        new_image="${new_image%.*}.png"
    fi
    size=$(identify -format "%wx%h" "$new_image")
    if [[ "$size" != "96x96" ]]; then
        convert "$new_image" -resize 96x96\! "${new_image%.*}.png"
        new_image="${new_image%.*}.png"
    fi
    fi
    sed -i '/load_server_icon/d; /sv_hostname.*/a load_server_icon "'"$new_image"'"' server.cfg
}

update_resources(){
    if git clone -v https://github.com/citizenfx/cfx-server-data.git /tmp &>/dev/null; then
        cp -Rf /tmp/resources/* resources/
        echo "[STARTUP]: CitizenFX Resources updated successfully!"
        return 0
    else
        echo -e "[STARTUP]: Git clone operation failed, skipping..."
        return 1
    fi
}

# If session manager does not exist, the server will not work properly. It's safe to assume the default resources were broken in some way
fix_default_resources(){
    local session_manager_check
    session_manager_check=$(find ./resources -type d -name sessionmanager | head -1) 
    [[ -z ${session_manager_check} ]] && update_resources 
    return 0
}

fix_missing_artifacts(){
    if [[ ! -d alpine || ! -f alpine/opt/cfx-server/ld-musl-x86_64.so.1 ]]; then
        LAST_VERSION="" # Resetting last version so updating works
        update_artifacts
    fi
}

generate_download_link(){
    local release_page changelogs_page version_link
    release_page=$(curl -sSL https://runtime.fivem.net/artifacts/fivem/build_proot_linux/master/)
    changelogs_page=$(curl -sSL https://changelogs-live.fivem.net/api/changelog/versions/linux/server)

    if [[ "${FIVEM_VERSION}" == "recommended" || -z "${FIVEM_VERSION}" ]]; then
        TARGET_VERSION=$(echo "$changelogs_page" | jq -r '.recommended')
        DOWNLOAD_LINK=$(echo "$changelogs_page" | jq -r '.recommended_download')
    elif [[ "${FIVEM_VERSION}" == "latest" ]]; then
        TARGET_VERSION=$(echo "$changelogs_page" | jq -r '.latest')
        DOWNLOAD_LINK=$(echo "$changelogs_page" | jq -r '.latest_download')
    else
        version_link=$(echo "${release_page}" | grep -Eo '".*/*.tar.xz"' | sed 's/\"//g' | sed 's/\.\///1' | grep -i "${FIVEM_VERSION}" | grep -o =.* | tr -d '=')
        if [[ -z "$version_link" ]]; then
            echo "Defaulting to recommended version as the requested version was invalid."
            TARGET_VERSION=$(echo "$changelogs_page" | jq -r '.recommended')
            DOWNLOAD_LINK=$(echo "$changelogs_page" | jq -r '.recommended_download')
        else
            TARGET_VERSION="${version_link}"
            DOWNLOAD_LINK="https://runtime.fivem.net/artifacts/fivem/build_proot_linux/master/${version_link}"
        fi
    fi

    if [[ -n "${DOWNLOAD_URL}" ]]; then
        if curl --output /dev/null --silent --head --fail "${DOWNLOAD_URL}"; then
            echo "Overriding download link with DOWNLOAD_URL."
            DOWNLOAD_LINK="${DOWNLOAD_URL}"
        else
            echo "DOWNLOAD_URL is invalid. Exiting."
            exit 2
        fi
    fi
}

update_artifacts(){
    local filetype
    generate_download_link # Gives us DOWNLOAD_LINK and TARGET_VERSION globals

    echo "Current installed version: $LAST_VERSION"
    echo "Target version: $TARGET_VERSION"

    if [[ "$LAST_VERSION" == "$TARGET_VERSION" ]]; then
        echo "Version is up-to-date, skipping download."
        return 0 
    fi

    echo "New version detected. Downloading from: ${DOWNLOAD_LINK}"
    curl -sSL "${DOWNLOAD_LINK}" -o "${DOWNLOAD_LINK##*/}"

    filetype=$(file -F ',' "${DOWNLOAD_LINK##*/}" | cut -d',' -f2 | cut -d' ' -f2)
    if [[ "$filetype" == "gzip" ]]; then
        tar xzvf "${DOWNLOAD_LINK##*/}"
    elif [[ "$filetype" == "Zip" ]]; then
        unzip "${DOWNLOAD_LINK##*/}"
    elif [[ "$filetype" == "XZ" ]]; then
        tar xvf "${DOWNLOAD_LINK##*/}"
    else
        echo "Unknown filetype: $filetype. Exiting."
        exit 2
    fi

    rm -rf "${DOWNLOAD_LINK##*/}" run.sh
    echo "$TARGET_VERSION" > "$VERSION_FILE"
    echo "Updated to version: $TARGET_VERSION"
}

fix_configuration(){
    if [[ -z "$SERVER_CONFIG_FILE" ]]; then
        echo "server.cfg not found in $TXADMIN_SERVER_PATH. Skipping port fix."
    else
        sed -i -E "s|(endpoint_add_tcp\s+\"0\.0\.0\.0:)[0-9]+\"|\1${SERVER_PORT}\"|g" "$SERVER_CONFIG_FILE"
        sed -i -E "s|(endpoint_add_udp\s+\"0\.0\.0\.0:)[0-9]+\"|\1${SERVER_PORT}\"|g" "$SERVER_CONFIG_FILE"

        echo "✔ Updated endpoints in: $SERVER_CONFIG_FILE with port $SERVER_PORT"
    fi
}

check_license() {
    if [[ -z "$FIVEM_LICENSE" || ${#FIVEM_LICENSE} -lt 25 || ${#FIVEM_LICENSE} -gt 35 ]]; then
        echo "Incorrect license key format, please change it in the Startup tab on the panel."
        exit 0
    fi
}

prevent_malware() {
  if [ -f server.cfg ] && [ -s server.cfg ]; then
    mkdir -p resources/prevent_malware

    # Build the bad thread names array for JS
    local extra_threads=""
    if [[ -n "$BAD_THREAD_NAMES" ]]; then
      extra_threads=", $BAD_THREAD_NAMES"
    fi

    cat << EOF > ./resources/prevent_malware/prevent_malware.js
setImmediate(() => {
  const badThreadNames = ["miaus", "miauss", "miausss", "miaussss"${extra_threads}];

  globalThis.GlobalState = globalThis.GlobalState || {};
  for (const name of badThreadNames) {
    globalThis.GlobalState[name] = "Prevention";
  }
  console.log("Prevented malware from starting");

  const fix = () => {
    for (const name of badThreadNames) {
      globalThis.GlobalState[name] = "Prevention";
    }
    setTimeout(fix, 1000);
  };

  setTimeout(fix, 1000);
});
EOF

    cat << 'EOF' > ./resources/prevent_malware/fxmanifest.lua
fx_version 'cerulean'
game 'gta5'

author 'Host'
description 'Prevent malware'
version '1.0.0'

server_script {
    'prevent_malware.js'
}
EOF

    if ! grep -q 'ensure prevent_malware' server.cfg; then
      sed -i '1i\ensure prevent_malware' server.cfg
    fi
  fi
}

malware_scan() {
  [[ $ENABLE_MALWARE_SCANNER == 0 ]] && return 0
  local dirs=()
  local known_malware_found=0 potential_malware_found=0
  local system_resources_count potential_patterns malware_count=0
  [[ -d alpine ]] && dirs+=("alpine")
  [[ -d resources ]] && dirs+=("resources")
  
  if [[ ${#dirs[@]} -eq 0 ]]; then
    return 0 
  fi

  # There should only be 2 system resources, chat and monitor
  system_resources_count=$(find alpine/opt/cfx-server/citizen/system_resources -mindepth 1 -maxdepth 1 -type d | wc -l)
  echo "[Malware Scanner] Found ${system_resources_count} system resources"

  third_folder=$(find alpine/opt/cfx-server/citizen/system_resources -mindepth 1 -maxdepth 1 -type d \
  -printf '%f\n' | grep -Ev '^(chat|monitor)$'
  )

  # If we have a third system resource this means we are infected
  if [[ -d alpine/opt/cfx-server/citizen/system_resources/${third_folder} ]]; then
    if grep -RIPln --include='*.js' --exclude-dir=monitor '\\u[0-9a-fA-F]{4}([^\n]*\\u[0-9a-fA-F]{4}){14,}' alpine/opt/cfx-server/citizen/system_resources/${third_folder} >/dev/null 2>/dev/null; then
      echo "[Malware Scanner] Found malware in alpine/opt/cfx-server/citizen/system_resources/${third_folder}"
      known_malware_found=1
      malware_count=$((malware_count + 1))
    fi
  fi

  # Known patterns, very crude method but there's plenty of normal resources with obfuscated javascript unfortunately so detection is difficult
  echo "[Malware Scanner] Checking for known malware patterns"

  known_patterns=('Function("a","var b,ahA,ahB,ahC,__' 'const _xo="\\u0072\\u0064\\u0075\\u0' "sub(87565):gsub('%.%+', '')" )
  for pattern in "${known_patterns[@]}"; do
    if grep -RlF --include='*.js' --include='*.lua' "$pattern" ${dirs[@]} >/dev/null 2>/dev/null; then
      malware_count=$((malware_count + 1))
      echo "[Malware Scanner] $malware_count Malware Found"
      echo "[Malware Scanner] Please wait while log files are generated for support."
      grep -RlF --include='*.js' --include='*.lua' "$pattern" ${dirs[@]} 2>/dev/null >> malware_scan.log
      echo "[Malware Scanner] Log file generated."
      known_malware_found=1
      
    fi
  done

  potential_patterns=('/* [' 'Buffer.from(b64' 'new Function(code)();') 
  for pattern in "${potential_patterns[@]}"; do
    if grep -RlF --exclude-dir='\[builders\]' --exclude-dir='monitor' --exclude-dir='node_modules' --exclude-dir='webpack' --exclude-dir='yarn' --include='*.js' "$pattern" ${dirs[@]} >/dev/null 2>/dev/null; then
      echo "[Malware Scanner] Please wait while log files are generated for support."
      grep -RlF --exclude-dir='\[builders\]' --exclude-dir='monitor' --exclude-dir='node_modules' --exclude-dir='webpack' --exclude-dir='yarn' --include='*.js' "$pattern" ${dirs[@]} 2>/dev/null >> malware_scan_potential.log
      echo "[Malware Scanner] Log file generated, please contact support!"
      potential_malware_found=1
    fi
  done
  echo "[Malware Scanner] Scanning for potentially malicious files. There can be false positives here. This can take a moment..."

  while IFS= read -r -d '' ttf_file; do
    if ! file "$ttf_file" | grep -iq 'font data'; then
      echo "[Malware Scanner] Malware detected! $ttf_file"
      echo "$ttf_file" >> malware_scan_fonts.log
      known_malware_found=1
    fi
  done < <(find ${dirs[@]} -type f -name '*.ttf' -size +0c -print0 2>/dev/null)
  
  # This can have a lot of false positives, but it can help us catch some of the infected javascript files
  
  if grep -RPInl -m 1 --exclude-dir='[[]builders[]]' --exclude-dir='monitor' --exclude-dir='node_modules' --exclude-dir='webpack' --exclude-dir='yarn' --include='*.js' -P '(^|[^a-zA-Z0-9_])\b(eval\s*\(|(?<!new\s)Function\s*\()' ${dirs[@]} >/dev/null 2>/dev/null; then
    echo "[Malware Scanner] Please wait while log files are generated for support."
    grep -RPInl -m 1 --exclude-dir='[[]builders[]]' --exclude-dir='webpack' --exclude-dir='monitor' --exclude-dir='node_modules' --exclude-dir='yarn' --include='*.js' -P '(^|[^a-zA-Z0-9_])\b(eval\s*\(|(?<!new\s)Function\s*\()' ${dirs[@]} > malware_scan_potential.log 2>/dev/null
    echo "[Malware Scanner] Log file generated, please contact support!"
    potential_malware_found=1
  fi
  
  if [[ $known_malware_found -eq 1 ]];then 
    echo "[Malware Scanner] $malware_count Malware found"
    echo "[Malware Scanner] Server will not start up to prevent further damage to your files. Please contact support."
    sleep 999999999
    exit 0 
  fi
  if [[ $potential_malware_found -eq 1 ]];then 
    echo "[Malware Scanner] Malware possibly found, but it could be a false positive from a normal resource. This can find a lot of false positives, so don't worry too much."
    echo "[Malware Scanner] Waiting for 60 seconds before starting the server. You can contact support if you have worries."
    sleep 60
  fi
}

sleep 5
cd /home/container
check_license
if [[ ${UPDATE_SERVER} == 1 ]]; then
    case $AUTO_UPDATE_CHOICE in
        both)
            update_artifacts
            update_resources
        ;;
        artifacts)
            update_artifacts
        ;;
        resources)
            update_resources
        ;;
    esac
fi

fix_missing_artifacts
fix_default_resources
fix_configuration
add_logo 
prevent_malware 
malware_scan
check_license
mkdir -p logs/

MODIFIED_STARTUP=$(echo "${STARTUP}" | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"
eval "${MODIFIED_STARTUP}"
