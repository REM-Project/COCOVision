del /Q data
cd openpose
:LOOP
    bin\\OpenPoseDemo.exe --image_dir ../inputdata/192.168.10.5-0/ --write_json ../data/ --num_gpu 1
goto :LOOP

exit /b 0

