# requetes de test

## fuzziness

Résultats approchant à 2 caractères pret : 

```
GET oscar-person/_search
{
  "query": {
    "multi_match": {
      "query": "BOUVERI",
      "fuzziness": 2,
      "fields": ["lastname", "firstname", "affectation"]
    }
  }
}
```

Recherche multi-champs avec poids sur les champs

```json
GET oscar-person/_search
{
    "query_string": {
      "fields": ["lastname^5","fullname^3", "firstname^.25"], 
      "query": "DAR",
      "default_operator": "AND",
      "fuzziness": 2
    }
  }
}
```