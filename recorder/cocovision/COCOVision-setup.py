import os
import pymysql.cursors
import PySimpleGUI as sg
import ipget


layout1 = [
  [sg.Text('データベースIPアドレス入力画面')],
  [sg.Text('データベースのIPアドレス', size=(22,1)), sg.InputText('172.30.8.14')],
  [sg.Submit(button_text='接続')]]

roomlist=[]
cameralist=["0","1","2"]
layout2 = [
  [sg.Text('COCOVision-Config')],
  [sg.Text('部屋名', size=(15, 1)),sg.Combo((roomlist),readonly=True,default_value="部屋一覧",size=(20, 1))],
  [sg.Text('カメラ接続個数', size=(15, 1)),sg.Combo((cameralist),readonly=True,default_value="0",size=(20, 1))],
  [sg.Submit(button_text='適用')]]


def showWin1():
    win1 = sg.Window('COCOVision-Config', layout1)
    while True:
        event, values = win1.read()
        if event is None: break
        if event == '接続':
            try:
                connection = pymysql.connect(host=values[0],
                                             user='recorder',
                                             password='th1117',
                                             db='cocovision',
                                             charset='utf8')
                sg.popup('接続しました')
                win1.close()
                return connection,values[0]
            except Exception as e:
                sg.popup('接続に失敗しました:'+str(e))

def showWin2(connection,dbip):
    try:#get to room list
        cursor = connection.cursor()
        cursor.execute("select room_name from room_info;")
        result=cursor.fetchall()
        for row in result:
            roomlist.append(str(row[0]))
    except:
        sg.popup('接続できませんでした、もう一度入力してください')
        
    win2 = sg.Window('COCOVision-Config', layout2)
    while True:
        event, values = win2.read()
        if event is None: break
        if event == '適用':
            if values[0]=="部屋一覧":
                sg.popup('部屋を選択してください')
            else:
                setupCnf(values,dbip,cursor)
                setupDb(connection,values)
    win2.close()

def setupCnf(values,dbip,cursor):
    path = os.getcwd()
    room_name=values[0]
    camera=values[1]
    with open(path+"/config/COCOVision.config", "w") as f:
        f.write(room_name+"\n"+str(camera)+"\n"+dbip)


def setupDb(connection,values):
    try: #mysqlデータベースに接続
        cursor=connection.cursor()
        ip = str(ipget.ipget().ipaddr("wlan0")).split('/')
        sql = '''update room_info set device_ip_address=%(ip)s,num_camera=%(camera)s where room_name=%(room)s;'''
        into ={'ip':ip[0],'camera':values[1],'room':values[0]}
        cursor.execute(sql,into)
        connection.commit()
        cursor.close()
        sg.popup('適用しました')
    except: #接続できなかったらエラー文
        print("データベースへの登録に失敗しました")
        sg.popup('データベースへの登録に失敗しました')



#start task
reWin1=showWin1()
showWin2(reWin1[0],reWin1[1])
