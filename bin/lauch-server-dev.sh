#!/bin/bash
# A exécuter depuis le dossier développement
PORT=4000
APPLICATION_ENV=development php -S 127.0.0.1:$PORT -t public public/index.php --env=dev
