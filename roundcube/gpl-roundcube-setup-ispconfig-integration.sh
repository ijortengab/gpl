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
        --[^-]*) shift ;;
        *) _new_arguments+=("$1"); shift ;;
    esac
done
set -- "${_new_arguments[@]}"
unset _new_arguments

# Functions.
[[ $(type -t GplRoundcubeSetupIspconfigIntegration_printVersion) == function ]] || GplRoundcubeSetupIspconfigIntegration_printVersion() {
    echo '0.1.0'
}
[[ $(type -t GplRoundcubeSetupIspconfigIntegration_printHelp) == function ]] || GplRoundcubeSetupIspconfigIntegration_printHelp() {
    cat << EOF
GPL Roundcube Setup
Variation ISPConfig Integration
Version `GplRoundcubeSetupIspconfigIntegration_printVersion`

EOF
    cat << 'EOF'
Usage: gpl-roundcube-setup-ispconfig-integration.sh [options]

Global Options.
   --fast
        No delay every subtask.
   --version
        Print version of this script.
   --help
        Show this help.
   --root-sure
        Bypass root checking.

Environment Variables.
   ISPCONFIG_REMOTE_USER_ROUNDCUBE
        Default to roundcube
   ISPCONFIG_INSTALL_DIR
        Default to /usr/local/ispconfig
   ISPCONFIG_DB_USER_HOST
        Default to localhost
   ISPCONFIG_FQDN_LOCALHOST
        Default to ispconfig.localhost
EOF
}

# Help and Version.
[ -n "$help" ] && { GplRoundcubeSetupIspconfigIntegration_printHelp; exit 1; }
[ -n "$version" ] && { GplRoundcubeSetupIspconfigIntegration_printVersion; exit 1; }

