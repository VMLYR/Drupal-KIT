#!/bin/bash

# save theme folder
# THEMEPATH="${BASH_SOURCE[0]}"
THEMEPATH=$0;
PARENTDIR="$(dirname "$THEMEPATH")"
SCRIPTPATH=$PWD"/"$PARENTDIR

echo "$PWD" > sh/themevars.txt # use > to (re)write and >> to append to file

# find sh file
# copy to .git folder
echo 'run preinstall script';
echo 'path to script =' $SCRIPTPATH;

# check if path to our script contains a docroot

HASROOT=0

if [[ ${SCRIPTPATH} == *"/docroot/"* ]]; then
  HASROOT=1
fi

if [[ $HASROOT == 1 ]]; then

  # find path to this script file
  REMAINDER=${SCRIPTPATH##*docroot/}'/'

  # build a path to docroot parent
  # where a .git folder could be located
  CHAR="/"
  awk -F"${CHAR}" '{print NF-1}' <<< "${REMAINDER}"
  END="$(echo "${REMAINDER}" | awk -F"${CHAR}" '{print NF-1}')"
  NEWPATH=""
  for ((i=1;i<=END;i++)); do
    NEWPATH=${NEWPATH}"../"
  done

  # use that path to try and find .git

  if [ -d "$NEWPATH.git" ]; then

    echo '.git exists';

    if [ ! -d "$NEWPATH.git/hooks" ]; then
      echo 'make hooks folder';
      mkdir $NEWPATH.git/hooks
      chmod +x $NEWPATH.git/hooks
    else
      echo 'hooks folder exists';
    fi

    if [ ! -d "$NEWPATH.git/hooks/pre-commit.d" ]; then
      echo 'make a precommit files folder';
      mkdir $NEWPATH.git/hooks/pre-commit.d
      chmod +x $NEWPATH.git/hooks/pre-commit.d
    else
      echo 'precommit folder exists';
    fi

    # only if precommit file does not exist yet, do we add our own
    # normally it should already have been created by the project create composer, but you never know
    if [ ! -f "$NEWPATH.git/hooks/pre-commit" ]; then
      echo 'add our pre-commit file that can access those hooks';
      cp -R sh/pre-commit $NEWPATH.git/hooks/pre-commit
      chmod +x $NEWPATH.git/hooks/pre-commit
    else
      echo 'precommit hook already exists';
    fi

    if [ ! -d "$NEWPATH.git/hooks/pre-commit.d" ]; then
      echo 'create a precommit files folder';
      mkdir $NEWPATH.git/hooks/pre-commit.d
      chmod +x $NEWPATH.git/hooks/pre-commit.d
    fi

    if [ ! -f "$NEWPATH.git/hooks/pre-commit.d/pre-commit-theming" ]; then
      echo 'add our theming pre-commit file';
      cp -R sh/pre-commit-theming $NEWPATH.git/hooks/pre-commit.d/pre-commit-theming
      chmod +x $NEWPATH.git/hooks/pre-commit.d/pre-commit-theming
    fi

    echo 'copy or override themevars in .git hooks';
    cp -R sh/themevars.txt $NEWPATH.git/hooks/themevars

  fi

fi

exit 0
