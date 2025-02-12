# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:mariadb-11.7
# ----------------------------------

FROM mariadb:11.7-noble-rc

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update \
    && apt install iproute2 -y \
    && useradd -d /home/container -m container -s /bin/bash

RUN apt update \
    && apt install software-properties-common apt-transport-https ca-certificates gnupg tar unzip git -y || (echo "Failed to install required packages" && exit 1) \
    && LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php || (echo "Failed to add PHP repository" && exit 1) \
    && apt update 

RUN apt update \
    && apt install php8.3 php8.3-cli php8.3-gd php8.3-mysql php8.3-pdo php8.3-mbstring php8.3-tokenizer php8.3-bcmath php8.3-xml php8.3-fpm php8.3-curl php8.3-zip curl socat wget supervisor -y \
    && phpenmod -v 8.3 mysqli gd pdo_mysql mbstring tokenizer bcmath xml curl zip 


RUN mkdir -p /var/www \
    && cd /var/www/ \
    && wget https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip \
    && unzip phpMyAdmin-5.2.1-all-languages.zip \
    && mv phpMyAdmin-5.2.1-all-languages /var/www/phpmyadmin \
    && cd /var/www/phpmyadmin \
    && chown -R www-data:www-data /var/www \
    && systemctl disable php8.3-fpm
    
RUN curl -L https://github.com/caddyserver/caddy/releases/download/v2.9.1/caddy_2.9.1_linux_amd64.tar.gz -o /tmp/caddy.tar.gz \
    && cd /tmp \
    && tar -xzf caddy.tar.gz caddy --strip-components=0 \
    && mv caddy /usr/bin/caddy \
    && chmod +x /usr/bin/caddy \
    && rm caddy.tar.gz

RUN phpenmod -v 8.3 mysqli pdo_mysql

COPY template/etc/php-fpm.conf /etc/php/8.3/fpm/php-fpm.conf
COPY template/etc/supervisord.conf /supervisord.conf


USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]