#!/bin/sh
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
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
drush9 @$drush_alias entup -y --strict=0 --uri=$uri
drush9 @$drush_alias cim sync -y --uri=$uri
drush9 @$drush_alias cr --uri=$uri
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
