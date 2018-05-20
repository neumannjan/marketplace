# Instalace

## Požadavky

### Backend

* _PHP &gt;= 7.1.3_
  * _Composer_
  * _Intl rozšíření_ (pro formátování cen a měn)
  * _SQlite3 rozšíření_ (požadavek implementace vyhledávání)
  * _OpenSSL rozšíření_
  * _PDO rozšíření_
  * _Mbstring rozšíření_
  * _Tokenizer rozšíření_
  * _XML rozšíření_
  * _Ctype rozšíření_
  * _JSON rozšíření_
* _Redis 3+_
* _MySQL &gt;= 5.7_ (jiné databázové platformy pravděpodobně nebudou fungovat)
* _Lokální úložiště_ pro nahrané obrázky
  * Podpora jiných souborových systémů (např. S3) není naprogramována.
* _Jakákoli e-mailová služba_ podporovaná Laravel frameworkem, případně _SMTP server_.

### WebSocket Server

* _Node.js_ (nejlépe alespoň aktuální LTS)
  * _npm_
* _Redis 3+_

## Docker

Konfigurační soubor [Docker](https://www.docker.com/) kontejnerů `docker-compose.yml` je k dispozici pro rychlé splnění všech požadavků a spuštění aplikace s minimem dodatečných kroků.

Obstaráno je vše níže uvedené:

* Instalace a konfigurace Apache, PHP, Redis, MySQL a Node.js
* Spuštění HTTP serveru
* Spuštění WebSocket serveru
* Spuštění [queue workeru](https://laravel.com/docs/5.5/queues) Laravel frameworku
* Pravidelné spouštění příkazu `schedule:run` Laravel frameworku každou minutu pomocí cron.

!> **Použitá Docker konfigurace je určena pouze pro lokální používání**. Pro úpravu pro použití v praxi prosím zkontrolujte správné nastavení souboru `docker-compose.yml` a složky `docker`, která obsahuje `Dockerfile` soubory.

### Použití

Nástroje Docker a `docker-compose` (nejnovější verze - doporučeno stáhnout pomocí nástroje `pip`) jsou vyžadovány. Vše lze poté spustit pomocí jednoho příkazu:

```bash
$ docker-compose up
```

Po zavolání tohoto příkazu postupujte podle [#návod-k-instalaci](návodu k instalaci) níže.

Aplikace bude následně k dispozici na této adrese:
```
http://localhost:8080/
```

Pro ukončení běhu aplikace zavolejte následující příkaz:

```bash
$ docker-compose down
```

### Komplikace přístupu k databázi

Všechny `php artisan` příkazy, které přistupují k databázi, musí být volány v Docker kontejneru `web`. Volat je lze takto:

```bash
$ docker-compose exec web php app/artisan <zbytek příkazu>
```

Alternativně lze provést toto:

```bash
$ docker-compose exec web bash
$ cd app
```

Po zavolání těchto příkazů se budete nacházet ve správném Docker kontejneru a budete moci volat všechny `php artisan` příkazy obvyklým způsobem.

Následně kontejner opustíte takto:

```bash
$ exit
```

## Návod k instalaci

?> Dobrá znalost [Laravel](https://laravel.com/docs/5.6) je pro instalaci a správu běžící aplikace výhodou.
  Níže uvedené instrukce ovšem všechny potřebné informace pro instalaci obsahují.

Přejděte do složky projektu pomocí `cd`.

### Zajistěte správná oprávnění adresářů

Přečtěte si [dokumentaci Laravel frameworku](https://laravel.com/docs/5.6/installation#configuration) pro více informací.

### Nakonfigurujte HTTP server (Apache, Nginx,...)

?> Používáte-li poskytovanou Docker konfiguraci, tento krok přeskočte.

Kořenový veřejný adresář HTTP serveru by měl být adresář `public`.

### Nainstalujte Composer závislosti

```bash
# Pokud NEPOUŽÍVÁTE poskytovanou Docker konfiguraci
$ composer install --no-dev

# Pokud používáte poskytovanou Docker konfiguraci
$ docker-compose exec php sh -c "cd app && composer install --no-dev"
```

### Nainstalujte NPM závislosti

```bash
# Pokud NEPOUŽÍVÁTE poskytovanou Docker konfiguraci
$ npm install --production

# Pokud používáte poskytovanou Docker konfiguraci
$ docker-compose exec nodejs sh -c "cd app && npm install --production"
```

Pokud budete upravovat a [kompilovat CSS a JS](cs/vyvoj.md#kompilace-css-a-js), vynechte `--production`.

### Připravte soubor `.env`

Přejmenujte `.env.example` na `.env`.

```bash
$ cp .env.example .env
```

Proměnné `APP_ENV` a `APP_DEBUG` jsou předem nakonfigurovány pro vývoj. Pro použití aplikace v praxi je nastavte takto:

```text
APP_ENV=production
APP_DEBUG=false
```

Nastavte `APP_NAME` dle vlastní libosti.

### Nastavte dodatečné proměnné `.env`

?> Používáte-li poskytovanou Docker konfiguraci, tento krok přeskočte.

Nastavte proměnnou `APP_URL`.

### Nakonfigurujte queue worker a plánovač úloh

?> Používáte-li poskytovanou Docker konfiguraci, tento krok přeskočte.

Přečtěte si [dokumentaci Laravel frameworku](https://laravel.com/docs/5.6/scheduling#introduction) pro více informací o plánování úloh.

Zajistěte spouštění následující služby: (těchto procesů je možné mít spuštěno více najednou)

```bash
$ php artisan queue:work --sleep=3 --tries=3
```

!> Queue worker je potřebný pro indexování dat pro vyhledávač a pro úpravu velikosti nahraných obrázků.
  Pokud nechcete používat frontu (_nedoporučujeme_), nastavte v `.env` souboru `SCOUT_QUEUE` proměnnou na `false` a `QUEUE_DRIVER` proměnnou na `sync`.

### Nakonfigurujte připojení k MySQL a Redis

?> Používáte-li poskytovanou Docker konfiguraci, tento krok přeskočte.

Konfiguraci lze provést v souborech `.env` nebo `config/database.php`. Více informací najdete v [dokumentaci Laravel frameworku](https://laravel.com/docs/5.6).

Jiná databázová řešení než MySQL &gt;= 5.7 nejsou podporována. Dotazy specifické pro MySQL jsou volány funkcí `scopeConversationsWith` v souboru `app/Message.php`.

### Nakonfigurujte odesílání e-mailu

!> Toto není automaticky konfigurováno pomocí poskytované Docker konfigurace! Ve výchozím nastavení nejsou žádné e-maily odesílány, informace o nich je pouze zaznamenána do souboru `storage/logs/laravel.log`.

Přečtěte si [dokumentaci Laravel frameworku](https://laravel.com/docs/5.6/mail#introduction).

### Vygenerujte unikátní klíč aplikace

```bash
$ php artisan key:generate
```

### Zavolejte databázové migrations

!> Při používání poskytované Docker konfigurace je nutné dbát na [komplikace přístupu k databázi](#komplikace-přístupu-k-databázi)!

```bash
$ php artisan migrate
```

### Vytvořte administrátorský účet

!> Při používání poskytované Docker konfigurace je nutné dbát na [komplikace přístupu k databázi](#komplikace-přístupu-k-databázi)!

```bash
$ php artisan user:create <jmeno> <email> <heslo> -a
```

Možnost `-a` udělí vytvářenému uživateli administrátorská oprávnění.

?> Pokud tento příkaz zavoláte špatně (například s překlepy v e-mailu, hesle nebo uživatelském jméně), je nutné databázi upravit ručně (tabulka `users`).
  Alternativně lze smazat veškerá data z databáze pomocí `php artisan migrate:fresh`.

### Nastavte oprávnění nově vytvořených souborů

?> Používáte-li poskytovanou Docker konfiguraci, tento krok přeskočte.

Vytvořením nového uživatele došlo k vytvoření souboru `storage/search/users.index`. Zajistěte, aby byl soubor čitelný a upravitelný webovým serverem.

### Nakonfigurujte a spusťte WebSocket server

?> Používáte-li poskytovanou Docker konfiguraci, tento krok přeskočte.

Nejprve nakonfigurujte proměnnou `WEBSOCKET_PORT`, která určuje, na kterém portu WebSocket server poběží. Soubor `.env` je jediné místo, kde je třeba tuto proměnnou nakonfigurovat (pokud nepoužíváte poskytovanou Docker konfiguraci a `docker-compose.yml`).

Poté zajistěte, aby proměnná `WEBSOCKET_APP_ENDPOINT` odpovídala proměnné `APP_ENV`. Alternativně můžete tuto proměnnou smazat úplně.

Následně službu spusťte:

```bash
$ node websocket
```

Použit je [laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server). Prohlédněte si spodek souboru `websocket` (v kořenovém adresáři projektu), abyste porozuměli, jak je nakonfigurován (při spuštění automaticky tahá informace ze souboru `.env`).
