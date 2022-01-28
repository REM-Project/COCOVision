# coding: utf-8

# ソケット通信(クライアント側)
import socket

ip1 = '172.30.8.14'
port1 = 8765
server1 = (ip1, port1)

socket1 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
socket1.connect(server1)

line = ''
while line != 'bye':
    
    # サーバから受信
    data1 = socket1.recv(4096).decode()
    
    # サーバから受信したデータを出力
    print('現在人数: ' + str(data1))

socket1.close()
print('クライアント側終了です')