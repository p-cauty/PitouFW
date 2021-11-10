rm -rf pitoufw/.github pitoufw/.git pitoufw/.gitignore pitoufw/README.md pitoufw/html
echo "$TAG" > pitoufw/version.txt
touch release.tar.gz
tar --exclude=release.tar.gz -zcvf release.tar.gz pitoufw
