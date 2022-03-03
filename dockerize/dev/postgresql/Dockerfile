From postgres:13.5-bullseye AS postgresDevBullseye

ARG proxy_environnement

LABEL Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>
LABEL version="macclane"
LABEL description="Version Dev Docker Oscar Postgree"

#ACTIVATE OR DESACTIVATE PROXY
RUN if [ "$proxy_environnement" != "noProxy" ]; \
    then echo "Acquire::http::proxy \"$proxy_environnement\";" > /etc/apt/apt.conf.d/proxy; \
    fi

RUN apt-get update && apt-get upgrade -y
#RUN apt-get install -y nano

# Install bdd fichier SQL
COPY oscar-install.sql /docker-entrypoint-initdb.d/
