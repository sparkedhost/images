
# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-proton-8
# ----------------------------------


FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN  dpkg --add-architecture i386 \
     && apt update \
     && apt -y upgrade \
     && apt install -y --no-install-recommends alsa-tools cabextract curl dbus ffmpeg git gnupg gnupg2 gnutls-bin iproute2 locales numactl mesa-utils net-tools netcat-openbsd pulseaudio python3 python3-pip python3-setuptools python3-venv software-properties-common tar tini virt-what wget winbind xauth xvfb lib32gcc-s1 libao-common libasound2 libsdl1.2debian libsdl1.2debian:i386 libsdl2-2.0-0 libsdl2-2.0-0:i386 libgdiplus libncurses5-dev:i386 libncurses6 libntlm0 libpulse-dev libpulse0 \ 
     && curl -sLOJ "https://github.com/GloriousEggroll/proton-ge-custom/releases/download/GE-Proton8-32/GE-Proton8-32.tar.gz" \
     && tar -xzf GE-Proton8-32.tar.gz -C /usr/local/bin/ --strip-components=1 \
     && rm GE-Proton8-32.tar.gz \
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

COPY ./proton-8/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
