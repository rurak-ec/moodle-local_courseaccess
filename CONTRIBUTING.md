# Contributing to Condiciones de Curso

¡Gracias por tu interés en contribuir! Este documento proporciona lineamientos para contribuir al plugin.

## 🚀 Cómo Contribuir

### Reportar Bugs

Si encuentras un bug, por favor crea un issue incluyendo:

- **Descripción clara**: Qué esperabas que sucediera vs. qué sucedió
- **Pasos para reproducir**: Lista detallada de pasos
- **Versión de Moodle**: Qué versión estás usando
- **Versión del Plugin**: Visible en Administración → Plugins
- **Logs de error**: Si están disponibles

### Sugerir Mejoras

Las sugerencias de nuevas funcionalidades son bienvenidas. Abre un issue describiendo:

- **Caso de uso**: Por qué esta funcionalidad sería útil
- **Implementación propuesta**: Si tienes ideas de cómo implementarla
- **Alternativas consideradas**: Otras formas de lograr lo mismo

### Pull Requests

1. **Fork** el repositorio
2. **Crea una rama** desde `main`:
   ```bash
   git checkout -b feature/mi-nueva-funcionalidad
   ```
3. **Realiza tus cambios** siguiendo los estándares de código
4. **Añade tests** si es aplicable
5. **Commit** con mensajes descriptivos:
   ```bash
   git commit -m "Add: descripción de la funcionalidad"
   ```
6. **Push** a tu fork:
   ```bash
   git push origin feature/mi-nueva-funcionalidad
   ```
7. **Abre un Pull Request** describiendo los cambios

## 📝 Estándares de Código

### Moodle Coding Style

Este plugin sigue los [Moodle Coding Standards](https://moodledev.io/general/development/policies/codingstyle):

- **Indentación**: 4 espacios (no tabs)
- **Nombres de funciones**: `snake_case` con prefijo `local_course_access_`
- **Nombres de clases**: `PascalCase` en namespace `local_course_access`
- **Comentarios**: PHPDoc para todas las funciones públicas
- **Headers**: Licencia GPL en todos los archivos PHP

### Ejemplo de Función

```php
/**
 * Brief description of what this function does
 *
 * Longer description providing more context about the function's
 * purpose, parameters, and return value.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return bool True on success, false otherwise
 */
function local_course_access_example_function($userid, $courseid) {
    global $DB;
    
    // Implementation here
    return true;
}
```

### Base de Datos

- **Prefijo de tablas**: `local_course_access_`
- **Nombres de columnas**: `lowercase` con guiones bajos
- **Timestamps**: Siempre incluir `timecreated` y `timemodified`
- **Índices**: Agregar índices a columnas frecuentemente consultadas

### JavaScript

- **Formato AMD**: Todos los módulos JS deben usar formato AMD de Moodle
- **Ubicación**:
  - Fuente: `amd/src/`
  - Build: `amd/build/` (generado, no editar manualmente)
- **Lint**: Seguir estándares ESLint de Moodle

### Language Strings

- **Archivo**: `lang/[idioma]/local_course_access.php`
- **Formato**: `$string['key'] = 'Value';`
- **Naming**: Descriptivo y consistente
- **Ambos idiomas**: Siempre actualizar `en` y `es`

## 🧪 Testing

### Tests Manuales

Antes de hacer PR, prueba:

1. **Instalación limpia**: Plugin se instala sin errores
2. **Actualización**: Plugin se actualiza correctamente desde versión anterior
3. **Funcionalidad básica**:
   - Profesor puede configurar condiciones
   - Estudiante ve modal en primera visita
   - Estudiante puede cambiar selección
   - Restricciones de acceso funcionan
4. **Permisos**: Verificar que roles funcionan correctamente
5. **Diferentes navegadores**: Chrome, Firefox, Safari

### Checklist antes del PR

- [ ] Código sigue Moodle Coding Standards
- [ ] Sin errores de sintaxis PHP
- [ ] Language strings actualizados (en/es)
- [ ] README actualizado si es necesario
- [ ] CHANGELOG actualizado
- [ ] Versión incrementada en `version.php`
- [ ] Tested en Moodle 5.1+
- [ ] No hay warnings en logs de Moodle

## 📚 Recursos

- [Moodle Developer Documentation](https://moodledev.io/)
- [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle)
- [Moodle Plugin Types](https://moodledev.io/docs/apis/plugintypes)
- [Moodle Database API](https://moodledev.io/docs/apis/core/dml)

## 📞 Contacto

- **Issues**: [GitHub Issues](https://github.com/rurak-ec/moodle-course_access/issues)
- **Discussions**: [GitHub Discussions](https://github.com/rurak-ec/moodle-course_access/discussions)

## 📄 Licencia

Al contribuir, aceptas que tus contribuciones se licencien bajo GNU GPL v3+.
