#!/usr/bin/env bash
printenv | grep -E "^APP_ROOT" | sed 's/^\(.*\)$/export \1/g' > /get_app_root.sh