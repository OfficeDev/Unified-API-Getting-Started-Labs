# Getting Started with the Office 365 Unified API (AngularJS) #
This folder contains a hands-on lab for getting started with the Office 365 Unified API in an AngularJS single-page application (SPA). This solution is part of a broader getting started series across a number of platforms/languages.

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
16.	Click on the **MANAGE MANIFEST** button in the footer and then select **Download Manifest** to download the application manifest to a safe location.
![Download manifest](http://i.imgur.com/hukJyV1.jpg)
17.	Open the Application Manifest in Notepad and change the **oauth2AllowImplicitFlow** to **true**.
![implicit flow setting](http://i.imgur.com/n6wrnjU.jpg)
18.	Save and close the Application Manifest before uploading it back into Azure by clicking the **MANAGE MANIFEST** button in the footer and selecting **Upload Manifest**.
![upload manifest](http://i.imgur.com/TLICN80.jpg)

## Step 2: Provision the Project Scaffolding ##
There are a number of IDEs, templates, and generators for creating the project scaffolding. This hands-on lab will leverage Node Package Manager (NPM), Bower, and Visual Studio Code, but you can use anything you are comfortable with.

1. Create a new project folder somewhere on your development machine.
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

		> bower install bootstrap angular angular-route adal-angular --save
7. Add an **index.html** file and **app** folder to the project root directory.

## Step 3: Build the App ##
1. Open the project folder in your favorite web editor (Visual Studio Code is used for illustration, but it could be any editor).
2. Open the **index.html** file and add the core HTML elements such as html, head, body, etc (most web editors have commands to generate this).

		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<title></title>
		</head>
		<body>
			<div ng-view></div>
		</body>
		</html>
3. Modify **index.html** to reference **bootstrap.css** in the **head** of and all the scripts imported via bower  (**jquery**, **bootstrap**, **angular**, **angular-route**, **adal.js**, **adal-angular.js**) into the **body**.

		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<title>My Organization</title>
			<link rel="stylesheet" href="lib/bootstrap/dist/css/bootstrap.min.css">
		</head>
		<body>
			<!-- JQuery and Bootstrap references -->
			<script type="text/javascript" src="lib/jquery/dist/jquery.min.js"></script>
			<script type="text/javascript" src="lib/bootstrap/dist/js/bootstrap.min.js"></script>
			
			<!-- Angular reference -->
			<script type="text/javascript" src="lib/angular/angular.min.js"></script>
			<script type="text/javascript" src="lib/angular-route/angular-route.min.js"></script>
			
			<!-- ADAL reference -->
			<script type="text/javascript" src="lib/adal-angular/dist/adal.min.js"></script>
			<script type="text/javascript" src="lib/adal-angular/dist/adal-angular.min.js"></script>
			
			<!-- App scripts -->
			<script type="text/javascript" src="app/app.js"></script>
		</body>
		</html>
4. Add an **ng-app** attribute directive to the body element.

		<body ng-app="app">
5. Add a div element inside the body and include an **ng-view** attribute directive.

		<div ng-view></div>
6. Create an **app.js** file in the **app** folder and define angular modules for **app.services**, **app.controllers**, and **app** (with dependency references on the other two modules).

	    (function() {
			"use strict";
			
			angular.module("app.services", []);
			
			angular.module("app.controllers", []);

			angular.module("app", ["app.services", "app.controllers"]);
		})();
7. This Angular app will leverage angular routing and the Azure Active Directory Authentication Library (ADAL) for authentication/token, so extend the app module with the **ngRoute** and **AdalAngular** dependencies.

		angular.module("orgExplorer", [app.services", "app.controllers", "ngRoute", "AdalAngular"])
8. Next, configure the module with routes using the **$routeProvider** dependency. Notice the additional ADAL dependencies and the use of **requireADLogin** on each route (this will force a login when true).

		angular.module("orgExplorer", [app.services", "app.controllers", "ngRoute", "AdalAngular"])
		.config(["$routeProvider", "$httpProvider", "adalAuthenticationServiceProvider", function ($routeProvider, $httpProvider, adalProvider) {
			$routeProvider.when("/login", {
				controller: "loginCtrl",
				templateUrl: "/app/templates/view-login.html",
				requireADLogin: false
			})
			.when("/user", {
				controller: "meCtrl",
				templateUrl: "/app/templates/view-user.html",
				requireADLogin: true
			})
			.when("/user/:id", {
				controller: "userCtrl",
				templateUrl: "/app/templates/view-user.html",
				requireADLogin: true
			})
			.otherwise({ redirectTo: "/login" });
9. Below the route, you need to initialize the ADAL Provider with app details from the registration you did earlier. Make sure you update the details below with your **tenant** and **clientId**.

		adalProvider.init({
			instance: "https://login.microsoftonline.com/",
			tenant: "dxdemos.onmicrosoft.com",
			clientId: "0fe23ba5-f632-4a93-a898-b6b42adbfe2b",
			endpoints: {
				"https://graph.microsoft.com/": "https://graph.microsoft.com"
			}
		}, $httpProvider);
10. The completed **app** module should look as follows.
11. Next, you will build out the app.services module, which will use an angular factory to provide services across the controllers. Start by extending the module with a factory and dependencies for **$http** and **$q**.

		angular.module("app.services", [])
		.factory("appService", ["$http", "$q", function ($http, $q) {
			var appService = {};
			
			return appService;
		}]);
12. Add a **getUser** function on the **appService** that accepts a **path** parameter and returns a deferred promise.

		angular.module("app.services", [])
		.factory("appService", ["$http", "$q", function ($http, $q) {
			var appService = {};
			
			appService.getUser = function(path) {
				var deferred = $q.defer();
				
				//make Unified API calls HERE
					
				return deferred.promise;
			};
			
			return appService;
		}]);
13. Next, complete the getUser function to make four different calls to the Unified API. One for the **user** (/), one for their **manager** (/manager), one for their **directReports** (/directReports), and one for their **files** (/files). Make sure you wait for all four calls to return before you resolve the deferred promise.

		angular.module("app.services", [])
		.factory("appService", ["$http", "$q", function ($http, $q) {
			var appService = {};
			
			appService.getUser = function(path) {
				var deferred = $q.defer();
				
				//setup response
				var user = { user: null, manager: null, directReports: null, files: null };
				
				//get the user
				$http.get("https://graph.microsoft.com/beta/" + path).then(function(r) { 
					user.user = r.data;
					if (user.user !== null && user.manager !== null && user.directReports !== null &&
					user.files !== null)
						deferred.resolve(user); 
				});
				//get the manager
				$http.get("https://graph.microsoft.com/beta/" + path + "/manager").then(function(r) { 
					user.manager = r.data;
					if (user.user !== null && user.manager !== null && user.directReports !== null &&
					user.files !== null)
						deferred.resolve(user); 
				}, function(er) {
					user.manager = {};
					if (user.user !== null && user.manager !== null && user.directReports !== null &&
					user.files !== null)
						deferred.resolve(user); 
				});
				//get the directReports
				$http.get("https://graph.microsoft.com/beta/" + path + "/directReports").then(function(r) { 
					user.directReports = r.data;
					if (user.user !== null && user.manager !== null && user.directReports !== null &&
					user.files !== null)
						deferred.resolve(user); 
				});
				//get the files
				$http.get("https://graph.microsoft.com/beta/" + path + "/files").then(function(r) { 
					user.files = r.data;
					if (user.user !== null && user.manager !== null && user.directReports !== null &&
					user.files !== null)
						deferred.resolve(user); 
				}, function(er) {
					user.files = {};
					if (user.user !== null && user.manager !== null && user.directReports !== null &&
					user.files !== null)
						deferred.resolve(user); 
				});
					
				return deferred.promise;
			};
			
			return appService;
		}]);
14. Next, modify the app.controllers module with the three modules referenced in the routes (**loginCtrl**, **meCtrl**, **userCtrl**). The loginCtrl should handle the authentication using the adalAuthenticationService dependency.

		angular.module("orgExplorer.controllers", [])
		.controller("loginCtrl", ["$scope", "$location", "adalAuthenticationService", "appService", function ($scope, $location, adalService, appService) {
		}])
		.controller("meCtrl", ["$scope", "appService", function ($scope, appService) {
		}])
		.controller("userCtrl", ["$scope", "$routeParams", "appService", function ($scope, $routeParams, appService) {
		}]);
15. The **loginCtrl** should check is the user is authenticated using the adalAuthenticationService. If so, take the user to the "**me**" view, otherwise wire up the login button event.

		.controller("loginCtrl", ["$scope", "$location", "adalAuthenticationService", "appService", function ($scope, $location, adalService, appService) {
			if (adalService.userInfo.isAuthenticated) {
				$location.path("/user");
			}
			
			$scope.login = function() {
				adalService.login();	
			};
		}])
16. The **meCtrl** should call **appService.getUser** for the "**me**" path.

		.controller("meCtrl", ["$scope", "appService", function ($scope, appService) {
			appService.getUser("me").then(function(d) { 
				$scope.data = d; 
			});
		}])
17. The **userCtrl** should call **appService.getUser** for the user id passed in the **$routeParams.id** parameter. 

		.controller("userCtrl", ["$scope", "$routeParams", "appService", function ($scope, $routeParams, appService) {
			appService.getUser("myorganization/users/" + $routeParams.id).then(function(d) { 
				$scope.data = d; 
			});
		}]);
18. Finally, you just need to stub out the two views that are referenced in the routes. Create a **view-login.html** and **view-user.html** in a new **templates** folder under **app**.
19. Add markup for **view-login.html** (should have **ng-click** attribute directive pointing to the **login**() function).

		<button class="btn btn-primary btn-block" ng-click="login()">Sign-in with Office 365</button>
20. Add markup for **view-user.html**.

		<div class="row">
			<div class="col-xs-6">
				<h2>Manager: <a href="#/user/{{data.manager.objectId}}">{{data.manager.displayName}}</a></h2>
				<h2>Employee: {{data.user.displayName}}</h2>
				<h2>Direct Reports: 
					<span ng-repeat="report in data.directReports.value">
						<a href="#/user/{{report.objectId}}">{{report.displayName}}</a>
						<span ng-show="$index < data.directReports.value.length - 1">, </span>
					</span>
				</h2>
			</div>
			<div class="col-xs-6">
				<h2 ng-show="data.user.displayName">{{data.user.displayName}}'s files</h2>
				<ul>
					<li ng-repeat="file in data.files.value">{{file.name}}</li>
				</ul>
			</div>
		</div>

## Step 4: Testing the App ##
The app should be ready to test. Testing approach will vary based on development environment. Visual Studio provides IIS Express. Other environment could use Node.js to host the application (which might be overkill). For this sample developed on a Mac and Visual Studio Code, we used Superstatic.