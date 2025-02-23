# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:bot-redbot
# ----------------------------------

FROM          python:3.11-slim

LABEL         author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN           mkdir -p /usr/share/man/man1
RUN           apt update \
              && apt -y upgrade \
              && apt -y install git ca-certificates dnsutils iproute2 wget curl xz-utils openjdk-17-jre-headless zlib1g-dev libffi-dev libmagickwand-dev unzip libaa1-dev build-essential tk-dev libncurses5-dev libncursesw5-dev libreadline6-dev libdb5.3-dev libgdbm-dev libsqlite3-dev libssl-dev libbz2-dev libexpat1-dev liblzma-dev ffmpeg imagemagick

RUN           pip install --upgrade pip
RUN           pip install python-forecastio tweepy unidecode mcstatus bs4 sqlalchemy geocoder valve python-valve py-cpuinfo psutil

RUN           mkdir -p /home/container/.config/Red-DiscordBot/
RUN           ln -s /home/container/.config/Red-DiscordBot/ /usr/local/share/Red-DiscordBot

RUN           useradd -m -d /home/container -s /bin/bash container

USER          container
ENV           USER=container HOME=/home/container

WORKDIR       /home/container

COPY          ./entrypoint.sh /entrypoint.sh
CMD           ["/bin/bash", "/entrypoint.sh"]