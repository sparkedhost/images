# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:games-beammp
# ----------------------------------

FROM debian:trixie-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN useradd -m -d /home/container -s /bin/bash container

RUN ln -s /home/container/ /nonexistent

ENV USER=container HOME=/home/container

RUN dpkg --add-architecture i386 \
    && apt update \
    && apt upgrade -y \
    && apt install -y --no-install-recommends gcc g++ libgcc-12-dev libc++-dev gdb libc6 git wget curl tar zip unzip binutils xz-utils liblzo2-2 cabextract iproute2 net-tools netcat-traditional telnet libatomic1 libsdl1.2debian libsdl2-2.0-0 libfontconfig1 icu-devtools libunwind8 libssl-dev sqlite3 libsqlite3-dev libmariadb-dev-compat libduktape207 locales ffmpeg gnupg2 apt-transport-https ca-certificates liblua5.3-0 libz3-dev libzadc4 rapidjson-dev tzdata libevent-dev libprotobuf32 libfluidsynth3 procps libstdc++6 tini pcre2-utils

RUN if [ "$(uname -m)" = "x86_64" ]; then \
        wget http://archive.ubuntu.com/ubuntu/pool/main/i/icu/libicu66_66.1-2ubuntu2.1_amd64.deb && \
        wget http://archive.ubuntu.com/ubuntu/pool/main/o/openssl/libssl1.1_1.1.0g-2ubuntu4_amd64.deb && \
        dpkg -i libicu66_66.1-2ubuntu2.1_amd64.deb && \
        dpkg -i libssl1.1_1.1.0g-2ubuntu4_amd64.deb && \
        rm libicu66_66.1-2ubuntu2.1_amd64.deb libssl1.1_1.1.0g-2ubuntu4_amd64.deb; \
    fi
RUN update-locale lang=en_US.UTF-8 \
    && dpkg-reconfigure --frontend noninteractive locales

WORKDIR /home/container

COPY ./beammp/entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]