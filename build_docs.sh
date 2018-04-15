#!/bin/sh

ROOT=$(dirname "$0")
DIR=$ROOT/docs/reference_docs

rm -r $DIR
mkdir -p $DIR
mkdir -p $DIR/php
mkdir -p $DIR/ts

# PHP documentation
phpdoc -d $ROOT/app,$ROOT/database,$ROOT/bootstrap,$ROOT/routes \
 -t $DIR/php

# TypeScript documentation
 npx typedoc --out $DIR/ts --name Marketplace --mode modules --excludeExternals resources/assets/js