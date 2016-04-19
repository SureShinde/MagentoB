#!/bin/bash
while [ 1 ]
do
	curl -u bilnamaster:'s3arch' -i "http://solrstage.bilna.com:8080/solr/stock/dataimport?command=delta-import&clean=false&commit=true"
	sleep 5
done
