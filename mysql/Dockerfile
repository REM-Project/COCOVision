FROM mysql:5.7
EXPOSE 3306
COPY ./setting/my.cnf /etc/mysql/conf.d/my.cnf
#COPY ./setting/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
COPY ./init/ /docker-entrypoint-initdb.d/
ENV MYSQL_ROOT_PASSWORD=th1117

RUN chmod 644 /etc/mysql/conf.d/my.cnf
