# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:games-installer
# ----------------------------------

FROM debian:trixie-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update && apt upgrade -y \
    && apt -y --no-install-recommends install ca-certificates curl git unzip zip tar jq wget lib32gcc-s1 libcap2-bin file