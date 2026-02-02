# --- ETAPA 1: CONSTRUCCIÓN (Node.js) ---
FROM node:18-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
# Compila para producción
RUN npm run build -- --configuration production

# --- ETAPA 2: SERVIDOR (Nginx) ---
FROM nginx:alpine
# Copiamos la configuración de Nginx (paso 3)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# ¡OJO AQUÍ! Revisa el nombre de tu carpeta en dist/
# Si en tu angular.json el outputPath es "dist/logisteia", usa esto:
COPY --from=build /app/dist/logisteia /usr/share/nginx/html
# Si no estás seguro, entra en el contenedor luego y lo miramos.

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]