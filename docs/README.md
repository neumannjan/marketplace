# Marketplace

A marketplace web application developed in PHP and TypeScript \(Laravel, Vue.js\), similar in functionality to Letgo or Facebook's Marketplace.

![A Representative Screenshot](/images/screenshot.png)

## Functionality

* Users upload offers which are then shown to other users in a **feed-like view of the newest offers**.

  Existing offers can be edited or removed by their owners.

  * An offer disappears \(expires\) after two months since its publication.

    The owner of the offer is able to 'bump' the offer to make it reappear on top as new at any time, even after it had expired. This functionality may, however, only be used twice per offer at most.

  * An offer is removed entirely after a year since it is published or bumped.

  * **Fulltext search** is available for published offers.

* Whenever a user wants to buy a product, he/she is automatically redirected to a **chat** with the author of the offer.

  * Users are notified about received chat messages directly in the application and over e-mail. \(E-mails are sent only once per day per conversation.\)

* Users can **report inappropriate offers to administrators**.

  * Administrators may edit and remove all offers. They may also ban any user. Banned users are unable to reuse their e-mail addresses in new registrations.

## Main Libraries/Frameworks Used

* Backend
  * [_Laravel 5.6_](https://laravel.com)
  * [_Socket.io_](https://socket.io/)
  * [_TNTSearch_](https://github.com/teamtnt/tntsearch)
  * [_Intervention Image_](http://image.intervention.io/)
* Frontend
  * [_Vue.js_](https://vuejs.org/)
  * [_Bootstrap 4_](https://getbootstrap.com/)

Other third-party dependencies are specified in [installation instructions](installation.md), `composer.json` and `package.json`.