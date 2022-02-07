import os

#初期定義
# このファイルのディレクトリpath
path=os.path.dirname(__file__)

print(path)

# python3.9以前用に相対パスから絶対パスに変換
path=os.path.abspath(path)

print(path)
# カレントディレクトリをこのファイルがあるディレクトリに変更
os.chdir(path)