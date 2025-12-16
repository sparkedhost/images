# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:graalvm-21
# ----------------------------------

FROM        debian:bookworm-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt-get update -y \
    && apt-get install fontconfig iproute2 curl ca-certificates unzip tar jq zlib1g-dev build-essential -y \
    && useradd -d /home/container -m container \
    && mkdir -p /opt/java

# Download pre-built Temurin JRE binaries
ADD https://github.com/graalvm/graalvm-ce-builds/releases/download/jdk-21.0.2/graalvm-community-jdk-21.0.2_linux-x64_bin.tar.gz /opt/java/java.tar.gz
RUN tar -C /opt/java --strip-components=1 -xzf /opt/java/java.tar.gz \
    && rm -f /opt/java/java.tar.gz

# Set user that will run the entrypoint script, and overwrite USER, HOME and PATH environment variables
# We need to inject the path to the Java binaries in the PATH environment variable
USER container
ENV  USER=container HOME=/home/container PATH="$PATH:/opt/java/bin" LANG=C.UTF-8

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]