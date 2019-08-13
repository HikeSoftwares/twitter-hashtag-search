<?php
/* Connect to the database */
$mysqli = new mysqli("localhost", "quarterpro", "quarterpro@#11", "quarterpro");
if ($mysqli->connect_errno) {
exit();
}

/* Set the DB charset to utf8 */
$mysqli->query("SET CHARACTER SET utf8");
/* Setup the Twitter API */
ini_set('display_errors', 1);
require_once('twitterApi.php'); // Twitter API
/* Your Twitter app's credentials */
$settings = array(
'oauth_access_token' => "***",
'oauth_access_token_secret' => "***",
'consumer_key' => "***",
'consumer_secret' => "***"
);
/* Twitter API version 1.1 and the endpoint search */
$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = '?q=Quarterpro&count=100&result_type=recent'; // We're searching for #halloween and return 100 (max) tweets each time
$requestMethod = 'GET';
/* Get tweets with a specific word */
$twitter = new TwitterAPIExchange($settings);
$response = $twitter->setGetfield($getfield)
->buildOauth($url, $requestMethod)
->performRequest();
$tweets = json_decode($response);


/* Insert each tweet into the database (prepared statement) */
$timestamp = date("Y-m-d h:i:s");
foreach ($tweets->statuses as $tweet) {
    $query = $mysqli->prepare("INSERT INTO tweets (id_str, user, content, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("sssss", $tweet->id_str, $tweet->user->screen_name, $tweet->text, $timestamp, $timestamp);
    $query->execute();
    $query->close();

    $query = $mysqli->prepare("INSERT INTO twitter_users (username, created_at, updated_at) VALUES (?,?,?)");
    $query->bind_param("sss", $tweet->user->screen_name, $timestamp, $timestamp);
    $query->execute();
    $query->close();
}
/* Delete duplicate screen names. Save the latest tweet. */
//$query = $mysqli->prepare("DELETE n1 FROM tweets n1, tweets n2 WHERE n1.id > n2.id AND n1.user = n2.user");
//$query->execute();
//$query->close();
/* Close the connection */
$mysqli->close();

echo"<pre>";
print_r($tweets);
echo"</pre>";
?>
