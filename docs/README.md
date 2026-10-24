# Backend Lotería en Línea

API REST para la explotación de un juego de lotería en línea, con gestión
completa del ciclo de vida de los Términos y Condiciones y la Política de
Privacidad, y registro probatorio de aceptaciones conforme a la Ley 13/2011
de regulación del juego y al Reglamento General de Protección de Datos (RGPD).

---

## Tabla de contenidos

1. [Descripción general](#descripción-general)
2. [Características principales](#características-principales)
3. [Stack tecnológico](#stack-tecnológico)
4. [Requisitos previos](#requisitos-previos)
5. [Instalación](#instalación)
6. [Configuración](#configuración)
7. [Base de datos](#base-de-datos)
8. [Uso](#uso)
9. [Endpoints de la API](#endpoints-de-la-api)
10. [Sistema de Términos y Condiciones](#sistema-de-términos-y-condiciones)
11. [Estructura del proyecto](#estructura-del-proyecto)
12. [Documentación operativa](#documentación-operativa)
13. [Testing](#testing)
14. [Seguridad](#seguridad)
15. [Cumplimiento normativo](#cumplimiento-normativo)
16. [Contribución](#contribución)
17. [Licencia](#licencia)
18. [Contacto](#contacto)

---

## Descripción general

Este proyecto implementa el backend de una plataforma de lotería en línea.
Incluye la gestión de usuarios, autenticación mediante tokens, y un sistema
robusto y legalmente defendible para el registro probatorio de la aceptación
de Términos y Condiciones, Política de Privacidad y demás documentos legales.

El sistema está diseñado para cumplir con los requisitos de la Dirección
General de Ordenación del Juego (DGOJ) y con la normativa europea de
protección de datos, garantizando la trazabilidad completa de la relación
contractual con cada usuario.

---

## Características principales

- **Autenticación de usuarios** con Laravel Sanctum (tokens de API).
- **Registro de usuarios** con validación de edad, email único y aceptación
  obligatoria de Términos y Condiciones y Política de Privacidad en el
  mismo acto transaccional.
- **Versionado inmutable de documentos legales** con hash criptográfico
  SHA-256 para detección de alteraciones.
- **Log append-only de aceptaciones**, con captura de IP, User-Agent,
  timestamp UTC y URL de origen.
- **Re-aceptación automática** cuando se publica una nueva versión de los
  documentos legales.
- **Endpoint público** para consultar la versión vigente, apto para
  pantallas de registro previas a la autenticación.
- **Generación de certificados PDF** de aceptación con valor probatorio.
- **Comando Artisan** para publicar nuevas versiones de documentos legales.
- **Middleware de captura de contexto** para IP y User-Agent fiables
  detrás de proxies.
- **Gestión de errores centralizada** con respuestas JSON consistentes.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Lenguaje | PHP 8.1 o superior |
| Framework | Laravel 10 (compatible con 9) |
| Base de datos | PostgreSQL 13+ (recomendado) / MySQL 8+ |
| Autenticación | Laravel Sanctum |
| Generación PDF | barryvdh/laravel-dompdf |
| Servidor local | XAMPP / Laravel Valet / Sail |

---

## Requisitos previos

Antes de instalar el proyecto, asegúrate de tener instalado:

- **PHP 8.1** o superior, con las extensiones `pdo`, `mbstring`, `openssl`,
  `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`.
- **Composer 2.x** ([getcomposer.org](https://getcomposer.org/)).
- **PostgreSQL 13+** o **MySQL 8+**.
- **Node.js 18+** y **npm** (si vas a compilar assets).
- **Git**.

Comprueba las versiones instaladas:

```bash
php -v
composer -V
psql --version   # o mysql --version
node -v && npm -v
git --version


* * * * * cd /ruta && php artisan schedule:run >> /dev/null 2>&1
