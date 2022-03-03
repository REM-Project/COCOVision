import json
import socket
import time
import itertools

for Path_number in itertools.count():
	Path = 'C:/rem-project/demo/openpose/data/03/{:0>12}_keypoints.json'
	json_open = open(Path.format(Path_number), 'r')
	json_load = json.load(json_open)
	peo=len(json_load['people'])
	print(str(peo)+"人")
	print(Path.format(Path_number))
	time.sleep(3)

