FROM apache/kvrocks:2.16.0

LABEL author="DevOps Team at Sparked Host" \
      maintainer="devops@sparkedhost.com" \
      org.opencontainers.image.title="Sparked Host KVRocks 2.16" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates iproute2 supervisor \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && rm -rf /var/lib/apt/lists/*

COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY entrypoint.sh /entrypoint.sh

RUN chmod 0755 /entrypoint.sh \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
STOPSIGNAL SIGTERM
ENTRYPOINT []
CMD ["/entrypoint.sh"]
