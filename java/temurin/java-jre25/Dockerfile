# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:java-jre25
# ----------------------------------

FROM        debian:bookworm-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt-get update -y \
 && apt-get install fontconfig iproute2 curl ca-certificates unzip tar jq -y \
 && useradd -d /home/container -m container \
 && mkdir -p /opt/java

# Download pre-built Temurin JRE binaries
ADD https://github.com/adoptium/temurin25-binaries/releases/download/jdk-25.0.1%2B8/OpenJDK25U-jdk_x64_linux_hotspot_25.0.1_8.tar.gz /opt/java/java.tar.gz
RUN tar -C /opt/java --strip-components=1 -xzf /opt/java/java.tar.gz \
 && rm -f /opt/java/java.tar.gz


# Set user that will run the entrypoint script, and overwrite USER, HOME and PATH environment variables
# We need to inject the path to the Java binaries in the PATH environment variable
USER container
ENV  USER=container HOME=/home/container PATH="$PATH:/opt/java/bin" LANG=C.UTF-8

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]

