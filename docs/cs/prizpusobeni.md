# Přizpůsobení

Všechna konfigurace spjatá s přizpůsobováním aplikace je k dispozici v souboru `config/app.php`.

## Název aplikace

Nastavte klíč `name` v souboru `config/app.php` a proměnnou `APP_NAME` v souboru `.env`.

## Jazyk aplikace

Aplikace je k dispozici v angličtině a češtině.

Více informací u klíčů `available_locales`, `locale` a `fallback_locale` v souboru `config/app.php`.

Soubory s překlady se nachází v adresáři `resources/lang`, což odpovídá standardům Laravel.

## Měny

Lze nakonfigurovat, které měny jsou k výběru ve formuláři pro tvorbu nových nabídek a která měna je vybrána ve výchozím stavu. Viz klíče `available_currencies` a `currency` v souboru `config/app.php`.
