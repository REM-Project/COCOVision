#https://zenn.dev/wok/scraps/a2b5839326c7e7


import sys
import time
from tkinter import Tk, messagebox
#import board
#import adafruit_scd4x
import pymysql.cursors
from tkinter import ttk
import tkinter
import tkinter.font as font
import datetime
import threading
import os
import socket
#import pygame
#from mutagen.mp3 import MP3 as mp3
from datetime import timedelta
import subprocess
import StreamCamera
from multiprocessing import Process
#同じ値なのに変更処理が入らないように定義
oldco2 = 0
oldtemp = 0
oldhum = 0
oldcong = 0
olddate = 0
#同じ色なのに変更処理が入らないように定義
green = 0
orange = 1
red = 2
purple = 3
rectanglecolor = -1 #1=緑 2=橙 3=赤 4=紫 初期値を0にしているのは初期設定中のメッセージを変更するため
#同じメッセージなのに処理変更が入らないように定義
tempsituation = 0
humsituation = 0
congsituation = 0
camerasituation = 0

normal="" #正常値の場合のメッセージ

root = Tk()
root.title("室内環境")
root.attributes('-fullscreen', True)
root.geometry("1920x1080")

canvas = tkinter.Canvas(root, width = 1920, height = 1080,background="PaleGoldenrod") #canvasの設定,背景色変更
canvas.place(x=0, y=0) #canvas設置

#左側の四角描画
canvas.create_rectangle(40, 210, 950, 850, fill = 'white', outline="#009D5B", width = 30, tag="rect")
#右側の四角描画
mesrect = canvas.create_rectangle(1000, 310, 1850, 750, fill = 'white', outline="#000000", width = 10 )
#右下の四角描画
canvas.create_rectangle(1300, 870, 1850, 1020, fill = 'white', outline="#000000", width = 10)

mesrectpos = canvas.coords(mesrect) #右側の四角の座標を取得
mesrect_x = mesrectpos[0] + (mesrectpos[2]-mesrectpos[0])/ 2 #x軸の真ん中の座標を取得
mesrect_y = mesrectpos[1] + (mesrectpos[3]-mesrectpos[1])/2 #y軸の真ん中の座標を取得

#利用するフォントスタイルを定義
fontStyle = font.Font(family="MSゴシック",size=80)
messagefont = font.Font(family="MSゴシック", size=50)
datefont = font.Font(family="MSゴシック",size=30)

#ラベル定義と設置(これがないとwhile文のplace_forget()でエラーがでる)
messagetext = tkinter.StringVar(value = "初期設定中")
co2label = ttk.Label(root,text = 0, font=fontStyle, background='white', anchor="w")
templabel = ttk.Label(root,text = 0, font=fontStyle, background='white', anchor="w")
humlabel = ttk.Label(root,text = 0, font=fontStyle, background='white', anchor="w")
conglabel = ttk.Label(root,text = 0, font=fontStyle, background='white', anchor="w")
todaylabel = ttk.Label(root,text="", font=datefont, background='white', anchor="w")
nowlabel = ttk.Label(root,text="", font=datefont, background='white', anchor="w")
todaydate = "日時：" + str(0) + "年" + str(0) + "月" + str(0) + "日"
nowdate = "時刻：" + str(0) + "時" + str(0) + "分" + str(0) + "秒"
co2label.place(x=400,y=250)
templabel.place(x=400,y=380)
humlabel.place(x=400,y=510)
conglabel.place(x=450,y=640)
todaylabel.place(x=1320, y=890)
nowlabel.place(x=1320, y=950)

#CO2, 温度, 湿度, メッセージ表示
label_1 = ttk.Label(root,text='CO2', font=fontStyle, background='white', anchor="w" )
label_2 = ttk.Label(root,text='温度', font=fontStyle, background='white', anchor="w" )
label_3 = ttk.Label(root,text='湿度', font=fontStyle, background='white', anchor="w" )
label_4 = ttk.Label(root,text='混雑度', font=fontStyle, background='white', anchor="w" )
messagelabel = ttk.Label(root, text='メッセージ', font=messagefont, background='PaleGoldenrod', anchor="w")
label_1.place(x=70, y=250)
label_2.place(x=70, y=380)
label_3.place(x=70, y=510)
label_4.place(x=70, y=640)
messagelabel.place(x=1010, y=200)

