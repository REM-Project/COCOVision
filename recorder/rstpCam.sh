ffmpeg -i $1 -vcodec $2 -f mpegts -|vlc -I dummy - --sout="#std{access=http,mux=ts,dst=:$3}"