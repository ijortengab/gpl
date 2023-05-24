#!/bin/bash

# Prerequisite.
[ -f "$0" ] || { echo -e "\e[91m" "Cannot run as dot command. Hit Control+c now." "\e[39m"; read; exit 1; }

# Parse arguments. Generated by parse-options.sh.
_new_arguments=()
while [[ $# -gt 0 ]]; do
    case "$1" in
        --help) help=1; shift ;;
        --version) version=1; shift ;;
        --digitalocean) dns_authenticator=digitalocean; shift ;;
        --dns-authenticator=*) dns_authenticator="${1#*=}"; shift ;;
        --dns-authenticator) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then dns_authenticator="$2"; shift; fi; shift ;;
        --domain=*) domain="${1#*=}"; shift ;;
        --domain) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then domain="$2"; shift; fi; shift ;;
        --fast) fast=1; shift ;;
        --subdomain=*) subdomain="${1#*=}"; shift ;;
        --subdomain) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then subdomain="$2"; shift; fi; shift ;;
        --[^-]*) shift ;;
        *) _new_arguments+=("$1"); shift ;;
    esac
done
set -- "${_new_arguments[@]}"
unset _new_arguments

# Functions.
[[ $(type -t GplIspconfigSetupWrapperCertbotSetupNginx_printVersion) == function ]] || GplIspconfigSetupWrapperCertbotSetupNginx_printVersion() {
    echo '0.1.1'
}
[[ $(type -t GplIspconfigSetupWrapperCertbotSetupNginx_printHelp) == function ]] || GplIspconfigSetupWrapperCertbotSetupNginx_printHelp() {
    cat << EOF
GPL ISPConfig Setup
Variation Wrapper Certbot Setup Nginx
Version `GplIspconfigSetupWrapperCertbotSetupNginx_printVersion`

EOF
    cat << 'EOF'
Usage: gpl-ispconfig-setup-wrapper-certbot-setup-nginx.sh [options]

Options:
   --subdomain
        Set the subdomain if any.
   --domain
        Set the domain.
   --dns-authenticator
        Available value: digitalocean.

Global Options:
   --fast
        No delay every subtask.
   --version
        Print version of this script.
   --help
        Show this help.
   --root-sure
        Bypass root checking.

Environment Variables:
   MAILBOX_HOST
        Default to hostmaster
   TOKEN
        Default to $HOME/.$dns_authenticator-token.txt
   TOKEN_INI
        Default to $HOME/.$dns_authenticator-token.ini

Dependency:
   gpl-certbot-setup-nginx.sh
EOF
}

# Help and Version.
[ -n "$help" ] && { GplIspconfigSetupWrapperCertbotSetupNginx_printHelp; exit 1; }
[ -n "$version" ] && { GplIspconfigSetupWrapperCertbotSetupNginx_printVersion; exit 1; }

# Dependency.
while IFS= read -r line; do
    command -v "${line}" >/dev/null || { echo -e "\e[91m""Unable to proceed, ${line} command not found." "\e[39m"; exit 1; }
done <<< `GplIspconfigSetupWrapperCertbotSetupNginx_printHelp | sed -n '/^Dependency:/,$p' | sed -n '2,/^$/p' | sed 's/^ *//g'`

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
[[ $(type -t fileMustExists) == function ]] || fileMustExists() {
    # global used:
    # global modified:
    # function used: __, success, error, x
    if [ -f "$1" ];then
        __; green File '`'$(basename "$1")'`' ditemukan.; _.
    else
        __; red File '`'$(basename "$1")'`' tidak ditemukan.; x
    fi
}
[[ $(type -t isFileExists) == function ]] || isFileExists() {
    # global used:
    # global modified: found, notfound
    # function used: __
    found=
    notfound=
    if [ -f "$1" ];then
        __ File '`'$(basename "$1")'`' ditemukan.
        found=1
    else
        __ File '`'$(basename "$1")'`' tidak ditemukan.
        notfound=1
    fi
}

