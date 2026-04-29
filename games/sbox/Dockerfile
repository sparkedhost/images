# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:games-sbox
# ----------------------------------

FROM ubuntu:24.04

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND=noninteractive

RUN apt update -y && \
    apt install -y ca-certificates curl wget iproute2 tini libicu-dev libgdiplus libssl-dev libkrb5-3 libc6 lib32gcc-s1 libstdc++6 \ 
    && rm -rf /var/lib/apt/lists/*

RUN wget https://dot.net/v1/dotnet-install.sh && \
    chmod +x dotnet-install.sh && \
    ./dotnet-install.sh -i /usr/share/dotnet -channel 10.0 && \
    ln -s /usr/share/dotnet/dotnet /usr/bin/dotnet

COPY ./sbox/openssl.conf /etc/openssl.cnf
ENV OPENSSL_CONF=/etc/openssl.cnf

# Container user
RUN useradd -d /home/container -m container

COPY ./sbox/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER container
ENV USER=container HOME=/home/container DOTNET_ROOT=/usr/share/dotnet

WORKDIR /home/container

CMD ["/bin/bash", "/entrypoint.sh"]