FROM docker.elastic.co/elasticsearch/elasticsearch:7.5.2
ARG service_oscar_name
ARG service_gearman_name
ARG service_elastic_search_name
#NO PROXY POUR LES CONTAINERS ENTRE EUX
ENV no_proxy=${service_oscar_name},${service_gearman_name},${service_elastic_search_name}
