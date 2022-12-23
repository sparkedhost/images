# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:bot-bastion
# ----------------------------------

FROM        node:18-bullseye

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

## install mongo
RUN wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | apt-key add - \
 && echo "deb http://repo.mongodb.org/apt/debian bullseye/mongodb-org/6.0 main" | tee /etc/apt/sources.list.d/mongodb-org-6.0.list \
 && apt update \
 && apt install -y mongodb-org mongodb-org-server mongodb-org-shell mongodb-org-mongos mongodb-org-tools \
 && apt install -y python3 build-essential git libtool netcat ffmpeg iproute2 curl tzdata \
 && npm install -g npm@latest \
 && useradd -d /home/container -m container -s /bin/bash

USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./bastion/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]