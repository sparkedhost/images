# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-bedrock
# ----------------------------------

FROM ghcr.io/sparkedhost/images:generic-debian

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

USER root

RUN curl -fsSL --retry 3 https://apollo-assets-s3.sparkedhost.us/nethernetproxy -o /usr/local/bin/nethernetproxy \
    && chmod 755 /usr/local/bin/nethernetproxy

COPY ./spark-utils/spark-utils.sh /spark-utils.sh

USER container

CMD ["/bin/bash", "-c", "source /spark-utils.sh && startup_bedrock"]
