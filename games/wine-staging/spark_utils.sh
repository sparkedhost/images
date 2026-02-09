#!/usr/bin/env bash

# Extracted from the provided install snippet, merged to support multiple games.
install_bepinex() {
    local api_url zip_prefix extracted_dir zip_name
    local version_file=".apollo/bepinex_version"
    local api_response download_url version_number installed_version

    case "${SRCDS_APPID}" in
        896660)
            # Valheim
            api_url="https://thunderstore.io/api/experimental/package/denikson/BepInExPack_Valheim/"
            zip_prefix="denikson-BepInExPack_Valheim"
            extracted_dir="BepInExPack_Valheim"
            ;;
        1829350)
            # V Rising
            api_url="https://thunderstore.io/api/experimental/package/BepInEx/BepInExPack_V_Rising/"
            zip_prefix="BepInEx-BepInExPack_V_Rising"
            extracted_dir="BepInExPack_V_Rising"
            ;;
        *)
            return 1
            ;;
    esac

    echo "-------------------------------------------------------"
    echo "installing BepInEx..."
    echo "-------------------------------------------------------"
    if ! api_response=$(curl -sfSL -H "accept: application/json" "${api_url}"); then
            echo "Error: could not retrieve BepInEx release info from Thunderstore.io API"
            return 1
    fi

    download_url=$(jq -r  ".latest.download_url" <<< "$api_response" )
    version_number=$(jq -r  ".latest.version_number" <<< "$api_response" )
    zip_name="${zip_prefix}-${version_number}.zip"

    cd /home/container || return 1
    mkdir -p ".apollo" || return 1

    installed_version="$(cat "${version_file}" 2>/dev/null || true)"
    
    if [[ -d "BepInEx" && -n "${installed_version}" && "${installed_version}" == "${version_number}" ]]; then
        echo "BepInEx up to date (${installed_version})"
        return 0
    fi

    wget --content-disposition "$download_url"
    unzip -o "${zip_name}"
    cp -al "/home/container/${extracted_dir}/"* /home/container


    ##cleanup
    echo "-------------------------------------------------------"
    echo "cleanup files..."
    echo "-------------------------------------------------------"
    local -a cleanup_paths=(
        "${extracted_dir}"
        "${zip_name}"
        "icon.png"
        "manifest.json"
        "CHANGELOG.md"
        "README.m"
        "README.md"
    )
    rm -rf "${cleanup_paths[@]}"
    printf '%s\n' "${version_number}" > "${version_file}"


    echo "-------------------------------------------------------"
    echo "Installation completed"
    echo "-------------------------------------------------------"
}

game_pre_startup(){
    case $SRCDS_APPID in
        1829350)
            if [[ "${INSTALL_BEPINEX:-0}" -eq 1 ]]; then
                export WINEDLLOVERRIDES="winhttp=n,b${WINEDLLOVERRIDES:+;${WINEDLLOVERRIDES}}"
            fi
        ;;
    esac
}
