#!/bin/bash

# Prerequisite.
[ -f "$0" ] || { echo -e "\e[91m" "Cannot run as dot command. Hit Control+c now." "\e[39m"; read; exit 1; }

# Parse arguments. Generated by parse-options.sh
_new_arguments=()
while [[ $# -gt 0 ]]; do
    case "$1" in
        --help) help=1; shift ;;
        --version) version=1; shift ;;
        --binary-directory-exists-sure) binary_directory_exists_sure=1; shift ;;
        --fast) fast=1; shift ;;
        --root-sure) root_sure=1; shift ;;
        --[^-]*) shift ;;
        *) _new_arguments+=("$1"); shift ;;
    esac
done
set -- "${_new_arguments[@]}"
unset _new_arguments

# Command.
command="$1"

# Functions.
[[ $(type -t GplDependencyManager_printVersion) == function ]] || GplDependencyManager_printVersion() {
    echo '0.1.2'
}
[[ $(type -t GplDependencyManager_printHelp) == function ]] || GplDependencyManager_printHelp() {
    cat << EOF
GPL Dependency Manager
Variation Default
Version `GplDependencyManager_printVersion`

EOF
    cat << 'EOF'
Usage: gpl-dependency-manager.sh <file>

Global Options.
   --fast
        No delay every subtask.
   --version
        Print version of this script.
   --help
        Show this help.
   --binary-directory-exists-sure
        Bypass binary directory checking.
   --root-sure
        Bypass root checking.

Environment Variables:
   BINARY_DIRECTORY
        Default to $HOME/bin
EOF
}

# Help and Version.
[ -n "$help" ] && { GplDependencyManager_printHelp; exit 1; }
[ -n "$version" ] && { GplDependencyManager_printVersion; exit 1; }

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
[[ $(type -t ArrayDiff) == function ]] || ArrayDiff() {
    # Computes the difference of arrays.
    #
    # Globals:
    #   Modified: _return
    #
    # Arguments:
    #   1 = Parameter of the array to compare from.
    #   2 = Parameter of the array to compare against.
    #
    # Returns:
    #   None
    #
    # Example:
    #   ```
    #   my=("cherry" "manggo" "blackberry" "manggo" "blackberry")
    #   yours=("cherry" "blackberry")
    #   ArrayDiff my[@] yours[@]
    #   # Get result in variable `$_return`.
    #   # _return=("manggo" "manggo")
    #   ```
    local e
    local source=("${!1}")
    local reference=("${!2}")
    _return=()
    # inArray is alternative of ArraySearch.
    inArray () {
        local e match="$1"
        shift
        for e; do [[ "$e" == "$match" ]] && return 0; done
        return 1
    }
    if [[ "${#reference[@]}" -gt 0 ]];then
        for e in "${source[@]}";do
            if ! inArray "$e" "${reference[@]}";then
                _return+=("$e")
            fi
        done
    else
        _return=("${source[@]}")
    fi
}
[[ $(type -t ArrayUnique) == function ]] || ArrayUnique() {
    # Removes duplicate values from an array.
    #
    # Globals:
    #   Modified: _return
    #
    # Arguments:
    #   1 = Parameter of the input array.
    #
    # Returns:
    #   None
    #
    # Example:
    #   ```
    #   my=("cherry" "manggo" "blackberry" "manggo" "blackberry")
    #   ArrayUnique my[@]
    #   # Get result in variable `$_return`.
    #   # _return=("cherry" "manggo" "blackberry")
    #   ```
    local e source=("${!1}")
    # inArray is alternative of ArraySearch.
    inArray () {
        local e match="$1"
        shift
        for e; do [[ "$e" == "$match" ]] && return 0; done
        return 1
    }
    _return=()
    for e in "${source[@]}";do
        if ! inArray "$e" "${_return[@]}";then
            _return+=("$e")
        fi
    done
}

# Title.
title GPL Dependency Manager
_ 'Variation '; yellow Default; _.
_ 'Version '; yellow `GplDependencyManager_printVersion`; _.
____