class mes: #メソッド内で使う変数を定義
    ##savedatabaseで使用
    nowdate = 0
    savetime = datetime.datetime.now() #後にデータベースに記録する時間を入れている
    databasesitu = 0
    #sensormeasurementで使用(savedatabase)
    i = 30 #はじめの準備時間を定義
    count = 0 #センサー値を合計した回数を記録し、平均を求めるときに利用
    co2 = 0
    temp = 0
    hum = 0
    co2sum = 0 
    tempsum = 0
    humsum = 0
    #congrecieveで使用(savedatabase)
    congdata = 0
    cameracount = 0
    congsum = 0
    #textlocationで使用
    co2save = "" #値に応じてメッセージを入れる 
    tempsave = ""
    humsave = ""
    congsave = ""
    text_id = canvas.create_text(0,0,font=("MSゴシック",48) ,text=messagetext.get(), tag="mestext") #表示するメッセージ
    #soundmethodに行く判定で使用
    sound_count = 0
    #DBconnectに行く判定で使用
    DB_count = 0    
canvas.move(mes.text_id, mesrect_x, mesrect_y)


def congrecieve():#混雑度を計測するメソッド
    print('call congrecieve')
    try:
        if socket1.recv(4096).decode()!=-1: #人数が測定不能(-1)でないとき
            mes.congdata = (float(socket1.recv(4096).decode()) / float(room_capacity))*100 #混雑度(%)を代入
            mes.congsum = mes.congsum + mes.congdata #平均値を求めるために代入
            mes.cameracount = mes.cameracount + 1 #データを計測した回数を測定
    except:
        print('cong error')

def savedatabase(): #データベースに環境値を送信するメソッド
    #各データの平均値を計算
    mes.co2sum = round(mes.co2sum / mes.count ,3)
    mes.tempsum = round(mes.tempsum / mes.count ,3)
    mes.humsum = round(mes.humsum / mes.count , 3)
    if(mes.cameracount==0): #カメラを使用しない場合
        mes.congsum=-1
    else: #カメラを使用する場合
        mes.congsum = round(mes.congsum / mes.cameracount , 3)
    try: #データベースに値を送信
        with connection.cursor() as cursor:
            sql = "INSERT INTO "+table_name+"(rec_time,co2,temp,humi,cong) VALUES(%(time)s,%(co2)s,%(temp)s,%(humi)s,%(cong)s);"
            into = {'time':str(mes.nowdate.strftime("%Y-%m-%d %H:%M:00")),'co2':mes.co2sum,'temp':mes.tempsum,'humi':mes.humsum,'cong':mes.congsum}
            cursor.execute(sql,into)
            connection.commit()
            cursor.close()
            print("Data commited.")
    except: #送信に失敗したらエラーメッセージ
        mes.databasesitu = 0
    #各データを初期化し、次にデータを送信する時間を指定
    mes.count = 0
    mes.cameracount = 0
    mes.co2sum = 0
    mes.tempsum = 0
    mes.humsum = 0
    mes.congsum = 0
    mes.savetime = mes.nowdate + timedelta(minutes=2)
    os.system('echo 1 | sudo tee /proc/sys/vm/drop_caches>/dev/null')
    
def sensormeasurement(): #センサーの値を変数に代入するメソッド
    if scd4x.data_ready:
        mes.co2 = scd4x.CO2
        mes.temp = scd4x.temperature
        mes.hum = scd4x.relative_humidity
        mes.count = mes.count + 1
        mes.co2sum = mes.co2sum + mes.co2
        mes.tempsum = mes.tempsum + mes.temp
        mes.humsum = mes.humsum + mes.hum
    if (mes.i > 0 ): #はじめの30秒ほどはデータベースに送信する値を取らない
        mes.i = mes.i - 1
        print("準備中.." , mes.i)
        mes.co2sum = 0
        mes.tempsum = 0
        mes.humsum = 0
        mes.count = 0
    elif (mes.i == 0): #30秒経過後
        mes.i = -1
        mes.savetime = mes.nowdate + timedelta(minutes=2)

def textlocation():
    if(normal != ""):
        messagetext.set(normal)
    else:
        messagetext.set(mes.co2save + mes.tempsave + mes.humsave + mes.congsave)
    canvas.itemconfigure("mestext", text=messagetext.get())
    text_size = canvas.bbox(mes.text_id)
    mestext_x = text_size[0] + (text_size[2] - text_size[0]) / 2
    mestext_y = text_size[1] + (text_size[3] - text_size[1]) / 2 + 40
    canvas.move(mes.text_id, mesrect_x - mestext_x , mesrect_y - mestext_y )
    
