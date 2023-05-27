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
        --filename=*) filename="${1#*=}"; shift ;;
        --filename) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then filename="$2"; shift; fi; shift ;;
        --php-version=*) php_version="${1#*=}"; shift ;;
        --php-version) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then php_version="$2"; shift; fi; shift ;;
        --root=*) root="${1#*=}"; shift ;;
        --root) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then root="$2"; shift; fi; shift ;;
        --root-sure) root_sure=1; shift ;;
        --server-name=*) server_name+=("${1#*=}"); shift ;;
        --server-name) if [[ ! $2 == "" && ! $2 =~ ^-[^-] ]]; then server_name+=("$2"); shift; fi; shift ;;
        --[^-]*) shift ;;
        *) _new_arguments+=("$1"); shift ;;
    esac
done
set -- "${_new_arguments[@]}"
unset _new_arguments

# Functions.
[[ $(type -t GplNginxSetupDrupal_printVersion) == function ]] || GplNginxSetupDrupal_printVersion() {
    echo '0.1.1'
}
[[ $(type -t GplNginxSetupDrupal_printHelp) == function ]] || GplNginxSetupDrupal_printHelp() {
    cat << EOF
GPL Nginx Setup
Variation Drupal
Version `GplNginxSetupDrupal_printVersion`

Reference:
https://www.drupal.org/project/drupal/issues/2937161
https://www.drupal.org/files/issues/drupal-nginx-conf.patch

EOF
    cat << 'EOF'
Usage: gpl-nginx-setup-drupal.sh [options]

Options.
   --filename
        Set the filename to created inside /etc/nginx/sites-available directory.
   --root
        Set the value of root directive.
   --php-version
        Set the version of PHP FPM.
   --server-name
        Set the value of server_name directive. Multivalue.

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
[ -n "$help" ] && { GplNginxSetupDrupal_printHelp; exit 1; }
[ -n "$version" ] && { GplNginxSetupDrupal_printVersion; exit 1; }

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
title GPL Nginx Setup
_ 'Variation '; yellow Drupal; _.
_ 'Version '; yellow `GplNginxSetupDrupal_printVersion`; _.
____

# Require, validate, and populate value.
chapter Dump variable.
until [[ -n "$filename" ]];do
    read -p "Argument --filename required: " filename
done
code 'filename="'$filename'"'
until [[ -n "$root" ]];do
    read -p "Argument --root required: " root
done
code 'root="'$root'"'
until [[ ${#server_name[@]} -gt 0 ]];do
    read -p "Argument --server-name required: " _server_name
    [ -n "$_server_name" ] && server_name+=("$_server_name")
done
code 'server_name=('"${server_name[@]}"')'
code 'php_version="'$php_version'"'
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

file_config="/etc/nginx/sites-available/$filename"
create_new=
chapter Memeriksa file konfigurasi.
if [ -f "$file_config" ];then
    __ File ditemukan: '`'$file_config'`'.
    string="unix:/run/php/php${php_version}-fpm.sock"
    string_quoted=$(sed "s/\./\\\./g" <<< "$string")
    if grep -q -E "^\s*fastcgi_pass\s+.*$string_quoted.*;\s*$" "$file_config";then
        __ Directive fastcgi_pass '`'$string'`' sudah terdapat pada file config.
    else
        __ Directive fastcgi_pass '`'$string'`' belum terdapat pada file config.
        create_new=1
    fi
    string="$root"
    string_quoted=$(sed "s/\./\\\./g" <<< "$string")
    if grep -q -E "^\s*root\s+.*$string_quoted.*;\s*$" "$file_config";then
        __ Directive root '`'$string'`' sudah terdapat pada file config.
    else
        __ Directive root '`'$string'`' belum terdapat pada file config.
        create_new=1
    fi
    for string in "${server_name[@]}" ;do
        string_quoted=$(sed "s/\./\\\./g" <<< "$string")
        if grep -q -E "^\s*server_name\s+.*$string_quoted.*;\s*$" "$file_config";then
            __ Directive server_name '`'$string'`' sudah terdapat pada file config.
        else
            __ Directive server_name '`'$string'`' belum terdapat pada file config.
            create_new=1
        fi
    done
else
    __ File tidak ditemukan: '`'$file_config'`'.
    create_new=1
fi
____

if [ -n "$create_new" ];then
    chapter Membuat file konfigurasi $file_config.
    if [ -f "$file_config" ];then
        __ Backup file "$file_config".
        backupFile move "$file_config"
    fi
    __ Membuat file "$file_config".
    cat <<'EOF' > "$file_config"
server {
    listen 80;
    listen [::]:80;
    root __ROOT__;
    index index.php;
    server_name ;
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }
    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php__PHP_VERSION__-fpm.sock;
    }
}
EOF
    sed -i "s|__ROOT__|${root}|g" "$file_config"
    sed -i "s|__PHP_VERSION__|${php_version}|g" "$file_config"
    cd /etc/nginx/sites-enabled/
    ln -sf ../sites-available/$filename
    cd - >/dev/null
    for string in "${server_name[@]}" ;do
        sed -i -E "s/server_name([^;]+);/server_name\1 "${string}";/" "$file_config"
    done
    sed -i -E "s/server_name\s{2}/server_name /" "$file_config"
    __; _, Mengecek link di direktori sites-enabled:' ';
    if [ -L /etc/nginx/sites-enabled/$filename ];then
        _, Link sudah ada.; _.
    else
        _ Membuat link.' '
        cd /etc/nginx/sites-enabled/
        ln -sf ../sites-available/$filename
        cd - >/dev/null
        if [ -L /etc/nginx/sites-enabled/$filename ];then
            success Berhasil dibuat.
        else
            error Gagal dibuat.; x
        fi
    fi
    ____

    chapter Reload nginx configuration.
    if nginx -t 2> /dev/null;then
        code nginx -s reload
        nginx -s reload; sleep .5
    else
        error Terjadi kesalahan konfigurasi nginx. Gagal reload nginx.; x
    fi
    ____
fi

chapter Memeriksa ulang file konfigurasi.
string="unix:/run/php/php${php_version}-fpm.sock"
string_quoted=$(sed "s/\./\\\./g" <<< "$string")
if grep -q -E "^\s*fastcgi_pass\s+.*$string_quoted.*;\s*$" "$file_config";then
    __; green Directive fastcgi_pass '`'$string'`' sudah terdapat pada file config.; _.
else
    __; red Directive fastcgi_pass '`'$string'`' belum terdapat pada file config.; x
fi
string="$root"
string_quoted=$(sed "s/\./\\\./g" <<< "$string")
if grep -q -E "^\s*root\s+.*$string_quoted.*;\s*$" "$file_config";then
    __; green Directive root "$string" sudah terdapat pada file config.; _.
    reload=1
else
    __; red Directive root "$string" belum terdapat pada file config.; x
fi
for string in "${server_name[@]}" ;do
    string_quoted=$(sed "s/\./\\\./g" <<< "$string")
    if grep -q -E "^\s*server_name\s+.*$string_quoted.*;\s*$" "$file_config";then
        __; green Directive server_name "$string" sudah terdapat pada file config.; _.
    else
        __; red Directive server_name "$string" belum terdapat pada file config.; x
    fi
done
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
# --root
# --php-version
# --filename
# )
# MULTIVALUE=(
# --server-name
# )
# FLAG_VALUE=(
# )
# EOF
# clear