# COCOVision
COCOVisionは室内の環境値を測定・警告し、新型コロナウイルス感染拡大を抑止することを目的としたシステムです。

[recorder](recorder/)内に配置されているのが室内端末側のコードで、その他は処理用PC側のコードです。

最新動作確認版は[v1.0.14](https://github.com/REM-Project/COCOVision/releases/v1.0.14)です。

又、明記されていない限り、このREADME.mdに記載された日付は2022年のものです。


# 実行
* ## 処理用PC
  ダウンロード・解凍した後、[cocovision](./)内で `docker compose up -d` を実行します。

  [demo](demo)には本来Windows版のOpenPoseを配置しています。

  [公式のリリース](https://github.com/CMU-Perceptual-Computing-Lab/openpose/releases)から落としてください。

  また、ダウンロードしdemoに配置した後に~/cocovision/demo/openpose/models内のbatchファイルを実行してmodelのダウンロードを行うことも忘れないで下さい。

  その後[demo/script/Congestion.py](demo/script/Congestion.py) を実行して下さい。（カメラ映像から人数解析・室内端末に送信）


* ## 室内端末
  [recorder](recorder/)内の[cocovision](recorder/cocovision)フォルダをダウンロードします。
  ファルダ内の[COCOVision.py](recorder/cocovision/COCOVision.py)を実行することで処理が開始されます。
 
  初回実行時は実行前に[COCOVision-setup.py](recorder/cocovision/COCOVision-setup.py)を実行し、部屋情報設定を行ってから[COCOVision.py](recorder/cocovision/COCOVision.py)を実行してください。
 
  [COCOVision-configtable.py](recorder/cocovision/cocoVision-configtable.py)を実行するとGUIでデータベース内の部屋情報を新規登録・削除できます。
 
  実行に必要なライブラリは[requirements.txt](recorder/cocovision/requirements.txt)からpipして下さい。

# 実行環境
## OS・ドライバー
* Windows11
* NVIDIA Studio Driver 511.09
* Docker Desktop 4.4.4
* WSL2(Ubuntu20.04)

## ハードウェア
* Intel Core i5-9600K
* NVIDIA Geforce RTX 2070

## 室内端末
* Raspberry Pi 3 / 4
* [SCD40](https://www.switch-science.com/catalog/7169/)（CO₂・温湿度センサー） 
* webカメラ（任意数 - 2/7 1台の動作のみ確認）

# Q&A
1. ### [demo/script/Congestion.py](demo/script/Congestion.py)を実行しても、[CongServer.py](demo/script/CongServer.py)は動くが[OpenPoseIpCamToJson.py](demo/script/OpenPoseIpCamToJson.sh)(OpenPose)が動かない。
    <p>A. WSLのデフォルトのディストリビューションを確認して下さい</p>
    <p>dockerのものになっているとbashが使えなくなります</p>
    <p>対処法は下記の通りです</p>

    ```
    #確認コマンド
    wsl -l
    #変更するコマンド
    wsl -s <ディストリビューション名>

    ```
2. ### カメラ映像が取得できない
    <p>A. 以下に当て嵌まるかどうか確認・該当した箇所を修正して下さい</p>
    
    * データベースに計測デバイスのIPアドレスは登録されていますか
    * 処理用PCと同じネットワークに属していますか
    * データベースとOpenPoseを別々のPCで実行していませんか
    
3. ### Raspberry Piから音が出ない
    <p>A. 音声の出力先を確認・変更して下さい</p>
    


# 引継事項
## センサーについて
利用しているscd40についてですか利用方法について実装できなかった改善点があるので残します

電圧のノイズで測定値がブレるそうなので間にLDO（リニアレギュレータ）をかませることが推奨されています。scd40は3.3V動作なので5VにLDOを挟むことで安定すると思います。

その他の注意点については[公式のデザインガイド](https://sensirion.com/media/documents/0D0C9129/61653848/Sensirion_CO2_Sensors_SCD4x_design-in_guide.pdf)を読んで参考にして下さい。

<br>
<br>
<br>
<br>

## ubuntu（Linux）版について
Dockerを利用しているため、Linuxでも同じように実行できますが、
Ubuntu上で実行した際、cudaのバージョンが合わずOpenPoseが機能しなかったため断念しました。

エラー内容から、ホストとDockerコンテナのcudaを同じバージョンにすれば実行できるはずですが、現段階で実行することが出来ませんでした。

もし、取り組む際は以下を参考にすると良いと思います。（どちらもDocker上でOpenPoseを実行するものです）

>* https://github.com/STomoya/openpose-docker
>* https://github.com/myoshimi/openpose-docker

<br>
<br>
<br>
<br>

# おまけ
確実に返答する保証はできませんが、出来る限り質問には答えます。（質問事項だけ送って頂いて結構です）
* remprojectpbl@gmail.com
