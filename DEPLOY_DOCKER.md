# Despliegue en VPS con Docker

Esta configuracion levanta Laravel con PHP-FPM, Nginx y MariaDB.

## Requisitos en la VPS

- Docker
- Docker Compose v2
- Un dominio apuntando a la IP de la VPS, si se va a publicar en internet

## Primer despliegue

1. Copia el proyecto a la VPS.
2. Crea el archivo de entorno:

```bash
cp .env.production.example .env
```

3. Edita `.env` y cambia al menos:

```bash
APP_URL=https://tu-dominio.com
DB_PASSWORD=una_clave_segura
DB_ROOT_PASSWORD=otra_clave_segura
```

4. Genera una clave de aplicacion:

```bash
docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Copia el valor generado en `APP_KEY`.

5. Construye y arranca los contenedores:

```bash
docker compose up -d --build
```

El contenedor `app` espera a MariaDB, ejecuta migraciones y optimiza Laravel automaticamente.

## Comandos utiles

Ver logs:

```bash
docker compose logs -f app nginx mariadb
```

Ejecutar migraciones manualmente:

```bash
docker compose exec app php artisan migrate --force
```

Crear el enlace de storage manualmente:

```bash
docker compose exec app php artisan storage:link --force
```

Detener la aplicacion:

```bash
docker compose down
```

Detener y borrar los datos de MariaDB:

```bash
docker compose down -v
```

## HTTPS

Este compose expone Nginx en `APP_PORT`, por defecto el puerto `80`. Para HTTPS puedes poner delante un proxy como Traefik, Caddy o Nginx Proxy Manager, o instalar certificados en el Nginx del host y reenviar el trafico al contenedor.

## Dokploy

Si usas Dokploy, despliega como Docker Compose y no como Stack, porque este proyecto construye imagenes desde el `Dockerfile`.

La opcion mas simple es configurar el Compose Path como:

```text
dokploy.compose.yml
```

Ese archivo no publica puertos en el host; Dokploy/Traefik debe enrutar el dominio al servicio `nginx`, puerto interno `80`.

Tambien puedes usar `docker-compose.yml` junto con `docker-compose.dokploy.yml` si tu instalacion permite indicar varios archivos compose.

Configura el dominio en Dokploy asi:

```text
Service: nginx
Port: 80
HTTPS: enabled
Certificate: Let's Encrypt
```

Define las variables del archivo `.env.production.example` en la pestana Environment de Dokploy. Este compose ya usa `env_file: .env`, por lo que Dokploy escribira esas variables en el `.env` del despliegue.
