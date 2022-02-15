from mutagen.mp3 import MP3 as mp3
from pydub import AudioSegment
import pygame
import time
sound=[1,5]
for a in sound:
    filename = 'sound/sound'+str(a)+'.mp3' #再生したいmp3ファイル
    pygame.mixer.init()
    pygame.mixer.music.load(filename) #音源を読み込み
    mp3_length = mp3(filename).info.length #音源の長さ取得
    pygame.mixer.music.play(1) #再生開始。1の部分を変えるとn回再生(その場合は次の行の秒数も×nすること)
    time.sleep(mp3_length + 1.0) #再生開始後、音源の長さだけ待つ(0.25待つのは誤差解消)
    pygame.mixer.music.stop() #音源の長さ待ったら再生停止
