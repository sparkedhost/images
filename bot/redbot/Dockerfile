# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:bot-redbot
# ----------------------------------

FROM        python:3.11-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN         mkdir -p /usr/share/man/man1 \
            && apt update \
            && apt -y install git ca-certificates dnsutils iproute2 wget curl xz-utils git openjdk-11-jre \
            zlib1g-dev libffi-dev git libmagickwand-dev unzip libaa1-dev build-essential tk-dev libncurses5-dev libncursesw5-dev libreadline6-dev libdb5.3-dev libgdbm-dev libsqlite3-dev libssl-dev libbz2-dev libexpat1-dev liblzma-dev ffmpeg imagemagick \
            && pip install python-forecastio tweepy unidecode discord-text-sanitizer mcstatus bs4 sqlalchemy geocoder valve python-valve py-cpuinfo psutil \
            && useradd -m -d /home/container container \
            && mkdir -p /home/container/.config/Red-DiscordBot/ \
            && ln -s /home/container/.config/Red-DiscordBot/ /usr/local/share/Red-DiscordBot

ADD         ca.crt /usr/local/share/ca-certificates/sparked-ca.crt
RUN         chmod 644 /usr/local/share/ca-certificates/sparked-ca.crt && update-ca-certificates

USER        container
ENV         USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./redbot/entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]