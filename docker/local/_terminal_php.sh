#!/bin/bash

cd $( dirname -- "$0"; )

docker exec -it -u "$(id -u):$(id -g)" pinscher_php sh

sleep 2s
