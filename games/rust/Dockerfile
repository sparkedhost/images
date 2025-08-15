# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-rust
# ----------------------------------

FROM node:22-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND=noninteractive

RUN	dpkg --add-architecture i386 \
	&& apt update \
	&& apt upgrade -y \
	&& apt install -y lib32gcc-s1 lib32stdc++6 locales unzip curl iproute2 tzdata libgdiplus libsdl2-2.0-0:i386 \
	&& mkdir /wrapper \
	&& npm install --prefix /wrapper ws

RUN  sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen && \
     locale-gen
ENV  LANG=en_US.UTF-8
ENV  LANGUAGE=en_US:en
ENV  LC_ALL=en_US.UTF-8

RUN  useradd -m -d /home/container -s /bin/bash container
USER container
ENV USER=container HOME=/home/container

WORKDIR /home/container

COPY ./rust/entrypoint.sh /entrypoint.sh
COPY --chown=container:container ./rust/wrapper.js /wrapper/wrapper.js
RUN	chmod +x /wrapper/wrapper.js

CMD	[ "/bin/bash", "/entrypoint.sh" ]