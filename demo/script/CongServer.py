# coding: utf-8
#混雑度-検出人数送信(サーバー側)
#SendCongestion.pyより呼び出し想定

from http import client
import json
import sys
import time
import os
import socket

#ローカル（同階層）
import ip_addr_config
 

def main(device_ip,port,num_camera):
    # このファイルのディレクトリpath
    path=os.path.dirname(__file__)

	# python3.9以前用に相対パスから絶対パスに変換
    path=os.path.abspath(path)

    # カレントディレクトリをこのファイルがあるディレクトリに変更
    os.chdir(path)
    
    #読み込み
    host = ip_addr_config.IP_ADDR
    

    while True:
        try:
            socket1 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            socket1.bind((host, port))
            socket1.listen(1)
            
            print('クライアントとの接続まち状態')

            # コネクションとアドレスを取得
            connection, address = socket1.accept()
            print('----接続したクライアント情報----')
            print(address[0] +':' + str(address[1]))

            sendline = ''
            base_dir='../output_json/'+device_ip+"_"

            while True:
                answer=0
                for num in range(num_camera):
                    dir=base_dir+str(num)+"/"
                    max_json_number=int(sum(os.path.isfile(os.path.join(dir,name)) for name in os.listdir(dir)))-1
                    json_path=dir+'{:0>12}_keypoints.json'
                    try:
                        json_open = open(json_path.format(max_json_number), 'r')
                        json_load = json.load(json_open)
                        peo=len(json_load['people'])
                        answer+=peo
                    except:
                        print("検出失敗 - 対象:",dir)
                        answer=str(-1)
                    
                sendline = str(answer).encode('utf-8')
                print('検出人数：'+str(answer)+'人')
                connection.send(sendline)
                time.sleep(5)
            # クローズ
            connection.close()
            socket1.close()
            print('サーバー側終了です')
        except OSError as e:
            # クローズ
            connection.close()
            socket1.close()
            print('接続が切断されました：'+str(e))


if __name__ == '__main__':
    #コマンドライン引数から読み込み
    print(len(sys.argv))
    for a in sys.argv:
        print(a)
    device_ip=sys.argv[1]
    port = int(sys.argv[2])
    num_camera=int(sys.argv[3])
    main(device_ip,port,num_camera)