# Requirement.
command -v "mysql" >/dev/null || { echo -e "\e[91m" "Unable to proceed, mysql command not found." "\e[39m"; exit 1; }
command -v "pwgen" >/dev/null || { echo -e "\e[91m" "Unable to proceed, pwgen command not found." "\e[39m"; exit 1; }
command -v "php" >/dev/null || { echo -e "\e[91m" "Unable to proceed, php command not found." "\e[39m"; exit 1; }
command -v "unzip" >/dev/null || { echo -e "\e[91m" "Unable to proceed, unzip command not found." "\e[39m"; exit 1; }
command -v "ispconfig.sh" >/dev/null || { echo -e "\e[91m" "Unable to proceed, ispconfig.sh command not found." "\e[39m"; exit 1; }

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
[[ $(type -t databaseCredentialIspconfig) == function ]] || databaseCredentialIspconfig() {
    if [ -f /usr/local/share/ispconfig/credential/database ];then
        local ISPCONFIG_DB_NAME ISPCONFIG_DB_USER ISPCONFIG_DB_USER_PASSWORD
        . /usr/local/share/ispconfig/credential/database
        ispconfig_db_name=$ISPCONFIG_DB_NAME
        ispconfig_db_user=$ISPCONFIG_DB_USER
        ispconfig_db_user_password=$ISPCONFIG_DB_USER_PASSWORD
    else
        ispconfig_db_user_password=$(pwgen -s 32 -1)
        mkdir -p /usr/local/share/ispconfig/credential
        cat << EOF > /usr/local/share/ispconfig/credential/database
ISPCONFIG_DB_USER_PASSWORD=$ispconfig_db_user_password
EOF
        chmod 0500 /usr/local/share/ispconfig/credential
        chmod 0400 /usr/local/share/ispconfig/credential/database
    fi
}
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
[[ $(type -t getRemoteUserIdIspconfigByRemoteUsername) == function ]] || getRemoteUserIdIspconfigByRemoteUsername() {
    # Get the remote_userid from table remote_user in ispconfig database.
    #
    # Globals:
    #   ispconfig_db_user, ispconfig_db_user_password,
    #   ispconfig_db_user_host, ispconfig_db_name
    #
    # Arguments:
    #   $1: Filter by remote_username.
    #
    # Output:
    #   Write remote_userid to stdout.
    local remote_username="$1"
    local sql="SELECT remote_userid FROM remote_user WHERE remote_username = '$remote_username';"
    local u="$ispconfig_db_user"
    local p="$ispconfig_db_user_password"
    local remote_userid=$(mysql \
        --defaults-extra-file=<(printf "[client]\nuser = %s\npassword = %s" "$u" "$p") \
        -h "$ispconfig_db_user_host" "$ispconfig_db_name" -r -N -s -e "$sql"
    )
    echo "$remote_userid"
}
[[ $(type -t insertRemoteUsernameIspconfig) == function ]] || insertRemoteUsernameIspconfig() {
    local remote_username="$1"
    local _remote_password="$2"
    local _remote_functions="$3"
    CONTENT=$(cat <<- EOF
require '${ispconfig_install_dir}/interface/lib/classes/auth.inc.php';
echo (new auth)->crypt_password('$_remote_password');
EOF
    )
    local remote_password=$(php -r "$CONTENT")
    local remote_functions=$(tr '\n' ';' <<< "$_remote_functions")
    local sql="INSERT INTO remote_user
(sys_userid, sys_groupid, sys_perm_user, sys_perm_group, sys_perm_other, remote_username, remote_password, remote_access, remote_ips, remote_functions)
VALUES
(1, 1, 'riud', 'riud', '', '$remote_username', '$remote_password', 'y', '127.0.0.1', '$remote_functions');"
    local u="$ispconfig_db_user"
    local p="$ispconfig_db_user_password"
    mysql --defaults-extra-file=<(printf "[client]\nuser = %s\npassword = %s" "$u" "$p") \
        -h "$ispconfig_db_user_host" "$ispconfig_db_name" -e "$sql"
    remote_userid=$(getRemoteUserIdIspconfigByRemoteUsername "$remote_username")
    if [ -n "$remote_userid" ];then
        return 0
    fi
    return 1
}
[[ $(type -t isRemoteUsernameIspconfigExist) == function ]] || isRemoteUsernameIspconfigExist() {
    # Insert the remote_username to table remote_user in ispconfig database.
    #
    # Globals:
    #   Used: ispconfig_install_dir
    #         ispconfig_db_user_host
    #         ispconfig_db_user
    #         ispconfig_db_name
    #         ispconfig_db_user_password
    #   Modified: remote_userid
    #
    # Arguments:
    #   $1: remote_username
    #   $2: remote_password
    #   $3: remote_functions
    #
    # Return:
    #   0 if exists.
    #   1 if not exists.
    local remote_username="$1"
    remote_userid=$(getRemoteUserIdIspconfigByRemoteUsername "$remote_username")
    if [ -n "$remote_userid" ];then
        return 0
    fi
    return 1
}
[[ $(type -t remoteUserCredentialIspconfig) == function ]] || remoteUserCredentialIspconfig() {
    # Check if the remote_username from table remote_user exists in ispconfig database.
    #
    # Globals:
    #   Modified: remote_userid
    #
    # Arguments:
    #   $1: remote_username to be checked.
    #
    # Return:
    #   0 if exists.
    #   1 if not exists.
    local user="$1"
    if [ -f /usr/local/share/ispconfig/credential/remote/$user ];then
        local ISPCONFIG_REMOTE_USER_PASSWORD
        . /usr/local/share/ispconfig/credential/remote/$user
        ispconfig_remote_user_password=$ISPCONFIG_REMOTE_USER_PASSWORD
    else
        ispconfig_remote_user_password=$(pwgen -s 32 -1)
        mkdir -p /usr/local/share/ispconfig/credential/remote
        cat << EOF > /usr/local/share/ispconfig/credential/remote/$user
ISPCONFIG_REMOTE_USER_PASSWORD=$ispconfig_remote_user_password
EOF
        chmod 0500 /usr/local/share/ispconfig/credential
        chmod 0500 /usr/local/share/ispconfig/credential/remote
        chmod 0400 /usr/local/share/ispconfig/credential/remote/$user
    fi
}

# Title.
title GPL Roundcube Setup
_ 'Variation '; yellow ISPConfig Integration; _.
_ 'Version '; yellow `GplRoundcubeSetupIspconfigIntegration_printVersion`; _.
____

