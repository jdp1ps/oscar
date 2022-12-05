From postgres:13.5-bullseye AS postgresDevBullseye

ARG proxy_environnement

LABEL Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>
LABEL description="Postgresql for Oscar"

#ACTIVATE OR DESACTIVATE PROXY
RUN if [ "$proxy_environnement" != "" ]; \
    then echo "Acquire::http::proxy \"$proxy_environnement\";" > /etc/apt/apt.conf.d/proxy; \
    fi

# Note : Supprimé car provoque une erreur de locale
## nitdb: error: invalid locale settings; check LANG and LC_* environment variables
#RUN apt-get update && apt-get upgrade -y
#RUN apt-get install -y nano

# Install bdd fichier SQL
COPY oscar-install.sql /opt/
COPY init-db.sh /docker-entrypoint-initdb.d/
