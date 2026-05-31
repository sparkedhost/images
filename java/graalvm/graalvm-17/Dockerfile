# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:graalvm-17
# ----------------------------------

FROM        debian:bookworm-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt-get update -y \
    && apt-get install fontconfig iproute2 curl ca-certificates unzip tar jq zlib1g-dev build-essential -y \
    && useradd -d /home/container -m container \
    && mkdir -p /opt/java

# Dont download full .tar file, and auto-update java
RUN DOWNLOAD_URL=$(curl -s https://api.github.com/repos/graalvm/graalvm-ce-builds/releases | jq -r '.[] | select(.tag_name | startswith("jdk-17")) | .assets[] | select(.name | contains("linux-x64") and endswith(".tar.gz")) | .browser_download_url' | head -n 1) \
 && curl -L "$DOWNLOAD_URL" -o /opt/java/java.tar.gz \
 && tar -C /opt/java --strip-components=1 -xzf /opt/java/java.tar.gz \
 && rm -f /opt/java/java.tar.gz

# Set user that will run the entrypoint script, and overwrite USER, HOME and PATH environment variables
# We need to inject the path to the Java binaries in the PATH environment variable
USER container
ENV  USER=container HOME=/home/container PATH="$PATH:/opt/java/bin" LANG=C.UTF-8

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]