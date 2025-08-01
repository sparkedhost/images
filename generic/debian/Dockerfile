# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:generic-debian
# ----------------------------------

FROM debian:bookworm

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN useradd -m -d /home/container -s /bin/bash container

RUN ln -s /home/container/ /nonexistent

ENV USER=container HOME=/home/container

RUN apt update \
 && apt upgrade -y

RUN apt install -y gcc g++ libgcc1 libc++-dev libstdc++6 gdb libc6 git wget curl tar zip unzip binutils xz-utils liblzo2-2 cabextract iproute2 net-tools telnet libatomic1 libsdl1.2debian libsdl2-2.0-0 jq \
    libfontconfig icu-devtools libunwind8 libssl-dev sqlite3 libsqlite3-dev locales ffmpeg gnupg2 apt-transport-https software-properties-common ca-certificates tzdata dnsutils

RUN update-locale lang=en_US.UTF-8 \
 && dpkg-reconfigure --frontend noninteractive locales

WORKDIR /home/container

COPY ./entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]
