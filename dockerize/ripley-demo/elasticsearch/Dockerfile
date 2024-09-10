FROM docker.elastic.co/elasticsearch/elasticsearch:7.16.1

# Issue du docker-compose
ARG proxy_environnement
ARG service_oscar_name
ARG service_gearman_name
ARG service_elasticsearch_name

RUN if [ "$proxy_environnement" != "" ]; \
    then echo "Acquire::http::proxy \"$proxy_environnement\";" > /etc/apt/apt.conf.d/proxy; \
    fi

#NO PROXY POUR LES CONTAINERS ENTRE EUX
ENV no_proxy=${service_oscar_name},${service_gearman_name},${service_elasticsearch_name}
