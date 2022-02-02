#cong-server.pyで利用するipアドレスのセットアップGUI（正直手で打ったほうが早いがipconfigもしてくれるのでyou know 有能）

import os
import PySimpleGUI as sg
import socket
import psutil



def main():   
    #初期化
    ip_list=[]

    #ipv4アドレス取得
    ipv4s = list(get_ip_addresses(socket.AF_INET))
    for ip in ipv4s :
        ip_list.append(ip[1])
    
    #ipv6アドレス取得
    # ipv6s=list(get_ip_addresses(socket.AF_INET6))
    # for ip in ipv6s :
    #     ip_list.append(ip[1])
    

    title='利用IPアドレス - COCOVision'
    layout = [
    [sg.Text(title)],
    [sg.Text('IPアドレス選択', size=(15, 1)),sg.Combo((ip_list),readonly=True,default_value="選択して下さい",size=(20, 1))],
    [sg.Submit(button_text='適用')]
    ]

    win = sg.Window(title, layout)
    while True:
        event, values = win.read()
        if event == '適用':
            ip_addr=values[0]
            if ip_addr == "選択して下さい":
                sg.popup('IPアドレスを選択して下さい')
            else:            
                path = os.path.dirname(__file__)
                with open(path+"/ip_address_config.py", "w",encoding="utf-8") as f:
                    f.write("IP_ADDR='"+ip_addr+"'")
                sg.popup('適用しました')
        else:
            win.close()
            break

def get_ip_addresses( family ):
    for interface, snics in psutil.net_if_addrs().items() :
        for snic in snics :
            if snic.family == family :
                yield( interface, snic.address )
 

if __name__ == '__main__':
    main()