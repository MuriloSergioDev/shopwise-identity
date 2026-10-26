# shopwise-identity

Customers, credentials and access tokens. Every other service trusts identity's RS256 JWTs through the kit's
`auth.jwt` middleware; nobody else stores passwords. Created from `shopwise-service-template` — structure,
checks and how to run PHP are described there and still apply.

## Service facts
- **Owns**: `customers`, `signing_keys` (RSA keys, private key encrypted with `APP_KEY`), `refresh_tokens` (sha256 hashes only). Database `identity`.
- **Publishes**: `identity.customer-registered` (schema `shopwise-contracts/schemas/events/identity/customer-registered.v1.json`).
- **Consumes**: nothing.
- **HTTP** (BFF only) — contract `shopwise-contracts/openapi/identity.yaml`: `POST /api/customers`, `POST /api/tokens`, `POST /api/tokens/refresh`, `POST /api/tokens/revoke`, `GET /api/me` (`auth.jwt`), `GET /.well-known/jwks.json`.
- **Local URL**: http://localhost:8101 (Tilt port-forward).

## Token rules (change them only with the contract)
- Access token: 15 min, claims `iss=shopwise-identity`, `sub` (customer id), `email`, `roles`, `jti`; header `kid`. Signed only in `app/Tokens` (arch test).
- Refresh token: opaque, 30 days, single use. Refreshing spends it and returns a new pair in the same family; presenting a spent one revokes the family (theft detection). The BFF must therefore never refresh the same token twice concurrently.
- Keys: created on first use; `php artisan identity:rotate-key` retires the current one. Retired keys stay in the JWKS for `KeyRing::RETIRED_GRACE_SECONDS` (longer than access TTL + the 300 s JWKS cache in other services).
- `/api/me` verifies through `App\Tokens\DatabaseKeySource` (bound to the kit's `KeySource`), never over HTTP to itself.
- `/api/customers` and `/api/tokens` are throttled per email + IP (`credentials` limiter in `AppServiceProvider`).

## Admin
- `php artisan identity:grant-role <email> admin` (effective on the next token). `db:seed` creates `admin@shopwise.test` and `customer@shopwise.test`, password `correct-horse-battery`.
