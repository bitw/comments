# Comments

This module allows you to leave comments on various pages.
Permissions for writing comments are configured in the configuration.
There is support for permission for authorized users and guests.
You can also configure the links and routes on which pages you can post comments.
Comments support tree view. To maintain the tree system, use the package (kalnoy / nestedset) [https://github.com/lazychaser/laravel-nestedset]

### Install
`composer require bitw/laravel-comments`

`php artisan migrate`

### Configure
`php artisan vendor:publish --provider="Bitw\Comments\ServiceProvider"`