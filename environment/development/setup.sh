#!/bin/bash
DIR="$( cd "$( dirname "$0" )" && pwd )"
if ! [ -e "$DIR/../../.git/hooks/pre-commit" ]
then
	ln -fs $DIR/githooks/pre-commit $DIR/../../.git/hooks/pre-commit
fi

