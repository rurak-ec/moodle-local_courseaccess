# Condiciones del Curso - Plugin de Moodle

[![Moodle](https://img.shields.io/badge/Moodle-5.1+-orange.svg)](https://moodle.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPLv3-green.svg)](https://www.gnu.org/copyleft/gpl.html)
[![Status](https://img.shields.io/badge/Status-Stable-green.svg)]()

Plugin local de Moodle que permite a los profesores definir condiciones específicas para su curso (como "Turno", "Grupo de Laboratorio", "Modalidad") y requerir que los estudiantes seleccionen una opción antes de acceder al contenido.

**Autor:** Rurak
**Versión:** v1.7.19 (Stable)
**Build:** 2025012522
**Tipo de Plugin:** Local
**Licencia:** GNU GPL v3 or later

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Novedades v1.7.19](#-novedades-v1719)
- [Instalación](#-instalación)
- [Configuración](#️-configuración)
- [Uso](#-uso)
- [Arquitectura Técnica](#-arquitectura-técnica)
- [Privacidad y GDPR](#-privacidad-y-gdpr)

---

## ✨ Características

### General
- **Condición Única por Curso**: Cada curso **puede** tener una única condición definida (ej. "Turno" o "Grupo").
- **Selección Obligatoria**: Bloquea el acceso al curso hasta que el estudiante haya seleccionado una opción.
- **Integración Nativa**: Utiliza campos de perfil de usuario personalizados para almacenar la selección.
- **ID Personalizado**: Configura un identificador personalizado para el campo de perfil (por defecto es el ID del curso).
- **Habilitar/Deshabilitar**: Activa o desactiva condiciones sin perder datos de estudiantes.

### Para Estudiantes
- **Experiencia Fluida**: Modal bloqueante automático si falta la selección.
- **Persistencia**: La selección se guarda permanentemente en su perfil.
- **Cambio de Selección**: Pueden modificar su elección posterior mediante enlace en la navegación del curso.
- **Transparencia**: Ven advertencia sobre impacto en restricciones antes de cambiar.

### Para Profesores
- **Gestión Simplificada**: Configura la condición y sus opciones en una sola pantalla.
- **Validación**: El sistema asegura que la condición tenga al menos 2 opciones.
- **Control de Acceso**: Restringe actividades basándose en la selección del estudiante.
- **Configuración Avanzada**: Personaliza el ID del campo de perfil y habilita/deshabilita condiciones.
- **Migración Automática**: Al cambiar el ID personalizado, los datos de los estudiantes se migran automáticamente.

---

## 🆕 Novedades v1.7.19

### Configuración Avanzada

#### **ID Personalizado para Campos de Perfil**

Por defecto, el plugin crea campos de perfil con el formato `cond_{courseid}_{conditionid}`. Ahora puedes personalizar el identificador:

- **Campo**: "ID Personalizado" en Configuración Avanzada
- **Uso**: Útil para compartir un mismo campo de perfil entre múltiples cursos
- **Migración Automática**: Al cambiar el ID, todos los campos de perfil de los estudiantes se actualizan automáticamente
- **Ejemplo**:
  - Por defecto: `cond_5_1` (curso ID 5, condición ID 1)
  - Personalizado: `cond_matematicas_1` (compartido entre todos los cursos de matemáticas)

**Advertencia:** La migración puede tardar varios minutos si hay muchos estudiantes.

#### **Habilitar/Deshabilitar Condiciones**

Ahora puedes desactivar temporalmente una condición sin perder los datos:

- **Checkbox**: "Habilitar Condición" en Configuración Avanzada
- **Cuando está deshabilitada**:
  - Los estudiantes no ven el modal de selección
  - No se aplican restricciones de acceso
  - Los datos de selección se conservan en la base de datos
- **Cuando se reactiva**:
  - Los estudiantes recuperan automáticamente sus selecciones previas
  - Las restricciones de acceso vuelven a aplicarse

**Caso de uso:** Desactiva la condición durante un período de exámenes y reactívala después sin que los estudiantes tengan que volver a seleccionar.

---

## 🆕 Novedades v1.7.0

Esta versión refactoriza completamente el plugin para centrarse en condiciones específicas del curso:

- **Arquitectura Centrada en el Curso**: Se eliminaron las condiciones globales.
- **Condición Única**: Simplificación para gestionar una sola condición por curso.
- **Nueva Interfaz de Configuración**: Formulario dinámico para gestionar opciones.
- **Persistencia Mejorada**: Las selecciones se guardan de forma segura.

---

## 🚀 Instalación

### Requisitos
- Moodle 5.1 o superior
- PHP 8.0 o superior

### Pasos

1. **Generar el paquete de instalación**:
   ```bash
   bash crear-zip.sh
   ```
   Este script automáticamente:
   - Incrementa la versión del plugin (patch + build number)
   - Crea el archivo ZIP listo para instalar
   - Muestra las instrucciones de instalación

   **Nota**: Cada ejecución genera una versión nueva, forzando que Moodle detecte la actualización.

2. **Instalar en Moodle**:
   - Ve a `Administración del sitio → Plugins → Instalar plugins`
   - Sube el archivo `local_course_conditions_v{version}.zip` generado
   - Haz clic en "Instalar plugin desde archivo ZIP"
   - Completa el proceso de actualización

3. **Verificar**:
   - Confirma que se crean/actualizan las tablas en la base de datos
   - Purga todas las cachés de Moodle
   - Verifica que la nueva versión aparezca en la lista de plugins instalados

---

## ⚙️ Configuración (Administrador/Profesor)

### 1. Acceder a la Configuración

- Entra al curso como profesor o administrador
- En la **barra de navegación del curso** (donde aparece "Participantes", "Calificaciones"), busca **"Configure Course Conditions"**
- Solo visible para usuarios con permiso `local/course_conditions:configure`

### 2. Crear la Condición

1. Haz clic en **Configure Course Conditions**
2. Define:
   - **Nombre**: Nombre de la condición (ej. "Turno", "Grupo de Laboratorio")
   - **Descripción** (opcional): Explicación para los estudiantes
3. Añade **mínimo 2 opciones**, cada una con:
   - **Nombre**: Texto visible para elegir (ej. "Mañana", "Tarde")
   - **Valor**: Código interno único (ej. `turno_manana`, `turno_tarde`)
     - Este valor se guardará en el campo de perfil
     - Lo usarás para las restricciones de acceso
4. Haz clic en **Save Changes**

> **💡 Detrás de escena:**
> Al guardar, el plugin crea automáticamente un **Campo de Perfil de Usuario** personalizado:
> - **Shortname técnico**: `cond_{courseid}_{conditionid}`
> - **Nombre visible**: `[NombreCurso]` (ej: "MATE101", "FISICA2023")
> - **Valor almacenado**: La opción que eligió el estudiante (ej: "turno_manana")
> - **Ubicado en**: Administración → Usuarios → Campos de perfil de usuario → "Condiciones de Curso"

### 3. Aplicar Restricciones a Actividades

1. Edita cualquier actividad o recurso del curso
2. Despliega la sección **"Restringir acceso"**
3. Haz clic en **"Añadir restricción" → "Perfil de usuario"**
4. Selecciona el campo creado (aparecerá con el nombre corto del curso, ej: "MATE101")
5. Configura la condición:
   - "Campo de perfil de usuario" → [nombre de tu curso]
   - "debe ser igual a" → `turno_manana` (el valor técnico que definiste en las opciones)
6. Guarda los cambios

**Resultado**: Solo los estudiantes que eligieron "Mañana" verán esta actividad.

**Ejemplo práctico**:
- Campo de perfil: "MATE101"
- Condición: "debe ser igual a" → `turno_manana`
- Efecto: Solo estudiantes que seleccionaron la opción con valor `turno_manana` ven esta actividad

---

## 📖 Uso (Estudiante)

### Flujo Completo del Estudiante

#### **Primera vez que entra al curso:**

1. **Intento de acceso**: El estudiante hace clic en el curso
2. **Detección automática**:
   - El observer detecta el evento `course_viewed`
   - Verifica si existe una condición para el curso
   - Comprueba si el campo de perfil está vacío
3. **Modal bloqueante aparece**:
   - Se muestra un popup que **bloquea toda la pantalla**
   - No se puede cerrar haciendo clic fuera ni con ESC
   - Muestra el nombre y descripción de la condición
   - Presenta un dropdown con las opciones disponibles
4. **Estudiante elige**:
   - Selecciona una opción del dropdown
   - Hace clic en **"Guardar"**
5. **Guardado (AJAX)**:
   - Se envía la selección al servidor sin recargar
   - El sistema guarda:
     - Registro en `local_course_conditions_selections` (historial interno)
     - Valor en el campo de perfil personalizado (para restricciones)
   - La página se recarga automáticamente
6. **Acceso concedido**: El modal desaparece y el estudiante ve el contenido del curso

#### **Visitas posteriores:**

- El sistema verifica el campo de perfil
- Si ya tiene valor → **No muestra el modal**
- El estudiante accede normalmente
- Las restricciones de acceso se aplican automáticamente según su selección

#### **Cambiar la selección (funcionalidad opcional):**

Si un estudiante necesita cambiar su selección:

1. **Acceso**: En la barra de navegación del curso aparece **"Cambiar [NombreCondición]"** (ej: "Cambiar Turno")
   - Este enlace solo aparece si el estudiante ya ha hecho una selección previa
2. **Página de cambio**: Al hacer clic, se abre una página (no un modal bloqueante) que muestra:
   - Advertencia sobre el impacto del cambio en las restricciones de acceso
   - La selección actual del estudiante
   - Un dropdown con todas las opciones disponibles
3. **Modificar selección**:
   - El estudiante elige una nueva opción
   - Hace clic en "Guardar"
   - El sistema actualiza:
     - Tabla `local_course_conditions_selections`
     - Campo de perfil personalizado
     - Tabla de historial con el cambio realizado
4. **Efecto inmediato**: Las restricciones de acceso se aplican automáticamente con la nueva selección

---

## 🔍 Cómo Funciona el Plugin (Explicación Detallada)

### 🎨 Front-End (Vista del Usuario)

#### **Interfaz del Profesor (configure.php)**

Cuando un profesor accede a **"Configure Course Conditions"** desde la barra de navegación del curso:

1. **Carga Inicial**:
   - El navegador carga [configure.php](local/course_conditions/configure.php:27)
   - PHP verifica permisos con `require_capability('local/course_conditions:configure', $context)`
   - Si existe una condición previa, se consulta de la base de datos vía `local_course_conditions_get_conditions_for_course()`

2. **Renderizado Dinámico con JavaScript**:
   - Se inyecta el JavaScript inline en la página ([configure.php:152-284](local/course_conditions/configure.php:152-284))
   - La función `renderConditionForm()` construye el formulario HTML dinámicamente:
     ```javascript
     function renderConditionForm() {
         // Crea campos: nombre, descripción, y contenedor de opciones
         // Si hay condición existente, pre-rellena los valores
     }
     ```
   - La función `addOption()` genera filas de opciones con dos inputs:
     - **Nombre**: Lo que ve el estudiante (ej: "Mañana")
     - **Valor**: Código técnico usado en restricciones (ej: `turno_manana`)

3. **Interacción del Usuario**:
   - El profesor puede:
     - Editar nombre y descripción de la condición
     - Agregar opciones con el botón "Añadir opción"
     - Eliminar opciones (mínimo 2 requeridas)
   - Validación en tiempo real:
     - No permite eliminar si quedan solo 2 opciones
     - Campos marcados como `required` en HTML

4. **Envío del Formulario**:
   - Al hacer clic en "Guardar", el formulario se envía vía POST tradicional
   - Los datos se estructuran como:
     ```
     condition[id] = 123
     condition[name] = "Turno"
     condition[description] = "Selecciona tu turno"
     condition[options][0][id] = 456
     condition[options][0][name] = "Mañana"
     condition[options][0][value] = "turno_manana"
     ...
     ```

#### **Interfaz del Estudiante (Modal Bloqueante)**

Cuando un estudiante ingresa al curso por primera vez:

1. **Inyección del Modal**:
   - PHP llama a `local_course_conditions_inject_modal()` ([lib.php:383](local/course_conditions/lib.php:383))
   - Se ejecuta `$PAGE->requires->js_call_amd()` para cargar el módulo AMD
   - Se pasa un objeto JSON con:
     ```javascript
     {
         courseid: 123,
         condition: {id: 1, name: "Turno", description: "..."},
         options: [{id: 1, name: "Mañana"}, {id: 2, name: "Tarde"}]
     }
     ```

2. **Renderizado del Modal** ([condition_modal.js:23-102](local/course_conditions/amd/src/condition_modal.js:23-102)):
   - El método `init()` crea el HTML del modal con estilos inline
   - El modal se posiciona con `position: fixed; z-index: 9999`
   - Fondo semi-transparente (`rgba(0,0,0,0.8)`) bloquea interacción con el resto de la página
   - Se genera un `<select>` con las opciones disponibles

3. **Interacción del Estudiante**:
   - El estudiante ve el modal sobre todo el contenido
   - Debe seleccionar una opción del dropdown
   - No puede cerrar el modal (sin botón X, sin clic fuera)
   - Solo puede proceder haciendo clic en "Guardar"

4. **Guardado AJAX** ([condition_modal.js:108-145](local/course_conditions/amd/src/condition_modal.js:108-145)):
   - Al enviar, se ejecuta `saveSelection()`
   - Se muestra un spinner de carga
   - Se envía una petición AJAX POST a [ajax.php](local/course_conditions/ajax.php) con:
     ```javascript
     {
         sesskey: M.cfg.sesskey,  // Token CSRF
         courseid: 123,
         conditionid: 1,
         optionid: 2  // ID de la opción seleccionada
     }
     ```
   - Si la respuesta es exitosa (`response.success === true`), se recarga la página
   - Si hay error, se muestra una alerta y se restaura el formulario

---

### ⚙️ Back-End (Procesamiento del Servidor)

#### **Flujo de Configuración del Profesor**

1. **Recepción de Datos** ([configure.php:33-97](local/course_conditions/configure.php:33-97)):
   ```php
   if ($data = data_submitted() && confirm_sesskey()) {
       // Validar sesskey (protección CSRF)
       // Obtener $_POST['condition'] directamente
       $raw_condition = $_POST['condition'];
   ```

2. **Limpieza y Validación**:
   ```php
   // Limpiar cada campo individualmente
   $condition_name = clean_param($raw_condition['name'], PARAM_TEXT);

   // Validar que el nombre no esté vacío
   if (empty($condition_name)) {
       throw new moodle_exception(...);
   }

   // Procesar cada opción
   foreach ($raw_condition['options'] as $oid => $odata) {
       $option_name = clean_param($odata['name'], PARAM_TEXT);
       $option_value = clean_param($odata['value'], PARAM_TEXT);
       // Saltar opciones vacías
   }

   // Validar mínimo 2 opciones
   if (count($condition_data['options']) < 2) {
       throw new moodle_exception(...);
   }
   ```

3. **Guardado en Base de Datos** ([lib.php:138-253](local/course_conditions/lib.php:138-253)):
   ```php
   function local_course_conditions_save_conditions($courseid, $conditions) {
       global $DB;

       // Iniciar transacción
       $transaction = $DB->start_delegated_transaction();

       try {
           foreach ($conditions as $cond) {
               if ($cond['id']) {
                   // Actualizar condición existente
                   $DB->update_record('local_course_conditions_conditions', ...);
               } else {
                   // Insertar nueva condición
                   $cond['id'] = $DB->insert_record('local_course_conditions_conditions', ...);
               }

               // Crear campo de perfil personalizado
               local_course_conditions_create_profile_field($courseid, $cond);

               // Guardar opciones
               foreach ($cond['options'] as $opt) {
                   if ($opt['id']) {
                       $DB->update_record('local_course_conditions_options', ...);
                   } else {
                       $DB->insert_record('local_course_conditions_options', ...);
                   }
               }
           }

           // Confirmar transacción
           $transaction->allow_commit();
       } catch (Exception $e) {
           // Revertir cambios si hay error
           $transaction->rollback($e);
       }
   }
   ```

4. **Creación del Campo de Perfil** ([lib.php:188-243](local/course_conditions/lib.php:188-243)):
   ```php
   function local_course_conditions_create_profile_field($courseid, $conditionid, $conditionname) {
       // Buscar o crear categoría "Condiciones de Curso"
       $category = $DB->get_record('user_info_category', ['name' => 'Condiciones de Curso']);

       // Generar shortname único: cond_{courseid}_{conditionid}
       $shortname = "cond_{$courseid}_{$conditionid}";

       // Crear campo con valores:
       // - name: "{CourseShortname}" (ej: "MATE101")
       // - datatype: 'text'
       // - locked: 1 (estudiantes no pueden editar)
       // - visible: 2 (visible para usuario y profesores)
       $DB->insert_record('user_info_field', $field);
   }
   ```

#### **Flujo de Selección del Estudiante**

1. **Detección del Evento** ([observer.php:63-96](local/course_conditions/classes/observer.php:63-96)):
   ```php
   public static function course_viewed(\core\event\course_viewed $event) {
       global $USER, $PAGE;

       // SKIP 1: Admins nunca ven el modal
       if (is_siteadmin()) {
           return;
       }

       // SKIP 2: Profesores con permiso de configurar
       if (has_capability('local/course_conditions:configure', $context)) {
           return;
       }

       // SKIP 3: Si ya completó la selección
       if (local_course_conditions_user_has_completed_all($userid, $courseid)) {
           return;  // Verifica campo de perfil directamente
       }

       // Obtener condición del curso
       $conditions = local_course_conditions_get_conditions_for_course($courseid);
       if (empty($conditions)) {
           return;  // No hay condiciones = no hay modal
       }

       // Inyectar modal
       local_course_conditions_inject_modal($courseid, reset($conditions));
   }
   ```

2. **Verificación de Completitud** ([lib.php:336-358](local/course_conditions/lib.php:336-358)):
   ```php
   function local_course_conditions_user_has_completed_all($userid, $courseid) {
       global $DB;

       // Obtener condiciones del curso
       $conditions = local_course_conditions_get_conditions_for_course($courseid);

       foreach ($conditions as $condition) {
           // Obtener shortname del campo de perfil
           $fieldshortname = "cond_{$courseid}_{$condition->id}";

           // Verificar si el campo tiene valor
           $sql = "SELECT uid.data
                   FROM {user_info_data} uid
                   JOIN {user_info_field} uif ON uid.fieldid = uif.id
                   WHERE uid.userid = ? AND uif.shortname = ?";

           $value = $DB->get_field_sql($sql, [$userid, $fieldshortname]);

           if (empty($value)) {
               return false;  // Al menos una condición sin completar
           }
       }

       return true;  // Todas las condiciones completadas
   }
   ```

3. **Guardado de la Selección** ([ajax.php:21-69](local/course_conditions/ajax.php:21-69)):
   ```php
   // Validar sesskey (protección CSRF)
   if (!confirm_sesskey(optional_param('sesskey', '', PARAM_RAW))) {
       echo json_encode(['success' => false, 'error' => 'Invalid session key']);
       die();
   }

   // Obtener parámetros
   $courseid = required_param('courseid', PARAM_INT);
   $conditionid = required_param('conditionid', PARAM_INT);
   $optionid = required_param('optionid', PARAM_INT);

   // Verificar permisos
   require_login();
   $context = context_course::instance($courseid);

   // Iniciar transacción
   $transaction = $DB->start_delegated_transaction();

   try {
       // 1. Guardar en tabla de selecciones
       $selection = new stdClass();
       $selection->userid = $USER->id;
       $selection->courseid = $courseid;
       $selection->conditionid = $conditionid;
       $selection->optionid = $optionid;
       $selection->timeselected = time();

       // Verificar si ya existe (reemplazar)
       if ($existing = $DB->get_record('local_course_conditions_selections', ...)) {
           $selection->id = $existing->id;
           $DB->update_record('local_course_conditions_selections', $selection);
       } else {
           $DB->insert_record('local_course_conditions_selections', $selection);
       }

       // 2. Actualizar campo de perfil
       $fieldshortname = "cond_{$courseid}_{$conditionid}";
       $field = $DB->get_record('user_info_field', ['shortname' => $fieldshortname]);

       $option = $DB->get_record('local_course_conditions_options', ['id' => $optionid]);

       // Guardar el valor en el campo de perfil
       if ($data = $DB->get_record('user_info_data', ['userid' => $USER->id, 'fieldid' => $field->id])) {
           $data->data = $option->value;
           $DB->update_record('user_info_data', $data);
       } else {
           $data = new stdClass();
           $data->userid = $USER->id;
           $data->fieldid = $field->id;
           $data->data = $option->value;
           $DB->insert_record('user_info_data', $data);
       }

       // 3. Guardar en historial
       $history = new stdClass();
       $history->userid = $USER->id;
       $history->conditionid = $conditionid;
       $history->optionid = $optionid;
       $history->timemodified = time();
       $DB->insert_record('local_course_conditions_history', $history);

       // Confirmar transacción
       $transaction->allow_commit();

       echo json_encode(['success' => true]);
   } catch (Exception $e) {
       $transaction->rollback($e);
       echo json_encode(['success' => false, 'error' => $e->getMessage()]);
   }
   ```

#### **Integración con Restricciones de Acceso de Moodle**

Una vez que el estudiante ha seleccionado una opción:

1. **Campo de Perfil Poblado**:
   - El campo personalizado `cond_{courseid}_{conditionid}` ahora tiene valor
   - Por ejemplo: `turno_manana`, `grupo_a`, etc.

2. **Restricciones de Actividades**:
   - El profesor configura restricciones en actividades/recursos
   - Usa la restricción nativa "Perfil de usuario" de Moodle
   - Selecciona el campo creado por el plugin
   - Define condiciones como: "debe ser igual a `turno_manana`"

3. **Evaluación Automática**:
   - Moodle evalúa estas restricciones automáticamente
   - Compara el valor del campo de perfil del estudiante con la condición
   - Si coincide → Muestra la actividad
   - Si no coincide → Oculta la actividad (o muestra mensaje de restricción)

#### **Flujo de Cambio de Selección (change_selection.php)**

Esta funcionalidad permite a los estudiantes cambiar su selección después de la primera vez:

1. **Enlace de Navegación** ([lib.php:398-438](local/course_conditions/lib.php:398-438)):
   ```php
   function local_course_conditions_extend_navigation_course($navigation, $course, $context) {
       // Para estudiantes: mostrar enlace solo si ya tienen selección
       if (local_course_conditions_user_has_completed_all($USER->id, $course->id)) {
           $node = navigation_node::create(
               get_string('changeselection_nav', 'local_course_conditions', $condition->name),
               $url,
               ...
           );
       }
   }
   ```

2. **Página de Cambio** ([change_selection.php](local/course_conditions/change_selection.php)):
   - **NO es modal bloqueante**, es una página normal de Moodle
   - Muestra advertencia sobre impacto en restricciones
   - Obtiene selección actual: `local_course_conditions_get_user_selection()`
   - Pre-selecciona la opción actual en el dropdown
   - Permite cancelar y volver al curso

3. **Guardado del Cambio**:
   ```php
   // Dentro de transacción
   $transaction = $DB->start_delegated_transaction();

   // 1. Actualizar tabla de selecciones
   $selection->optionid = $new_optionid;
   $DB->update_record('local_course_conditions_selections', $selection);

   // 2. Actualizar campo de perfil
   local_course_conditions_save_to_profile($userid, $courseid, $conditionid, $new_value);

   // 3. Guardar en historial (para auditoría)
   $history->old_optionid = $old_optionid;
   $history->new_optionid = $new_optionid;
   $history->changed_by = $USER->id;
   $DB->insert_record('local_course_conditions_history', $history);

   $transaction->allow_commit();
   ```

4. **Historial de Cambios**:
   - Tabla `local_course_conditions_history` registra todos los cambios
   - Incluye: usuario, condición, opción anterior, opción nueva, quién hizo el cambio, timestamp
   - Útil para auditoría y seguimiento

#### **Arquitectura de Seguridad**

- **Protección CSRF**: Todos los formularios y AJAX usan `sesskey` validado con `confirm_sesskey()`
- **Validación de Permisos**: `require_capability()` y `has_capability()` en todas las operaciones sensibles
- **Limpieza de Datos**: `clean_param()` con tipos específicos (`PARAM_TEXT`, `PARAM_INT`)
- **Transacciones de BD**: `start_delegated_transaction()` y `rollback()` para garantizar integridad
- **Campos Bloqueados**: Los campos de perfil tienen `locked = 1`, estudiantes no pueden modificarlos manualmente
- **Separación de Accesos**: Profesores ven "Configure", estudiantes ven "Cambiar [Condición]"
- **Auditoría**: Tabla de historial registra todos los cambios de selección

---

## 🏗️ Arquitectura Técnica

### Tablas de Base de Datos

El plugin crea 4 tablas principales:

1. **`mdl_local_course_conditions_conditions`**: Almacena las condiciones definidas por curso
   - Cada curso puede tener una condición (ej: "Turno", "Grupo")

2. **`mdl_local_course_conditions_options`**: Opciones para cada condición
   - Cada condición debe tener al menos 2 opciones
   - Cada opción tiene un `name` (visible) y un `value` (técnico)

3. **`mdl_local_course_conditions_selections`**: Selecciones de los estudiantes
   - Registra qué opción eligió cada estudiante
   - Índice único por `userid` y `conditionid`

4. **`mdl_local_course_conditions_history`**: Historial de cambios
   - Auditoría de cambios en las selecciones

### Flujo de Eventos

1. **Event Observer**: `course_viewed` → Detecta cuando un estudiante entra al curso
2. **Verificación**: Revisa si el campo de perfil tiene valor
3. **Modal Injection**: Si no tiene valor, inyecta JavaScript AMD module
4. **AJAX Save**: Guarda en tabla `selections` y campo de perfil vía `ajax.php`
5. **Reload**: Página se recarga, modal desaparece

### Campos de Perfil Automáticos

- **Categoría**: "Condiciones de Curso"
- **Shortname**: `cond_{courseid}_{conditionid}` (ej: `cond_5_1`)
- **Nombre visible**: `{CourseShortname}` (ej: "MATE101", "FISICA2023")
- **Valor almacenado**: Opción seleccionada por el estudiante (ej: "turno_manana", "grupo_a")
- **Tipo**: Text field (locked, visible a usuario y profesores)

Estos campos se usan en las restricciones de acceso nativas de Moodle.

---

## 🔒 Privacidad y GDPR

El plugin implementa la API de Privacidad de Moodle (`privacy/provider.php`).
- **Exportación**: Los usuarios pueden exportar sus datos de selección.
- **Eliminado**: Los datos se eliminan si se solicita el borrado del usuario o si se elimina el curso/condición.