# Requirement, validate, and populate value.
chapter Dump variable.
ISPCONFIG_REMOTE_USER_ROUNDCUBE=${ISPCONFIG_REMOTE_USER_ROUNDCUBE:=roundcube}
code 'ISPCONFIG_REMOTE_USER_ROUNDCUBE="'$ISPCONFIG_REMOTE_USER_ROUNDCUBE'"'
ISPCONFIG_INSTALL_DIR=${ISPCONFIG_INSTALL_DIR:=/usr/local/ispconfig}
code 'ISPCONFIG_INSTALL_DIR="'$ISPCONFIG_INSTALL_DIR'"'
ISPCONFIG_DB_USER_HOST=${ISPCONFIG_DB_USER_HOST:=localhost}
code 'ISPCONFIG_DB_USER_HOST="'$ISPCONFIG_DB_USER_HOST'"'
ISPCONFIG_FQDN_LOCALHOST=${ISPCONFIG_FQDN_LOCALHOST:=ispconfig.localhost}
code 'ISPCONFIG_FQDN_LOCALHOST="'$ISPCONFIG_FQDN_LOCALHOST'"'
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

chapter Dump variable of ISPConfig Credential
databaseCredentialIspconfig
ispconfig_db_user_host="$ISPCONFIG_DB_USER_HOST"
code ispconfig_db_user="$ispconfig_db_user"
code ispconfig_db_user_host="$ispconfig_db_user_host"
code ispconfig_db_user_password="$ispconfig_db_user_password"
code ispconfig_db_name="$ispconfig_db_name"
_ispconfig_db_user=$(php -r "include '$ISPCONFIG_INSTALL_DIR/interface/lib/config.inc.php';echo DB_USER;")
_ispconfig_db_user_password=$(php -r "include '$ISPCONFIG_INSTALL_DIR/interface/lib/config.inc.php';echo DB_PASSWORD;")
_ispconfig_db_user_host=$(php -r "include '$ISPCONFIG_INSTALL_DIR/interface/lib/config.inc.php';echo DB_HOST;")
_ispconfig_db_name=$(php -r "include '$ISPCONFIG_INSTALL_DIR/interface/lib/config.inc.php';echo DB_DATABASE;")
has_different=
for string in ispconfig_db_name ispconfig_db_user ispconfig_db_user_host ispconfig_db_user_password
do
    parameter=$string
    parameter_from_shell=${!string}
    string="_${string}"
    parameter_from_php=${!string}
    if [[ ! "$parameter_from_shell" == "$parameter_from_php" ]];then
        __ Different from PHP Scripts found.
        __; echo -n Value of '`'"$parameter"'`' from shell:' '
        echo "$parameter_from_shell"
        __; echo -n Value of '`'"$parameter"'`' from PHP script:' '
        echo "$parameter_from_php"
        has_different=1
    fi
done
if [ -n "$has_different" ];then
    __; red Terdapat perbedaan value.; x
fi
____

chapter Mengecek Remote User ISPConfig '"'$ISPCONFIG_REMOTE_USER_ROUNDCUBE'"'
notfound=
if isRemoteUsernameIspconfigExist "$ISPCONFIG_REMOTE_USER_ROUNDCUBE" ;then
    __ Found '(remote_userid:'$remote_userid')'.
else
    __ Not Found.
    notfound=1
fi
____

if [ -n "$notfound" ];then
    chapter Insert Remote User ISPConfig '"'$ISPCONFIG_REMOTE_USER_ROUNDCUBE'"'
    functions='server_get,server_config_set,get_function_list,client_templates_get_all,server_get_serverid_by_ip,server_ip_get,server_ip_add,server_ip_update,server_ip_delete,system_config_set,system_config_get,config_value_get,config_value_add,config_value_update,config_value_replace,config_value_delete
