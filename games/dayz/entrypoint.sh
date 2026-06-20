MOD_FILE=modlist.html
SERVER_HOME=/home/container
STEAMCMD_ATTEMPTS=${STEAMCMD_ATTEMPTS:-3} # Default to 3 attempts

source /spark-utils.sh

sleep 1

cd /home/container

startup_game
