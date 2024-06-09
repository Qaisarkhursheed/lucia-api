#!/bin/bash
docker-compose --project-name lucia-api logs | grep app --color=auto