client_get_all,client_get,client_add,client_update,client_delete,client_get_sites_by_user,client_get_by_username,client_get_by_customer_no,client_change_password,client_get_id,client_delete_everything,client_get_emailcontact
mail_user_get,mail_user_add,mail_user_update,mail_user_delete
mail_alias_get,mail_alias_add,mail_alias_update,mail_alias_delete
mail_forward_get,mail_forward_add,mail_forward_update,mail_forward_delete
mail_spamfilter_user_get,mail_spamfilter_user_add,mail_spamfilter_user_update,mail_spamfilter_user_delete
mail_policy_get,mail_policy_add,mail_policy_update,mail_policy_delete
mail_fetchmail_get,mail_fetchmail_add,mail_fetchmail_update,mail_fetchmail_delete
mail_spamfilter_whitelist_get,mail_spamfilter_whitelist_add,mail_spamfilter_whitelist_update,mail_spamfilter_whitelist_delete
mail_spamfilter_blacklist_get,mail_spamfilter_blacklist_add,mail_spamfilter_blacklist_update,mail_spamfilter_blacklist_delete
mail_user_filter_get,mail_user_filter_add,mail_user_filter_update,mail_user_filter_delete'
    remoteUserCredentialIspconfig $ISPCONFIG_REMOTE_USER_ROUNDCUBE
    if [[ -z "$ispconfig_remote_user_password" ]];then
        __; red Informasi credentials tidak lengkap: '`'/usr/local/share/ispconfig/credential/remote/$ISPCONFIG_REMOTE_USER_ROUNDCUBE'`'.; x
    else
        code ispconfig_remote_user_password="$ispconfig_remote_user_password"
    fi
    # Populate Variable.
    . ispconfig.sh export >/dev/null
    code ispconfig_install_dir="$ispconfig_install_dir"
    if insertRemoteUsernameIspconfig  "$ISPCONFIG_REMOTE_USER_ROUNDCUBE" "$ispconfig_remote_user_password" "$functions" ;then
        __; green Remote username "$ISPCONFIG_REMOTE_USER_ROUNDCUBE" created '(remote_userid:'$remote_userid')'.; _.
    else
        __; red Remote username "$ISPCONFIG_REMOTE_USER_ROUNDCUBE" failed to create.; x
    fi
    ____
fi

# Populate Variable.
chapter Dump variable of '`'ispconfig.sh export'`' command
. ispconfig.sh export >/dev/null
code phpmyadmin_install_dir="$phpmyadmin_install_dir"
code roundcube_install_dir="$roundcube_install_dir"
code ispconfig_install_dir="$ispconfig_install_dir"
code scripts_dir="$scripts_dir"
____

filename_path=$roundcube_install_dir/plugins/ispconfig3_account/config/config.inc.php
filename=$(basename "$filename_path")
chapter Mengecek existing '`'$filename'`'
__; code filename_path=$filename_path
isFileExists "$filename_path"
____

if [ -n "$notfound" ];then
    chapter Menginstall Plugin Integrasi Roundcube dan ISPConfig
    __ Mendownload Plugin
    cd /tmp
    if [ ! -f /tmp/ispconfig3_roundcube-master.zip ];then
        wget https://github.com/w2c/ispconfig3_roundcube/archive/master.zip -O ispconfig3_roundcube-master.zip
    fi
    fileMustExists /tmp/ispconfig3_roundcube-master.zip
    __ Mengextract Plugin
    unzip -u -qq ispconfig3_roundcube-master.zip
    cd ./ispconfig3_roundcube-master
    cp -r ./ispconfig3_* $roundcube_install_dir/plugins/
    cd $roundcube_install_dir/plugins/ispconfig3_account/config
    cp config.inc.php.dist config.inc.php
    fileMustExists "$filename_path"
    ____
fi

chapter Dump variable of ispconfig remote user
remoteUserCredentialIspconfig $ISPCONFIG_REMOTE_USER_ROUNDCUBE
if [[ -z "$ispconfig_remote_user_password" ]];then
    __; red Informasi credentials tidak lengkap: '`'/usr/local/share/ispconfig/credential/remote/$ISPCONFIG_REMOTE_USER_ROUNDCUBE'`'.; x
else
    __; code ISPCONFIG_REMOTE_USER_ROUNDCUBE="$ISPCONFIG_REMOTE_USER_ROUNDCUBE"
    __; code ispconfig_remote_user_password="$ispconfig_remote_user_password"
fi
____

