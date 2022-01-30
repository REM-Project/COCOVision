# COCOVision
COCOVisionは室内の環境値を測定・警告し、新型コロナウイルス感染拡大を抑止することを目的としたシステムです。

現在このリポジトリに含まれているコードはシステムの中のwebシステム部と混雑度測定に関する部分です。（開発途上の計測デバイス用コード含有。全コード追加・更新予定）

# 実行
ダウンロード・解凍した後、docker-compose.ymlと同一ディレクトリで docker compose up -d を実行します。

Dockerコンテナ内で作業したい場合は docker exec -it コンテナ名 bash で中に入ることができます。


# 実行環境
## OS・ドライバー
* Windows10
* NVIDIA Studio Driver 511.09（2022/01/18現在最新版）

## ハードウェア
* Intel Core i5-9600K
* NVIDIA Geforce RTX 2070


# 引継事項
demo内は本来Windows版のOpenPoseを配置しています。

Githubで管理する関係上省きましたが、プレゼン等でデモンストレーションを行う際に必要であれば[公式のリリース](https://github.com/CMU-Perceptual-Computing-Lab/openpose/releases)から落としてください。

開発途中でapache2からnginxに切り替えましたが、apache2のDockerfile自体は残してありますので切り替えは可能です。

その際はdocker-compose.ymlを書き換えて下さい。




Dockerを利用しており、WSL2のカーネルの調整等は行っていないためLinuxでも動作すると考えられますが、
Ubuntu上で実行した際、cudaのバージョンが合わずOpenPoseが実行できなかったため断念しました。

ホストとDockerコンテナのcudaを同じバージョンにすれば実行できるはずですが、現段階で実行することが出来ませんでした。

もし、取り組む際は以下を参考にすると良いと思います。（どちらもDocker上でOpenPoseを実行するものです）

https://github.com/STomoya/openpose-docker

https://github.com/myoshimi/openpose-docker

また元の想定ではカメラ映像は画像として保存してから解析するのではなく、仮想カメラ入力として受け取りOpenPoseで解析する予定でしたが、
OpenPoseが仮想カメラ入力を認識できなかったため断念しました。

詳しく確認していませんがもしかすると以下に解決策があるかもしれませんので参考程度に記載致します。

https://github.com/eqs/OpenPose-Docker/tree/master/docker

仮想カメラに取り組む場合はv4l2あたりを使うことになると思いますが、
Windows上で実装するためにはWSL2のカーネルにv4l2のカーネルモジュールを追加・ビルド、
現行のWSL2カーネルと差し替えを行う必要があります。

カーネルモジュールの追加方法は検索すれば方法は見つかりますので実装は可能だと思いますが、時間が無かったり難しいと感じた場合には
Virtual Box上でUbuntu等を実行しその上で実装するのが良いと思います。


```




















































```
# 連絡先
確実に返答する保証はできませんが、覚えている限りのことについての質問には答えます。
* k4vanna@gmail.com
