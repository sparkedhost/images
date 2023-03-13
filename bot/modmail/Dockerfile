# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:bot-modmail
# ----------------------------------

FROM        python:3.11-slim-bullseye

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt update \
 && apt install -y gnupg build-essential git netcat ffmpeg iproute2 curl tzdata wget \
 && wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | apt-key add - \
 && echo "deb http://repo.mongodb.org/apt/debian bullseye/mongodb-org/6.0 main" | tee /etc/apt/sources.list.d/mongodb-org-6.0.list \
 && apt update \
 && apt install -y mongodb-org mongodb-org-server mongodb-org-shell mongodb-org-mongos mongodb-org-tools \
 && useradd -d /home/container -m container -s /bin/bash \
 && /usr/local/bin/python -m pip install --upgrade pip

USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./bastion/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
