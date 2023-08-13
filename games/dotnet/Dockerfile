# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-dotnet
# ----------------------------------


FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN  apt update -y \
     && apt upgrade -y \
     && apt install -y apt-transport-https wget curl libgdiplus \
     && wget https://dot.net/v1/dotnet-install.sh \
     && D_V="$(curl -sSL https://dotnet.microsoft.com/en-us/download/dotnet/6.0 | grep -i  '<h3 id="sdk-6.*">SDK 6.*.*</h3>'  | head -1 | awk -F\" '{print $3}' | awk '{print $2;}' | sed 's/<\/h3>//g')" \   
     && chmod +x dotnet-install.sh \
	 && ./dotnet-install.sh -i /usr/share -v $D_V \
     && ln -s /usr/share/dotnet /usr/bin/dotnet

USER        container
ENV         USER=container HOME=/home/container
WORKDIR     /home/container

COPY ./dotnet/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]