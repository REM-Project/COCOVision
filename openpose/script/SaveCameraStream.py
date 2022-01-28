#ライブカメラ映像を画像に書き出し
#実行コマンドは./script/SaveCameraStream.pyとする（相対パスの関係上）

#ライブラリ
import subprocess
import os

#ローカル
import GetDevicesInfo as DevicesInfo #同一ディレクトリに配置



#メインストリーム
def main():
    #カメラのIPアドレス・台数を取得 - 処理はGetDevicesInfo.pyに記載
    devicesIp,numCameras=DevicesInfo.get()
    
    #取得した値の確認（デバッグ用）
    print(devicesIp)
    print(numCameras)

    #パラメータの設定
    # ポート番号は指定した番号から始まり台数に応じて1ずつ連番で割り当てられる - 例 7900 ~ 7902
    # 改変する場合は映像送信側も確認・変更すること
    # 開発段階での初期値は7900
    firstPort=7900
    # 画像名
    # OpenPoseで解析する際、ファイル名を指定するため改変時はそちらも確認・改変すること
    filename="img.jpg"

    #成形したコマンドを格納する変数
    #実行コマンド例 : ffmpeg -re -i http://192.168.10.5:7900 -f image2 -update 1 ./inputdata/192.168.10.5-0/img.jpg
    execCmd=[]
    
    #取得したipアドレス数分ループ
    for i in range(len(devicesIp)):
        #ipアドレス代入
        ip=devicesIp[i]
        #カメラ台数分ループ - 0 ~ n-1
        for num in range(numCameras[i]):
            #固定値
            cmd=["ffmpeg","-re","-i","-f","image2","-update","1"]

            #使用ポート番号成形
            port=firstPort+num
            
            #取得URL成形
            address=ip+":"+str(port)

            #ソース元指定
            cmd.insert(3,"http://"+address)
            
            #書き出し先フォルダパス成形
            savePath="./inputdata/"+ip+"-"+str(num)
            
            #書き出し先指定
            cmd.insert(8,savePath+"/"+filename)

            #書き出し先のフォルダを生成
            os.makedirs(savePath, exist_ok=True)

            #成形完了・実行用の配列に挿入
            execCmd.append(cmd)
    
    #成形したコマンドを実行
    for cmd in execCmd:
        #debug
        print(cmd)

        #非同期実行
        subprocess.Popen(cmd)


#エントリーポイント
if __name__ == '__main__':
    main()
