<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="280" alt="Laravel Logo">
</p>

# Plataforma de Encuestas Educativas

Aplicación construida con **Laravel 12** y la plantilla **Start Bootstrap SB Admin 2**, enfocada en la gestión de encuestas educativas, invitaciones y análisis apoyados por OpenAI.

## 🚀 Características principales

- Autenticación Breeze (Blade) con roles `administrador`, `docente` y `estudiante`.
- Dashboard responsive con tarjetas, gráficos (Chart.js) y sidebar dinámico por rol.
- Integración con OpenAI centralizada en `App\Services\OpenAIService` y procesada mediante `ProcessQuizAnalysisJob`.
- Vista detallada de análisis IA por encuesta, con gráficos de barras/pastel y recomendaciones.
- Exportación del informe completo a **PDF** (`dompdf`) con métricas cuantitativas y temas cualitativos.
- Flujo de estudiante optimizado: mantiene el layout con sidebar al completar encuestas.
- Modal global con loader para acciones largas (publicar/cerrar encuestas, regenerar informes, actualizar perfil, etc.).
- Módulo de perfil adaptado a SB Admin 2 (datos personales, cambio de contraseña y eliminación por modal).
- Submenú “Reportes” operativo (resumen, estudiantes, encuestas) listo para futuras métricas.

## 📦 Requisitos

- PHP 8.2+
- Composer 2.5+
- Node.js 18+ y npm
- MySQL/MariaDB (XAMPP recomendado)
- Extensiones PHP: `zip`, `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`

## ⚙️ Instalación rápida

```bash
git clone <repo> proyectopdilaravel
cd proyectopdilaravel
composer install
npm install
cp .env.example .env        # o copiar manualmente
php artisan key:generate
```

Edita `.env` para configurar la base de datos y OpenAI:

```env
DB_DATABASE=proyectopdi
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=tu_clave
OPENAI_MODEL_1=gpt-4o-mini
OPENAI_TEMP_1=0.7
OPENAI_MAXTOKENS_1=800
```

Luego ejecuta:

```bash
php artisan migrate --force
php artisan db:seed
npm run build   # o npm run dev para desarrollo
php artisan serve
```

> **Nota:** el job `ProcessQuizAnalysisJob` se lanza en el cierre de encuestas. Actualmente se ejecuta en modo síncrono (`dispatchSync`), por lo que no necesitas un worker separado; si deseas usar colas, cambia a `dispatch` y ejecuta `php artisan queue:work`.

## 🧠 Uso del análisis con IA

1. Crea y publica una encuesta desde el panel de docente.
2. Comparte el código con estudiantes; al responder, verán el formulario dentro del layout principal.
3. Cierra la encuesta (`Cerrar encuesta`). Esto dispara el análisis IA y genera un informe resumido en la vista de detalle.
4. Para profundizar, ingresa a **“Ver informe detallado”**: encontrarás gráficos, hallazgos cuantitativos/cualitativos y recomendaciones.
5. Exporta el reporte con **“Exportar informe”**, que produce un PDF descargable.

## 📋 Menú “Reportes”

- **Resumen:** tarjetas de adopción (placeholder listo para métricas globales).
- **Estudiantes:** pautas para participación y futuras tablas comparativas.
- **Encuestas:** recordatorio para clasificaciones y exportaciones históricas.

Estas vistas funcionan como base para añadir filtros y datasets reales.

## 👥 Accesos de ejemplo

| Rol            | Email                  | Contraseña |
|----------------|------------------------|------------|
| Administrador  | admin@example.com      | password   |
| Docente demo   | docente@example.com    | password   |
| Estudiante demo| estudiante@example.com | password   |

## 🗂️ Estructura destacada

- `resources/views/layouts/` — Layouts SB Admin 2 personalizados (app y guest).
- `resources/views/quizzes/analysis.blade.php` — Informe detallado con gráficos y recomendaciones.
- `resources/views/quizzes/analysis-pdf.blade.php` — Plantilla PDF del reporte exportable.
- `resources/views/profile/` — Formularios de perfil adaptados a SB Admin 2.
- `app/Services/OpenAIService.php` — Servicio para consumir OpenAI con perfiles configurables.
- `app/Services/QuizAnalyticsService.php` — Agregaciones cuantitativas/cualitativas reutilizables.
- `database/seeders/AdminUserSeeder.php` — Creación de usuarios demo con roles.

## 🛠️ Scripts útiles

```bash
php artisan migrate:fresh --seed   # Reinicia la BD con datos demo
php artisan make:controller ...    # Generar controladores adicionales
npm run dev                        # Recarga assets durante el desarrollo
```

## ✅ Próximos pasos sugeridos

- Enriquecer las vistas de “Reportes” con datos reales e indicadores educativos.
- Agregar filtros/segmentaciones en la vista de análisis detallado (por curso, rango de fechas, etc.).
- Configurar colas en segundo plano si el análisis IA tarda más tiempo o quieres liberarlo del request.
- Añadir pruebas funcionales y documentación de usuario final para el despliegue del TFE.

---
Desarrollado con ❤️ para apoyar procesos educativos basados en encuestas y análisis inteligente. Ajusta libremente esta base para tus necesidades. Si tienes dudas, revisa el código o contacta al equipo. ¡Éxitos! 🎓

