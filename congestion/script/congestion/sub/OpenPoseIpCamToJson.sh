#!/bin/sh

while true :
do
    pwd
    echo "exec openpose $1"
    rm -rf $2 
    mkdir -p $2
    
    #本番環境
    # bin/OpenPoseDemo.exe --render_pose 0 --display 0 --disable_multi_thread --ip_camera $1 --write_json $2 --num_gpu 1 &
    
    #デモ（OpenPose画面表示）
    bin/OpenPoseDemo.exe --disable_multi_thread --ip_camera $1 --write_json $2 --num_gpu 1 &
    
    wait
    echo "disconnect $1"
    sleep 3
done