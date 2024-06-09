#!/bin/bash

docker-compose  --compatibility --project-name lucia-api up  -d

#docker rmi $(docker images | grep "^<none>" | awk "{print $3}")
