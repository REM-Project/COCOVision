#混雑度返答（といいつつ雑にshellコマンドを実行する.py）

#ライブラリ
import sys
import subprocess
import os
import platform
import signal

import GetDevicesInfo as DevicesInfo
#メインストリーム
def main():
    #初期定義
    # 子プロセス捕捉用
    popen=[]

    # デバイスIP、カメラ台数取得
    devicesIp,numCameras=DevicesInfo.get()

    # OpenPose実行コマンドテンプレート（5にカメラアドレス、その後7に書き出し先ディレクトリ）
    OP_BASE_CMD=["bin/OpenPoseDemo.exe","--display","0","--disable_multi_thread","--ip_camera","--write_json"]

    #　必ずfinallyを実行させる
    signal.signal(signal.SIGTERM, sig_handler)

    try:
        #pf=platform.system()
        path=os.path.dirname(__file__)
        cmds=[]
        OP_BASE_CMD=["bin/OpenPoseDemo.exe","--display","0","--disable_multi_thread","--ip_camera","--write_json"]
        



        # （いら）ないです NYN
        # cmds.append(["python3",path+"/SaveCameraStream.py"])
        cmds.append(["python3",path+"/CongServer.py"])
        for cmd in cmds:
            popen.append(subprocess.Popen(cmd))
        for i in range(len(devicesIp)):
            #ipアドレス代入
            ip=devicesIp[i]
            #カメラ台数分ループ - 0 ~ n-1
            for num in range(numCameras[i]):

                cmd.insert(5,++filename)
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

def kill_popen(popen):
    for p in popen:
        p.kill()

def sig_handler(signum,frame) -> None:
    sys.exit(1)

#エントリーポイント
if __name__ == '__main__':
    main()
