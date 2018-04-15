# Uživatelské rozhraní

## Hlavní okno

![Main Interface Showcase](../images/main.png)

* Hlavní navigace
    * č. 1 - ![Nástěnka](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/home.png ':no-zoom') *Nástěnka* - přehled nejnovějších nabídek
    * č. 2 - ![Hledat](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/search.png ':no-zoom') *Hledat* 
    * č. 3 - ![Můj profil](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/user.png ':no-zoom') *Můj profil* - profil přihlášeného uživatele (pouze přihlášení uživatelé)
    * č. 4 - ![Administrace](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/flag.png ':no-zoom') *Administrace* (pouze administrátoři)
    * č. 5 - ![Mé nastavení](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/cog.png ':no-zoom') *Mé nastavení* - current user's profile settings  (pouze přihlášení uživatelé)
        * Alternativně: ![Registrovat se](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/user-plus.png ':no-zoom') *Registrovat se* (pouze anonymní uživatelé)
    * č. 6 - ![Odhlásit se](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/sign-out.png ':no-zoom') *Odhlásit se*  (pouze přihlášení uživatelé)
        * Alternativně: ![Přihlásit se](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/sign-in.png ':no-zoom') *Přihlásit se* (pouze anonymní uživatelé)
* č. 7 - Pole pro vyhledávání nabídek
* č. 8 - Hlavní obsah, například přehled nabídek
* č. 9 - Tlačítka akce / vyskakovacích oken
    * ![Vytvořit novou nabídku](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/plus.png ':no-zoom') *Vytvořit novou nabídku* (odkaz na formulář)
    * ![Chat](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/comment.png ':no-zoom') *Chat* - otevře vyskakovací okno pro chat
    * ![Jít zpět](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/chevron-left.png ':no-zoom') *Jít zpět*
    * ![Jít nahoru](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/chevron-up.png ':no-zoom') *Jít nahoru*

## Karta nabídky

![Karta nabídky (../images/offer.png)
![Karta nabídky (../images/offer_large.png)

* č. 1 - *Profil autora* - jméno k zobrazení, uživatelské jméno, odkaz na profil
* č. 2 - *Hlavní fotografie nabídky* (kompaktní verze) / *Přepínač všech obrázků* (rozšířená verze)
* č. 3 - *Jméno nabídky*
* č. 4 - *Počítadlo nahlášení nevhodnosti* (viditelné pouze administrátorům)
    * Počítadlo, kolikrát byla nabídka uživateli nahlášena jako nevhodná
* č. 5 - *Popis nabídky*
* č. 6 - *Cena*
    * Má pouze informativní význam. Podmínky prodeje jsou předmětem konverzace mezi prodejcem a kupujícím. K tomuto účelu je určena služba chatu v aplikaci.
* č. 7 - *Tlačítko žádosti o nákup*
    * Odešle zprávu vlastníkovi do chatu a otevře příslušné vyskakovací okno chatu.
* č. 8 - *Dodatečná nastavení nabídky*
    * *Upravit / smazat nabídku* (vlastník nebo administrátoři)
    * *"Popostrčit" nabídku* (vlastník)
    * *Nahlásit nabídku* (přihlášení uživatelé)
    * *Označit nabídku za vhodnou* (administrátoři)
* č. 9 - *Zobrazit více* - otevřít navídku ve vyskakovacím okně nebo na separátní stránce

## Navigace profilu uživatele

![Navigace profilu uživatele](../images/user_navigation.png)
![Navigace profilu přihlášeného uživatele](../images/user_navigation_this.png)

* č. 1 - *Profilový obrázek*
* č. 2 - *Zobrazované jméno uživatele*
* č. 3 - *Uživatelské jméno*
* č. 4 - *Otevřít chat* (viditelné pouze přihlášeným uživatelům)
    * otevře vyskakovací okno chatu s daným uživatelem
* č. 5 - *Udělit ban / očistit od banu* (viditelné pouze administrátorům)
* č. 6 - *Záložka Můj profil* (viditelné pouze vlastníkovi profilu)
    * Zobrazí uživatelovy nejnovější nabídky (ostatním uživatelům zobrazeno automaticky)
* č. 7 - *Záložka Mé nastavení* (viditelné pouze vlastníkovi profilu)
    * Zobrazí uživatelské nastavení

## Formulář pro tvorbu/úpravu nabídky

![Formulář pro tvorbu/úpravu nabídky](../images/offer_form.png)

* č. 1 - *Jméno*
* č. 2 - *Popis*
* č. 3 - *Cena*
* č. 4 - *Výběr měny*
* č. 5 - *Nahrávání fotografií*
* č. 6 - *Řazení fotografií*
    * Řazení fotografií lze změnit tažením
* č. 7 - *Již nahrané fotografie* (pouze při úpravě existující nabídky)
    * Lze odstranit červeným tlačítkem v pravém horním rohu náhledu.
* č. 8 - *Fotografie k nahrání*
    * Lze odstranit výběrem jiných fotografií k nahrání.
* č. 9 - *Tlačítko Obnovit původní fotografie* (pouze při úpravě existující nabídky)
    * Obnoví fotografie a jejich řazení na původní hodnoty
* č. 10 - *Tlačítko Zveřejnit*
    * Zveřejní novou nabídku nebo aktualizuje upravovanou.

## Vyskakovací okno - chat

### Přehled konverzací

Seřazeno od nejnovějších.

![Vyskakovací okno - chat (../images/chat_conversations.png)

### V konverzaci

![Vyskakovací okno - chat (../images/chat_user.png)

* č. 1 - *Tlačítko Zpět*
    * Návrat do přehledu konverzací
* č. 2 - *Profil uživatele* 
    * Profilový obrázek, jméno uživatele (varianta určená k zobrazení, ne uživatelské jméno), odkaz na profil.
* č. 3 - *Zprávy* 
* č. 4 - *Indikátor stavu zprávy*
    * *Přečteno* (zobrazen profilový obrázek)
    * ![Přijato](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/check-circle.png ':no-zoom') *Přijato*
    * ![Odesláno](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/check-circle-o.png ':no-zoom') *Odesláno*
    * ![Odesílání](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/circle-o.png ':no-zoom') *Odesílání*
* č. 5 - *Indikátor psaní* 
* č. 6 - *Vstupní pole pro obsah sdělení* 
* č. 6 - *Tlačítko odeslání zprávy* 