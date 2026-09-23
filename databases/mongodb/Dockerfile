ARG MONGO_VERSION=8.0.4
FROM mongo:${MONGO_VERSION}

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates supervisor \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && rm -rf /var/lib/apt/lists/*

COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY entrypoint.sh /entrypoint.sh

RUN chmod 0755 /entrypoint.sh \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]
