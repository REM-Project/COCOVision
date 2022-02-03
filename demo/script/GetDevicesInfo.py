#ライブラリ
import sys
import pymysql #要インストール
#ローカル
import mysql_config #MySQLのログイン情報・同一ディレクトリに用意


#やってること
#データベースからデバイスのIPアドレス・カメラ数を取得する

#メインストリーム
def main():
    #mysqlconfig.pyからロード
    MYSQL_USER=mysql_config.MYSQL_USER
    MYSQL_PASS=mysql_config.MYSQL_PASS
    MYSQL_HOST=mysql_config.MYSQL_HOST

    #変数初期化
    device_ips = []
    num_cameras = []
    ids = []

    #ユーザ情報確認（デバッグ用）
    print("user:"+MYSQL_USER+",host:"+MYSQL_HOST)

    #データベースに接続
    cCheck,connection=connect_database(MYSQL_USER,MYSQL_PASS,MYSQL_HOST)
    #if:正常に接続した
    if cCheck:
        #クエリ実行（IPアドレス・カメラ数取得）
        rCheck,result=exec_query(connection)
        #if:正常に取得した
        if rCheck:
            #変数に結果を格納
            for record in result:
                device_ips.append(record[0])
                num_cameras.append(record[1])
                ids.append(record[2])
        else:
            #エラーコード代入
            device_ips=result[0]
            num_cameras=[0]
    else:
        #エラーコード代入
        device_ips=connection
        num_cameras=[0]
    #値の返却
    return device_ips,num_cameras,ids




#DB接続（返り値:接続成否 True or False,DBコネクション connection or エラーコード）
def connect_database(MYSQL_USER,MYSQL_PASS,MYSQL_HOST):
    try:
        connection = pymysql.connect(host=MYSQL_HOST,
                                        user=MYSQL_USER,
                                        password=MYSQL_PASS,
                                        db='cocovision',
                                        charset='utf8')
        return True,connection
    except Exception as e:
        eCode="error-01"
        print(eCode)
        print(e)
        return False,[eCode]

#SQL実行（返り値:実行成否 True or False,実行結果 result[レコード数][カラム数] or エラーコード）
def exec_query(connection):
    device_ips=[]
    try:    
        cursor = connection.cursor()
        cursor.execute("select device_ip_address,num_camera,id from room_info where num_camera != 0;")
        result=cursor.fetchall()
        return True,result
    except Exception as e:
        eCode="error-02"
        print(eCode)
        print(e)
        return False,[eCode]




#外部pythonからの呼び出し応答
def get_all():
    device_ips,num_cameras,ids=main()
    return device_ips,num_cameras,ids

def get():
    device_ips,num_cameras,ids=main()
    return device_ips,num_cameras,ids


#shellからの呼び出し応答（返答形式 xxx.yyy.zzz.aaa,カメラ数 改行(\n)）
if __name__ == '__main__':
    device_ips,num_cameras=main()

    for i in range(len(device_ips)):
        sys.stdout.write(str(device_ips[i])+","+str(num_cameras[i])+"\n")




    