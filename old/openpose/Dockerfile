#元イメージ
FROM nvidia/cuda:11.1.1-cudnn8-devel-ubuntu20.04

ENV DEBIAN_FRONTEND noninteractive 
#使うグラボ
ENV NVIDIA_VISIBLE_DEVICES all
#使う領域
ENV NVIDIA_DRIVER_CAPABILITIES video,compute,utility

#タイムゾーン
ENV TZ=Asia/Tokyo

#ubuntuが20.04より古い場合cmakeのバージョンが古くてミスる（もっとバージョン上げたほうがいいかも？）
#ENV CMAKE_VER="3.16"

#毎回OpenPoseをgit cloneするのも時間かかるのでは？理論に基づき事前ダウンロードしたものをコピーする作戦
#今現在使ってない
#COPY ./openpose/ /usr/local/openpose/

# Install required Packages

#RUN sed -i 's@archive.ubuntu.com@ftp.jaist.ac.jp/pub/Linux@g' /etc/apt/sources.list
RUN perl -p -i.bak -e 's%(deb(?:-src|)\s+)https?://(?!archive\.canonical\.com|security\.ubuntu\.com)[^\s]+%$1http://linux.yz.yamagata-u.ac.jp/pub/linux/ubuntu/archives/%' /etc/apt/sources.list

RUN set -xe && \
    apt-get update -y && \
    apt-get upgrade -y && \
    apt-get update -y && \
    apt-get install -y \
            tzdata \
            git sudo wget \
            build-essential \
            ffmpeg \
            gdebi \
            libopencv-dev \
            cmake \
            bash \
            apt-utils \
            curl \
            v4l2loopback-dkms \
            v4l2loopback-utils \
            iputils-ping \
            net-tools \
            dnsutils \
            python3 \
            python3-distutils \
            curl
RUN curl -kL https://bootstrap.pypa.io/get-pip.py | python3
RUN pip install pymysql
RUN pip install psutil

#v4ls setup
#RUN echo "options v4l2loopback video_nr=0 exclusive_caps=1" | sudo tee -a /etc/modprobe.d/v4l2loopback.conf
#RUN echo v4l2loopback | sudo tee -a /etc/modules-load.d/modules.conf
#RUN sudo systemctl restart systemd-modules-load.service

# Build OpenPose

WORKDIR /usr/local
RUN set -xe && \
    git clone https://github.com/CMU-Perceptual-Computing-Lab/openpose

RUN cd /usr/local/openpose && \
    bash ./scripts/ubuntu/install_deps.sh

RUN mkdir -p /usr/local/openpose/build && \
    cd /usr/local/openpose/build && \
    cmake .. && \
    make -j$(nproc) && \
    make install

# Cleanup

WORKDIR /usr/local
RUN set -xe && \
    apt-get clean && \
    rm -rf /tmp/cmake.tar.gz
    
#bash bin/openpose.binで動かすためだけのcp
RUN cp /usr/local/openpose/build/examples/openpose/openpose.bin /usr/local/bin/
    #ln -s /usr/local/openpose/build/examples/openpose/openpose.bin /usr/local/bin/openpose.bin
    
#CMD bin/openpose.bin --camera num --write_json /data


