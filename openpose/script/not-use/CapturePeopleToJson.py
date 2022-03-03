#OpenPoseにカメラを読ませて人数吐かせる.py
# demo/script内にOpenPoseIpCamToJson.shと一緒に配置

#ライブラリ
import shutil
import sys
import subprocess
import os
import platform
import signal

#ローカル（同階層）
import GetDevicesInfo as DevicesInfo
#メインストリーム
def main():
    #初期定義
    # このファイルのディレクトリpath
    path=os.path.dirname(__file__)

    # カレントディレクトリをこのファイルがあるディレクトリに変更
    os.chdir(path)

    
    # 子プロセス捕捉用
    popen=[]

    # デバイスIP、カメラ台数取得
    device_ips,num_cameras=DevicesInfo.get()

    # 受信port
    #  ポート番号は指定した番号から始まり台数に応じて1ずつ連番で割り当てられる - 例 7900 ~ 7902
    #  改変する場合は映像送信側も確認・変更すること
    #  開発段階での初期値は7900
    firstPort=7900

    

    # 必ずfinallyを実行させるための呪腹
    signal.signal(signal.SIGTERM, sig_handler)

    try:
        #pf=platform.system()
        for i in range(len(device_ips)):
            #ipアドレス代入
            ip=device_ips[i]
            #カメラ台数分ループ - 0 ~ n-1
            for num in range(num_cameras[i]):
                # 成形
                port=firstPort+num
                camera_addr="http://"+ip+":"+str(port)
                # cocovision/demo/openposeから見た相対パス
                out_dir="../output_json/"+ip+"_"+str(num)

                # 実行
                p=exec_openpose(camera_addr,out_dir)

                # 終了させるために格納
                popen.append(p)

        # 中断されるまで待機（ループ）
        while True:
            pass
                    
    finally:
        #Ctrl+Cやkillをキャンセル
        signal.signal(signal.SIGTERM, signal.SIG_IGN)
        signal.signal(signal.SIGINT, signal.SIG_IGN)
        
        #子プロセスをkill
        kill_popen(popen)
        
        #デフォルトに戻す
        signal.signal(signal.SIGTERM, signal.SIG_DFL)
        signal.signal(signal.SIGINT, signal.SIG_DFL)


# OpenPose実行
def exec_openpose(camera_addr,out_dir):
    #debug
    print(camera_addr)
    print(out_dir)

    # 書き出し先のフォルダを中身ごと削除
    if os.path.exists(out_dir):
        shutil.rmtree(out_dir)
    # 書き出し先のフォルダを生成
    os.makedirs(out_dir, exist_ok=True)

    cmd=["bash","OpenPoseIpCamToJson.sh",camera_addr,out_dir]

    #debug
    # print()
    # print(cmd)
    # print(cwd)
    # print()
    try:
        return subprocess.Popen(args=cmd,stderr=subprocess.STDOUT)
    except Exception as e:
        print(e)



# 子プロセス終了
def kill_popen(popen):
    for p in popen:
        p.kill()

# finallyを実行させるための形式上の宣言
def sig_handler(signum,frame) -> None:
    sys.exit(1)





#エントリーポイント
if __name__ == '__main__':
    main()
