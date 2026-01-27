# --- Etapa 1: Compilar Angular (Igual que antes) ---
FROM node:20 as build-step

WORKDIR /app
COPY src/frontend/package.json src/frontend/package-lock.json ./
RUN npm install

COPY src/frontend/ .
RUN npm run build -- --configuration production

# --- Etapa 2: Caddy como Proxy Inverso y Servidor Web ---
FROM caddy:alpine

# Copiamos el 'Caddyfile' de configuración al contenedor
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

# Copiamos la Web compilada (Angular) a donde Caddy la espera
COPY --from=build-step /app/dist/frontend/browser /srv

# Exponemos puertos HTTP y HTTPS
EXPOSE 80
EXPOSE 443