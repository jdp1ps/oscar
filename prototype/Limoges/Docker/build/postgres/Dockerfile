#image Apache-PHP7 pour OSCAR

FROM library/postgres:9.6

MAINTAINER damien.rieu@unilim.fr

RUN apt-get install -y locales

# Set the locale
RUN sed -i -e 's/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen
ENV LANG fr_FR.UTF-8 
ENV LANGUAGE fr_FR:fr  
ENV LC_ALL fr_FR.UTF-8
ENV LC_NUMERIC fr_FR.UTF-8  
ENV LC_MONETARY fr_FR.UTF-8


COPY ./dev/pg_hba.conf  /etc/postgresql/9.6/main/
COPY ./dev/postgresql.conf /etc/postgresql/9.6/main/

RUN /etc/init.d/postgresql restart

COPY ./dev/init.sql /tmp/
#RUN psql -h localhost -U postgres < /tmp/init.sql

#cd /var/oscar/oscar/
#psql -h localhost -U oscar < data/backup_oscar-empty.sql

EXPOSE 5432
CMD ["postgres"]
