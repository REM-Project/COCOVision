#https://zenn.dev/wok/scraps/a2b5839326c7e7



from itertools import count
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
import pygame
from mutagen.mp3 import MP3 as mp3
from datetime import timedelta
import subprocess
import StreamCamera
from multiprocessing import Process


def main():
    #グローバル定義
    global CONFIG,ROOM_NAME,NUM_CAMERA,HOST,BASE_PORT,START_INTERVAL_TIME,SEND_INTERVAL,SOUND_INTERVAL,CO2_LEVEL,TEMP_LEVEL,HUMI_LEVEL,CONG_LEVEL,ROOT
    # 設定情報
    CONFIG=[]
    ROOM_NAME,NUM_CAMERA,HOST=None





    #調整項目
    # 初期待機時間（起動した時間+30秒）
    START_INTERVAL_TIME = datetime.datetime.now()+timedelta(seconds=30)
    # データベースに送信する間隔（分単位）
    SEND_INTERVAL=2
    # 警告を再生する間隔（分単位）
    SOUND_INTERVAL=10
    # 混雑度（人数）受信時に利用するポート番号のテンプレート
    BASE_PORT=9000
    # 各値の閾値（min~maxの順に配置、要素数を変える場合は適用されるif文も変更すること）
    CO2_LEVEL=[1000,1500,2000]#それぞれの値以上で段階的に警告
    TEMP_LEVEL=[18,28]#[0]未満、[1]超過で警告
    HUMI_LEVEL=[40,70]#[0]未満、[1]超過で警告
    CONG_LEVEL=[100]#[0]超過で警告
    



    #画面定義
    ROOT = Tk()
    ROOT.title("室内環境")
    ROOT.attributes('-fullscreen', True)
    ROOT.geometry("1920x1080")


    #変数定義
    # データベース接続状態
    is_connect_db=False
    # カメラ利用状態
    is_stream_cam=False
    # データベースに送信する合計値（送信時に平均化）(データベースに送信する毎にリセット)
    sum_co2,sum_temp,sum_humi=0
    sum_cong=0.0
    # センサー値を取得した回数（データベースに送信する毎にリセット）
    count_get_values=0
    count_get_cong=0

    # 最後にデータベースに送信した時間（or起動した時間）
    send_time = datetime.datetime.now()
    # 最後に音警告をした時間（or起動した時間）
    sound_time=datetime.datetime.now()




    #COCOVision.configをロードしてCONFIGに格納（失敗時終了）
    try:
        with open("COCOVision.config", "r",encoding="utf-8") as f:
            CONFIG=f.read().splitlines()
        ROOM_NAME=CONFIG[0]
        NUM_CAMERA=CONFIG[1]
        HOST=CONFIG[2]
    except IOError as e:
        messagebox.showerror('IOError', 'COCOVision.configが見つかりません。COCOVision-setup.pyを実行してください。')
        sys.exit(str(e))

    #カメラ台数が0台ならOFF
    if(NUM_CAMERA>0):
        is_stream_cam=True
    else:
        is_stream_cam=False

    #カメラ起動
    if(is_stream_cam):
        p = Process(target=StreamCamera.main)
        p.start()

    #データベース接続・部屋情報取得
    is_connect_db,room_capacity,table_name,room_id=get_room_info(ROOM_NAME,HOST)

    #ディスプレイ初期化
    display(datetime.datetime.now(),0,0,0,0)
    #時間更新
    th_d_date = threading.Thread(target=display_datetime)
    th_d_date.start()



    #メインストリーム
    while True:

        now_datetime=datetime.datetime.now()
        #センサー値取得
        co2,temp,humi=0
        #データベース送信用に格納(最初の30秒は格納しない)
        if(now_datetime>=START_INTERVAL_TIME):
            co2,temp,humi=get_value()
            sum_co2+=co2
            sum_temp+=temp
            sum_humi+=humi
            count_get_values+=1

        #混雑度取得
        cong=-2
        if(is_stream_cam and is_connect_db):
            cong=get_cong(room_id,room_capacity)
            if(cong!=-1):
                sum_cong+=cong
                count_get_cong+=1

        #画面更新
        display(now_datetime,co2,temp,humi,cong)

        #音出力
        if(now_datetime>=sound_time+timedelta(minutes=SOUND_INTERVAL)):
            soundmethod(co2,temp,humi,cong)
            sound_time=now_datetime
        

        #データベースに送信
        if(now_datetime>=(send_time+timedelta(minutes=SEND_INTERVAL))):
            #記録時間
            rec_time=now_datetime.strftime("%Y-%m-%d %H:%M:00")
            #平均値算出
            avg_co2=round(sum_co2/count_get_values,3)
            avg_temp=round(sum_temp/count_get_values,3)
            avg_humi=round(sum_humi/count_get_values,3)
            avg_cong=round(sum_cong/count_get_cong,3)

            #データベースの接続に失敗していた場合
            if(not is_connect_db):
                is_connect_db,room_capacity,table_name,room_id=get_room_info()
            if(is_connect_db):
                #データベースに送信
                is_send=send_db(HOST,table_name,rec_time,avg_co2,avg_temp,avg_humi,avg_cong)
                if(is_send):
                    sum_co2,sum_temp,sum_humi,sum_cong,count_get_values,count_get_cong=0



