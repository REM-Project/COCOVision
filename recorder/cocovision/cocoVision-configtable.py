import pymysql.cursors
import PySimpleGUI as sg

roomlist = []
layout1 = [
  [sg.Text('データベースIPアドレス入力画面')],
  [sg.Text('データベースのIPアドレス:', size=(23,1)), sg.InputText('192.168.10.38')],
  [sg.Submit(button_text='追加'),sg.Submit(button_text='削除')]]

layout2 = [
  [sg.Text('テーブル追加画面')],
  [sg.Text('部屋の名前:', size=(15,1)), sg.InputText('◯◯室')],
  [sg.Text('テーブルの名前:', size=(15,1)), sg.InputText('roomname'),sg.Text('_values')],
  [sg.Text('部屋の収容人数:', size=(15,1)), sg.InputText('20')],
  [sg.Submit(button_text='追加')]]

layout3 = [
  [sg.Text('テーブル削除画面')],
  [sg.Text('部屋名:', size=(10, 1)),sg.Combo((roomlist),readonly=True,default_value="部屋一覧",size=(20, 1))],
  [sg.Submit(button_text='削除')]]

def showWin1():
    win1 = sg.Window('COCOVision-configtable', layout1,size=(350, 100))
    while True:
        event, values = win1.read()
        if event is None: return event,"null"
        if event == '追加' or event == '削除':
            try:
                connection = pymysql.connect(host=values[0],
                                             user='recorder',
                                             password='th1117',
                                             db='cocovision',
                                             charset='utf8')
                sg.popup('接続しました')
                win1.close()
                return event,connection
            except Exception as e:
                sg.popup('接続に失敗しました:'+str(e))

def showWin2(connection):   
    win2 = sg.Window('COCOVision-Settable',layout2)
    while True:
        event, values = win2.read()
        if event is None: break
        if event == '追加':
            setupDb(connection,values)
    win2.close()

def setupDb(connection,values):
    try: #mysqlデータベースに接続
        cursor=connection.cursor()
        sql = '''insert into room_info(room_name,table_name,room_capacity) values(%(room)s,%(table)s,%(capacity)s);'''
        into ={'room':values[0],'table':values[1]+"_values",'capacity':values[2]}
        cursor.execute(sql,into)
        sql = 'create table '+values[1]+'_values like values_template;'
        cursor.execute(sql)
        connection.commit()
        cursor.close()
        sg.popup('適用しました')
    except Exception as e: #接続できなかったらエラー文
        sg.popup('データベースへの追加に失敗しました:' +str(e))
        print(str(e))
        
def showWin3(connection):
    try:#get to room list
        cursor = connection.cursor()
        cursor.execute("select room_name from room_info;")
        result=cursor.fetchall()
        for row in result:
            roomlist.append(str(row[0]))
    except:
        sg.popup('接続できませんでした、もう一度入力してください')
        
    win3 = sg.Window('COCOVision-deletetable',layout3,size=(340, 100))
    while True:
        event, values = win3.read()
        if event is None: break
        if event == '削除':
            if values[0]=="部屋一覧":
                sg.popup('部屋を選択してください')
            else:
                deleteDb(connection,values)
    win3.close()

def deleteDb(connection,values):
    try: #mysqlデータベースに接続
        cursor=connection.cursor()
        sql = '''select table_name from room_info where room_name=%s;'''
        into = values[0]
        cursor.execute(sql,into)
        into = cursor.fetchall()[0][0]
        sql = '''delete from room_info where table_name=%s;'''
        cursor.execute(sql,into)
        sql = 'drop table '+into+';'
        cursor.execute(sql)
        connection.commit()
        cursor.close()
        sg.popup('削除しました')
    except Exception as e: #接続できなかったらエラー文
        sg.popup('テーブル削除に失敗しました:' +str(e))
        print(str(e))
        
#start task
reWin1=showWin1()
if reWin1[0] == "追加":
    showWin2(reWin1[1])
elif reWin1[0] == "削除":
    showWin3(reWin1[1])



