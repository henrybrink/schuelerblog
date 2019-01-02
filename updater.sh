#!/usr/bin/env bash
#  _________      .__                  .__              ___.   .__
# /   _____/ ____ |  |__  __ __   ____ |  |   __________\_ |__ |  |   ____   ____
# \_____  \_/ ___\|  |  \|  |  \_/ __ \|  | _/ __ \_  __ \ __ \|  |  /  _ \ / ___\
# /        \  \___|   Y  \  |  /\  ___/|  |_\  ___/|  | \/ \_\ \  |_(  <_> ) /_/  >
#/_______  /\___  >___|  /____/  \___  >____/\___  >__|  |___  /____/\____/\___  /
#        \/     \/     \/            \/          \/          \/           /_____/
#
# Updater - UTP - Does magic.
updateURL=https://user1:HyLLAyp5pVEWQKtt@update.henrybrink.de/schuelerblog/downloads/latest.zip

# create dir for the update
mkdir updater-download
# download the zip
wget --prefix updater-download ${updateURL}
# unzip the file
unzip updater-download/latest.zip

# move the files - to protect config files only specific folders are copied, because maybe something goes wrong while packaging
cp -R updater-download/src/* src/
cp -R updater-download/templates/* templates/
cp -R updater-download/public/* public/
cp -R updater-download/assets/* assets/
cp updater-download/package.json .
cp updater-download/package-lock.json .
cp updater-download/webpack.config.js .

# remove the updater
rm -rf updater-download
