#データベース（MYSQL）に接続する際のユーザー情報
# ユーザー名
MYSQL_USER="recorder"

# パスワード
MYSQL_PASS="th1117"

# データベースのアドレス
#  同一Docker-composeのデータベースにアクセスする際は宣言したサービス名を入力
#MYSQL_HOST="mysql"

#  自機稼働のdockerコンテナにアクセスする場合はlocalhostではなく127.0.0.1
MYSQL_HOST="127.0.0.1"
