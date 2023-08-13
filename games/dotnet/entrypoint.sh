sleep 1

cd /home/container

export DOTNET_ROOT=/usr/share/

MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@sparkedhost:~\$\033[0m ${MODIFIED_STARTUP}"

eval ${MODIFIED_STARTUP}