#!/bin/bash

# Prerequisite.
[ -f "$0" ] || { echo -e "\e[91m" "Cannot run as dot command. Hit Control+c now." "\e[39m"; read; exit 1; }

# Parse arguments. Generated by parse-options.sh.
_new_arguments=()
while [[ $# -gt 0 ]]; do
    case "$1" in
        --help) help=1; shift ;;
        --version) version=1; shift ;;
        --fast) fast=1; shift ;;
        --[^-]*) shift ;;
        *) _new_arguments+=("$1"); shift ;;
    esac
done
set -- "${_new_arguments[@]}"
unset _new_arguments

# Functions.
[[ $(type -t GplCertbotDigitaloceanAutoinstaller_printVersion) == function ]] || GplCertbotDigitaloceanAutoinstaller_printVersion() {
    echo '0.1.0'
}
[[ $(type -t GplCertbotDigitaloceanAutoinstaller_printHelp) == function ]] || GplCertbotDigitaloceanAutoinstaller_printHelp() {
    cat << EOF
GPL Certbot Autoinstaller
Variation Default
Version `GplCertbotDigitaloceanAutoinstaller_printVersion`

EOF
    cat << 'EOF'
Usage: gpl-certbot-autoinstaller.sh [options]

Global Options.
   --fast
        No delay every subtask.
   --version
        Print version of this script.
   --help
        Show this help.
   --root-sure
        Bypass root checking.
EOF
}

# Help and Version.
[ -n "$help" ] && { GplCertbotDigitaloceanAutoinstaller_printHelp; exit 1; }
[ -n "$version" ] && { GplCertbotDigitaloceanAutoinstaller_printVersion; exit 1; }

# Requirement.

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

# Functions.
# Functions.
[[ $(type -t downloadApplication) == function ]] || downloadApplication() {
    local aptnotfound=
    chapter Melakukan instalasi aplikasi "$@".
    [ -z "$aptinstalled" ] && aptinstalled=$(apt --installed list 2>/dev/null)
    for i in "$@"; do
        if ! grep -q "^$i/" <<< "$aptinstalled";then
            aptnotfound+=" $i"
        fi
    done
    if [ -n "$aptnotfound" ];then
        __ Menginstal.
        code apt install -y"$aptnotfound"
        apt install -y --no-install-recommends $aptnotfound
        aptinstalled=$(apt --installed list 2>/dev/null)
    else
        __ Aplikasi sudah terinstall seluruhnya.
    fi
}
[[ $(type -t validateApplication) == function ]] || validateApplication() {
    local aptnotfound=
    for i in "$@"; do
        if ! grep -q "^$i/" <<< "$aptinstalled";then
            aptnotfound+=" $i"
        fi
    done
    if [ -n "$aptnotfound" ];then
        __; red Gagal menginstall aplikasi:"$aptnotfound"; exit; _.
    fi
}

# Title.
title GPL Certbot Autoinstaller
_ 'Variation '; yellow Default; _.
_ 'Version '; yellow `GplCertbotDigitaloceanAutoinstaller_printVersion`; _.
____

# Require, validate, and populate value.
chapter Dump variable.
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

downloadApplication snapd
validateApplication snapd
____

command -v "snap" >/dev/null || {
    [ -f /etc/profile.d/apps-bin-path.sh ] && . /etc/profile.d/apps-bin-path.sh
}

chapter Mengecek apakah snap core installed.
notfound=
if grep '^core\s' <<< $(snap list core);then
    __ Snap core installed.
else
    __ Snap core not found.
    notfound=1
fi
____

if [ -n "$notfound" ];then
    chapter Menginstall snap core
    code snap install core
    code snap refresh core
    snap install core
    snap refresh core
    if grep '^core\s' <<< $(snap list core);then
        __; green Snap core installed.; _.
    else
        __; red Snap core not found.; x
    fi
    ____
fi

chapter Mengecek apakah snap certbot installed.
notfound=
if grep '^certbot\s' <<< $(snap list certbot);then
    __ Snap certbot installed.
else
    __ Snap certbot not found.
    notfound=1
fi
____

if [ -n "$notfound" ];then
    chapter Menginstall snap certbot
    code snap install --classic certbot
    snap install --classic certbot
    snap set certbot trust-plugin-with-root=ok
    if grep '^certbot\s' <<< $(snap list certbot);then
        __; green Snap certbot installed.; _.
    else
        __; red Snap certbot not found.; x
    fi
    ____
fi

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
# )
# VALUE=(
# )
# MULTIVALUE=(
# )
# FLAG_VALUE=(
# )
# CSV=(
# )
# EOF
# clear
