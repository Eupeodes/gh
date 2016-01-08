#!/bin/bash

#up the version
version=$(<version)
parts=(${version//./ })

case $1 in
    "major")
        i=0
        parts[1]=0
        parts[2]=0
        ;;
    "minor")
        i=1
        parts[2]=0
        ;;
    *)
        i=2
        ;;
esac


((parts[i]=${parts[i]}+1))
version=$(printf ".%s" "${parts[@]}")
version=${version:1}

#compile and minify css and minify js
sass --style compressed --sourcemap=none scss/style.scss public/css/style.css
yc public/js/script.js -o public/js/script.min.js

#merge css and js into single files
cat `grep -o '"[^"]*\.js"' template/index.tpl.php | sed 's/script.js/script.min.js/g;s/"//g;s/^/public/g'` > public/js/js.js
cat `grep -o '"[^"]*\.css"' template/index.tpl.php | sed 's/"//g;s/^/public/g'` > public/css/css.css

#store version
echo $version > version

echo Current version is $version