php=$(cat <<'EOF'
$args = $_SERVER['argv'];
$mode = $args[1];
$file = $args[2];
// die('op');
$array = unserialize($args[3]);
include($file);
$config = isset($config) ? $config : [];
//$result = array_diff_assoc($array, $config);
//var_dump($config);
//var_dump($result);
$is_different = !empty(array_diff_assoc($array, $config));
//$config = array_replace_recursive($config, $array);
//var_dump($config);
//var_export($config);
switch ($mode) {
    case 'is_different':
        $is_different ? exit(0) : exit(1);
        break;
    case 'replace':
        if ($is_different) {
            $config = array_replace_recursive($config, $array);
            $content = '$config = '.var_export($config, true).';'.PHP_EOL;
            $content = <<< EOF
<?php
$content
EOF;
            file_put_contents($file, $content);
        }
        break;
}
EOF
)
reference="$(php -r "echo serialize([
    'identity_limit' => false,
    'remote_soap_user' => '$ISPCONFIG_REMOTE_USER_ROUNDCUBE',
    'remote_soap_pass' => '$ispconfig_remote_user_password',
    'soap_url' => 'http://${ISPCONFIG_FQDN_LOCALHOST}/remote/',
    'soap_validate_cert' => false,
]);")"
chapter Mengecek variable pada script '`'$filename'`'
is_different=
if php -r "$php" is_different \
    "$filename_path" \
    "$reference";then
    is_different=1
    __ Diperlukan modifikasi file '`'$filename'`'.
else
    __ File '`'$filename'`' tidak ada perubahan.
fi
____

if [ -n "$is_different" ];then
    chapter Memodifikasi file '`'$filename'`'.
    __ Backup file "$filename_path"
    backupFile copy "$filename_path"
    php -r "$php" replace \
        "$filename_path" \
        "$reference"
    if php -r "$php" is_different \
    "$filename_path" \
    "$reference";then
        __; red Modifikasi file '`'$filename'`' gagal.; x
    else
        __; green Modifikasi file '`'$filename'`' berhasil.; _.
    fi
    ____
fi

filename_path=$roundcube_install_dir/config/config.inc.php
filename=$(basename "$filename_path")
chapter Mengecek existing '`'$filename'`'
__; code filename_path=$filename_path
isFileExists "$filename_path"
____

#@todo, ganti semua replace menjadi save.
php=$(cat <<'EOF'
$args = $_SERVER['argv'];
$mode = $args[1];
$file = $args[2];
// die('op');
$array = unserialize($args[3]);
//var_dump($array);
// die('op');
include($file);
$config = isset($config) ? $config : [];
$is_different = false;
$merge=[];
$replace=[];
// Compare plugins.
$plugins = isset($config['plugins']) ? $config['plugins'] : [];
$arg_plugins = isset($array['plugins']) ? $array['plugins'] : [];
$result = array_diff($arg_plugins, $plugins);
if (!empty($result)) {
    $is_different = true;
    $merge['plugins'] = $result;
}
// Compare identity_select_headers.
$identity_select_headers = isset($config['identity_select_headers']) ? $config['identity_select_headers'] : [];
$arg_identity_select_headers = isset($array['identity_select_headers']) ? $array['identity_select_headers'] : [];
$result = array_diff($arg_identity_select_headers, $identity_select_headers);
if (!empty($result)) {
    $is_different = true;
    $merge['identity_select_headers'] = $result;
}
switch ($mode) {
    case 'is_different':
        $is_different ? exit(0) : exit(1);
        break;
    case 'save':
        if ($is_different && $merge) {
            $config = array_merge_recursive($config, $merge);
            $content = '$config = '.var_export($config, true).';'.PHP_EOL;
            $content = <<< EOF
<?php
$content
EOF;
            file_put_contents($file, $content);
        }
        break;
}
EOF
)

chapter Mengecek variable pada script '`'$filename'`'
reference="$(php -r "echo serialize([
    'plugins' => [
        'ispconfig3_account',
        'ispconfig3_autoreply',
        'ispconfig3_pass',
        'ispconfig3_filter',
        'ispconfig3_forward',
        'ispconfig3_wblist',
        'identity_select',
    ],
    'identity_select_headers' => ['To'],
]);")"
is_different=
if php -r "$php" is_different \
    "$filename_path" \
    "$reference";then
    is_different=1
    __ Diperlukan modifikasi file '`'$filename'`'.
else
    __ File '`'$filename'`' tidak ada perubahan.
fi
____

if [ -n "$is_different" ];then
    chapter Memodifikasi file '`'$filename'`'.
    __ Backup file "$filename_path"
    backupFile copy "$filename_path"
    php -r "$php" save \
        "$filename_path" \
        "$reference"
    if php -r "$php" is_different \
    "$filename_path" \
    "$reference";then
        __; red Modifikasi file '`'$filename'`' gagal.; x
    else
        __; green Modifikasi file '`'$filename'`' berhasil.; _.
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
# FLAG_VALUE=(
# )
# EOF
# clear
