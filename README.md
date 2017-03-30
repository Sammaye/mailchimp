A PHP API wrapper for v3.0 of the MailChimp API

## Installing

The recommended way to install this is via composer, however, if you install it manually it will need Guzzle as well.

## How to use

```php
$client = new sammaye\mailchimp\Chimp();
$client->apikey = 'chimp_chimp';
$client->get('/');
```

Other than that you are done. Everything else please refer to the 
documentation for the API: [http://developer.mailchimp.com/documentation/mailchimp/reference/overview/](http://developer.mailchimp.com/documentation/mailchimp/reference/overview/)

## Exceptions

The only other point I need to make is about exceptions.

I have implemented every exception as a class name which extends the base `MailChimpException`.

This will make it easier to detect specific errors but also if you wish to catch any errors this API may bring up 
then you need to catch the `MailChimpException`.
