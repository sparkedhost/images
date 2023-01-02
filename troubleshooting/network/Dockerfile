# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:network-debian
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN useradd -m -d /home/container -s /bin/bash container

RUN ln -s /home/container/ /nonexistent

ENV USER=container HOME=/home/container

RUN apt update \
 && apt upgrade -y

RUN apt install -y ca-certificates tzdata curl net-tools iproute2 dnsutils iputils-ping jq

WORKDIR /home/container

COPY ./network/entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]
