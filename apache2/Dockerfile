FROM ubuntu

MAINTAINER hoge

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2

RUN apt-get update && \
    apt-get install -y tzdata && \
    apt-get install -y apache2 && \
    apt-get install -y php libapache2-mod-php

EXPOSE 80

#COPY ./html/ /var/www/html/

CMD ["apachectl", "-D", "FOREGROUND"]

FROM php:7.4-apache

WORKDIR /var/www/html

RUN apt-get update -y && \
    apt-get install -y zip unzip vim libpq-dev && \
    docker-php-ext-install pdo_mysql pdo_pgsql pcntl
