# Marketplace

A marketplace web application developed in PHP and TypeScript (Laravel, Vue.js), similar in functionality to Letgo or Facebook's marketplace.

## Main libraries/frameworks used

* Backend
    * *[Laravel 5.6](https://laravel.com)*
    * *[Socket.io](https://socket.io/)*
    * *[TNTSearch](https://github.com/teamtnt/tntsearch)*
    * *[Intervention Image](http://image.intervention.io/)*
* Frontend
    * *[Vue.js](https://vuejs.org/)*
    * *[Bootstrap 4](https://getbootstrap.com/)*


## Functionality

* A user may create offers which are then shown to other users in a feed-like view.
Existing offers can be edited or removed.
* An offer disappears (expires) after two months since it is published.
The owner of the offer is, however, able to 'bump' the offer to make it reappear on top as new at any time.
This functionality may only be used twice per offer at most.
* An offer is removed entirely after a year since it is published or bumped.
* **Fulltext search** is available for users to search for other users' offers.
* Whenever a user wants to buy an item, he/she is redirected to a **realtime chat** with the owner of the offer.
* Users are notified about their chat messages over e-mail. (Only once per day per conversation!)
* Users can **report inappropriate offers to administrators**.
* Administrators may edit and remove all offers. They may also ban users, who are then unable to reuse their e-mail addresses.

## Documentation

Available [here](https://neumann.gitbook.io/marketplace).