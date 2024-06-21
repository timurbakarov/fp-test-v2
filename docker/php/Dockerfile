FROM php:8.3

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

# Create user
RUN groupadd -g ${GID} webadmin; \
    useradd -m -u ${UID} -g ${GID} -s /bin/bash webadmin

RUN apt-get update

RUN apt-get install -y unzip
RUN apt-get install -y wget
RUN apt-get install -y git

RUN docker-php-ext-install mysqli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./php-fpm/php-fpm.conf /etc/php-fpm.conf
COPY ./php-fpm/php.ini /etc/php.ini

WORKDIR /usr/share/nginx/html
