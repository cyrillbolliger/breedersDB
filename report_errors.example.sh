#!/usr/bin/env bash

RECEIVER="mail@example.com"
APP_NAME="breedersdb.com"
LOGS=( "./logs/error.log" "./logs/debug.log" )

set -e

for LOG in "${LOGS[@]}"
do
    if [ -e "$LOG" ]
    then
        echo "Check attached log file: $LOG" | mail -s "$APP_NAME\: $LOG" -a "$LOG" "$RECEIVER"
        rm "$LOG"
    fi
done