def judge_level(co2,temp,humi,cong):
    j_co2,j_temp,j_humi,j_cong=0

    for i in range(len(CO2_LEVEL)):
        if(CO2_LEVEL[i] <= co2):
            j_co2=i+1
    
    if(temp<TEMP_LEVEL[0]):
        j_temp=-1
    elif(TEMP_LEVEL[1]<temp):
        j_temp=1

    if(humi<HUMI_LEVEL[0]):
        j_humi=-1
    elif(HUMI_LEVEL[1]<humi):
        j_humi=1

    if(cong<0):
        j_cong=-1
    elif(CONG_LEVEL[0]<cong):
        j_cong=1
    
    return j_co2,j_temp,j_humi,j_cong

    
def display_datetime():
    now_datetime=datetime.datetime.now()
    datefont = font.Font(family="MSゴシック",size=30)
    todaylabel = ttk.Label(ROOT,text="", font=datefont, background='white', anchor="w")
    nowlabel = ttk.Label(ROOT,text="", font=datefont, background='white', anchor="w")
    todaydate = "日時：" + str(0) + "年" + str(0) + "月" + str(0) + "日"
    nowdate = "時刻：" + str(0) + "時" + str(0) + "分" + str(0) + "秒"
    todaylabel.place(x=1320, y=890)
    nowlabel.place(x=1320, y=950)
    

    todaydate = "日時：" + str(now_datetime.year) + "年" + str(now_datetime.month) + "月" + str(now_datetime.day) + "日"
    todaylabel["text"] = todaydate 

    nowtime = "時刻：" + str(now_datetime.hour) + "時" + str(now_datetime.minute) + "分" + str(now_datetime.second) + "秒" #時刻は毎秒更新
    nowlabel["text"] = nowtime 

    ROOT.update_idletasks()
    ROOT.update()



