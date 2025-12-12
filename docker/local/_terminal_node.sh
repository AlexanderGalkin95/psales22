#!/bin/bash

cd $( dirname -- "$0"; )

docker exec -it -u "$(id -u):$(id -g)" pinscher_node sh

sleep 2s
