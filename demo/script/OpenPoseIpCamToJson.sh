cd ../openpose
echo "exec openpose"
#bin/OpenPoseDemo.exe --render_pose 0 --display 0 --disable_multi_thread --ip_camera $1 --write_json $2 --num_gpu 1
bin/OpenPoseDemo.exe --disable_multi_thread --ip_camera $1 --write_json $2 --num_gpu 1