def display(now_datetime,co2,temp,humi,cong):
    #画面定義

    canvas = tkinter.Canvas(ROOT, width = 1920, height = 1080,background="PaleGoldenrod") #canvasの設定,背景色変更
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
    co2label = ttk.Label(ROOT,text = 0, font=fontStyle, background='white', anchor="w")
    templabel = ttk.Label(ROOT,text = 0, font=fontStyle, background='white', anchor="w")
    humlabel = ttk.Label(ROOT,text = 0, font=fontStyle, background='white', anchor="w")
    conglabel = ttk.Label(ROOT,text = 0, font=fontStyle, background='white', anchor="w")
    todaylabel = ttk.Label(ROOT,text="", font=datefont, background='white', anchor="w")
    nowlabel = ttk.Label(ROOT,text="", font=datefont, background='white', anchor="w")
    todaydate = "日時：" + str(0) + "年" + str(0) + "月" + str(0) + "日"
    nowdate = "時刻：" + str(0) + "時" + str(0) + "分" + str(0) + "秒"
    co2label.place(x=400,y=250)
    templabel.place(x=400,y=380)
    humlabel.place(x=400,y=510)
    conglabel.place(x=450,y=640)
    todaylabel.place(x=1320, y=890)
    nowlabel.place(x=1320, y=950)

    #CO2, 温度, 湿度, メッセージ表示
    label_1 = ttk.Label(ROOT,text='CO2', font=fontStyle, background='white', anchor="w" )
    label_2 = ttk.Label(ROOT,text='温度', font=fontStyle, background='white', anchor="w" )
    label_3 = ttk.Label(ROOT,text='湿度', font=fontStyle, background='white', anchor="w" )
    label_4 = ttk.Label(ROOT,text='混雑度', font=fontStyle, background='white', anchor="w" )
    messagelabel = ttk.Label(ROOT, text='メッセージ', font=messagefont, background='PaleGoldenrod', anchor="w")
    label_1.place(x=70, y=250)
    label_2.place(x=70, y=380)
    label_3.place(x=70, y=510)
    label_4.place(x=70, y=640)
    messagelabel.place(x=1010, y=200)

    text_id = canvas.create_text(0,0,font=("MSゴシック",48) ,text=messagetext.get(), tag="mestext")

    canvas.move(text_id, mesrect_x, mesrect_y)


    #画面定義終わり

    msg_co2,msg_temp,msg_humi,msg_cong=""



    co2label["text"] = str(co2) + "ppm" 
    templabel["text"] = str(round(temp, 1)) + "℃"
    humlabel["text"] = str(round(humi , 1)) + "%"
    if(cong==-2):
        conglabel["text"] = "計測不可"
    elif(cong==-1):
        conglabel["text"] = "計測失敗"
    else:
        conglabel["text"] = str(round(cong,1)) + "%"


    todaydate = "日時：" + str(now_datetime.year) + "年" + str(now_datetime.month) + "月" + str(now_datetime.day) + "日"
    todaylabel["text"] = todaydate 

    nowtime = "時刻：" + str(now_datetime.hour) + "時" + str(now_datetime.minute) + "分" + str(now_datetime.second) + "秒" #時刻は毎秒更新
    nowlabel["text"] = nowtime 

    j_co2,j_temp,j_humi,j_cong=judge_level(co2,temp,humi,cong)
    #CO2濃度によって枠線の色を変更
    if (j_co2==1) :
        canvas.itemconfigure("rect" ,outline="Orange")
        msg_co2 = "\n換気を行ってください"
        co2label["foreground"] = '#ff0033'
    elif(j_co2==2):
        canvas.itemconfigure("rect" ,outline="Red")
        msg_co2 = "\n換気を行ってください"
        co2label["foreground"] = '#ff0033'
    elif(j_co2==3):
        canvas.itemconfigure("rect" ,outline="Purple")
        msg_co2 = "\n今すぐ換気してください"
        co2label["foreground"] = '#ff0033'
    else:
        canvas.itemconfigure("rect" ,outline="#009D5B")
        msg_co2 = ""
        co2label["foreground"] = '#000000'


    if(j_temp==-1):
        msg_temp = "\n暖房してください"
        templabel["foreground"] = '#0066cc'
    elif(j_temp==1):
        msg_temp = "\n冷房してください"
        templabel["foreground"] = '#ff0033'
    else:
        msg_temp = ""
        templabel["foreground"] = '#000000'
        

    if(j_humi==-1):
        msg_humi = "\n加湿してください"
        humlabel["foreground"] = '#0066cc'
    elif(j_humi==1):
        msg_humi = "\n除湿してください"
        humlabel["foreground"] = '#ff0033'
    else:
        msg_humi = ""
        humlabel["foreground"] = '#000000'
        

    if(j_cong==1):
        msg_cong = "\n収容人数を超過しています"
        conglabel["foreground"] = '#ff0033'
    else:
        msg_cong = ""
        conglabel["foreground"] = '#000000'
    
    if(j_co2==j_temp==j_humi==0 and j_cong<=0):
        normal = "\n正常値です"
    elif(now_datetime<START_INTERVAL_TIME):
        normal = ""
        

    if(now_datetime<=START_INTERVAL_TIME):
        tkinter.StringVar(value = "初期設定中")
    elif(normal != ""):
        messagetext.set(normal)
    else:
        messagetext.set(msg_co2 + msg_temp + msg_humi + msg_cong)

    canvas.itemconfigure("mestext", text=messagetext.get())
    text_size = canvas.bbox(text_id)
    mestext_x = text_size[0] + (text_size[2] - text_size[0]) / 2
    mestext_y = text_size[1] + (text_size[3] - text_size[1]) / 2 + 40
    canvas.move(text_id, mesrect_x - mestext_x , mesrect_y - mestext_y )

    ROOT.update_idletasks()
    ROOT.update()


