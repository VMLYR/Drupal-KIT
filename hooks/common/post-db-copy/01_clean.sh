#!/bin/sh
#
# Cloud Hook: post-db-copy
#
# The post-db-copy hook is run whenever you use the Workflow page to copy a
# database from one environment to another. See ../README.md for
# details.
#
# Usage: post-db-copy site target-env db-name source-env

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

drush_alias=$site'.'$target_env
uri=`drush9 sa @$drush_alias --format=yaml | sed -n "s/.*uri: \(.*\)/\1/p"`

# Main
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
drush9 @$drush_alias entup -y --strict=0 --uri=$uri
drush9 @$drush_alias cim sync -y --uri=$uri
drush9 @$drush_alias cr --uri=$uri
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
