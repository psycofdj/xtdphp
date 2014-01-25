#!/bin/sh

l_path=$(dirname $0)
l_basePath=$(realpath $l_path/..)

php apigen/apigen.php -s "${l_basePath}/src" -d gendoc --exclude "${l_basePath}/src/core/libs" --access-levels "public,protected,private"
