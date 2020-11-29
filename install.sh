#!/usr/bin/env bash

set -e

mkdir -p .docker/mysql/data

# get containers ready
docker-compose pull
docker-compose build app

# install dependencies
docker-compose run app composer install
docker-compose run node npm install

# initialize database
docker-compose run app bin/bake migrations migrate --no-lock

# start up containers
docker-compose up -d
