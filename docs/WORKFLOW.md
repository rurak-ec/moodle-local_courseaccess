# Workflow de desarrollo

## 1) Validar estructura local

```bash
./scripts/verify_isolation.sh
```

## 2) Editar plugin

Trabaja en:
- `workspace/local_course_conditions`

Archivos más usados:
- `workspace/local_course_conditions/configure.php`
- `workspace/local_course_conditions/lib.php`
- `workspace/local_course_conditions/amd/src/condition_modal.js`
- `workspace/local_course_conditions/version.php`

> Si editas `amd/src/`, regenera el build con `grunt amd` en tu Moodle (el módulo cargado es `amd/build/condition_modal.min.js`).

## 3) Empaquetar ZIP instalable

```bash
./scripts/package_workspace.sh
```

Salida:
- `build/course_conditions_YYYYMMDD_HHMMSS.zip` (carpeta interna `course_conditions/`).

## 4) Instalar/probar en Moodle

- Descomprimir en `local/course_conditions`
- Ejecutar:
  ```bash
  php admin/cli/upgrade.php --non-interactive
  php admin/cli/purge_caches.php
  ```

## 5) Publicar cambios en GitHub

```bash
git add .
git commit -m "feat: cambios en local_course_conditions"
git push
```
