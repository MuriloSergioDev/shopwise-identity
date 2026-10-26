# shopwise-identity

Customers, credentials and RS256 JWTs for Shopwise. Conventions: `AGENTS.md`.

Run it from `shopwise-platform`: `tilt up -- --only=identity`, then

```sh
curl -s -XPOST localhost:8101/api/customers -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"email":"ada@example.com","password":"correct-horse-battery","name":"Ada"}'
curl -s localhost:8101/api/me -H "Authorization: Bearer <accessToken>"
curl -s localhost:8101/.well-known/jwks.json
```

Seed demo accounts: `kubectl exec deploy/identity-http -- php artisan db:seed --force`.
