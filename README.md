# Gak Pake Lama

Pisau lipat serba guna bagi seorang `devops`.

Mempercepat saat developing dan memudahkan untuk operating.

## Getting Started

Download `gpl-dependency-manager.sh` first.

```
mkdir -p ~/bin
export PATH=~/bin:$PATH
cd ~/bin
wget https://github.com/ijortengab/gpl/raw/master/gpl-dependency-manager.sh -O gpl-dependency-manager.sh
cd -
```

Download any script you wants using `gpl-dependency-manager.sh`.

Example, download `gpl-nginx-setup-hello-world-static.sh`.

```
gpl-dependency-manager.sh gpl-nginx-setup-hello-world-static.sh
```

then feels free to execute command.

```
gpl-nginx-setup-hello-world-static.sh --domain ijortengab.my.id
```

## Using Wrapper

If you wants to prompt every available options of the command and auto download every dependency, use the `gpl-wrapper.sh`.

Download:

```
gpl-dependency-manager.sh gpl-wrapper.sh
gpl-wrapper.sh gpl-nginx-setup-hello-world-static.sh
```

The `gpl-wrapper.sh` command can list for you all available command, just execute without operand.

```
gpl-wrapper.sh
```
