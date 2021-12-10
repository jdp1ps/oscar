#FROM debian
FROM debian:bullseye-backports

# Issue du docker-compose
ARG proxy_environnement
ARG service_oscar_name
ARG service_gearman_name
ARG service_elastic_search_name

# Infos
LABEL authors="Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>"
LABEL version=${TAG}
LABEL description="Version Démo Docker Oscar branch $TAG"

#ACTIVER OU DESACTIVER PROXY
RUN if [ "$proxy_environnement" != "noProxy" ]; \
    then echo "Acquire::http::proxy \"$proxy_environnement\";" > /etc/apt/apt.conf.d/proxy; \
    fi

# GEARMAN SERVER JOB SERVER
RUN apt-get update\
    && apt-get upgrade -y \
    && apt-get install -y procps gearman-job-server \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

#NO PROXY POUR LES CONTAINERS ENTRE EUX
ENV no_proxy=${service_oscar_name},${service_gearman_name},${service_elastic_search_name}
ENTRYPOINT [ "gearmand" ]
