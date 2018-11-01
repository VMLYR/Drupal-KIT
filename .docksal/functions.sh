# Abort if anything fails
set -e

# PROJECT_ROOT is passed from fin.
# The following variables are configured in the '.env' file: DOCROOT, VIRTUAL_HOST.
DOCKSAL_PATH="${PROJECT_ROOT}/.docksal"
DOCROOT_PATH="${PROJECT_ROOT}/${DOCROOT}"
SITES_PATH="${DOCROOT_PATH}/sites"

# Check whether the process has a builder property.
BUILDER="false"
for var in "$@"
do
  case $var in
    builder)
      BUILDER="true"
      ;;
  esac
done

# Console colors
red='\033[0;31m'
green='\033[0;32m'
green_bg='\033[30;42m'
yellow='\033[1;33m'
NC='\033[0m'

# Print various colored responses
echo-red () { echo -e "${red}$1${NC}"; }
echo-green () { echo -e "${green}$1${NC}"; }
echo-green-bg () { echo -e "${green_bg}$1${NC}"; }
echo-yellow () { echo -e "${yellow}$1${NC}"; }

# Print a header
header() {
  local text="$1"
  section=$text
  echo -e "${yellow}==========[${green} ${text} ${yellow}]==========${NC}"
}

# Print a sub-header
subheader() {
  local text="$1"
  section=$text
  echo -e "${green}${text}${NC}"
}

# Print a step-header
step_header() {
  local text="$1"
  echo -e "${yellow}${section} ${green}> ${yellow}Step ${step} ${green}> ${NC}${text}"
  ((step++))
}

# Print a warning
warning() {
  echo -e "${yellow}WARNING${red}!${NC} $1";
}

# Print an error
error() {
  echo -e "${red}ERROR! $1${NC}";
  exit 1
}

# Checks if value exists in array.
in_array () {
  local e
  for e in "${@:2}"; do [[ "$e" == "$1" ]] && return 0; done
  return 1
}
