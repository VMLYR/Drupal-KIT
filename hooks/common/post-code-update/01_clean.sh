#!/bin/sh
#
# Cloud Hook: post-code-update
#
# The post-code-update hook runs in response to code commits.
# When you push commits to a Git branch, the post-code-update hooks runs for
# each environment that is currently running that branch. See
# ../README.md for details.
#
# Usage: post-code-update site target-env source-branch deployed-tag repo-url
#                         repo-type

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
drush9 @$drush_alias cim sync -y --uri=$uri
drush9 @$drush_alias cr --uri=$uri
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
