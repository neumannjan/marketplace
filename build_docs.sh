#!/bin/sh

ROOT=$(dirname "$0")

rm -r $ROOT/reference_docs
mkdir -p $ROOT/reference_docs
mkdir -p $ROOT/reference_docs/php
mkdir -p $ROOT/reference_docs/ts

# PHP documentation
phpdoc -d $ROOT/app,$ROOT/database,$ROOT/bootstrap,$ROOT/routes \
 -t $ROOT/reference_docs/php

# TypeScript documentation
 npm run documentation