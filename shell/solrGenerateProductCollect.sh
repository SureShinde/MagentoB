#!/bin/bash
for a in images config bundle detail sales
do
	echo "collect $a"
	php ../worker/solr/GenerateProduct.php --verbose --mode collect --type $a
done
