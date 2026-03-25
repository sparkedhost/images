# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-cs2
# ----------------------------------
FROM registry.gitlab.steamos.cloud/steamrt/sniper/platform 

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt-get update && \
	apt-get install -y --no-install-recommends libstdc++6 libgcc-s1 lib32gcc-s1 libc6-i386 

ENV DEBIAN_FRONTEND noninteractive
USER container
ENV  USER container
ENV  HOME /home/container
WORKDIR /home/container

COPY ./cs2/entrypoint.sh /entrypoint.sh

ENV LD_LIBRARY_PATH=/home/container/game/bin/linuxsteamrt64:$LD_LIBRARY_PATH

CMD ["/bin/bash", "/entrypoint.sh"]