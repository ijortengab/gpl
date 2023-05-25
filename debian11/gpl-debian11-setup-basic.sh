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
[[ $(type -t GplDebian11SetupBasic_printVersion) == function ]] || GplDebian11SetupBasic_printVersion() {
    echo '0.1.4'
}
[[ $(type -t GplDebian11SetupBasic_printHelp) == function ]] || GplDebian11SetupBasic_printHelp() {
    cat << EOF
GPL Debian 11 Setup Server
Variation Basic
Version `GplDebian11SetupBasic_printVersion`

EOF
    cat << 'EOF'
Usage: gpl-debian11-setup-basic.sh [options]

Options:
   --timezone
        Set the timezone of this machine.

Global Options:
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
[ -n "$help" ] && { GplDebian11SetupBasic_printHelp; exit 1; }
[ -n "$version" ] && { GplDebian11SetupBasic_printVersion; exit 1; }

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
[[ $(type -t backupFile) == function ]] || backupFile() {
    local mode="$1"
    local oldpath="$2" i newpath
    i=1
    newpath="${oldpath}.${i}"
    if [ -f "$newpath" ]; then
        let i++
        newpath="${oldpath}.${i}"
        while [ -f "$newpath" ] ; do
            let i++
            newpath="${oldpath}.${i}"
        done
    fi
    case $mode in
        move)
            mv "$oldpath" "$newpath" ;;
        copy)
            local user=$(stat -c "%U" "$oldpath")
            local group=$(stat -c "%G" "$oldpath")
            cp "$oldpath" "$newpath"
            chown ${user}:${group} "$newpath"
    esac
}

# Title.
title GPL Debian 11 Setup Server
_ 'Variation '; yellow Basic; _.
_ 'Version '; yellow `GplDebian11SetupBasic_printVersion`; _.
____

# Requirement, validate, and populate value.
chapter Dump variable.
delay=.5; [ -n "$fast" ] && unset delay
if [ -f /etc/os-release ];then
    . /etc/os-release
fi
if [ -z "$ID" ];then
    error OS not supported; x;
fi
code 'ID="'$ID'"'
code 'VERSION_ID="'$VERSION_ID'"'
case $ID in
    debian)
        case "$VERSION_ID" in
            11)
                repository_required=$(cat <<EOF
deb http://deb.debian.org/debian bullseye main
deb-src http://deb.debian.org/debian bullseye main
deb http://security.debian.org/debian-security bullseye-security main
deb-src http://security.debian.org/debian-security bullseye-security main
deb http://deb.debian.org/debian bullseye-updates main
deb-src http://deb.debian.org/debian bullseye-updates main
EOF
)
                application=
                application+=' lsb-release apt-transport-https ca-certificates software-properties-common'
                application+=' sudo patch curl wget net-tools apache2-utils openssl rkhunter'
                application+=' binutils dnsutils pwgen daemon apt-listchanges lrzip p7zip'
                application+=' p7zip-full zip unzip bzip2 lzop arj nomarch cabextract'
                application+=' libnet-ident-perl libnet-dns-perl libauthen-sasl-perl'
                application+=' libdbd-mysql-perl libio-string-perl libio-socket-ssl-perl'
            ;;
            *) error OS "$ID" version "$VERSION_ID" not supported; x;
        esac
        ;;
    *) error OS "$ID" not supported; x;
esac
code 'timezone="'$timezone'"'
if [ -n "$timezone" ];then
    if [ ! -f /usr/share/zoneinfo/$timezone ];then
        __ Timezone is not valid.
        timezone=
        code 'timezone="'$timezone'"'
    fi
fi
____

if [ -z "$root_sure" ];then
    chapter Mengecek akses root.
    if [[ "$EUID" -ne 0 ]]; then
        error This script needs to be run with superuser privileges.; x
    else
        __ Privileges.
    fi
    ____
fi