# Title.
title GPL ISPConfig Setup
_ 'Variation '; yellow Wrapper Certbot Setup Nginx; _.
_ 'Version '; yellow `GplIspconfigSetupWrapperCertbotSetupNginx_printVersion`; _.
____

# Require, validate, and populate value.
chapter Dump variable.
delay=.5; [ -n "$fast" ] && unset delay
MAILBOX_HOST=${MAILBOX_HOST:=hostmaster}
code 'MAILBOX_HOST="'$MAILBOX_HOST'"'
code 'subdomain="'$subdomain'"'
until [[ -n "$domain" ]];do
    read -p "Argument --domain required: " domain
done
code 'domain="'$domain'"'
if [ -n "$subdomain" ];then
    fqdn_project="${subdomain}.${domain}"
else
    fqdn_project="${domain}"
fi
code 'fqdn_project="'$fqdn_project'"'
case "$dns_authenticator" in
    digitalocean) ;;
    *) dns_authenticator=
esac
until [[ -n "$dns_authenticator" ]];do
    _ Available value:' '; yellow digitalocean.; _.
    read -p "Argument --dns-authenticator required: " dns_authenticator
    case "$dns_authenticator" in
        digitalocean) ;;
        *) dns_authenticator=
    esac
done
code 'dns_authenticator="'$dns_authenticator'"'
TOKEN=${TOKEN:=$HOME/.$dns_authenticator-token.txt}
code 'TOKEN="'$TOKEN'"'
TOKEN_INI=${TOKEN_INI:=$HOME/.$dns_authenticator-token.ini}
code 'TOKEN_INI="'$TOKEN_INI'"'
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

if [[ "$dns_authenticator" == 'digitalocean' ]]; then
    chapter Mengecek DNS Authenticator
    __ Menggunakan DNS Authenticator '`'digitalocean'`'
    ____

    chapter Mengecek Token
    fileMustExists "$TOKEN"
    digitalocean_token=$(<$TOKEN)
    __; code 'digitalocean_token="'$digitalocean_token'"'
    isFileExists "$TOKEN_INI"
    if [ -n "$notfound" ];then
        __ Membuat file "$TOKEN_INI"
        cat << EOF > "$TOKEN_INI"
dns_digitalocean_token = $digitalocean_token
EOF
    fi
    fileMustExists "$TOKEN_INI"
    if [[ $(stat "$TOKEN_INI" -c %a) == 600 ]];then
        __ File  '`'"$TOKEN_INI"'`' memiliki permission '`'600'`'.
    else
        __ File  '`'"$TOKEN_INI"'`' tidak memiliki permission '`'600'`'.
        tweak=1
    fi
    if [ -n "$tweak" ];then
        chmod 600 "$TOKEN_INI"
        if [[ $(stat --cached=never "$TOKEN_INI" -c %a) == 600 ]];then
            __; green File  '`'"$TOKEN_INI"'`' memiliki permission '`'600'`'.; _.
        else
            __; red File  '`'"$TOKEN_INI"'`' tidak memiliki permission '`'600'`'.; x
        fi
    fi
    ____

    chapter Prepare arguments.
    email=$(certbot show_account 2>/dev/null | grep -o -P 'Email contact: \K(.*)')
    if [ -n "$email" ];then
        __ Certbot account has found: "$email"
    else
        email="${MAILBOX_HOST}@${domain}"
    fi
    code 'email="'$email'"'
    domain="$fqdn_project"
    code 'domain="'$fqdn_project'"'
    _;_, ____________________________________________________________________;_.;_.;

    INDENT+="    ";
    source $(command -v "gpl-certbot-setup-nginx.sh") -- --dns-digitalocean --dns-digitalocean-credentials "$TOKEN_INI"
    INDENT=${INDENT::-4}
    _;_, ____________________________________________________________________;_.;_.;
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
# --domain
# --subdomain
# --dns-authenticator
# )
# MULTIVALUE=(
# )
# FLAG_VALUE=(
# )
# CSV=(
    # long:--digitalocean,parameter:dns_authenticator,type:flag,flag_option:true=digitalocean
# )
# EOF
# clear
