#!/bin/bash

# $1 => total worker
# $2 => type: images|config|bundle|detail|sales

for a in `seq 1 $1 `
do
    php worker/solr/GenerateProduct.php --verbose --mode process --type $2 >> var/log/solr_process_$2_$a.log &
    sleep 1
done
