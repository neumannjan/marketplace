#!/bin/sh
callable="docker-compose -f docker-compose.yml"
isEnv=false

for arg in "$@"
do
    if [ ${isEnv} = true ]; then
        callable="${callable} -f docker-compose.${arg}.yml"
        isEnv=false
    elif [ ${arg} = "--env" ] || [ ${arg} == "-e" ]; then
        isEnv=true
    else
        callable="${callable} ${arg}"
    fi
done

eval ${callable}