chapter Mengecek '$PATH'
code PATH="$PATH"
notfound=
if grep -q '/usr/sbin' <<< "$PATH";then
  __ '$PATH' sudah lengkap.
else
  __ '$PATH' belum lengkap.
  notfound=1
fi

if [[ -n "$notfound" ]];then
    chapter Memperbaiki '$PATH'
    PATH=/usr/local/sbin:/usr/sbin:/sbin:$PATH
    if grep -q '/usr/sbin' <<< "$PATH";then
      __; green '$PATH' sudah lengkap.; _.
      __; code PATH="$PATH"
    else
      __; red '$PATH' belum lengkap.; x
    fi
fi
____

chapter Mengecek shell default
is_dash=
if [[ $(realpath /bin/sh) =~ dash$ ]];then
    __ '`'sh'`' command is linked to dash.
    is_dash=1
else
    __ '`'sh'`' command is linked to $(realpath /bin/sh).
fi
____

if [[ -n "$is_dash" ]];then
    chapter Disable dash
    __ '`sh` command link to dash. Disable now.'
    echo "dash dash/sh boolean false" | debconf-set-selections
    DEBIAN_FRONTEND=noninteractive dpkg-reconfigure dash
    if [[ $(realpath /bin/sh) =~ dash$ ]];then
        __; red '`'sh'`' command link to dash.; _.
    else
        __; green '`'sh'`' command link to $(realpath /bin/sh).; _.
        is_dash=
    fi
    ____
fi

if [[ -n "$is_dash" ]];then
    chapter Disable dash again.
    __ '`sh` command link to dash. Override now.'
    path=$(command -v sh)
    cd $(dirname $path)
    ln -sf bash sh
    if [[ $(realpath /bin/sh) =~ dash$ ]];then
        __; red '`'sh'`' command link to dash.; x
    else
        __; green '`'sh'`' command link to $(realpath /bin/sh).; _.
    fi
    ____
fi

adjust=
if [ -n "$timezone" ];then
    chapter Mengecek timezone.
    current_timezone=$(realpath /etc/localtime | cut -d/ -f5,6)
    if [[ "$current_timezone" == "$timezone" ]];then
        __ Timezone is match: ${current_timezone}
    else
        __ Timezone is different: ${current_timezone}
        adjust=1
    fi
    ____
fi

if [[ -n "$adjust" ]];then
    chapter Adjust timezone.
    __ Backup file '`'/etc/localtime'`'
    backupFile move /etc/localtime
    __; code ln -s /usr/share/zoneinfo/$timezone /etc/localtime
    ln -s /usr/share/zoneinfo/$timezone /etc/localtime
    current_timezone=$(realpath /etc/localtime | cut -d/ -f5,6)
    if [[ "$current_timezone" == "$timezone" ]];then
        __; green Timezone is match: ${current_timezone}; _.
    else
        __; red Timezone is different: ${current_timezone}; x
    fi
    ____
fi

chapter Update Repository

repository_required='# Trigger initialize update.'$'\n'"$repository_required"
while IFS= read -r string; do
    if [[ -n $(grep "# $string" /etc/apt/sources.list) ]];then
        sed -i 's,^# '"$string"','"$string"',' /etc/apt/sources.list
        update_now=1
    elif [[ -z $(grep "$string" /etc/apt/sources.list) ]];then
        CONTENT+="$string"$'\n'
        update_now=1
    fi
done <<< "$repository_required"

[ -z "$CONTENT" ] || {
    CONTENT=$'\n'"# Customize. ${NOW}"$'\n'"$CONTENT"
    echo "$CONTENT" >> /etc/apt/sources.list
}
if [[ $update_now == 1 ]];then
    code apt -y update
    apt -y update
else
    __ Repository updated.
fi
____

downloadApplication $application
validateApplication $application
____

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
# --timezone
# )
# FLAG_VALUE=(
# )
# EOF
# clear
