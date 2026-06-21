# Contributing to Course access (local_courseaccess)

Thanks for your interest in improving the **Course access** plugin.

## Ground rules
- The plugin is licensed under the **GNU GPL v3 or later**. By contributing you agree your
  contribution is released under the same license.
- Follow the [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle)
  and the [Moodle development policies](https://moodledev.io/general/development/policies).
- Keep all code comments and identifiers in **English**.
- Preserve existing copyright notices; add your own `@copyright` line rather than replacing one.

## Development
- Plugin source lives in [`workspace/local_courseaccess`](workspace/local_courseaccess).
- The AMD modules are **ES6** (`import`/`export`). After changing `amd/src/*.js`, rebuild with
  `grunt amd` (rollup) against a Moodle checkout; do **not** hand-edit `amd/build/*`.
- Bump `$plugin->version`/`$plugin->release` in `version.php` and add an upgrade step for any DB
  change, and update [`CHANGELOG.md`](workspace/local_courseaccess/CHANGELOG.md).

## Before opening a pull request
Run the same checks CI runs (via [moodle-plugin-ci](https://github.com/moodlehq/moodle-plugin-ci)):

```bash
moodle-plugin-ci phpcs --max-warnings 0
moodle-plugin-ci phpdoc --max-warnings 0
moodle-plugin-ci mustache
moodle-plugin-ci grunt --max-lint-warnings 0
moodle-plugin-ci phpunit
moodle-plugin-ci behat --profile chrome
```

## Reporting issues
Please use the GitHub issue tracker:
<https://github.com/rurak-ec/moodle-local_courseaccess/issues>.
Include your Moodle version, PHP version, and steps to reproduce.

---

## Español

Gracias por tu interés en mejorar **Course access**. Las contribuciones se publican bajo **GNU GPL v3
o posterior**; sigue el estilo de código y las políticas de desarrollo de Moodle, y mantén comentarios
e identificadores en **inglés**. El código fuente está en `workspace/local_courseaccess`; los módulos
AMD son ES6 y se compilan con `grunt amd` (no edites `amd/build/` a mano). Antes de un pull request,
ejecuta las comprobaciones de `moodle-plugin-ci` indicadas arriba. Reporta incidencias en el issue
tracker de GitHub indicando versión de Moodle, de PHP y pasos para reproducir.
