# Interface

## Main Window

![Main Interface Showcase](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/main.png)

* Main menu
    * No. 1 - ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/home.png) *Main dashboard - overview of newest offers* 
    * No. 2 - ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/search.png) *Search* 
    * No. 3 - ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/user.png) *Current user's profile*  (signed in users)
    * No. 4 - ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/flag.png) *Administrator tools*  (administrators)
    * No. 5 - ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/cog.png) *Current user profile settings*  (signed in users)
        * Alternative: ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/sign-in.png) *Sign in* (anonymous users)
    * No. 6 - ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/sign-out.png) *Log out*  (signed in users)
        * Alternative: ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/user-plus.png) *Register* (anonymous users)
* No. 7 - *Offer search bar*
* No. 8 - *Main content*, e. g., list of offers
* No. 9 - *Additional buttons*
    * ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/plus.png) *Add a new offer* (link to form)
    * ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/comment.png) *Display chat popup*
    * ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/chevron-left.png) *Go back*
    * ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/chevron-up.png) *Go to top*

## Offer Card

![Offer Interface (Compact)](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/offer.png)
![Offer Interface (Expanded)](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/offer_large.png)

* No. 1 - *Author profile* - display name, username, link to profile
* No. 2 - *Main offer image* (compact version) / *Carousel of all images* (expanded version)
* No. 3 - *Offer name*
* No. 4 - *Inappropriate reports counter* (visible for administrators only)
    * Counter of how many times the offer has been reported as inappropriate
* No. 5 - *Offer description*
* No. 6 - *Price*
    * Has just an informative purpose. Purchase conditions are a matter of discussion between the two parties involved. The application's chat service is expected to be used for this.
* No. 7 - *Request a purchase button*
    * Sends a chat message to the author of the offer and opens the chat window.
* No. 8 - *Additional offer settings dropdown*
    * *Edit / delete offer* (owners or administrators)
    * *Bump offer* (owners)
    * *Report offer* (logged in users)
    * *Mark reported offer as appropriate* (administrators)
* No. 9 - *Expand* - open offer in a popup or in a separate route

## User Profile Navigation

Visible on a user profile route.

![User Profile Navigation](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/user_navigation.png)
![User Profile Navigation (of the logged-in user)](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/user_navigation_this.png)

* No. 1 - *Profile image*
* No. 2 - *User display name*
* No. 3 - *Username*
* No. 4 - *Chat button* (visible for logged in users only)
    * opens a chat window with the user
* No. 5 - *Ban / unban button* (visible for administrators only)
* No. 6 - *My Profile tab* (visible for profile owners only)
    * Displays the user's offers
* No. 7 - *My Settings tab* (visible for profile owners only)
    * Displays the user's profile settings

## Offer Create / Offer Edit Form

![Offer Create / Offer Edit Form](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/offer_form.png)

* No. 1 - *Name*
* No. 2 - *Description*
* No. 3 - *Price*
* No. 4 - *Currency selection*
* No. 5 - *Image file uploader*
* No. 6 - *Image order*
    * Images may be reordered using drag & drop
* No. 7 - *Already uploaded images* (offer edit only)
    * Removable by pressing the red cross in the top right corner of the image preview
* No. 8 - *To-be-uploaded images*
    * Removable by reselecting images in the file uploader
* No. 9 - *Revert original photos button* (offer edit only)
    * Reverts images and their order to original values
* No. 10 - *Publish button*
    * Publishes a new offer or updates the offer that is being edited

## Chat Popup

### List of Conversations

Sorted by newest.

![Chat Popup (List of Conversations)](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/chat_conversations.png)

### A Conversation

![Chat Popup (A Conversation)](https://github.com/kogli/marketplace/raw/gh-pages/screenshots/chat_user.png)

* No. 1 - *Back button*
    * Return to the list of conversations
* No. 2 - *User profile* 
    * Profile picture, user display name, link to profile
* No. 3 - *Conversation messages* 
* No. 4 - *Message status indicator* 
    * Profile picture = Read
    * ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/check-circle.png) = Received
    * ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/check-circle-o.png) = Sent
    * ![](https://github.com/encharm/Font-Awesome-SVG-PNG/raw/master/black/png/24/circle-o.png) = Sending
* No. 5 - *Typing indicator* 
* No. 6 - *Message input* 
* No. 6 - *Message send button* 