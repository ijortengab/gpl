# Gak Pake Lama

Pisau lipat serba guna bagi seorang `devops`.

Mempercepat saat developing dan memudahkan untuk operating.

## Getting Started

Login as root.

```
sudo su
```

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

## Example 1

The script `gpl-nginx-setup-hello-world-static.sh` will create virtual host of input `$domain`, then return the `Hello World` of contents inside `index.html`.

Execute:

```
gpl-dependency-manager.sh gpl-nginx-setup-hello-world-static.sh
gpl-nginx-setup-hello-world-static.sh
```

If you wants to prompt every available options of the command and auto download every dependency , use the `gpl-wrapper.sh` as wrapper of shell script.

Example:

```
gpl-wrapper.sh gpl-nginx-setup-hello-world-static.sh
```

The `gpl-wrapper.sh` command can list for you all available command, just execute without operand.

```
gpl-wrapper.sh
```

## Example 2

If you change binary directory from default `$HOME/bin`, to others (i.e `/usr/local/bin`) then we must prepend environment variable (`$BINARY_DIRECTORY`) before execute the command.

Download `gpl-wrapper.sh` and `gpl-dependency-manager.sh` then save to other binary directory.

```
cd /usr/local/bin
wget https://github.com/ijortengab/gpl/raw/master/gpl-wrapper.sh -O gpl-wrapper.sh
chmod a+x gpl-wrapper.sh
wget https://github.com/ijortengab/gpl/raw/master/gpl-dependency-manager.sh -O gpl-dependency-manager.sh
chmod a+x gpl-dependency-manager.sh
cd -
```

```
BINARY_DIRECTORY=/usr/local/bin gpl-dependency-manager.sh gpl-nginx-setup-hello-world-static.sh
gpl-nginx-setup-hello-world-static.sh
```

or

```
BINARY_DIRECTORY=/usr/local/bin gpl-wrapper.sh gpl-nginx-setup-hello-world-static.sh
```
