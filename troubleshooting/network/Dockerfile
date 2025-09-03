# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:network-debian
# ----------------------------------

FROM debian:trixie-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN useradd -m -d /home/container -s /bin/bash container

RUN ln -s /home/container/ /nonexistent

ENV USER=container HOME=/home/container

RUN apt update \
 && apt upgrade -y

RUN apt install -y ca-certificates tzdata curl net-tools iproute2 dnsutils iputils-ping jq

RUN setcap "cap_net_raw+ep" "/bin/ping"

WORKDIR /home/container

COPY ./network/entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]
