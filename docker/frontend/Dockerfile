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
RUN printf 'logisteia.com {\n\
    root * /usr/share/caddy\n\
    try_files {path} {path}/ /index.html\n\
    reverse_proxy /api/* logisteia_backend:80\n\
}\n\
\n\
api.logisteia.com {\n\
    reverse_proxy logisteia_backend:80\n\
}\n\
\n\
pma.logisteia.com {\n\
    reverse_proxy logisteia_pma:80\n\
}\n' > /etc/caddy/Caddyfile
