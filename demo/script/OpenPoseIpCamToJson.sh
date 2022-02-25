#!/bin/sh
cd ../openpose/
while true
do
    echo "exec openpose $1"
    rm -rf $2 
    mkdir $2
    #bin/OpenPoseDemo.exe --render_pose 0 --display 0 --disable_multi_thread --ip_camera $1 --write_json $2 --num_gpu 1 &
    bin/OpenPoseDemo.exe --disable_multi_thread --ip_camera $1 --write_json $2 --num_gpu 1 &
    wait
    echo "disconnect $1"
    sleep 3
done