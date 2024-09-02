# Synchronisation OSCAR

## Serveur de test

```bash
cd install/demo/connectors-server
node server.js
```

Le serveur livre de façon basique les données de démonstration présentes dans le dossier `./install/demo`.

Pour pouvez ensuite configurer la synchro oscar : 

```yaml
## config/connectors/organization_rest.yml
url_organizations: 'http://localhost:8888/organizations'
url_organization: 'http://localhost:8888/organizations/%s'
```

Même principe pour les personnes

```yaml
## config/connectors/person_rest.yml
url_persons: 'http://localhost:8888/persons'
url_person: 'http://localhost:8888/persons/%s'
```

> Si vous modifiez les informations dans les fichiers JSON, pensez à relancer le serveur.