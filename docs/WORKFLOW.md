# Workflow de desarrollo

## 1) Validar estructura local

```bash
./scripts/verify_isolation.sh
```

## 2) Editar plugin

Trabaja en:
- `workspace/local_courseaccess`

Archivos más usados:
- `workspace/local_courseaccess/configure.php`
- `workspace/local_courseaccess/lib.php`
- `workspace/local_courseaccess/amd/src/condition_modal.js`
- `workspace/local_courseaccess/version.php`

> Si editas `amd/src/`, regenera el build con `grunt amd` en tu Moodle (el módulo cargado es `amd/build/condition_modal.min.js`).

## 3) Empaquetar ZIP instalable

```bash
./scripts/package_workspace.sh
```

Salida:
- `build/courseaccess_YYYYMMDD_HHMMSS.zip` (carpeta interna `courseaccess/`).

## 4) Instalar/probar en Moodle

- Descomprimir en `local/courseaccess`
- Ejecutar:
  ```bash
  php admin/cli/upgrade.php --non-interactive
  php admin/cli/purge_caches.php
  ```

## 5) Publicar cambios en GitHub

```bash
git add .
git commit -m "feat: cambios en local_courseaccess"
git push
```
