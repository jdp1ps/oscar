FROM debian:12.10-slim

LABEL authors="Stéphane BOUVRY<stephane.bouvry@unicaen.fr>"


RUN apt update && apt install -y nodejs npm
RUN npm install -g corepack

#
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/application/ui

RUN yarn install

ENTRYPOINT ["/entrypoint.sh"]