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
    devicesIp = []
    numCameras = []

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
                devicesIp.append(record[0])
                numCameras.append(record[1])
        else:
            #エラーコード代入
            devicesIp=result[0]
            numCameras=[0]
    else:
        #エラーコード代入
        devicesIp=connection
        numCameras=[0]
    #値の返却
    return devicesIp,numCameras




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
    devicesIp=[]
    try:    
        cursor = connection.cursor()
        cursor.execute("select device_ip_address,num_camera from room_info where num_camera != 0;")
        devicesIp=cursor.fetchall()
        return True,devicesIp
    except Exception as e:
        eCode="error-02"
        print(eCode)
        print(e)
        return False,[eCode]




#外部pythonからの呼び出し応答（mainと同一返答）
def get():
    devicesIp,numCameras=main()
    return devicesIp,numCameras

#shellからの呼び出し応答（返答形式 xxx.yyy.zzz.aaa,カメラ数 改行(\n)）
if __name__ == '__main__':
    devicesIp,numCameras=main()

    for i in range(len(devicesIp)):
        sys.stdout.write(str(devicesIp[i])+","+str(numCameras[i])+"\n")




    