# Gak Pake Lama

Pisau lipat serba guna bagi seorang `devops`.

Mempercepat saat developing dan memudahkan untuk operating.

## Getting Started

Download `gpl-wrapper.sh` and `gpl-dependency-manager.sh` first.

```
mkdir -p ~/bin
export PATH=~/bin:$PATH
cd ~/bin
wget https://github.com/ijortengab/gpl/raw/master/gpl-wrapper.sh -O gpl-wrapper.sh
chmod a+x gpl-wrapper.sh
wget https://github.com/ijortengab/gpl/raw/master/gpl-dependency-manager.sh -O gpl-dependency-manager.sh
chmod a+x gpl-dependency-manager.sh
cd -
```

Make sure that directory `~/bin` has been include as `$PATH` in `~/.bashrc`.

```
export PATH=~/bin:$PATH
```

then feels free to execute command.

```
gpl-wrapper.sh
```

## About Shell Script

Each script can execute direct, use `gpl-dependency-manager.sh` to auto download every dependency.

Example the script `gpl-nginx-setup-hello-world-static.sh` will create virtual host of input `$domain`, then return the `Hello World` of contents inside `index.html`.

```
gpl-dependency-manager.sh gpl-nginx-setup-hello-world-static.sh
read -p 'Domain: ' domain
gpl-nginx-setup-hello-world-static.sh --domain $domain
```

If you wants to prompt every available options of the command and auto download every dependency, use the `gpl-wrapper.sh` as wrapper of shell script.

Example:

```
gpl-wrapper.sh gpl-nginx-setup-hello-world-static.sh
```

The `gpl-wrapper.sh` command can list for you all available command, just execute without operand.

```
gpl-wrapper.sh
```
