#!/bin/bash
DIR="$( cd "$( dirname "$0" )" && pwd )"
ln -fs $DIR/githooks/pre-commit $DIR/../../.git/hooks/pre-commit

