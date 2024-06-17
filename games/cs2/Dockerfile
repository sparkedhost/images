# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-cs2
# ----------------------------------
FROM registry.gitlab.steamos.cloud/steamrt/sniper/platform 

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive
USER container
ENV  USER container
ENV  HOME /home/container

WORKDIR /home/container

COPY ./cs2/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]