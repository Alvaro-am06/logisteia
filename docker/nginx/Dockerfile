# --- Etapa 1: Compilar Angular 21 ---
FROM node:20 AS build-stage

WORKDIR /app

# Copiar dependencias
COPY src/frontend/package.json src/frontend/package-lock.json ./
RUN npm install

# Copiar código y compilar
COPY src/frontend/ .
RUN npm run build -- --configuration production

# --- Etapa 2: Nginx con proxy reverso ---
FROM nginx:alpine

# Copiar configuración nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Copiar aplicación Angular compilada
COPY --from=build-stage /app/dist/frontend/browser /usr/share/nginx/html

# Crear directorio de logs
RUN mkdir -p /var/log/nginx

# Exponemos puertos
EXPOSE 80
EXPOSE 443

# Comando de inicio
CMD ["nginx", "-g", "daemon off;"]
