#!/bin/bash
cd /home/container

export INTERNAL_IP=$(ip route get 1 | awk '{print $(NF-2);exit}')

MODS_DIR="/home/container/mods"
ENABLED_FILE="$MODS_DIR/enabled.json"

mkdir -p "$MODS_DIR"

if ! ls "$MODS_DIR"/*.tmod >/dev/null 2>&1; then
    echo "No .tmod files found in $MODS_DIR, skipping enabled.json generation."
else
    mods=()
    for file in "$MODS_DIR"/*.tmod; do
        modname=$(basename "$file" .tmod)
        mods+=("\"$modname\"")
    done

    {
        echo "["
        for i in "${!mods[@]}"; do
            if [ $i -lt $((${#mods[@]}-1)) ]; then
                echo "  ${mods[$i]},"
            else
                echo "  ${mods[$i]}"
            fi
        done
        echo "]"
    } > "$ENABLED_FILE"

    echo "Updated enabled.json saved to '$ENABLED_FILE'."
fi

MODIFIED_STARTUP=$(echo -e ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
echo "customer@apollopanel:~# ${MODIFIED_STARTUP}"

eval ${MODIFIED_STARTUP}
