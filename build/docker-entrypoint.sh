#!/usr/bin/env bash
set -e

sudo chmod +x ./$(dirname "$0")/fix-container-uid-gid.sh
sudo ./$(dirname "$0")/fix-container-uid-gid.sh $MYUID $MYGID $@

docker-php-entrypoint "$@"
