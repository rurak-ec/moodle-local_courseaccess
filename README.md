# moodle-course_conditions

Repositorio de desarrollo del plugin Moodle **`local_course_conditions`** (nombre visible: **Condiciones del Curso**).

El plugin permite al profesorado definir condiciones específicas de un curso (p. ej. "Turno", "Grupo de Laboratorio", "Modalidad") y obliga al estudiante a seleccionar una opción —mediante un modal bloqueante en la primera visita al curso— antes de acceder al contenido. La selección se guarda en campos de perfil personalizados para usarla con las restricciones de acceso nativas de Moodle.

## Estado del proyecto

- Componente técnico: `local_course_conditions`
- Carpeta fuente principal: `workspace/local_course_conditions`
- Moodle objetivo: 5.1+ (`requires 2024042200`)
- Madurez: `MATURITY_STABLE`

## Documentación principal del plugin

- README del plugin: [`workspace/local_course_conditions/README.md`](workspace/local_course_conditions/README.md)
- Notas de cambios: [`workspace/local_course_conditions/CHANGELOG.md`](workspace/local_course_conditions/CHANGELOG.md)

## Estructura del repositorio

- `workspace/local_course_conditions`: código del plugin
- `scripts/`: utilidades de verificación/empaquetado
- `docs/`: documentación técnica del flujo
- `build/`: artefactos ZIP generados (no fuente del plugin)

## Flujo rápido

```bash
./scripts/verify_isolation.sh

# Editar el plugin en:
# workspace/local_course_conditions

# Empaquetar ZIP instalable:
./scripts/package_workspace.sh
```

## Instalación en Moodle destino

1. Copiar `workspace/local_course_conditions` a `local/course_conditions`.
2. Ejecutar:

   ```bash
   php admin/cli/upgrade.php --non-interactive
   php admin/cli/purge_caches.php
   ```

3. Verificar en Administración del sitio > Plugins > Plugins locales.

## Licencia

GNU GPL v3 o posterior. Ver [LICENSE](LICENSE).
