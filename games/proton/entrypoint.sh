sleep 1

source /spark-utils.sh

if [[ -f "/usr/local/bin/proton" ]] && ! setup_proton_compatibility; then
    exit 0
fi

cd /home/container || exit 1

update_game_server

game_pre_startup
startup_game
