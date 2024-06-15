# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:java-jre16
# ----------------------------------

FROM        debian:bookworm-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt-get update -y \
 && apt-get install fontconfig iproute2 curl ca-certificates unzip tar jq -y \
 && useradd -d /home/container -m container \
 && mkdir -p /opt/java

# Download pre-built Temurin JRE binaries
# Pre-compiled by Sparked Host using https://github.com/adoptium/temurin-build
ADD https://s3-modpacks.sparkedhost.us/OpenJDK16U-jre_x64_linux_hotspot_16.0.2%2B7.tar.gz /opt/java/java.tar.gz
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

