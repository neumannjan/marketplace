# About

## Functionality

* A user may create offers which are then shown to other users in a feed-like view.

  Existing offers can be edited or removed.

* An offer disappears \(expires\) after two months since it is published.

  The owner of the offer is, however, able to 'bump' the offer to make it reappear on top as new at any time.

  This functionality may only be used twice per offer at most.

* An offer is removed entirely after a year since it is published or bumped.

* **Fulltext search** is available for users to search for other users' offers.

* Whenever a user wants to buy an item, he/she is redirected to a **realtime chat** with the owner of the offer.

* Users are notified about their chat messages over e-mail. \(Only once per day per conversation!\)

* Users can **report inappropriate offers to administrators**.

* Administrators may edit and remove all offers. They may also ban users, who are then unable to reuse their e-mail addresses.

![A Representative Screenshot](https://github.com/kogli/marketplace/raw/master/screenshot.png)

## Main libraries/frameworks used

* Backend
  * [_Laravel 5.6_](https://laravel.com)
  * [_Socket.io_](https://socket.io/)
  * [_TNTSearch_](https://github.com/teamtnt/tntsearch)
  * [_Intervention Image_](http://image.intervention.io/)
* Frontend
  * [_Vue.js_](https://vuejs.org/)
  * [_Bootstrap 4_](https://getbootstrap.com/)

Other used external tools and dependencies are specified in `composer.json` and `package.json`.