#!/bin/bash

# Prerequisite.
[ -f "$0" ] || { echo -e "\e[91m" "Cannot run as dot command. Hit Control+c now." "\e[39m"; read; exit 1; }

# Parse arguments. Generated by parse-options.sh.
_new_arguments=()
while [[ $# -gt 0 ]]; do
    case "$1" in
        --help) help=1; shift ;;
        --version) version=1; shift ;;
        --domain=*) domain="${1#*=}"; shift ;;
        --domain) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then domain="$2"; shift; fi; shift ;;
        --domain-strict) domain_strict=1; shift ;;
        --fast) fast=1; shift ;;
        --project-name=*) project_name="${1#*=}"; shift ;;
        --project-name) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then project_name="$2"; shift; fi; shift ;;
        --project-parent-name=*) project_parent_name="${1#*=}"; shift ;;
        --project-parent-name) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then project_parent_name="$2"; shift; fi; shift ;;
        --root-sure) root_sure=1; shift ;;
        --timezone=*) timezone="${1#*=}"; shift ;;
        --timezone) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then timezone="$2"; shift; fi; shift ;;
        --[^-]*) shift ;;
        *) _new_arguments+=("$1"); shift ;;
    esac
done
set -- "${_new_arguments[@]}"
unset _new_arguments

# Functions.
[[ $(type -t GplDrupalSetupVariation1_printVersion) == function ]] || GplDrupalSetupVariation1_printVersion() {
    echo '0.1.4'
}
[[ $(type -t GplDrupalSetupVariation1_printHelp) == function ]] || GplDrupalSetupVariation1_printHelp() {
    cat << EOF
GPL Drupal Setup
Variation 1. Debian 11, Drupal 10, PHP 8.2.
Version `GplDrupalSetupVariation1_printVersion`

EOF
    cat << 'EOF'
Usage: gpl-drupal-setup-variation1.sh [options]

Options.
   --project-name
        Set the project name. This should be in machine name format.
   --project-parent-name
        Set the project parent name. The parent is not have to installed before.
   --timezone
        Set the timezone of this machine.
   --domain
        Set the domain.
   --domain-strict
        Prevent installing drupal inside directory sites/default.

Global Options.
   --fast
        No delay every subtask.
   --version
        Print version of this script.
   --help
        Show this help.
   --root-sure
        Bypass root checking.

Dependency:
   gpl-debian11-setup-basic.sh
   gpl-nginx-autoinstaller.sh
   gpl-mariadb-autoinstaller.sh
   gpl-php-autoinstaller.sh
   gpl-php-setup-adjust-cli-version.sh
   gpl-php-setup-drupal.sh
   gpl-composer-autoinstaller.sh
   gpl-drupal-autoinstaller-nginx-php-fpm.sh
   gpl-drupal-setup-wrapper-nginx-setup-drupal.sh
   gpl-drupal-setup-wrapper-nginx-setup-drupal.sh
   gpl-drupal-setup-dump-variables.sh
EOF
}

# Help and Version.
[ -n "$help" ] && { GplDrupalSetupVariation1_printHelp; exit 1; }
[ -n "$version" ] && { GplDrupalSetupVariation1_printVersion; exit 1; }

# Dependency.
while IFS= read -r line; do
    command -v "${line}" >/dev/null || { echo -e "\e[91m""Unable to proceed, ${line} command not found." "\e[39m"; exit 1; }
done <<< `GplDrupalSetupVariation1_printHelp | sed -n '/^Dependency:/,$p' | sed -n '2,/^$/p' | sed 's/^ *//g'`

