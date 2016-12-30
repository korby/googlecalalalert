#!/usr/bin/env php
<?php

error_reporting(E_ALL);
ini_set("error_log", __DIR__ . '/error.log');

if (count($argv) < 3) {
    $msg = "2 arguments needed : calendar-id 2 event-title";
    throw new Exception($msg);
}

$calendarId = $argv[1];
$eventTitle = $argv[2];


// patch for php < 5.5
if (!function_exists('curl_reset'))
{
    function curl_reset(&$ch)
    {
        $ch = curl_init();
    }
}

require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Google Calendar API PHP');
define('CREDENTIALS_PATH', __DIR__ . '/access_token.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

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

    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

$now = new DateTime(null, new DateTimeZone("Europe/Paris"));
$now->add(new DateInterval("PT5M"));
$now->format('Y-m-d\TH:i:s');


//Set the Event data
$event = new Google_Service_Calendar_Event();
$event->setSummary($eventTitle);
$event->setDescription("Evénement enregistré par un automate de monitoring");

$reminders = new Google_Service_Calendar_EventReminders();
$reminders->setUseDefault(false);
$reminders->setOverrides(array(
        array('method' => 'sms', 'minutes' => 1)
    ));
$event->setReminders($reminders);

$start = new Google_Service_Calendar_EventDateTime();
$start->setDateTime($now->format('Y-m-d\TH:i:s'));
$start->setTimeZone("Europe/Paris");
$event->setStart($start);

$end = new Google_Service_Calendar_EventDateTime();
$end->setDateTime($now->format('Y-m-d\TH:i:s'));
$end->setTimeZone("Europe/Paris");
$event->setEnd($end);



$createdEvent = $service->events->insert($calendarId, $event);
echo $createdEvent->getId();

