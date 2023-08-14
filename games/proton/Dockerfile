
# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-proton
# ----------------------------------


FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN  dpkg --add-architecture i386 \
     && apt update \
     && apt install -y --no-install-recommends wget gnupg2 software-properties-common libntlm0 winbind xvfb xauth libncurses5-dev:i386 libncurses6 dbus libgdiplus lib32gcc-s1 \
     && apt install -y alsa-tools libpulse0 pulseaudio libpulse-dev libasound2 libao-common gnutls-bin gnupg locales cabextract curl python3 python3-pip python3-setuptools \
     && useradd -d /home/container -m container \
     && curl -sLOJ "$(curl -s https://api.github.com/repos/GloriousEggroll/proton-ge-custom/releases/latest | grep browser_download_url | cut -d\" -f4 | egrep .tar.gz)" \
     && tar -xzf GE-Proton*.tar.gz -C /usr/local/bin/ --strip-components=1 \
     && rm GE-Proton*.* \
     && rm -f /etc/machine-id \
     && dbus-uuidgen --ensure=/etc/machine-id \
     && rm /var/lib/dbus/machine-id \
     && dbus-uuidgen --ensure \
     && python3 -m pip install protontricks \
     && wget -q -O /usr/sbin/winetricks https://raw.githubusercontent.com/Winetricks/winetricks/master/src/winetricks \
     && chmod +x /usr/sbin/winetricks \
     && cd /tmp/ \
     && curl -sSL https://github.com/gorcon/rcon-cli/releases/download/v0.10.3/rcon-0.10.3-amd64_linux.tar.gz > rcon.tar.gz \
     && tar xvf rcon.tar.gz \
     && mv rcon-0.10.3-amd64_linux/rcon /usr/local/bin/
            
USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./proton/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]