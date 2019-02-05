# PHPIPAM-Archi

A PHPIPAM module which automatically generates a network architecture basing on Firewall zones.

# Installation

The installation should be done manually.

1- Download this project in the directory ``app/admin``:

```
cd app/admin
git clone https://github.com/mohamedaymenkarmous/PHPIPAM-Archi
```

2- Execute these lines under ``app/admin``

```
# Dupplicate the "Firewall Zones" menu and set the new menu for the "Architecture Menu"
line=$(grep "Firewall Zones" admin-menu-config.php | grep -v architecture | sed 's/Firewall Zones/Architecture/g' | sed 's/firewall-zones/architecture/g' | sed 's/Firewall zone management/Network Architecture/g')
# Set this menu under the "Firewall Zones" menu in the menu configuration file
# To be modified, actually it didn't work correctly
#awk -v line="$line" '/line/ { print; print "$line"; next }1' <(grep -v architecture admin-menu-config.php) > admin-menu-config.php.tmp && mv admin-menu-config.php.tmp admin-menu-config.php
```

