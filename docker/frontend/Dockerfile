# Etapa 1: Compilar Angular
FROM node:20-alpine as build

WORKDIR /app

COPY src/frontend/package*.json ./
RUN npm install

COPY src/frontend/ ./
RUN npm run build -- --configuration production

# Etapa 2: Servir con Caddy
FROM caddy:alpine

COPY --from=build /app/dist/frontend/browser /usr/share/caddy

# Si Angular genera index.csr.html, copiarlo como index.html
RUN if [ -f /usr/share/caddy/index.csr.html ]; then cp /usr/share/caddy/index.csr.html /usr/share/caddy/index.html; fi

# Configurar Caddy
RUN echo 'logisteia.com { \
    root * /usr/share/caddy \
    try_files {path} {path}/ /index.html \
    reverse_proxy /api/* logisteia_backend:80 \
} \
pma.logisteia.com { \
    reverse_proxy logisteia_pma:80 \
}' > /etc/caddy/Caddyfile
