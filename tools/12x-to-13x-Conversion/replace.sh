#!/bin/sh

if [ $# -ne 1 ]; then
  echo 1>&2 Usage: $0 [/path/to/module_or_theme]
  exit 127
fi
PWD=`pwd`

find $1/. | grep -v svn | egrep '\.htm$|\.html|\.tpl$' > _temp.txt
for c in `cat _temp.txt`
do
  php -f replace_templates.php $c
done

find $1/. | grep -v svn | egrep '\.php$' > _temp.txt
for c in `cat _temp.txt`
do
  php -f replace_php.php $c
done