# Requirement, validate, and populate value.
chapter Dump variable.
delay=.5; [ -n "$fast" ] && unset delay
[ -n "$command" ] && commands_required=("$command")
code 'commands_required=('"${commands_required[@]}"')'
[[ ${#commands_required[@]} -eq 0 ]] && { red Argument command required; x; }
BINARY_DIRECTORY=${BINARY_DIRECTORY:=$HOME/bin}
code 'BINARY_DIRECTORY="'$BINARY_DIRECTORY'"'
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

if [ -z "$binary_directory_exists_sure" ];then
    chapter Mempersiapkan directory binary.
    __; code BINARY_DIRECTORY=$BINARY_DIRECTORY
    notfound=
    if [ -d "$BINARY_DIRECTORY" ];then
        __ Direktori '`'$BINARY_DIRECTORY'`' ditemukan.
    else
        __ Direktori '`'$BINARY_DIRECTORY'`' tidak ditemukan.
        notfound=1
    fi
    ____

    if [ -n "$notfound" ];then
        chapter Membuat directory.
        mkdir -p "$BINARY_DIRECTORY"
        if [ -d "$BINARY_DIRECTORY" ];then
            __; green Direktori '`'$BINARY_DIRECTORY'`' ditemukan.; _.
        else
            __; red Direktori '`'$BINARY_DIRECTORY'`' tidak ditemukan.; x
        fi
        ____
    fi
fi

commands_exists=()
commands_downloaded=()
until [[ ${#commands_required[@]} -eq 0 ]];do
    _commands_required=()
    chapter Requires command.
    for each in "${commands_required[@]}"; do
        __ Requires command: "$each".
        if [[ -f "$BINARY_DIRECTORY/$each" && ! -s "$BINARY_DIRECTORY/$each" ]];then
            __ Empty file detected.
            __; magenta rm "$BINARY_DIRECTORY/$each"; _.
            rm "$BINARY_DIRECTORY/$each"
        fi
        if [ ! -f "$BINARY_DIRECTORY/$each" ];then
            __ Memulai download.
            __; magenta wget https://github.com/ijortengab/gpl/raw/master/$(cut -d- -f2 <<< "$each")/"$each" -O "$BINARY_DIRECTORY/$each"; _.
            wget -q https://github.com/ijortengab/gpl/raw/master/$(cut -d- -f2 <<< "$each")/"$each" -O "$BINARY_DIRECTORY/$each"
            if [ ! -s "$BINARY_DIRECTORY/$each" ];then
                __; magenta rm "$BINARY_DIRECTORY/$each"; _.
                rm "$BINARY_DIRECTORY/$each"
                __; red HTTP Response: 404 Not Found; x
            fi
            __; magenta chmod a+x "$BINARY_DIRECTORY/$each"; _.
            chmod a+x "$BINARY_DIRECTORY/$each"
            commands_downloaded+=("$each")
        elif [[ ! -x "$BINARY_DIRECTORY/$each" ]];then
            __; magenta chmod a+x "$BINARY_DIRECTORY/$each"; _.
            chmod a+x "$BINARY_DIRECTORY/$each"
        fi
        fileMustExists "$BINARY_DIRECTORY/$each"

        commands_exists+=("$each")
        _dependency=$("$BINARY_DIRECTORY/$each" --help | sed -n '/^Dependency:/,$p' | sed -n '2,/^$/p' | sed 's/^ *//g' | grep ^gpl-)
        if [ -n "$_dependency" ];then
            _dependency=($_dependency)
            ArrayDiff _dependency[@] commands_exists[@]
            if [[ ${#_return[@]} -gt 0 ]];then
                _commands_required+=("${_return[@]}")
                unset _return
            fi
            unset _dependency
        fi
    done
    ____


    chapter Dump variable.
    ArrayUnique _commands_required[@]
    commands_required=("${_return[@]}")
    unset _return
    unset _commands_required
    code 'commands_required=('"${commands_required[@]}"')'
    ____
done

chapter Finsih.
__ Total sebanyak "${#commands_downloaded[@]}" file yang di download.
for each in "${commands_downloaded[@]}"; do
__; _, '- '; green "$each"; _.
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
# --binary-directory-exists-sure
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
