# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:python-3.6
# ----------------------------------

FROM        python:3.6-slim-bullseye

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt update \
 && apt -y install git gcc g++ ca-certificates dnsutils curl iproute2 ffmpeg procps libpq-dev python3-dev \
 && useradd -m -d /home/container container \
 && /usr/local/bin/python -m pip install --upgrade pip

ADD ca.crt /usr/local/share/ca-certificates/sparked-ca.crt
RUN chmod 644 /usr/local/share/ca-certificates/sparked-ca.crt && update-ca-certificates

USER container
ENV  USER=container HOME=/home/container TMPDIR=/home/container/.tmp

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
