FROM debian:latest

RUN apt-get update
RUN apt-get -y install apache2 libapache2-mod-php mariadb-server-10.1 php-mysql php-zip php7.0-xml git
RUN git clone https://github.com/venelink/WARP /var/www/warp
RUN service mysql start && mysql -uroot -e "create database warp_db;create user 'warp_user'@'localhost' identified by 'CHANGE PASSWORD'; grant all on warp_db.* to 'warp_user'@'localhost';"
RUN cd /var/www/warp/db && service mysql start && mysql -u root warp_db < warp.sql
RUN cp /var/www/warp/apache/010-warp.conf /etc/apache2/sites-available/
RUN sed -i 's/ServerName HOST.COM/ServerName localhost/g' /etc/apache2/sites-available/010-warp.conf
RUN sed -i 's/ServerAdmin SOME@EMAL.COM/ServerAdmin webmaster@localhost/g' /etc/apache2/sites-available/010-warp.conf
RUN mkdir -p /var/log/apache2/warp/
RUN touch /var/log/apache2/warp-sql.log
RUN chown www-data:www-data /var/log/apache2/ -R
RUN chmod 755 /var/log/apache2/warp-sql.log
RUN sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php/7.0/apache2/php.ini
RUN a2enmod rewrite
RUN a2ensite 010-warp.conf
RUN a2dissite 000-default.conf
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

WORKDIR /var/www/warp
EXPOSE 80
CMD service mysql restart && apache2ctl -D FOREGROUND
