googlecalalalert
=======

A php script to send sms notification using Google calendar and its api

# Usage
./googlecalalert "calendar_id" "subject"

# Pre-requisites on the Google calendar site
1. Login into the google account which will be used to send notifications
2. In its google console (https://console.developers.google.com/) add a project and create within it an apikey
3. On the calendar parameters add your phone number

# Notes
Script made from : https://developers.google.com/drive/v3/web/quickstart/php

To prevent error "Uncaught exception 'LogicException' with message 'refresh token must be passed in or set as part of setAccessToken'" when token has expired (after one hour), changed that lines : 

```php
// Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
```

By :

```php

// Refresh the token if it's expired.
if ($client->isAccessTokenExpired()) {
    // save refresh token to some variable
    $refreshTokenSaved = $client->getRefreshToken();

    // update access token
    $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

    // pass access token to some variable
    $accessTokenUpdated = $client->getAccessToken();

    // append refresh token
    $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

    // save to file
    file_put_contents($credentialsPath, json_encode($accessTokenUpdated));
}
```
