#OpenPoseにカメラを読ませて人数吐かせる.py
# demo/script内にOpenPoseIpCamToJson.shと一緒に配置

#ライブラリ
import sys
import subprocess
import os
import signal

#メインストリーム
def main():
    #初期定義
    # このファイルのディレクトリpath
    path=os.path.dirname(__file__)

    # カレントディレクトリをこのファイルがあるディレクトリに変更
    os.chdir(path)

    # 子プロセス捕捉用
    popen=[]
    
    #COCOVision.configから読み込み
    with open("COCOVision.config") as f:
        config = [s.strip() for s in f.readlines()]
    
    #カメラ台数取得
    num_camera=config[1]
    
    # カメラ映像port
    #  ポート番号は指定した番号から始まり台数に応じて1ずつ連番で割り当てられる - 例 7900 ~ 7902
    #  改変する場合は映像送信側も確認・変更すること
    #  開発段階での初期値は7900
    CAMERA_FIRST_PORT=7900

    # 必ずfinallyを実行させるための呪腹
    signal.signal(signal.SIGTERM, sig_handler)

    try:
            #カメラ台数分ループ - 0 ~ n-1
        for num in range(num_camera):
            # 成形
            camera_port=CAMERA_FIRST_PORT+num
            camera_addr="http://:"+str(camera_port)

            # openpose(OpenPoseIpCamToJson.sh) 実行
            p=exec_stream(camera_addr)

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
def exec_stream(camera_addr):
    cmd=["bash","OpenPoseIpCamToJson.sh",camera_addr]
    
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
