#!/bin/bash

cd $( dirname -- "$0"; )

docker compose -f docker-compose.yml up -d
docker ps
read -n 1 -s -r -p "press any key"
