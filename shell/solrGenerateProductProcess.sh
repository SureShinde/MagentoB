#!/bin/bash
log="../var/log/solrGenerateProductProcess"
if [ "$1" == "stop" ]
then
	genID=`ps ax | grep "worker/solr/GenerateProduct.php" | grep -v grep | awk '{print $1}'`
	for a in $genID
	do
		kill -9 $a
	done
	exit 0
fi

if [ "$1" == "all" ]
then
	echo "GenerateProduct.php --verbose --mode process --type config bundle detail sales"
	php ../worker/solr/GenerateProduct.php --verbose --mode process --type config &
	php ../worker/solr/GenerateProduct.php --verbose --mode process --type bundle &
	php ../worker/solr/GenerateProduct.php --verbose --mode process --type detail &
	php ../worker/solr/GenerateProduct.php --verbose --mode process --type sales &
	exit 0
fi

if [ "$1" == "--help" ]
then
	echo "Usage: ./solrGenerateProductProcess.sh stop => kill all process generate product"
	echo "Usage: ./solrGenerateProductProcess.sh start => start all process generate product {image onfig bundle detail sales}"
	echo "Usage: ./solrGenerateProductProcess.sh {jumlahprocess} {type:images|config|bundle|detail|sales}"
	exit 0
fi

if [ "$1" == "start" ]
then
        echo "GenerateProduct.php --verbose --mode process --type images config bundle detail sales"
	for a in `seq 1 2`
	do
		php ../worker/solr/GenerateProduct.php --verbose --mode process --type images >> $log-images-"$a".log &
		sleep 1
	done
        php ../worker/solr/GenerateProduct.php --verbose --mode process --type config >> $log-config.log &
        php ../worker/solr/GenerateProduct.php --verbose --mode process --type bundle >> $log-bundle.log &
        php ../worker/solr/GenerateProduct.php --verbose --mode process --type detail >> $log-detail.log &
        php ../worker/solr/GenerateProduct.php --verbose --mode process --type sales >> $log-sales.log &
        exit 0
fi


if [ -z $1 ] || [ -z $2 ]
then
	echo "Usage: bash solrGenerateProductProcess.sh {jumlahprocess} {type:image\|bundle}"
	exit 0
fi

for a in `seq 1 $1`
do
	echo "GenerateProduct.php --verbose --mode process --type $2"
	php ../worker/solr/GenerateProduct.php --verbose --mode process --type $2 >> ../var/log/solrGenerateProductProcess-$2-"$a".log &
	sleep 1
done
