FROM php:8.3-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    git \
    unzip \
    curl \
    bash

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["tail", "-f", "/dev/null"]