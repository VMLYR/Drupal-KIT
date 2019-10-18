# Abort if anything fails
set -e

# PROJECT_ROOT is passed from fin.
# The following variables are configured in the '.env' file: DOCROOT, VIRTUAL_HOST.
PROJECT_ROOT=${PROJECT_ROOT:-"$(realpath "$(dirname "${BASH_SOURCE[0]}")/../../")"}
DOCKSAL_PATH="${PROJECT_ROOT}/.docksal"
DOCKSAL_COMMANDS_PATH="${DOCKSAL_PATH}/commands"
DOCROOT_PATH="${PROJECT_ROOT}/${DOCROOT}"

# Check whether the process has a builder property.
RUN_AS_CI="false"
for var in "$@"
do
  case $var in
    ci)
      RUN_AS_CI="true"
      ;;
  esac
done

# Check whether the fin function exists.
FIN_EXISTS="false"
if command -vvv fin > /dev/null 2>&1; then
  FIN_EXISTS="true"
fi

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
  echo -e "\n${yellow}==========[${green} ${text} ${yellow}]==========${NC}"
}

# Print a step-header
step_header() {
  local text="$1"
  echo -e "\n${yellow}${section} ${green}> ${yellow}Step ${step} ${green}> ${NC}${text}"
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

# Runs docksal commands.
run_command () {
  "${DOCKSAL_COMMANDS_PATH}/$@"
}
