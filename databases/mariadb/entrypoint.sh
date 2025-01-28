#!/bin/bash
cd /home/container

# Make internal Docker IP address available to processes.
INTERNAL_IP=$(ip route get 1 | awk '{print $(NF-2);exit}')
export INTERNAL_IP

# Determine correct executables to use
if which mariadbd-safe > /dev/null ; then
	MARIADBD_EXECUTABLE="mariadbd"
	MARIADB_INSTALLDB_EXECUTABLE="mariadb-install-db"
	MARIAAB_UPGRADE_EXECUTABLE="mariadb-upgrade"
	MARIADB_EXECUTABLE="mariadb"

else
	MARIADBD_EXECUTABLE="mysqld"
	MARIADB_INSTALLDB_EXECUTABLE="mysql_install_db"
	MARIAAB_UPGRADE_EXECUTABLE="mysql_upgrade"
	MARIADB_EXECUTABLE="mysql"
fi



# Setup NSS Wrapper - Some valiables already set in the Dockerfile
export USER_ID=$(id -u)
export GROUP_ID=$(id -g)
envsubst < /passwd.template > ${NSS_WRAPPER_PASSWD}
envsubst < /group.template > ${NSS_WRAPPER_GROUP}
if [ -f  /usr/lib/libnss_wrapper.so ]; then
	export LD_PRELOAD=/usr/lib/libnss_wrapper.so
else
	export LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libnss_wrapper.so
fi


# Ensure required folders exist
mkdir -p $HOME/run/mysqld
mkdir -p $HOME/log/mysql
mkdir -p $HOME/mysql


# Only run install db if the database is not setup
if [ ! -f mysql/mysql/user.frm ] ; then
	echo "Database not setup - running initial setup"
	$MARIADB_INSTALLDB_EXECUTABLE --datadir=$HOME/mysql
fi


echo "Starting MariaDB server..."
$MARIADBD_EXECUTABLE --datadir=$HOME/mysql &
PID=$!

sleep 5

echo "Upgrading MariaDB if needed..."
$MARIAAB_UPGRADE_EXECUTABLE -u container


# Replace Startup Variables
MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
echo ":/home/container$ ${MODIFIED_STARTUP}"

# Run the Server
eval ${MODIFIED_STARTUP}

# Wait for the server to stop
wait $PID > /dev/null 2>&1