# Common Functions.
[[ $(type -t red) == function ]] || red() { echo -ne "\e[91m" >&2; echo -n "$@" >&2; echo -ne "\e[39m" >&2; }
[[ $(type -t green) == function ]] || green() { echo -ne "\e[92m" >&2; echo -n "$@" >&2; echo -ne "\e[39m" >&2; }
[[ $(type -t yellow) == function ]] || yellow() { echo -ne "\e[93m" >&2; echo -n "$@" >&2; echo -ne "\e[39m" >&2; }
[[ $(type -t blue) == function ]] || blue() { echo -ne "\e[94m" >&2; echo -n "$@" >&2; echo -ne "\e[39m" >&2; }
[[ $(type -t magenta) == function ]] || magenta() { echo -ne "\e[95m" >&2; echo -n "$@" >&2; echo -ne "\e[39m" >&2; }
[[ $(type -t error) == function ]] || error() { echo -n "$INDENT" >&2; red "$@" >&2; echo >&2; }
[[ $(type -t success) == function ]] || success() { echo -n "$INDENT" >&2; green "$@" >&2; echo >&2; }
[[ $(type -t chapter) == function ]] || chapter() { echo -n "$INDENT" >&2; yellow "$@" >&2; echo >&2; }
[[ $(type -t title) == function ]] || title() { echo -n "$INDENT" >&2; blue "$@" >&2; echo >&2; }
[[ $(type -t code) == function ]] || code() { echo -n "$INDENT" >&2; magenta "$@" >&2; echo >&2; }
[[ $(type -t x) == function ]] || x() { echo >&2; exit 1; }
[[ $(type -t e) == function ]] || e() { echo -n "$INDENT" >&2; echo "$@" >&2; }
[[ $(type -t _) == function ]] || _() { echo -n "$INDENT" >&2; echo -n "$@" >&2; }
[[ $(type -t _,) == function ]] || _,() { echo -n "$@" >&2; }
[[ $(type -t _.) == function ]] || _.() { echo >&2; }
[[ $(type -t __) == function ]] || __() { echo -n "$INDENT" >&2; echo -n '    ' >&2; [ -n "$1" ] && echo "$@" >&2 || echo -n  >&2; }
[[ $(type -t ____) == function ]] || ____() { echo >&2; [ -n "$delay" ] && sleep "$delay"; }

#  Functions.
[[ $(type -t validateMachineName) == function ]] || validateMachineName() {
    local value="$1" _value
    local parameter="$2"
    if [[ $value = *" "* ]];then
        [ -n "$parameter" ]  && error "Variable $parameter can not contain space."
        return 1;
    fi
    _value=$(sed -E 's|[^a-zA-Z0-9]|_|g' <<< "$value" | sed -E 's|_+|_|g' )
    if [[ ! "$value" == "$_value" ]];then
        error "Variable $parameter can only contain alphanumeric and underscores."
        _ 'Suggest: '; yellow "$_value"; _.
        return 1
    fi
}

# Title.
title GPL Drupal Setup
_ 'Variation '; yellow 1; _, . Debian 11, Drupal 10, PHP 8.2. ; _.
_ 'Version '; yellow `GplDrupalSetupVariation1_printVersion`; _.
____

# Requirement, validate, and populate value.
chapter Dump variable.
php_version=8.2
code php_version="$php_version"
drupal_version=10
code drupal_version="$drupal_version"
until [[ -n "$project_name" ]];do
    read -p "Argument --project-name required: " project_name
done
code 'project_name="'$project_name'"'
if ! validateMachineName "$project_name" project_name;then x; fi
code 'project_parent_name="'$project_parent_name'"'
if [ -n "$project_parent_name" ];then
    if ! validateMachineName "$project_parent_name" project_parent_name;then x; fi
fi
code 'domain_strict="'$domain_strict'"'
code 'domain="'$domain'"'
delay=.5; [ -n "$fast" ] && unset delay
____

if [ -z "$root_sure" ];then
    chapter Mengecek akses root.
    if [[ "$EUID" -ne 0 ]]; then
        error This script needs to be run with superuser privileges.; x
    else
        __ Privileges.; root_sure=1
    fi
    ____
fi

_;_, ____________________________________________________________________;_.;_.;

INDENT+="    "
source $(command -v gpl-debian11-setup-basic.sh)
source $(command -v gpl-nginx-autoinstaller.sh)
source $(command -v gpl-mariadb-autoinstaller.sh)
source $(command -v gpl-php-autoinstaller.sh)
source $(command -v gpl-php-setup-adjust-cli-version.sh)
source $(command -v gpl-php-setup-drupal.sh)
source $(command -v gpl-composer-autoinstaller.sh)
source $(command -v gpl-drupal-autoinstaller-nginx-php-fpm.sh)
if [ -n "$domain" ];then
    _domain="$domain" # Backup variable.
    source $(command -v gpl-drupal-setup-wrapper-nginx-setup-drupal.sh) --domain="${_domain}"
    source $(command -v gpl-drupal-setup-wrapper-nginx-setup-drupal.sh) --subdomain="${_domain}" --domain="localhost"
    domain="$_domain" # Restore variable.
fi
source $(command -v gpl-drupal-setup-dump-variables.sh)
INDENT=${INDENT::-4}
_;_, ____________________________________________________________________;_.;_.;

# parse-options.sh \
# --without-end-options-double-dash \
# --compact \
# --clean \
# --no-hash-bang \
# --no-original-arguments \
# --no-error-invalid-options \
# --no-error-require-arguments << EOF | clip
# FLAG=(
# --fast
# --version
# --help
# --root-sure
# --domain-strict
# )
# VALUE=(
# --project-name
# --project-parent-name
# --timezone
# --domain
# )
# MULTIVALUE=(
# )
# FLAG_VALUE=(
# )
# CSV=(
# )
# EOF
# clear
