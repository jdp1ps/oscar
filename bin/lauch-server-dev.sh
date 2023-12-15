#!/bin/bash
# A exécuter depuis le dossier développement
PORT=8181
export APPLICATION_ENV=production
php -d variables_order=EGPCS -q -S 127.0.0.1:$PORT -t public/
