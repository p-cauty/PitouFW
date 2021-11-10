rm -rf html.new/.github html.new/.git html.new/.gitignore html.new/composer.json html.new/composer.lock
echo "$TAG" > html.new/version.txt
touch release.tar.gz
tar --exclude=release.tar.gz -zcvf release.tar.gz html.new
