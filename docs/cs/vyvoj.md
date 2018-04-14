# Vývoj

Větev `master` obsahuje, na rozdíl od větve `dev`, zkompilované CSS a JS soubory.

## Kompilace CSS a JS

Použit je [Webpack 4](https://webpack.js.org/). Toto jsou relevantní příkazy:

```bash
# Kompilace pro vývoj:
$ npm run dev

# Kompilace pro vývoj a sledování úprav souborů:
$ npm run watch

# Kompilace pro vydání:
$ npm run prod
```

## Plnění databáze falešným obsahem

{% hint style="warning" %}
Při používání poskytované Docker konfigurace je nutné dbát na [komplikace přístupu k databázi](instalace.md#komplikace-pristupu-k-databazi)!
{% endhint %}

```bash
$ php artisan db:seed
```

Zavoláním tohoto příkazu byly patrně vytvořeny soubory `storage/search/users.index` a `storage/search/offers.index`.
Zajistěte, aby tyto soubory byly čitelné a zapisovatelné webovým serverem. (Toto je při použití poskytované Docker konfigurace zajištěno automaticky.)

## Spouštění testů

Použity jsou nástroje [Codeception](https://codeception.com/). Bohužel, drtivá většina aplikace není kryta testy z důvodu časové tísně.

Testy jsou určeny pro použití s poskytovanou Docker konfigurací. Aplikace by ale měla být spouštěna odlišně:

```bash
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml up
```

Tento příkaz spouští [Selenium](https://www.seleniumhq.org/) společně se samotnou aplikací.

Pro ukončení běhu aplikace zavolejte následující příkaz:

```bash
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml down
```