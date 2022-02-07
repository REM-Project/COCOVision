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

    # python3.9以前用に相対パスから絶対パスに変換
    path=os.path.abspath(path)

    # カレントディレクトリをこのファイルがあるディレクトリに変更
    os.chdir(path)

    
    # 子プロセス捕捉用
    popen=[]

    # デバイスIP、カメラ台数取得
    device_ips,num_cameras,ids=DevicesInfo.get_all()

    # カメラ映像port
    #  ポート番号は指定した番号から始まり台数に応じて1ずつ連番で割り当てられる - 例 7900 ~ 7902
    #  改変する場合は映像送信側も確認・変更すること
    #  開発段階での初期値は7900
    CAMERA_FIRST_PORT=7900

    # 取得人数port
    #  指定した番号+room_infoのid（自動連番） - 例 9000 + id = 9001（利用ポート番号）
    #  開発段階の初期値は9000
    CONG_BASE_PORT=9000


    # 必ずfinallyを実行させるための呪腹
    signal.signal(signal.SIGTERM, sig_handler)

    try:
        #pf=platform.system()
        for i in range(len(device_ips)):
            #ipアドレス代入
            device_ip=device_ips[i]
            #カメラ台数分ループ - 0 ~ n-1
            for num in range(num_cameras[i]):
                # 成形
                camera_port=CAMERA_FIRST_PORT+num
                camera_addr="http://"+device_ip+":"+str(camera_port)
                # cocovision/demo/openposeから見た相対パス
                out_dir="../output_json/"+device_ip+"_"+str(num)

                # openpose(OpenPoseIpCamToJson.sh) 実行
                p=exec_openpose(camera_addr,out_dir)

                # 終了させるために格納
                popen.append(p)
            
            #人数取得・送信
            # 成形
            cong_port=CONG_BASE_PORT+ids[i]
            
            # CongServer.py 実行
            p=exec_cong_server(device_ip,cong_port,num_cameras[i])

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


#人数取得実行
def exec_cong_server(device_ip,cong_port,num_camera):
    cmd=["python3","CongServer.py",device_ip,str(cong_port),str(num_camera)]
    return subprocess.Popen(args=cmd,stderr=subprocess.STDOUT)



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
