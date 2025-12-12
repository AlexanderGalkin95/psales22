FROM node:16

ENV PUPPETEER_CACHE_DIR=/root/.cache/puppeteer

RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys E5267A6C \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C300EE8C

RUN npm i -g laravel-echo-server
RUN npm i -g --unsafe-perm puppeteer
RUN ln -s /usr/local/bin/laravel-echo-server /usr/bin/laravel-echo-server
RUN chmod +x /usr/bin/laravel-echo-server

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chmod +x /usr/local/bin/start-container

WORKDIR /var/www

EXPOSE 6001

ENTRYPOINT ["start-container"]
