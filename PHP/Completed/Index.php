<?php
session_start();
error_reporting(E_ALL|E_STRICT);
ini_set("display_errors", 1);

require_once("Settings.php");
require_once("AuthHelper.php");
require_once("Token.php");

//check for token in session first time in
if (!isset($_SESSION[Settings::$tokenCache])) {
  //redirect to login page
  header("Location:Login.php");
}
else {
  //use the refresh token to get a new access token
  $token = AuthHelper::getAccessTokenFromRefreshToken($_SESSION[Settings::$tokenCache]);

  $path = Settings::$unifiedAPIEndpoint . "me";
  if (isset($_GET["id"]) && isset($_SESSION[Settings::$apiRoot])) {
    //get the apiRoot from session
    $apiRoot = $_SESSION[Settings::$apiRoot];
    $path = $apiRoot . "/users/" . $_GET["id"];
  }

  //perform a REST query for the user
  $request = curl_init($path);
  curl_setopt($request, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $token->accessToken,
    "Accept: application/json"));
  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($request);
  $user = json_decode($response, true);

  //perform a REST query for the users manager
  $request = curl_init($path . "/manager");
  curl_setopt($request, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $token->accessToken,
    "Accept: application/json"));
  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($request);
  $httpCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
  if ($httpCode > 400) {
    $manager = NULL;
  }
  else {
    $manager = json_decode($response, true);
  }

  //perform a REST query for the users direct reports
  $request = curl_init($path . "/directReports");
  curl_setopt($request, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $token->accessToken,
    "Accept: application/json"));
  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($request);
  $directReports = json_decode($response, true);

  //perform a REST query for the users files
  $request = curl_init($path . "/files");
  curl_setopt($request, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $token->accessToken,
    "Accept: application/json"));
  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($request);
  $httpCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
  if ($httpCode > 400) {
    $files = NULL;
  }
  else {
    $files = json_decode($response, true);
  }

  //get API root for this organization
  $apiRoot = $user["@odata.context"];
  $apiRoot = substr($apiRoot, 0, strrpos($apiRoot, "/"));
  $apiRoot = substr($apiRoot, 0, strrpos($apiRoot, "/"));
  $_SESSION[Settings::$apiRoot] = $apiRoot;
}
?>
<html>
<head>
  <title>Org Explorer</title>
  <link rel="stylesheet" href="lib/bootstrap/dist/css/bootstrap.min.css">
  <script type="text/javascript" src="lib/jquery/dist/jquery.min.js"></script>
  <script type="text/javascript" src="lib/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-xs-6">
        <h2>Manager: <a href="index.php?id=<?php echo $manager["objectId"] ?>"><?php echo $manager["displayName"] ?></a></h2>
        <h2>Employee: <?php echo $user["displayName"] ?></h2>
        <h2>Direct Reports:
          <?php for ($i = 0; $i < sizeof($directReports["value"]); $i += 1) { ?>
            <span>
              <a href="index.php?id=<?php echo $directReports["value"][$i]["objectId"] ?>"><?php echo $directReports["value"][$i]["displayName"] ?></a><?php if ($i != sizeof($directReports["value"]) - 1) { echo ", "; } ?>
            </span>
          <?php } ?>
        </h2>
      </div>
      <div class="col-xs-6">
        <h2><?php echo $user["displayName"] ?>'s files</h2>
        <ul>
          <?php
            if ($files != NULL) {
              foreach ($files["value"] as $file) { ?>
                <li><?php echo $file["name"] ?></li>
          <?php }}?>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
