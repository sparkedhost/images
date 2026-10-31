# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-pocketmine
# ----------------------------------

FROM ghcr.io/sparkedhost/images:generic-debian

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

USER root

RUN curl -fsSL --retry 3 https://github.com/mikefarah/yq/releases/download/v4.54.1/yq_linux_amd64 -o /usr/local/bin/yq \
    && chmod 755 /usr/local/bin/yq

COPY ./pocketmine/entrypoint.sh /entrypoint.sh
COPY ./spark-utils/spark-utils.sh /spark-utils.sh

USER container

CMD ["/bin/bash", "/entrypoint.sh"]
