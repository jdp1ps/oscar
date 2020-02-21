From postgres:latest AS postgresDemo
#Get argument proxy
ARG proxy_environnement
#From postgres:9.4.25 AS postgresDemo
LABEL Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>
LABEL version="macclane"
LABEL description="Version Démo Docker Oscar Postgree"

#ACTIVATE OR DESACTIVATE PROXY
RUN if [ "$proxy_environnement" != "noProxy" ]; \
    then echo "Acquire::http::proxy \"$proxy_environnement\";" > /etc/apt/apt.conf.d/proxy; \
    fi

RUN apt-get update && apt-get upgrade -y
#Install Nano
RUN apt-get install -y nano
RUN apt-get update && apt-get upgrade -y
# Install bdd fichier SQL
COPY oscar-install.sql /docker-entrypoint-initdb.d/
