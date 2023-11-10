#!/bin/bash
# A exécuter depuis le dossier développement
PORT=8888
APPLICATION_ENV=production php -q -S 127.0.0.1:$PORT -t public/
