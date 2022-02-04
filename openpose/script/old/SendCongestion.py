#混雑度返答（といいつつ雑にshellコマンドを実行する.py）

#ライブラリ
import sys
import subprocess
import os
import platform
import signal

#メインストリーム
def main():
    #子プロセス捕捉用
    popen=[]

    #必ずfinallyを実行させる
    signal.signal(signal.SIGTERM, sig_handler)

    try:

        #pf=platform.system()
        path=os.path.dirname(__file__)
        cmds=[]


        cmds.append(["python3",path+"/SaveCameraStream.py"])
        cmds.append(["python3",path+"/CongServer.py"])
        for cmd in cmds:
            popen.append(subprocess.Popen(cmd))
        
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
