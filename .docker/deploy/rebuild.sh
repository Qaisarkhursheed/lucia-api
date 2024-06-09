#!/bin/bash

docker-compose --project-name lucia-api up --build -d

#docker rmi $(docker images | grep "^<none>" | awk "{print $3}")
