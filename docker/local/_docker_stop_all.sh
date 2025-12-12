#!/bin/bash

cd $( dirname -- "$0"; )

docker stop $(docker ps -q)

echo "all containers are stopped"
sleep 1s