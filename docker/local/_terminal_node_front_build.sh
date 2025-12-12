#!/bin/bash

cd $( dirname -- "$0"; )

docker exec -it -u "$(id -u):$(id -g)" pinscher_node sh -c "npm run development"

sleep 2s
