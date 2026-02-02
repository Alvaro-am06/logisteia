# Etapa 1: Compilar Angular
FROM node:20-alpine as build

WORKDIR /app

COPY src/frontend/package*.json ./
RUN npm install

COPY src/frontend/ ./
RUN npm run build --configuration production

# Etapa 2: Servir con Caddy
FROM caddy:alpine

COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
COPY --from=build /app/dist/frontend/browser /srv

# Si Angular genera index.csr.html, copiarlo como index.html
RUN if [ -f /srv/index.csr.html ]; then cp /srv/index.csr.html /srv/index.html; fi

EXPOSE 80 443
