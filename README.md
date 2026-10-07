# Plugin Moodle: Bloque de Integración SENCE SIC (Gestor Intermedio)

Plugin de tipo Bloque (`block_sence_integration`) desarrollado según las especificaciones del **Instructivo de Conexión: Integración entre LMS y SIC (API Gestor Intermedio V2.0 - Agosto 2023)** de SENCE Chile.

---

## Características Principales

1. **Soporte Multi-ID SENCE por curso:**
   * Permite asociar múltiples códigos de Oferta y Sección/Grupo SENCE a un mismo curso.
   * Permite segmentar por **Grupos/Cohortes de Moodle** o aplicar a todo el curso.
   * Posibilidad de sobrescribir el RUT OTEC y Token específico por oferta si aplica.

2. **Panel de Acciones en el Bloque del Curso:**
   * **Gestionar IDs SENCE:** Crear, editar y eliminar códigos vinculados.
   * **Enviar Avance Ahora (Manual):** Ejecuta la extracción de datos, arma el JSON estructurado y realiza el POST a `https://auladigital.sence.cl/gestor/API/avance-sic/enviarAvance`, mostrando el resultado en pantalla.
   * **Probar Conexión:** Ejecuta la prueba GET al Gestor Intermedio.
   * **Historial de Envíos:** Bitácora con los ID de Proceso y respuestas devueltas por SENCE.

3. **Cálculo Automático de Datos SENCE:**
   * **RUT Alumno:** Extraído del campo `idnumber` (Número de ID) del usuario en formato SENCE (`rutAlumno` numérico y `dvAlumno` texto).
   * **Dedicación / Conectividad:** Integrado con la tabla de `block_dedication` (con cálculo automático de tope de 45 min) y fallback a logs.
   * **Módulos / Secciones:** Extrae secciones, calculando actividades asincrónicas y sincrónicas (BigBlueButton, Teams, Zoom).
   * **Calificaciones y Avance:** Integrado con el libro de calificaciones y finalización de actividades de Moodle.
   * **Determinación de Estado:**
     * `1`: Cursando.
     * `2`: Aprobado (si cumple nota mínima y progreso mínimo parametrizados).
     * `3`: Reprobado (si expira la fecha de matrícula sin cumplir los requisitos).

4. **Automatización Diaria (Cron/Task API):**
   * Tarea programada `send_daily_progress` configurada para ejecutarse automáticamente todos los días a las 22:00 hrs (horario exigido por SENCE).

---

## Instalación en Moodle

1. Copiar la carpeta del plugin en el directorio de bloques de su instalación Moodle:
   `moodle/blocks/sence_integration`
2. Ingresar a Moodle como Administrador y completar la actualización de base de datos en **Administración del sitio > Notificaciones**.
3. Configurar las credenciales globales en **Administración del sitio > Extensiones > Bloques > Integración SENCE SIC**:
   * RUT OTEC (ej: `75062000-3`)
   * Token SENCE (UUID en mayúsculas)
   * ID Sistema (`1350`)
   * Criterios de Aprobación (% nota y % avance)
4. Entrar a cualquier curso y añadir el bloque **"Integración SENCE"**.
