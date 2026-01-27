# --- Etapa 1: Compilar Angular ---
FROM node:20 as build-step

WORKDIR /app

# Copiamos dependencias
COPY src/frontend/package.json src/frontend/package-lock.json ./
RUN npm install

# Copiamos el código y compilamos
COPY src/frontend/ .
RUN npm run build -- --configuration production

# --- Etapa 2: Servidor Caddy ---
FROM caddy:alpine

# Copiamos la configuración de Caddy
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

# Copiamos la aplicación compilada
COPY --from=build-step /app/dist/frontend/browser /srv

# --- ARREGLO CRÍTICO ---
# Angular 17+ SSR genera 'index.csr.html' en lugar de 'index.html'.
# Lo renombramos para que Caddy lo reconozca como la página principal.
RUN cp /srv/index.csr.html /srv/index.html

# Exponemos puertos
EXPOSE 80
EXPOSE 443