def soundmethod(color,temp,hum,cong): #警告ボイスを出すためのメソッド
    sound_number = [0]#ボイスを順番に流すための空の配列
    #それぞれがどの値を示しているかを区別するために値を足す(以下の○には1の位が当てはまる)
    color += 10 #co2に関する値:1○
    temp += 20 #気温に関する値:2○
    hum += 30 #湿度に関する値:3○
    cong += 40 #人数に関する値:4○
    situation = [color,temp,hum,cong]#ループ式で異常な値であるか確認するために配列に格納する
    for a in situation:
        if(a%10 != 0):#aが異常な値を示している(0＋10の倍数ではない)時にsound_numberという配列に格納する
            if(a == 11 or a == 12):#co2が1000以上2000以下のときは同じボイスを流す
                sound_number.append(11)
            else:#それ以外はそのままsound_numberに格納する
                sound_number.append(a)
    for a in sound_number:#ボイスを順番に流す
        filename = 'sound/sound'+str(a)+'.mp3' #再生したいmp3ファイル(ボイスの詳細はsoundファイル内のconfigにある)
        print(str(filename))
        pygame.mixer.init()
        pygame.mixer.music.load(filename) #音源を読み込み
        mp3_length = mp3(filename).info.length #音源の長さ取得
        pygame.mixer.music.play(1) #再生開始。1の部分を変えるとn回再生(その場合は次の行の秒数も×nすること)
        time.sleep(mp3_length + 1.0) #再生開始後、音源の長さだけ待つ(0.25待つのは誤差解消)
        pygame.mixer.music.stop() #音源の長さ待ったら再生停止
    sound_number.clear()

def databaseconnect():
    try: #mysqlデータベースに接続
        print("DB access....")
        connection = pymysql.connect(host=config[2],
                                     user='recorder',
                                     password='th1117',
                                     db='cocovision',
                                     charset='utf8')
        cursor = connection.cursor()
    except: #接続できなかったらエラー文
        print("DB access error")
        return False
    try:
        with connection.cursor() as cursor: 
            sql = "SELECT room_capacity,table_name,id FROM room_info WHERE room_name = %s" 
            cursor.execute(sql, config[0])
            room_name_list = cursor.fetchone()
            room_capacity = room_name_list[0]
            table_name = room_name_list[1]
            room_id = room_name_list[2]
            mes.databasesitu = 1
            connection.commit()
            cursor.close()
            print("DB access commit")
            return True,connection,cursor,room_capacity,table_name,room_id
    except:
        return False
    
def socketconnect(room_id):
    if int(config[1]) >= 1:
        ipsoc = '172.30.8.14'
        try:
            portsoc = 9000 + int(room_id)
            serversoc = (ipsoc, portsoc)
            socket1 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            socket1.connect(serversoc)
            return 0
        except:
            print("timed out")
            return 1
    else:
        return 1    
    
config=[]
try:
    with open("COCOVision.config", "r",encoding="utf-8") as f:
        config=f.read().splitlines()
except IOError as e:
    messagebox.showerror('IOError', 'COCOVision.configが見つかりません。COCOVision-setup.pyを実行してください。')
    sys.exit(str(e))
    #subprocess.Popen(['python3',os.getcwd()+"/COCOVision-setup.py"])



#センサーから値を出力するための記述
i2c = board.I2C()
scd4x = adafruit_scd4x.SCD4X(i2c)
print("Serial number:", [hex(i) for i in scd4x.serial_number])
scd4x.start_periodic_measurement()
print("Waiting for first measurement....")

