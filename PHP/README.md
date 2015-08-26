# Getting Started with the Office 365 Unified API (PHP) #
This folder contains a hands-on lab for getting started with the Office 365 Unified API using PHP. This solution is part of a broader getting started series across a number of platforms/languages.

## Step 1: Register the Application ##
1.	Login to the Azure Management Portal at [https://manage.azurewebsites.net](https://manage.azurewebsites.net "https://manage.azurewebsites.net") using an account that has access to the O365 Organization’s Azure Active Directory 
2.	Click on the **ACTIVE DIRECTORY** option towards the bottom of the left side menu and select the appropriate directory in the directory listing (you may only have one directory).
![Azure Active Directory](http://i.imgur.com/GbW9j2R.jpg)
3.	Next, click on the **APPLICATIONS** link in the top tab menu to display a list of applications registered in the directory.
![AAD Apps](http://i.imgur.com/EMLupXo.jpg)
4.	Click the **ADD** button in the middle of the footer (don’t confuse this with the +NEW button on the bottom left).
![Add app](http://i.imgur.com/1dEHMcp.jpg)
5.	In the **What do you want to do?** dialog, select **Add an application my organization is developing**
![New app wizard 1](http://i.imgur.com/fm2mHXz.jpg)
6.	Give the application a **NAME** (ex: Org Explorer) and select **WEB APPLICATION AND/OR WEB API** for the Type and then click the next arrow button.
![Name app](http://i.imgur.com/wtKPRV4.jpg)
7.	For App properties, enter a **SIGN-ON URL** and **APP ID URL**. The **SIGN-ON URL** will likely be localhost address during testing/development (ex: http://localhost:8000) and the **APP ID URL** should be globally unique, so something with tenant name is common (ex: https://TENANT.onmicrosoft.com/OrgExplorer).
![App URLs](http://i.imgur.com/gQt6ofs.jpg)
8.	When the application finishes provisioning, click on the **CONFIGURE** link in the top tab menu.
![Configure tab](http://i.imgur.com/szJEaAb.jpg)
9.	Locate the **CLIENT ID** and copy its value somewhere safe.
![Client ID](http://i.imgur.com/faOKIs5.jpg)
10.	Locate the **keys** section and use the duration dropdown to select key good for **2-years**.
![Generate Keys](http://i.imgur.com/mMzmXT8.jpg)
11.	Click the **SAVE** button in the footer to generate and display the key (also referred to as a secret or password) and then copy the key somewhere safe (**WARNING**: you cannot display an application key other than after a save, so it is urgent you copy it during this step).
![Generate Keys](http://i.imgur.com/6b1nDzG.jpg)
12.	Locate the permissions to other applications section and click on the Add application button to launch the Permissions to other applications dialog.
![Perms to other applications](http://i.imgur.com/r8kv0vh.jpg)
13.	Locate and add **Office 365 unified API (preview)** before clicking the check button to close the dialog.
![Permissions to other apps dialog](http://i.imgur.com/c9wFK5g.jpg)
14.	Add Delegated Permissions for **Access directory as signed-in user** and **Read signed-in user's files**.
![Unified API Permissions](http://i.imgur.com/izWrLrk.jpg)
15.	Click the **SAVE** button in the footer to save the updated application permissions.

## Step 2: Provision the Project Scaffolding ##
There are a number of IDEs, templates, and generators for creating the project scaffolding. This hands-on lab will leverage Node Package Manager (NPM), Bower, and Atom, but you can use anything you are comfortable with.

1. Create a new project folder somewhere on your development machine that will allow you to host PHP applications (ex: Applications/MAMP/htdocs)
2. Open a command prompt and change the directory to the new folder created in the previous step.
3. Initialize bower by typing **bower init**

		> bower init
4. Create a bower configuration file named **bowerrc** in the root directory.

		> touch .bowerrc
5. Open the new bowerrc file and specify a directory location for script libraries to download.

		{
			"directory": "lib"
		}
6. Return to the command prompt and use bower to import boostrap, angular, angular-route, and adal-angular.

		> bower install bootstrap --save

## Step 3: Build the App ##
1. Open the project folder in your favorite web editor (Atom is used for illustration, but it could be any editor).
2. Create a file called **Settings.php** and add the following constants, replacing the values for **$clientId** and **$password** from your application registration.

		<?php
		    class Settings
		    {
		        public static $clientId = '0fe23ba5-f632-4a93-a898-b6b42adbfe2b';
		        public static $password = 'GsU7UkE9R5CW2YWsWabIGsfyOAH/T90VOmifC91U6u0=';
		        public static $authority = 'https://login.microsoftonline.com/common/';
		        public static $redirectURI = 'https://localhost/gettingstarted/Login.php';
		        public static $unifiedAPIResource = 'https://graph.microsoft.com';
		        public static $unifiedAPIEndpoint = 'https://graph.microsoft.com/beta/';
		        public static $tokenCache = 'TOKEN_CACHE';
		        public static $apiRoot = 'API_ROOT';
		    }
		?>

3. Next, create **Token.php** file which will serve as the entity model for tokens passed from Azure AD.

		<?php
		    class Token
		    {
		      public $resource;
		      public $accessToken;
		      public $refreshToken;
		      public $idToken;
		    }
		?>
4. Next, create an **AuthHelper.php** file that will provide utility functions for working with Azure AD and OAuth. This file can be leveraged in almost any PHP application that talks to Azure AD. Take note of the important functions such as **getAuthorizationUrl** (used to get the initial redirect URL for login/grant code), **getAccessTokenFromCode** (gets an access token using a grant code from Azure AD) and **getAccessTokenFormRefreshToken** (gets a new access token from Azure AD using a refresh token).

		<?php
		  error_reporting(E_ALL|E_STRICT);
		  ini_set("display_errors", 1);
		
		
		    class AuthHelper
		    {
		        public static function getAuthorizationUrl()
		        {
		          $authUrl = Settings::$authority . "oauth2/authorize?" .
		            "response_type=code&" .
		            "client_id=" . Settings::$clientId . "&" .
		            "resource=" . Settings::$unifiedAPIResource . "&" .
		            "redirect_uri=" . Settings::$redirectURI;
		          return $authUrl;
		        }
		
		        public static function getAccessTokenFromCode($code)
		        {
		          //build the request body
		          $tokenRequestBody = "grant_type=authorization_code&" .
		            "client_id=" . Settings::$clientId . "&" .
		            "redirect_uri=" . Settings::$redirectURI . "&" .
		            "client_secret=" . Settings::$password . "&" .
		            "code=" . $code;
		
		            //setup the post to https://login.microsoftonlne.com/common/oauth2/token
		            $request = curl_init("https://login.microsoftonline.com/common/oauth2/token");
		            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		            curl_setopt($request, CURLOPT_POST, 1);
		            curl_setopt($request, CURLOPT_POSTFIELDS, $tokenRequestBody);
		
		            //perform the post and dispose
		            $response = curl_exec($request);
		            //curl_close($request);
		
		            //get http code
		            $httpCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
		            if ($httpCode > 400) {
		              //check error
		              $errorNum = curl_errno($request);
		              $errorTxt = curl_error($request);
		              print($errorNum . " - " . $errorTxt);
		            }
		
		            //parse the response json into a Token object
		            $tokenJSON = json_decode($response, true);
		            $token = new Token;
		            $token->resource = $tokenJSON["resource"];
		            $token->accessToken = $tokenJSON["access_token"];
		            $token->refreshToken = $tokenJSON["refresh_token"];
		            $token->idToken = $tokenJSON["id_token"];
		
		            //return the token
		            return $token;
		        }
		
		        public static function getAccessTokenFromRefreshToken($refreshToken)
		        {
		          //build the request body
		          $tokenRequestBody = "grant_type=refresh_token&" .
		            "refresh_token=" . $refreshToken . "&" .
		            "client_id=" . Settings::$clientId . "&" .
		            "client_secret=" . Settings::$password . "&" .
		            "resource=" . Settings::$unifiedAPIResource;
		
		            //setup the post to https://login.microsoftonlne.com/common/oauth2/token
		            $request = curl_init("https://login.microsoftonline.com/common/oauth2/token");
		            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		            curl_setopt($request, CURLOPT_POST, 1);
		            curl_setopt($request, CURLOPT_POSTFIELDS, $tokenRequestBody);
		
		            //perform the post and dispose
		            $response = curl_exec($request);
		            //curl_close($request);
		
		            //get http code
		            $httpCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
		            if ($httpCode > 400) {
		              //check error
		              $errorNum = curl_errno($request);
		              $errorTxt = curl_error($request);
		              print($errorNum . " - " . $errorTxt);
		            }
		
		            //parse the response json into a Token object
		            $tokenJSON = json_decode($response, true);
		            $token = new Token;
		            $token->resource = $tokenJSON["resource"];
		            $token->accessToken = $tokenJSON["access_token"];
		            $token->refreshToken = $tokenJSON["refresh_token"];
		
		            //return the token
		            return $token;
		        }
		    }
		?>
5. Create an **Index.php** file and modify it to check for a token in session. If a token exists, get the user, manager, directReports, and files for the user. If a token doesn't exist, redirect the user to **Login.php** (which you will create next).

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
6. Finally, create a Login.php file that handles logins. It should check for tokens in session and also look for a code passed in as a URL parameter from Azure AD (when Azure AD passes back a grant code during the first step of OAuth).

		<?php
		session_start();
		error_reporting(E_ALL|E_STRICT);
		ini_set("display_errors", 1);
		
		require_once("Settings.php");
		require_once("AuthHelper.php");
		require_once("Token.php");
		
		//check for authorization code in url parameter
		if (isset($_GET["code"])) {
		  //use the authorization code to get access token for the unified API
		  $token = AuthHelper::getAccessTokenFromCode($_GET["code"]);
		  if (isset($token->refreshToken)) {
		    $_SESSION[Settings::$tokenCache] = $token->refreshToken;
		    header("Location:Index.php");
		  }
		}
		 ?>
		
		<html>
		<head>
		  <title>Login</title>
		  <link rel="stylesheet" href="lib/bootstrap/dist/css/bootstrap.min.css">
		  <script type="text/javascript" src="lib/jquery/dist/jquery.min.js"></script>
		  <script type="text/javascript" src="lib/bootstrap/dist/js/bootstrap.min.js"></script>
		  <script type="text/javascript">
		    function login() {
		      <?php
		      echo "window.location = '" . AuthHelper::getAuthorizationUrl() . "';";
		       ?>
		    }
		  </script>
		</head>
		<body>
		  <div class="container">
		    <button type="button" name="button" onclick="login()" class="btn btn-primary btn-block">Login with Office 365</button>
		  </div>
		</body>
		</html>
## Step 4: Testing the App ##
The app should be ready to test. Testing approach will vary based on development environment. For illustration, we used Atom and MAMP.