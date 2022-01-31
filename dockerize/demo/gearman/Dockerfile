#FROM debian
FROM debian:bullseye-backports

# Infos
LABEL authors="Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>"
LABEL version="Oscar Spartan Gearman Demo"
LABEL description="Version Démo Docker Oscar branch $TAG"

# GEARMAN SERVER JOB SERVER
RUN apt-get update\
    && apt-get upgrade -y \
    && apt-get install -y procps gearman-job-server \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

#NO PROXY POUR LES CONTAINERS ENTRE EUX
ENV no_proxy=${service_oscar_name},${service_gearman_name},${service_elasticsearch_name}
ENTRYPOINT [ "gearmand" ]
