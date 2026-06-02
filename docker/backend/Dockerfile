# --- Etapa 1: Compilar Spring Boot con Maven ---
FROM maven:3.9-eclipse-temurin-25 AS build-stage

WORKDIR /build

# Copiar pom.xml y descargar dependencias
COPY pom.xml .
RUN mvn dependency:resolve

# Copiar código fuente y compilar
COPY src ./src
RUN mvn clean package -DskipTests

# --- Etapa 2: Runtime Spring Boot con Java 25 ---
FROM eclipse-temurin:25-jre

WORKDIR /app

# Copiar el JAR compilado
COPY --from=build-stage /build/target/logisteia-backend-*.jar app.jar

# Exponemos puerto 8080 (default de Spring Boot)
EXPOSE 8080

# Variables de entorno para la base de datos
ENV SPRING_DATASOURCE_URL=jdbc:mysql://db:3306/Logisteia
ENV SPRING_DATASOURCE_USERNAME=root
ENV SPRING_JPA_HIBERNATE_DDL_AUTO=update
ENV SERVER_PORT=8080

# Comando de inicio
ENTRYPOINT ["java", "-Dspring.profiles.active=production", "-jar", "app.jar"]
