# COCOVision
COCOVisionは室内の環境値を測定・警告し、新型コロナウイルス感染拡大を抑止することを目的としたシステムです。

[recorder](recorder/)内に配置されているのが計測デバイス用のコードで、その他は処理用PC側のコードです。

又、明記されていない限り、このREADME.mdに記載された日付は2022年のものです。



## 追記
* ### 2/3
    demo内に混雑度の処理を実装しました。

    [demo/exec_Congestion.bat](demo/exec_Congestion.bat) 又は [demo/script/Congestion.py](demo/script/Congestion.py) を実行して下さい。

    実行する際にWindows版のOpenPoseが必要です。[demoファルダについて](#demoファルダについて)を見て配置して下さい。

* ### 2/7
  * [recorder](recorder)内に計測デバイス（Raspberry Pi 3 / 4）のコードを追加しました。
  * [COCOVision.py](recorder/cocovision/COCOVision.py)を実行すると計測が開始されます。
  * [COCOVision-setup.py](recorder/cocovision/COCOVision-setup.py)を実行するとGUIでのデータベース・部屋選択ができます。又は[COCOVision.config](recorder/cocovision/COCOVision.config)を直接書き換えてください。
  * [COCOVision-configtable.py](recorder/cocovision/cocoVision-configtable.py)を実行するとGUIでデータベース内の部屋情報を新規登録・削除できます。~~（2/8 削除未対応）~~（2/14 削除対応）

* ### 2/14
  * 全動作確認・コメント文を追加したバージョン（v.1.0.4）をアップロードしました。[Releases](https://github.com/REM-Project/COCOVision/releases/)からダウンロードして下さい。

# 実行
* ## 処理用PC
  ダウンロード・解凍した後、[cocovision](./)内で `docker compose up -d` を実行します。

  その後[demo/script/Congestion.py](demo/script/Congestion.py) を実行して下さい。（カメラ映像から人数解析・計測デバイスに送信）

  下記取り消し線の内容はDocker上のOpenPoseに対しての説明のため不要です。

  ~~Dockerコンテナ内で作業したい場合は `docker exec -it コンテナ名 bash` で中に入ることができます。~~

  ~~OpenPoseが動作するか確認するのは下記で確認できると思います。~~
```
cd /usr/local/openpose/build/examples/openpose
mkdir data
openpose.bin --image_dir /usr/local/openpose/examples/media/ --write_json data/
```

* ## 計測デバイス
  [recorder](recorder/)内の[cocovision](recorder/cocovision)フォルダをダウンロードします。
  ファルダ内の[COCOVision.py](recorder/cocovision/COCOVision.py)を実行することで処理が開始されます。
  初回実行時は実行前に[COCOVision-setup.py](recorder/cocovision/COCOVision-setup.py)を実行し、部屋情報設定を行ってから[COCOVision.py](recorder/cocovision/COCOVision.py)を実行してください。

# 実行環境
## OS・ドライバー
* Windows10
* NVIDIA Studio Driver 511.09
* Docker Desktop 4.4.4
* WSL2(Ubuntu20.04)

## ハードウェア
* Intel Core i5-9600K
* NVIDIA Geforce RTX 2070

## 計測デバイス
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
    

# 引継事項
## demoファルダについて
[demo](demo)には本来Windows版のOpenPoseを配置しています。

また現時点（1/31）では開発途中のファイルがそのまま残っています。（2/4 Windows用のbatchが [demo/old/](demo/old/) に、使わなくなったpythonファイルが [demo/script/not-use](demo/script/not-use/) にあります。）bat形式のファイルがdemo版のOpenposeを動かすものです。Openposeの引数についてはそちらを参考にするか検索して下さい。

Githubで管理する関係上省きましたが、~~プレゼン等でデモンストレーションを行う際に必要であれば~~(Docker上で動いてないので現状必須です - 2/8) [公式のリリース](https://github.com/CMU-Perceptual-Computing-Lab/openpose/releases)から落としてください。

また、~/demo/openpose/models内のbatchファイルを実行してmodelのダウンロードを行うことも忘れないで下さい。

開発途中でapache2からnginxに切り替えましたが、apache2のDockerfile自体は残してありますので切り替えは簡単です。

その際は[docker-compose.yml](docker-compose.yml)を書き換えて下さい。

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

## OpenPoseはIPストリーミングをそのまま受け取れます。よってこれより下を読む必要はありません。

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

## ~~OpenPoseの入力データについて~~
~~元の想定ではカメラ映像は画像として保存してから解析するのではなく、仮想カメラ入力として受け取りOpenPoseで解析する予定でしたが、
OpenPoseが仮想カメラ入力を認識できなかったため断念しました。~~

~~詳しく確認していませんがもしかすると以下に解決策があるかもしれませんので参考程度に記載致します。~~

* ~~https://github.com/eqs/OpenPose-Docker/tree/master/docker~~

~~仮想カメラに取り組む場合はv4l2あたりを使うことになると思いますが、
Windows上で実装するためにはWSL2のカーネルにv4l2のカーネルモジュールを追加・ビルド、
現行のWSL2カーネルと差し替えを行う必要があります。~~

~~カーネルモジュールの追加方法は検索すれば方法は見つかりますので実装は可能だと思いますが、時間が無かったり難しいと感じた場合には
Virtual Box上でUbuntu等を実行しその上で実装するのが良いと思います。~~



<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

<br>
<br>
<br>
<br>

# おまけ
確実に返答する保証はできませんが、覚えている限りのことについての質問には答えます。（質問事項だけ送って頂いて結構です）
* k4vanna@gmail.com
