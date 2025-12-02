FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    supervisor \
    nginx \
    pdftk \
    iputils-ping \
    git \
    curl \
    logrotate \
    wget \
    libx11-xcb1 \
    libxcomposite1 \
    libasound2t64 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgbm1 \
    libgcc1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libxcb1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

ENV TZ=Asia/Jakarta
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions pdo_pgsql exif pcntl bcmath gd excimer zip opcache @composer imagick mongodb redis

# Install Node.js 24
RUN curl -sL https://deb.nodesource.com/setup_24.x | bash - \
    && apt install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install puppeteer
RUN npm install -g puppeteer --unsafe-perm=true --allow-root

# Install chrome-headless-shell-linux64
ARG CHROME_VERSION=142.0.7444.175
RUN wget https://storage.googleapis.com/chrome-for-testing-public/${CHROME_VERSION}/linux64/chrome-headless-shell-linux64.zip \
    && unzip chrome-headless-shell-linux64.zip -d /usr/bin \
    && rm chrome-headless-shell-linux64.zip
