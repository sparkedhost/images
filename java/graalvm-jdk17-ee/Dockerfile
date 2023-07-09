FROM --platform=$TARGETOS/$TARGETARCH amd64/debian

LABEL author="Louis Ravignot Dos Santos" maintainer="louis.rds@akticube.fr"
LABEL org.opencontainers.image.source="https://github.com/AktiCube/containers/"
LABEL org.opencontainers.image.description="A Docker image to run GraalVM EE JDK 17 on Pterodactyl" 

# Linux things
ARG DEBIAN_FRONTEND=noninteractive

# Installing GraalVM EE JDK 17
ARG GRAALVM_VERSION=22.3.2
ARG JAVA_VERSION=java17
ARG GRAALVM_PKG=https://akticu.be/GraalVM/graalvm-ee-${JAVA_VERSION}-linux-amd64-${GRAALVM_VERSION}.tar.gz
ARG GRAALVM_FOLDER_NAME=graalvm-ee-${JAVA_VERSION}-${GRAALVM_VERSION}

RUN apt-get update -y \
    && apt-get upgrade -y \
    && apt-get install -y lsof curl ca-certificates openssl git tar sqlite3 fontconfig libfreetype6 tzdata iproute2 libstdc++6 locales locales-all font-manager

# Setting the language
RUN echo "Setting the language..."
ENV LANG='en_US.UTF-8'
ENV LANGUAGE='en_US.UTF-8'
ENV LC_ALL='en_US.UTF-8'
RUN sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen && locale-gen

RUN echo ${GRAALVM_PKG}
RUN cd /usr/local/ \
    && echo "Downloading GraalVM EE..." && curl --retry 3 -Lfso /tmp/graalvm.tar.gz ${GRAALVM_PKG} \
    && echo "Extracting GraalVM EE..." && tar -xzf /tmp/graalvm.tar.gz \
    && echo "Delete temp file..." && rm /tmp/graalvm.tar.gz

RUN echo "Mooving GraalVM EE..." && mkdir /usr/local/graalvm && mv /usr/local/${GRAALVM_FOLDER_NAME}/* /usr/local/graalvm && rm -rf /usr/local/${GRAALVM_FOLDER_NAME}

RUN echo "Setting GraalVM EE environment variables..."
ENV JAVA_HOME=/usr/local/graalvm
ENV PATH=/usr/local/graalvm/bin:${PATH}

# Check if the installation was successful
RUN java -version 

# Pterodactyl things
RUN useradd -d /home/container -m container

USER container
ENV USER=container HOME=/home/container
WORKDIR /home/container

COPY ./../entrypoint.sh /entrypoint.sh
CMD [ "/bin/bash", "/entrypoint.sh" ]
