# Tasks for Today

For InfinityFree upload instructions and feature disclosure, see `UPLOAD_INFINITYFREE.md`.

This archive includes Laravel MVC source, migrations and a Mailable, plus a standalone PHP fallback in `fallback/app.php`. When `vendor/autoload.php` is missing, `public/index.php` runs the fallback. The fallback supports public pages, account registration/login, per-user task editing and archiving, validation, CSRF tokens, and one completion log entry per status change to done. It does **not** implement Laravel Breeze or deliver emails through Laravel Mail; do not submit it as satisfying the complete TSA4 Laravel rubric.
