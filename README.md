# COCOVision
COCOVisionは室内の環境値を測定・警告し、新型コロナウイルス感染拡大を抑止することを目的としたシステムです。

現在このリポジトリに含まれているコードはシステムの中のwebシステム部と混雑度測定に関する部分です。（1/31 開発途上の計測デバイス用コード含有。全コード追加・更新予定）

又、このドキュメントを作成したのは2022年です。明記されていない限り、このREADME.mdに記載された日付は2022年のものです。



## 追記
* 2/3
demo内に混雑度の処理を実装しました。
demo/exec_Congestion.bat又はdemo/script/Congestion.pyを実行して下さい。

# 実行
ダウンロード・解凍した後、docker-compose.ymlと同一ディレクトリで `docker compose up -d` を実行します。

Dockerコンテナ内で作業したい場合は `docker exec -it コンテナ名 bash` で中に入ることができます。

OpenPoseが動作するか確認するのは下記で確認できると思います。
```
cd /usr/local/openpose/build/examples/openpose
mkdir data
openpose.bin --image_dir /usr/local/openpose/examples/media/ --write_json data/
```

# 実行環境
## OS・ドライバー
* Windows10
* NVIDIA Studio Driver 511.09（2022/01/18現在最新版）

## ハードウェア
* Intel Core i5-9600K
* NVIDIA Geforce RTX 2070


# 引継事項
## demoファルダについて
demo内は本来Windows版のOpenPoseを配置しています。

現時点（1/31）では開発途中のファイルがそのまま残っています。bat形式のファイルがdemo版のOpenposeを動かすものです。Openposeの引数についてはそちらを参考にするか検索して下さい。

Githubで管理する関係上省きましたが、プレゼン等でデモンストレーションを行う際に必要であれば[公式のリリース](https://github.com/CMU-Perceptual-Computing-Lab/openpose/releases)から落としてください。

開発途中でapache2からnginxに切り替えましたが、apache2のDockerfile自体は残してありますので切り替えは可能です。

その際はdocker-compose.ymlを書き換えて下さい。



## ubuntu（Linux）版について
Dockerを利用しているため、Linuxでも同じように実行できますが、
Ubuntu上で実行した際、cudaのバージョンが合わずOpenPoseが機能しなかったため断念しました。

エラー内容から、ホストとDockerコンテナのcudaを同じバージョンにすれば実行できるはずですが、現段階で実行することが出来ませんでした。

もし、取り組む際は以下を参考にすると良いと思います。（どちらもDocker上でOpenPoseを実行するものです）

* https://github.com/STomoya/openpose-docker

* https://github.com/myoshimi/openpose-docker


## OpenPoseの入力データについて
元の想定ではカメラ映像は画像として保存してから解析するのではなく、仮想カメラ入力として受け取りOpenPoseで解析する予定でしたが、
OpenPoseが仮想カメラ入力を認識できなかったため断念しました。

詳しく確認していませんがもしかすると以下に解決策があるかもしれませんので参考程度に記載致します。

* https://github.com/eqs/OpenPose-Docker/tree/master/docker

仮想カメラに取り組む場合はv4l2あたりを使うことになると思いますが、
Windows上で実装するためにはWSL2のカーネルにv4l2のカーネルモジュールを追加・ビルド、
現行のWSL2カーネルと差し替えを行う必要があります。

カーネルモジュールの追加方法は検索すれば方法は見つかりますので実装は可能だと思いますが、時間が無かったり難しいと感じた場合には
Virtual Box上でUbuntu等を実行しその上で実装するのが良いと思います。


```




















































```
# おまけ
確実に返答する保証はできませんが、覚えている限りのことについての質問には答えます。（質問事項だけ送って頂いて結構です）
* k4vanna@gmail.com
