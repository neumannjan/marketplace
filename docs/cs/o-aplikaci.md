# O aplikaci

## Funkce

* Uživatelé vytvářejí nabídky, které jsou následně zobrazovány ostatním uživatelům v **aktuálním přehledu nejnovějších nabídek**.

  Existující nabídky mohou být vlastníky upravovány a mazány.

  * Nabídka po dvou měsících od zveřejnění automaticky "vyprší" (je skryta).

    Vlastník nabídky má kdykoli (i po jejím vypršení) možnost nabídku "popostrčit", čímž zajistí, že se nabídka znovu objeví v přehledech jako úplně nová. Toto lze provést s každou nabídkou nanejvýš dvakrát.

  * Nabídka je zcela odstraněna po roce od zveřejnění nebo posledního "popostrčení".

  * **Fulltextové vyhledávání** mezi zveřejněnými nabídkami je k dispozici.

* Chce-li uživatel nějaký zveřejněný produkt koupit, je automaticky spuštěn **chat** s autorem dané nabídky.

  * Uživatelé jsou o přijatých zprávách v chatu upozorňováni přímo v aplikaci a přes e-mail. (E-maily jsou odesílány jen jednou denně pro každou konverzaci.)

* Uživatelé mohou **nahlašovat nevhodné nabídky administrátorům**.

  * Administrátoři mohou upravovat i mazat všechny nabídky. Také mohou udělit kterémukoli uživateli ban. Zabanovaní uživatelé nemohou při nové registraci použít svoji původní e-mailovou adresu.

![Reprezentativní snímek obrazovky](https://github.com/kogli/marketplace/raw/master/screenshot.png)

## Použitý software

* Backend
  * [_Laravel 5.6_](https://laravel.com)
  * [_Socket.io_](https://socket.io/)
  * [_TNTSearch_](https://github.com/teamtnt/tntsearch)
  * [_Intervention Image_](http://image.intervention.io/)
* Frontend
  * [_Vue.js_](https://vuejs.org/)
  * [_Bootstrap 4_](https://getbootstrap.com/)

Další závislosti třetích stran jsou specifikovány v [pokynech pro instalaci](instalace.md) a v souborech `composer.json` a `package.json`.