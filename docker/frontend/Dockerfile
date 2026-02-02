# Etapa 1: Compilar Angular
FROM node:20-alpine as build

WORKDIR /app

COPY src/frontend/package*.json ./
RUN npm install

COPY src/frontend/ ./
RUN npm run build -- --configuration production

# Etapa 2: Servir con Nginx
FROM nginx:alpine

COPY --from=build /app/dist/frontend/browser /usr/share/nginx/html

# Si Angular genera index.csr.html, copiarlo como index.html
RUN if [ -f /usr/share/nginx/html/index.csr.html ]; then cp /usr/share/nginx/html/index.csr.html /usr/share/nginx/html/index.html; fi

EXPOSE 80
