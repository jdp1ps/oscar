From postgres:13.5-bullseye AS postgresDevBullseye

ARG proxy_environnement

LABEL Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>
LABEL version="Oscar Spartan Postgresql Demo"
LABEL description="Version Dev Docker Oscar Postgree"

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y nano

# Install bdd fichier SQL
COPY oscar-install.sql /docker-entrypoint-initdb.d/
