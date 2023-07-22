#!/bin/sh

# how to get PHP versions installed

# wp core download --path= --version=
# versions are like 6.2 or 6.2.2 (not 6.2.0)

# is there a list of versions, including alphas, betas, rcs, nightly somewhere?

# symlink "latest" and "nightly" in the shared dir?

# can we use mini-cli? Laravel Zero? Laravel Prompts?

# can we use wp-cli somehow?

# how/when to update the sqlite plugin

# Useful stuff on activating without MySQL here: https://github.com/WordPress/sqlite-database-integration/issues/7

###
# STEPS
###
QWP_DIR="$HOME/.quick-wp"
VERSION_REQUESTED='6.2.2'
INSTALL_DIR='.' # for now - will be a param
WP_CLI="$HOME/wp-cli/wp"
SCRIPT_DIR=$(dirname "$0")

# Start downloads

# Check for QWP_DIR and create if it doesn't exist
if [ ! -d "$QWP_DIR" ]; then
  mkdir $QWP_DIR
fi

# Check for sqlite-database-integration plugin and download if it doesn't exist
if [ ! -d "$QWP_DIR/sqlite-database-integration" ]; then
  curl https://downloads.wordpress.org/plugin/sqlite-database-integration.zip --silent --output $QWP_DIR/sqlite-database-integration.zip
  unzip $QWP_DIR/sqlite-database-integration.zip -d $QWP_DIR
fi

# Check for version of WP requested and download if it doesn't exist
if [ ! -d "$QWP_DIR/$VERSION_REQUESTED" ]; then
  $WP_CLI core download --path=$QWP_DIR/$VERSION_REQUESTED --version=$VERSION_REQUESTED
fi

# make directory
# (for now run IN a directory)

cd $INSTALL_DIR

# check for and download version

# symlink core
# - wp-admin
# - wp-includes
# - wp-*.php
# - index.php
# - xmlrpc.php
ln -s $QWP_DIR/$VERSION_REQUESTED/wp-admin $QWP_DIR/$VERSION_REQUESTED/wp-includes $QWP_DIR/$VERSION_REQUESTED/wp-*.php $QWP_DIR/$VERSION_REQUESTED/index.php $QWP_DIR/$VERSION_REQUESTED/xmlrpc.php $INSTALL_DIR

# copy config (have to copy this from source, not the symlink)
# or wp config create
$WP_CLI config create --dbname=localhost --dbuser=unused --skip-check --insecure

# make wp-content
mkdir $INSTALL_DIR/wp-content
# make wp-content/plugins
mkdir $INSTALL_DIR/wp-content/plugins
# make wp-content/themes
mkdir $INSTALL_DIR/wp-content/themes

# copy sqlite plugin
cp -r $QWP_DIR/sqlite-database-integration $INSTALL_DIR/wp-content/plugins

# copy plugins/sqlite-database-integration/db.copy to wp-content/db.php'
cp -r $QWP_DIR/sqlite-database-integration/db.copy $INSTALL_DIR/wp-content/db.php

# copy in a theme?
cp -r $QWP_DIR/$VERSION_REQUESTED/wp-content/themes/* $INSTALL_DIR/wp-content/themes/

# from https://github.com/WordPress/sqlite-database-integration/issues/7#issuecomment-1563465590
sed -i '' 's#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#${INSTALL_DIR}/wp-content/plugins/sqlite-database-integration#' ${INSTALL_DIR}/wp-content/db.php \
sed -i '' 's#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#' ${INSTALL_DIR}/wp-content/db.php
mkdir $INSTALL_DIR/wp-content/database && touch $INSTALL_DIR/wp-content/database/.ht.sqlite

# Could add --locale
$WP_CLI core install --url=http://localhost:8001 --title="A New Hope" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email

# copy the router.php in
cp $SCRIPT_DIR/router.php $INSTALL_DIR

# php -S localhost:<port> <path>/router.php
php -S localhost:8001 $INSTALL_DIR/router.php
