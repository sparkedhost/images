# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-mta
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV   DEBIAN_FRONTEND noninteractive
RUN   useradd -m -d /home/container -s /bin/bash container

RUN   apt update \
      && apt upgrade -y \
      && apt install -y iproute2 tar libssl1.1 curl git default-libmysqlclient-dev libncurses5-dev libncursesw5-dev libtool zip unzip libncurses5 libncursesw5 python3 openssl locales ffmpeg apt-transport-https libc6 binutils xz-utils liblua5.1-0 \
      && update-locale lang=en_US.UTF-8 \
      && dpkg-reconfigure --frontend noninteractive locales

USER container
ENV  USER container
ENV  HOME /home/container

WORKDIR /home/container

COPY  ./mta/entrypoint.sh /entrypoint.sh
CMD   [ "/bin/bash", "/entrypoint.sh" ]