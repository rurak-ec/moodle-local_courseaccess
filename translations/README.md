# Translations (kept out of the shipped plugin)

The Moodle plugins directory policy is that **only the English language pack ships with a plugin**:

> "Just the English strings should ship with the plugin. All other translations are supposed to be
> submitted as contributions at lang.moodle.org once your plugin is approved."
> — <https://moodledev.io/general/community/plugincontribution/checklist>

So the canonical English pack lives in the plugin (`workspace/local_course_access/lang/en/`), and any
other language is **kept here, outside the plugin**, so it is *not* packaged into the released ZIP.

## What's here
- `es/local_course_access.php` — Spanish translation, kept in sync with the English keys.

## How to use it

### After the plugin is approved (recommended)
Contribute the Spanish strings to **lang.moodle.org (AMOS)**. Once accepted, Spanish reaches **every**
Spanish Moodle site (including yours) through the normal language-pack update mechanism — no shipping
required. See <https://moodledev.io/general/community/plugincontribution/translatingplugins>.

### On your own site, before approval (optional, not for redistribution)
Copy `es/local_course_access.php` to `local/course_access/lang/es/local_course_access.php` on **your**
install (do not commit it into the published plugin), or use **Site administration → Language →
Language customisation** to override the `local_course_access` strings for `es`.

## Keeping it in sync
When you add/change a key in `workspace/local_course_access/lang/en/local_course_access.php`, mirror it
here so the Spanish stays complete for the eventual AMOS submission.