#データベース接続（直接は呼び出さない）
def connect_db():
    try: #mysqlデータベースに接続
        print("DB access....")
        connection = pymysql.connect(host=HOST,
                                    user='recorder',
                                    password='th1117',
                                    db='cocovision',
                                    charset='utf8')
        return True,connection
    except: #接続できなかったらエラー文
        print("DB access error")
        return False,None

def get_room_info():
    is_connected,connection=connect_db()
    if(is_connected):
        cursor = connection.cursor()
        try:
            with connection.cursor() as cursor: 
                sql = "SELECT room_capacity,table_name,id FROM room_info WHERE room_name = %s" 
                cursor.execute(sql, ROOM_NAME)
                room_info = cursor.fetchone()
                room_capacity = room_info[0]
                table_name = room_info[1]
                room_id = room_info[2]
                connection.commit()
                cursor.close()
                print("DB access commit")
                return True,room_capacity,table_name,room_id
        except:
            print("not get room_info")
            return False,None,None,None
    else:
        return False,None,None,None



def get_value():
    i2c = board.I2C()
    scd4x = adafruit_scd4x.SCD4X(i2c)
    print("Serial number:", [hex(i) for i in scd4x.serial_number])
    scd4x.start_periodic_measurement()
    print("Waiting for first measurement....")

    co2,temp,humi=scd4x.CO2,scd4x.temperature,scd4x.relative_humidity
    return co2,temp,humi


def get_cong(room_id,room_capacity):
    cong=-1
    try:
        portsoc = BASE_PORT + int(room_id)
        serversoc = (HOST, portsoc)
        socket1 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        socket1.connect(serversoc)
        if socket.recv(4096).decode()!=-1: #人数が測定不能(-1)でないとき
            cong = (float(socket.recv(4096).decode()) / float(room_capacity))*100 #混雑度(%)を代入
    except:
        print("timed out")

    return cong




def soundmethod(co2,temp,hum,cong): #警告ボイスを出すためのメソッド
    
    sound_number = [0]#ボイスを順番に流すための空の配列
    j_list=judge_level(co2,temp,hum,cong)

    if(j_list[0]==3):
        sound_number.append(12)
    elif(j_list[0]>=1):
        sound_number.append(11)
    
    cast_num=[1,0,0,2]#-1 -> 1 , 1 -> 2
    plus=20
    inc=10
    for judge in j_list:
        if(judge!=0):
            num=cast_num[judge+plus]
            sound_number.append()
        plus+=inc
    
    print(sound_number)#debug

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

def send_db(HOST,table_name,rec_time,avg_co2,avg_temp,avg_humi,avg_cong):
    is_connected,connection=connect_db(HOST)
    if(is_connected):
        try: #データベースに値を送信
            with connection.cursor() as cursor:
                sql = "INSERT INTO "+table_name+"(rec_time,co2,temp,humi,cong) VALUES(%(time)s,%(co2)s,%(temp)s,%(humi)s,%(cong)s);"
                into = {'time':str(rec_time),'co2':avg_co2,'temp':avg_temp,'humi':avg_humi,'cong':avg_cong}
                cursor.execute(sql,into)
                connection.commit()
                cursor.close()
                print("Data commited.")
                return True
        except: #送信に失敗したらエラーメッセージ
            print("送信エラー")
            os.system('echo 1 | sudo tee /proc/sys/vm/drop_caches>/dev/null')
            return False
    else:
        return False

if __name__=="main":
    main()