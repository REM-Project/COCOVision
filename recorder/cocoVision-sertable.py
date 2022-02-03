import os
import pymysql.cursors
import PySimpleGUI as sg
import ipget

layout1 = [
  [sg.Text('データベースIPアドレス入力画面')],
  [sg.Text('データベースのIPアドレス', size=(25,1)), sg.InputText('172.30.8.206')],
  [sg.Submit(button_text='接続')]]

layout2 = [
  [sg.Text('テーブル追加画面')],
  [sg.Text('部屋の名前', size=(15,1)), sg.InputText('○○室')],
  [sg.Text('テーブルの名前', size=(15,1)), sg.InputText('roomname'),sg.Text('_values')],
  [sg.Text('部屋の収容人数', size=(15,1)), sg.InputText('20')],
  [sg.Submit(button_text='追加')]]

def showWin1():
    win1 = sg.Window('COCOVision-Settable', layout1)
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
    win2 = sg.Window('COCOVision-Settable', layout2)
    while True:
        event, values = win2.read()
        if event is None: break
        if event == '追加':
            if values[0]=="部屋一覧":
                sg.popup('部屋を選択してください')
            else:
                setupDb(connection,values)
    win2.close()

def setupDb(connection,values):
    try: #mysqlデータベースに接続
        cursor=connection.cursor()
        sql = '''insert into room_info values(%(room)s,%(table)s,%(capacity)s);'''
        into ={'room':values[0],'table':values[1]+"_values",'capacity':values[2]}
        cursor.execute(sql,into)
        connection.commit()
        cursor.close()
        sg.popup('適用しました')
    except: #接続できなかったらエラー文
        print("データベースへの追加に失敗しました")
        sg.popup('データベースへの追加に失敗しました')



#start task
reWin1=showWin1()
showWin2(reWin1[0],reWin1[1])

