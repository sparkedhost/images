# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-mono
# ----------------------------------

FROM debian:trixie-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt-get update -y \
 && apt-get install -y fontconfig dirmngr curl ca-certificates openssl git tar tzdata iproute2 \
 && useradd -d /home/container -m container \
 && apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 3FA7E0328081BFF6A14DA29AA6A19B38D3D831EF \
 && sh -c 'echo "deb https://download.mono-project.com/repo/debian stable-buster main" > /etc/apt/sources.list.d/mono-official-stable.list' \
 && apt-get update \
 && apt-get install -y mono-complete lib32gcc-s1
 
USER container
ENV  USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./mono/entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
