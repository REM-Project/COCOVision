# coding: utf-8
#混雑度-検出人数送信(サーバー側)
#SendCongestion.pyより呼び出し想定

import json
import time
import os
import socket

#ローカル（同階層）
import ip_addr_config
 

def main():
	host = ip_addr_config.IP_ADDR
	port = 8765
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
			dir='openpose/data/'#

			while True:
				Path_number=int(sum(os.path.isfile(os.path.join(dir,name)) for name in os.listdir(dir)))-1
				Path=dir+'{:0>12}_keypoints.json'
				try:
					json_open = open(Path.format(Path_number), 'r')
					json_load = json.load(json_open)
					peo=len(json_load['people'])
					answer=str(peo)
				except:
					answer=str(-1)
				sendline = answer.encode('utf-8')
				print('検出人数：'+str(answer)+'人')
				#print(Path.format(Path_number))
				connection.send(sendline)
				time.sleep(5)
			# クローズ
			connection.close()
			socket1.close()
			print('サーバー側終了です')
		except OSError as e:
			print('接続が切断されました：'+str(e))

if __name__ == '__main__':
	main()