# HTTPS / mTLS for Threat Intelligent Center (agent clients)

## Server TLS

1. Place server certificate and key on the host, e.g.:
   - `/etc/nginx/certs/server.crt`
   - `/etc/nginx/certs/server.key`
2. Optionally place the CA that signed agent client certs:
   - `/etc/nginx/certs/client-ca.crt`
3. Use `default-ssl.conf` (listen 443) alongside or instead of plain HTTP.

## Agent client certificate (.p12)

1. Copy `client-new.p12` to the agent machine:
   `%ProgramData%\SOSECURE Threat inSight\Config\Key\client.p12`
2. Set the PKCS#12 password via environment (do **not** commit the password):

```bat
setx INSITE_CLIENT_CERT_PASS "your-p12-password"
```

Optional explicit path:

```bat
setx INSITE_CLIENT_CERT_PATH "C:\ProgramData\SOSECURE Threat inSight\Config\Key\client.p12"
```

3. Point the agent Server IP to `https://your-center-host` (not http).

## Notes

- The `.p12` is a **client** certificate for mTLS. The Center still needs its own **server** TLS certificate.
- Until a trusted CA is configured, the agent may still skip server verify when no client cert is present (legacy). With a client cert loaded and `system_client_tls_insecure=false`, verification is enabled.