#socketconnect
socket_decision = True
p = Process(target=StreamCamera.main)
p.start()
while True:
    if(mes.databasesitu == 0 and mes.DB_count!=1):
        mes.DB_count += 1
        redatabase = databaseconnect()
        if(redatabase[0]):
            connection = redatabase[1]
            cursor = redatabase[2]
            room_capacity = redatabase[3]
            table_name = redatabase[4]
            room_id = redatabase[5]
            if socket_decision:
                resocket = socketconnect(room_id)
                camerasituation = resocket
                socket_decision = False
    else:
        camerasituation = 1
    #スレッド1、2スタート
    mes.nowdate = datetime.datetime.now()
    if(mes.nowdate >= mes.savetime) and (mes.i == -1) and (mes.co2sum > 0):
        th1 = threading.Thread(target=savedatabase)
        th1.start()
        mes.sound_count += 1
    th2 = threading.Thread(target=sensormeasurement)
    th2.start()
    if (camerasituation == 0):
        th3 = threading.Thread(target=congrecieve)
        th3.start()
    if(oldco2 == mes.co2) and (oldtemp == mes.temp) and (oldhum == mes.hum): #センサーの値が更新されていないときに、ラベルが同じ値のまま更新されるのを防ぐために配置
        pass
    else: #センサーの値が更新されたときに以下を実行しラベルの再設置
        co2label["text"] = str(mes.co2) + "ppm" 
        templabel["text"] = str(round(mes.temp, 1)) + "℃"
        humlabel["text"] = str(round(mes.hum , 1)) + "%"
        if(camerasituation == 1):
            conglabel["text"] = "計測不可"
        else:
            conglabel["text"] = str(mes.congdata) + "%"
        oldco2 = mes.co2 #oldco2に現在のCO2濃度を格納
        oldtemp = mes.temp
        oldhum = mes.hum
        oldcong = mes.congdata
    #設置されてある日時ラベル削除,設定,設置
    if(olddate == mes.nowdate.today):#日付は更新されたタイミングで表示
        pass
    else:
        todaydate = "日時：" + str(mes.nowdate.year) + "年" + str(mes.nowdate.month) + "月" + str(mes.nowdate.day) + "日"
        todaylabel["text"] = todaydate 
        olddate = mes.nowdate.today
    nowtime = "時刻：" + str(mes.nowdate.hour) + "時" + str(mes.nowdate.minute) + "分" + str(mes.nowdate.second) + "秒" #時刻は毎秒更新
    nowlabel["text"] = nowtime 
    #CO2濃度によって枠線の色を変更
    if (mes.co2 >= 1000) and (mes.co2 < 1500) and (rectanglecolor != orange):
        canvas.itemconfigure("rect" ,outline="Orange")
        mes.co2save = "\n換気を行ってください"
        textlocation()
        rectanglecolor = orange
        co2label["foreground"] = '#ff0033'
    elif(mes.co2 >= 1500) and (mes.co2 < 2000) and (rectanglecolor != red):
        canvas.itemconfigure("rect" ,outline="Red")
        mes.co2save = "\n換気を行ってください"
        textlocation()
        rectanglecolor = red
        co2label["foreground"] = '#ff0033'
    elif(mes.co2 >= 2000) and (rectanglecolor != purple):
        canvas.itemconfigure("rect" ,outline="Purple")
        mes.co2save = "\n今すぐ換気してください"
        textlocation()
        rectanglecolor = purple
        co2label["foreground"] = '#ff0033'
    elif(mes.co2 <= 999) and (rectanglecolor != green):
        canvas.itemconfigure("rect" ,outline="#009D5B")
        mes.co2save = ""
        textlocation()
        rectanglecolor = green
        co2label["foreground"] = '#000000'

    if(mes.temp < 18) and (tempsituation != 1 ):
        mes.tempsave = "\n暖房してください"
        textlocation()
        templabel["foreground"] = '#0066cc'
        tempsituation = 1
    elif(mes.temp > 28) and (tempsituation != 2 ):
        mes.tempsave = "\n冷房してください"
        textlocation()
        templabel["foreground"] = '#ff0033'
        tempsituation = 2
    elif(mes.temp >= 18) and (mes.temp <= 28) and (tempsituation != 0):
        mes.tempsave = ""
        textlocation()
        tempsituation = 0
        templabel["foreground"] = '#000000'
        
    if(mes.hum < 40) and (humsituation != 1 ):
        mes.humsave = "\n加湿してください"
        textlocation()
        humlabel["foreground"] = '#0066cc'
        humsituation = 1
    elif(mes.hum > 70) and (humsituation != 2):
        mes.humsave = "\n除湿してください"
        textlocation()
        humlabel["foreground"] = '#ff0033'
        humsituation = 2
    elif(mes.hum >= 40) and (mes.hum <= 70) and (humsituation != 0):
        mes.humsave = ""
        textlocation()
        humsituation = 0
        humlabel["foreground"] = '#000000'
        
    if(mes.congdata > 100) and (congsituation == 0):
        savesituation()
        mes.congsave = "\n収容人数を超過しています"
        textlocation()
        conglabel["foreground"] = '#ff0033'
        congsituation = 1
    elif(mes.congdata <= 100) and (congsituation == 1):
        mes.congsave = ""
        textlocation()
        congsituation = 0
        conglabel["foreground"] = '#000000'
    
    if(mes.co2 <= 999) and (mes.temp >= 18) and (mes.temp <= 28) and (mes.hum >= 40) and (mes.hum <=70):
        normal = "\n正常値です"
        textlocation()
    elif(normal == "\n正常値です"):
        normal = ""
        textlocation()
        
    if(mes.sound_count == 5): #約10分経過後
        if(int(rectanglecolor)+int(tempsituation)+int(humsituation)+int(congsituation)!=0): #いずれかに警告が必要な場合
            #左から(co2,温度,湿度,人数)の「状態を表す数値」を示している
            soundmethod(rectanglecolor,tempsituation,humsituation,congsituation)
        #初期化
        mes.sound_count = 0
        mes.DB_count = 0
        
    root.update_idletasks()
    root.update()
    time.sleep(1)

connection.close()