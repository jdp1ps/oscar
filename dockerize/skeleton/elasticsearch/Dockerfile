FROM docker.elastic.co/elasticsearch/elasticsearch:7.16.1

# Issue du docker-compose
ARG service_oscar_name
ARG service_gearman_name
ARG service_elasticsearch_name

#NO PROXY POUR LES CONTAINERS ENTRE EUX
ENV no_proxy=${service_oscar_name},${service_gearman_name},${service_elasticsearch_name}
