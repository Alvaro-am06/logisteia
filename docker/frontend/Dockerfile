# Etapa 1: Compilación de Angular
FROM node:20 as build-step

WORKDIR /app

# Copiar package.json y lock desde la ruta correcta
COPY src/frontend/package.json src/frontend/package-lock.json ./

# Instalar dependencias
RUN npm install

# Copiar el código fuente del frontend
COPY src/frontend/ .

# Construir la aplicación para producción
RUN npm run build -- --configuration production

# Etapa 2: Servidor Web
FROM nginx:alpine

# Copiar los archivos compilados al servidor Nginx
# NOTA: Angular 17+ suele compilar en dist/frontend/browser
COPY --from=build-step /app/dist/frontend/browser /usr/share/nginx/html