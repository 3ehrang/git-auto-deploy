# git-auto-deploy
This is a simple PHP script. Automatically pull from a repository to a web server (using a webhook on GitHub, GitLab, or Bitbucket) and support git branch

# 1 - On the server

*Here we install and setup git on the server, we also create an SSH key so the server can talk to the origin without using passwords etc*

## Install and Setup git

Check is git installed before ?

    git --version

## - Generate a deploy public key for apache user
    ssh-keygen -t rsa
    
## Copy public key

Go to ssh directory and find id_rsa.pub file copy content in clipboard.

# 2 - On your origin (github, gitlab, bitbucket, ...)

## Add the SSH key to your user

Paste the deploy key you generated on the server before

## Set up service hook

Enter the URL to your deployment script - http://server.com/deploy.php

# 3 - On the Server

*Here we clone the origin repo into a chmodded /var/www/html folder you might clone it somewhere else*

## Pull from origin

    sudo chown -R www-data:www-data /var/www/html
    sudo -Hu www-data git clone git@github.com:you/server.git /var/www/html

## Sources
 * https://gist.github.com/oodavid/1809044 who in turn referenced