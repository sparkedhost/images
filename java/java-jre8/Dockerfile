# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:java-jre8
# ----------------------------------

FROM        debian:bullseye-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt-get update -y \
 && apt-get install fontconfig iproute2 curl ca-certificates tar -y \
 && useradd -d /home/container -m container \
 && mkdir -p /opt/java

# Download pre-built Temurin JRE binaries
ADD https://github.com/adoptium/temurin8-binaries/releases/download/jdk8u382-b05/OpenJDK8U-jre_x64_linux_hotspot_8u382b05.tar.gz /opt/java/java.tar.gz
RUN tar -C /opt/java --strip-components=1 -xzf /opt/java/java.tar.gz \
 && rm -f /opt/java/java.tar.gz

# Add Sparked certificate authority
ADD ca.crt /usr/local/share/ca-certificates/sparked-ca.crt
RUN chmod 644 /usr/local/share/ca-certificates/sparked-ca.crt && update-ca-certificates

# Set user that will run the entrypoint script, and overwrite USER, HOME and PATH environment variables
# We need to inject the path to the Java binaries in the PATH environment variable
USER container
ENV  USER=container HOME=/home/container PATH="$PATH:/opt/java/bin" LANG=C.UTF-8

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]

