# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:dotnet-8
# ----------------------------------

FROM        debian:bullseye-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV         DEBIAN_FRONTEND noninteractive

RUN         apt update -y \
            && apt install -y apt-transport-https wget curl iproute2 libgdiplus tini \
            && wget https://dot.net/v1/dotnet-install.sh \
            && D_V="$(curl -sSL https://dotnet.microsoft.com/en-us/download/dotnet/8.0 | grep -i  '<h3 id="sdk-8.*">SDK 8.*.*</h3>'  | head -1 | awk -F\" '{print $3}' | awk '{print $2;}' | sed 's/<\/h3>//g')" \ 
            && chmod +x dotnet-install.sh \
			&& ./dotnet-install.sh -i /usr/share -v $D_V \
            && ln -s /usr/share/dotnet /usr/bin/dotnet

COPY        --chown=container:container ./../entrypoint.sh /entrypoint.sh

RUN         chmod +x /entrypoint.sh
USER        container
ENV         USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh
CMD         ["/bin/bash", "/entrypoint.sh"]