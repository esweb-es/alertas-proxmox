# ProxmoxAlert - Manual de Uso

![ProxmoxAlert Dashboard](public/assets/images/screenshots/screenshot.png)

Manual operativo y técnico para desplegar, configurar y usar **Proxmox Alert**.

## Tabla de contenidos
- [1. Descripción](#1-descripción)
- [2. Requisitos](#2-requisitos)
- [3. Instalación](#3-instalación)
- [4. Primer acceso](#4-primer-acceso)
- [5. Configuración SMTP](#5-configuración-smtp)
- [6. Gestión de empresas](#6-gestión-de-empresas)
- [7. Integración con Proxmox (Webhook)](#7-integración-con-proxmox-webhook)
- [8. Gestión de alertas](#8-gestión-de-alertas)
- [9. Usuarios, grupos y permisos](#9-usuarios-grupos-y-permisos)
- [10. Operación recomendada](#10-operación-recomendada)
- [11. Resolución de problemas](#11-resolución-de-problemas)
- [12. Rutas principales](#12-rutas-principales)

## 1. Descripción
**Proxmox Alert** centraliza alertas de múltiples entornos Proxmox VE en una única interfaz web.

Capacidades principales:
- Alta y gestión de empresas/clientes.
- Recepción de eventos vía webhook.
- Clasificación de alertas por severidad.
- Resolución y borrado controlado de alertas.
- Envío de correo para alertas críticas.
- Control de acceso por grupos y permisos.

## 2. Requisitos
- **PHP**: 8.2+
- **Framework**: CodeIgniter 4.7.x
- **Base de datos**: SQLite 3
- **Composer**
- Extensiones PHP habituales para CI4 (`intl`, `mbstring`, `json`, `pdo_sqlite`, etc.)

## 3. Instalación
Desde la raíz del proyecto:

```bash
cp env .env

# O duplica el archivo env y colocale un punto (.env)

# (Opcional) php spark migrate && php spark db:seed DatabaseSeeder
php spark serve --host 0.0.0.0 --port 8081
```

> [!IMPORTANT]
> Debes editar el archivo `.env` y configurar `app.baseURL` con tu dominio o IP real (ej: `https://tudominio.com/` o `http://192.168.1.100:8080/`) para que los webhooks y las redirecciones funcionen correctamente.
Aplicación disponible por defecto en:
- `https://tudominio.com`

## 4. Primer acceso
Credenciales iniciales (seeder):
- **Usuario**: `admin`
- **Email**: `admin@demo.com`
- **Password**: `admin123`

Login:
- `https://tudominio.com/login`

Recomendado:
- Cambiar la contraseña en el primer inicio.

## 5. Configuración SMTP
Ruta:
- `https://tudominio.com/email`

Campos obligatorios:
- `fromEmail`
- `fromName`
- `SMTPHost`
- `SMTPPort`
- `SMTPUser`
- `SMTPPass`

Flujo recomendado:
1. Completar la configuración SMTP.
2. Ejecutar envío de prueba.
3. Guardar configuración definitiva.

Notas:
- El correo de prueba se envía al usuario autenticado.
- Esta sección está restringida a grupos `admin` y `superadmin`.

## 6. Gestión de empresas
Ruta principal:
- `https://tudominio.com/companies`

Datos relevantes al crear/editar:
- `nombre` (obligatorio)
- `email` (recomendado si se activan notificaciones)
- `proxmox_host` (IP/hostname del host Proxmox a monitorear por ping)
- `active` (empresa habilitada)
- `send_email` (activa envío automático de correo)

Comportamiento:
- El sistema genera automáticamente un `webhook_token` único por empresa.
- En edición de empresa (`/companies/edit/{id}` se puede ejecutar un ping manual con el botón `Ping`.

## 7. Integración con Proxmox (Webhook)
Endpoint receptor:
- `POST /webhook/proxmox/{token}`

Ejemplo local:
- `https://tudominio.com/webhook/proxmox/TOKEN_EMPRESA`

Por empresa se puede:
- Descargar script de configuración (`/companies/download-script/{id}`).
- Ver script en texto plano (`/companies/get-script/{id}`).

Formato JSON aceptado:
- Payload en raíz.
- Payload dentro de `body`.

Campos esperados:
- `title`
- `message`
- `severity`
- `timestamp`
- `hostname` o `node`

## 8. Gestión de alertas
Desde la vista de empresa:
- Filtrado por severidad y estado.
- Marcar alerta como resuelta.
- Borrado individual o masivo.

Reglas de borrado:
- No se elimina una alerta crítica pendiente.
- Se permite eliminar alertas en estado `resolved`.
- Se permite eliminar alertas informativas (`info`, `notice`, `debug`).

Reglas de correo automático:
- Se envía email solo si `send_email` está activo y la empresa tiene email.
- Se consideran críticas severidades que contengan: `error`, `crit`, `emerg` o `alert`.

## 9. Usuarios, grupos y permisos
Rutas clave:
- `/users`
- `/users/create`
- `/users/edit/{id}`
- `/users/perfil`

Control de acceso:
- Autenticación por sesión.
- Permisos por acción (por ejemplo: `users.view`, `empresas.edit`).
- Restricciones por grupo para áreas sensibles.

Recomendación:
- Aplicar principio de mínimo privilegio en cada perfil.

## 10. Operación recomendada
1. Revisar alertas nuevas al inicio del turno.
2. Marcar incidencias cerradas como `resolved`.
3. Limpiar alertas informativas antiguas.
4. Verificar SMTP de forma periódica.
5. Revisar usuarios activos y permisos.

## 11. Monitoreo de ping por cron (token interno)
El sistema incluye un endpoint interno para ejecutar chequeo masivo de ping en todas las empresas activas con `proxmox_host` configurado.

Configuración en `.env`:
- `cron.pingToken = 'TOKEN_LARGO_Y_SEGURO'`

Endpoint:
- `GET /monitoring/ping-check/{token}`

Ejemplo:
- `https://tudominio.com/monitoring/ping-check/TU_TOKEN`

Qué hace:
- Recorre empresas activas con host configurado.
- Ejecuta ping a cada host.
- Si falla, crea alerta en `alertas` con:
  - `title`: `Proxmox no responde`
  - `message`: `Incidente de conectividad detectado en {host}. Caída registrada a las {YYYY-MM-DD HH:MM:SS}.`
- Si el host vuelve a responder, resuelve automáticamente la alerta abierta con:
  - `message`: `Conectividad restablecida en {host} a las {YYYY-MM-DD HH:MM:SS}.`
- Deduplicación por estado: mientras exista una alerta de ping abierta para la empresa, no crea duplicados.

Respuesta:
- Devuelve JSON con resumen: `total`, `ok`, `failed`, `alerts_created`, `alerts_skipped`, `alerts_resolved`.

Uso recomendado en hosting (Cron):
1. Crear tarea programada cada 5 minutos.
2. Ejecutar llamada HTTP GET al endpoint con token.

Seguridad:
- Mantener el token solo en `.env`.
- Rotar token si se comparte o filtra.
- No publicar el enlace en lugares públicos.

## 12. Resolución de problemas
**No llegan alertas**
- Verificar empresa activa.
- Confirmar token de webhook.
- Validar conectividad de red entre Proxmox y la URL del sistema.

**No llegan correos**
- Revisar configuración SMTP en `/email`.
- Ejecutar prueba SMTP.
- Confirmar `send_email` activo y email válido en la empresa.

**No se puede iniciar sesión**
- Confirmar ejecución de migraciones y seeders.
- Revisar credenciales iniciales.

**El cron de ping no crea alertas**
- Verificar `cron.pingToken` en `.env`.
- Confirmar que la URL del cron usa exactamente ese token.
- Revisar que la empresa esté activa y tenga `proxmox_host` configurado.
- Confirmar que el hosting permite ejecutar `ping` desde el servidor web.

## 13. Rutas principales
- `GET /login`
- `GET /companies`
- `GET /companies/create`
- `GET /companies/edit/{id}`
- `GET /companies/view/{id}`
- `GET /companies/download-script/{id}`
- `GET /companies/get-script/{id}`
- `GET /companies/ping?host=IP_O_HOSTNAME` (ping manual desde UI)
- `POST /webhook/proxmox/{token}`
- `GET /monitoring/ping-check/{token}` (cron interno)
