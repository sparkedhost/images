# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-altv
# ----------------------------------

FROM debian:bookworm-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN     useradd -m -d /home/container -s /bin/bash container

RUN     apt update -y \
        && apt upgrade -y \
        && apt install -y g++ gcc libgcc-s1 lib32gcc-s1 gdb libstdc++6 libc6 git wget curl tar zip unzip binutils xz-utils liblzo2-2 cabextract iproute2 net-tools netcat-traditional telnet libatomic1 libsdl1.2debian libsdl2-2.0-0 \
        libfontconfig1 libicu72 icu-devtools libunwind8 libssl-dev sqlite3 libsqlite3-dev libmariadb-dev libduktape207 locales ffmpeg gnupg2 apt-transport-https software-properties-common ca-certificates tzdata libgdiplus \
        python3 dnsutils build-essential coreutils jq pcregrep tini

RUN     wget https://packages.microsoft.com/config/debian/12/packages-microsoft-prod.deb -O packages-microsoft-prod.deb \
        && dpkg -i packages-microsoft-prod.deb \
        && apt update -y \
        && apt install -y dotnet-sdk-6.0 dotnet-sdk-7.0 dotnet-sdk-8.0 libgdiplus

RUN     update-locale lang=en_US.UTF-8 \
        && dpkg-reconfigure --frontend noninteractive locales

USER    container
ENV     USER=container HOME=/home/container
WORKDIR /home/container
STOPSIGNAL SIGINT

COPY ./altv/entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]