# Runbook: Publicar nueva versión de Términos y Condiciones

## Información del Runbook

| Campo | Valor |
|---|---|
| **ID** | RUN-001 |
| **Descripción** | Procedimiento para publicar una nueva versión de los Términos y Condiciones o Política de Privacidad, forzando la re-aceptación de los usuarios. |
| **Herramientas** | PHP 8.x, Composer, acceso SSH al servidor, acceso a BD PostgreSQL/MySQL |
| **Permisos** | Acceso de escritura a `legal/` y a la tabla `legal_document_versions` |
| **Autor** | [Tu nombre] |
| **Última actualización** | 2026-10-15 |
| **Responsable de escalado** | [Nombre del responsable técnico] |

## Cuándo ejecutar este Runbook

- Cuando el departamento legal aprueba un cambio en los Términos y Condiciones.
- Cuando cambia la normativa aplicable (Ley 13/2011, RGPD, etc.).
- Cuando se modifican las reglas del juego o las condiciones de pago.

## Pre-requisitos

1. Texto legal aprobado por el departamento legal (formato HTML).
2. Número de versión asignado (ej. `2.0`, `2.1`).
3. Fecha de entrada en vigor decidida.
4. Acceso al repositorio del proyecto.

## Pasos

### 1. Preparar el archivo HTML

- [ ] Crear el archivo en `legal/terms-vX.Y.html` (o `privacy-vX.Y.html`).
- [ ] Verificar que el contenido es el aprobado por legal.
- [ ] **NO modificar** archivos de versiones anteriores. Cada versión es un archivo nuevo.

### 2. Publicar la versión en la base de datos

```bash
cd /ruta/al/proyecto
php artisan terms:publish terms X.Y legal/terms-vX.Y.html