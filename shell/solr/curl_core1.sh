#!/bin/bash
while [ 1 ]
do
	curl -u freshmeat:'s3arch' -i "http://freshmeat.bilna.com:8080/solr/core1/dataimport?command=delta-import&clean=false&commit=true"
	sleep 10
done
