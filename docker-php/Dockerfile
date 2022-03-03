#php ใช้ version 7.4 สามารถเปลี่ยนได้ตามที่เราต้องการแต่ต้องเป็นตัว fpm นะครับ เพื่อที่จะให้กับ nginx
FROM php:7.4-fpm-alpine 

WORKDIR /var/www/html

# Setup GD extension
RUN apk add \
    freetype \
    libjpeg-turbo \
    libpng \
    imap-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd \
      --with-freetype=/usr/include/ \
      --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    && apk del \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && rm -rf /tmp/* 

# ตามด้วยคำสั่งสำหรับการลง php extension ครับ
RUN apk add libzip-dev
RUN docker-php-ext-install pdo pdo_mysql zip bcmath imap

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN pear config-set php_ini "$PHP_INI_DIR"

RUN apk add --no-cache pcre-dev ${PHPIZE_DEPS}

RUN pecl install mongodb \
  && docker-php-ext-enable mongodb

RUN apk del pcre-dev ${PHPIZE_DEPS}

# อันนี้ลงเพิ่มเติมคือ composer นั้นเอง เป็นตัว package manager สำหรับการจัดการพวก dependency ของภาษา php ครับ
RUN curl -sS https://getcomposer.org/installer | \
  php -- --install-dir=/usr/local/bin --filename=composer