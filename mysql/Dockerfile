#元になるイメージ
FROM mysql:5.7
#ポート番号設定
EXPOSE 3306
#設定ファイル同期
COPY ./setting/my.cnf /etc/mysql/conf.d/my.cnf
#COPY ./setting/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
#初期化SQLファイルをここに入れると勝手に動かしてくれる。天才。（SQLファイルは命名規則がちゃんとあるので追加する場合はググれ）
COPY ./init/ /docker-entrypoint-initdb.d/
ENV MYSQL_ROOT_PASSWORD=th1117

#権限をちゃんとしないとmy.cnfは読み込まれないのでした
RUN chmod 644 /etc/mysql/conf.d/my.cnf
