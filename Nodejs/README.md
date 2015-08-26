# Getting Started with the Office 365 Unified API (Node.js) #
This folder contains a hands-on lab for getting started with the Office 365 Unified API using Node.js. This solution is part of a broader getting started series across a number of platforms/languages.

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
There are a number of IDEs, templates, and generators for creating the project scaffolding. This hands-on lab will leverage Node Package Manager (NPM), Express with Handlebars, Bower, and Visual Studio Code, but you can use anything you are comfortable with.

1. Open a terminal prompt to the location you want to create your project.
2. Generate the initial project scaffolding using **Express** with the **Handlebars (--hbs)** option.

		> express myapp --hbs
3. Change directories to the new project folder.

		> cd myapp
4. Run **npm install** to get all the necessary packages pulled down.

		> npm install
5. The lab uses the Azure Active Directory Libraries (ADAL) for Node.js (**adal-node**), so use npm to install that package.

		> npm install adal-node --save
7. Depending on the code editor you use, it might be helpful to pull in the typescript definitions into the project for intellisense. You can use **tsd** for that.

		> tsd query node express --action install
8. Next, initialize bower and accept all the defaults.

		> bower init
9. Create a **.bowerrc** file in the project root and add the following json to tell bower to use the **public** folder for libraries.

		{
		   "directory": "public"
		}
10. Finally, use bower to install **bootstrap** into the project.

	> bower install bootstrap --save
11. You should be able to open the project folder in your favorite editor now and be ready to code.

## Step 3: Build the App ##
1. Start by creating a **authHelper.js** in the root of the project and add an initialization to the ADAL package you imported.

		var AuthenticationContext = require("adal-node").AuthenticationContext;
2. Define a variable with your app details (be sure to replace the details below with the details from your own app registration, including **client_Id**, **client_secret**, and **redirect_url**).

		var credentials = {
			authority: "https://login.microsoftonline.com/common",
			client_id: "0fe23ba5-f632-4a93-a898-b6b42adbfe2b",
			client_secret: "GsU7UkE9R5CW2YWsWabIGsfyOAH/T90VOmifC91U6u0=",
			redirect_url: "http://localhost:5858/login"
		};
3. Next, define a **getAuthUrl** function that returns the redirect URL you need to start the OAuth flow with Azure AD.

		function getAuthUrl(res) {
			return credentials.authority + "/oauth2/authorize" +
				"?client_id=" + credentials.client_id +
				"&resources=" + res +
				"&response_type=code" +
				"&redirect_uri=" + credentials.redirect_url;
		};
4. Next, use the **AuthenticationContext** you initialized earlier to define two utility functions for **getTokenFromCode** (which gets an access token from Azure given an authorization code) and **getTokenFromRefreshToken** (which gets an access token using a refresh token). Notice that both of these functions accept a resource (which is a service...https://graph.microsoft.com in the case of the Unified API).

		function getTokenFromCode(res, code, callback) {
			var authContext = new AuthenticationContext(credentials.authority);
			authContext.acquireTokenWithAuthorizationCode(code, credentials.redirect_url, res, credentials.client_id, credentials.client_secret, function(err, response) {
				if (err)
					callback(null);
				else {
					callback(response);
				}
			});
		};
		
		function getTokenFromRefreshToken(res, token, callback) {
			var authContext = new AuthenticationContext(credentials.authority);
			authContext.acquireTokenWithRefreshToken(token, credentials.client_id, credentials.client_secret, res, function(err, response) {
				if (err)
					callback(null);
				else {
					callback(response);
				}
			});
		};
5. Lastly in **authHelper.js**, expose a few of the functions and constants using exports.

		exports.getAuthUrl = getAuthUrl;
		exports.getTokenFromCode = getTokenFromCode;
		exports.getTokenFromRefreshToken = getTokenFromRefreshToken;
		exports.TOKEN_CACHE_KEY = 'TOKEN_CACHE_KEY';
		exports.TENANT_CAHCE_KEY = 'TENANT_CAHCE_KEY';
6. Open **app.js**, which should be almost complete thanks to the express generator. However, you do want to tell the app to listen on a port towards the bottom of the file. Below port 5858 is used, but you can use whatever you prefer.

		app.listen(5858);
7. The express routes are defined in **routes/index.j**s. Open that file and add requires for **adal-node**, **authHelper.js**, and **https**.

		var express = require('express');
		var router = express.Router();
		var authContext = require('adal-node').AuthenticationContext;
		var authHelper = require('../authHelper.js');
		var https = require('https'); 
8. Create a utility function for **getJson** (which will perform a GET against an Azure secured service. Notice the use of an access token in the header of the request.

		function getJson(host, path, token, callback) {
		  var options = {
		    host: host, 
		    path: path, 
		    method: 'GET', 
		    headers: { 
		      'Content-Type': 'application/json',
		      'Accept': 'application/json',
		      'Authorization': 'Bearer ' + token 
		      }
		    };
		    
		  https.get(options, function(response) {
		    var body = "";
		    response.on('data', function(d) {
		      body += d;
		    });
		    response.on('end', function() {
		      callback(body);
		    });
		    response.on('error', function(e) {
		      callback(null);
		    });
		  });
		};
9. Next, add routes for **/login** and a dynamic route for **/:id**.

		router.get('/login', function(req, res, next) {
		});
		router.get('/:id', function(req, res, next) {
		});		
10.  The default route (**"/"**) and the dynamic route (**"/:id"**) do essentially the same thing, just for different users ("me" and a specific user id respectfully). Create a utility function to generically handle this by accepting a path parameter.

		function renderView(path, req, res) {
		  authHelper.getTokenFromRefreshToken('https://graph.microsoft.com/', req.cookies.TOKEN_CACHE_KEY, function(token) {
		    if (token !== null) {
		      var user = { user: null, manager: null, directReports: null, files: null };
		      
		      //get the user
		      getJson('graph.microsoft.com', '/beta/' + path, token.accessToken, function(result) {
		        if (result != null) {
		          user.user = JSON.parse(result);
		          if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
		            res.render('index', { title: 'Express', data: user });
		        }
		      });
		      
		      //get the manager
		      getJson('graph.microsoft.com', '/beta/' + path + '/manager', token.accessToken, function(result) {
		        if (result != null) {
		          user.manager = JSON.parse(result);
		          if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
		            res.render('index', { title: 'Express', data: user });
		        }
		      });
		      
		      //get the direct reports
		      getJson('graph.microsoft.com', '/beta/' + path + '/directReports', token.accessToken, function(result) {
		        if (result != null) {
		          user.directReports = JSON.parse(result);
		          if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
		            res.render('index', { title: 'Express', data: user });
		        }
		      });
		      
		      //get the files
		      getJson('graph.microsoft.com', '/beta/' + path + '/files', token.accessToken, function(result) {
		        if (result != null) {
		          user.files = JSON.parse(result);
		          if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
		            res.render('index', { title: 'Express', data: user });
		        }
		      });
		    }
		    else {
		      //TODO: ERROR
		    }
		  });
		};
11.  Complete the route definitions for "/" and "/:id" to leverage this function. Note that the default route will direct the user to login if they do not have a cached token.

		router.get('/', function(req, res, next) {
		  //check for token
		  if (req.cookies.TOKEN_CACHE_KEY === undefined)
		    res.redirect('login');
		  else {
		    renderView("me", req, res);
		  }  
		});

		router.get('/:id', function(req, res, next) {
		  var tenantId = req.cookies.TENANT_CAHCE_KEY;
		  renderView(tenantId + '/users/' + req.params.id, req, res);
		});
12.  Finally, complete the logic in the login route. It should check for a authorization code coming back from Azure AD (following the initial OAuth redirect).

		router.get('/login', function(req, res, next) {
		  if (req.query.code !== undefined) {
		    authHelper.getTokenFromCode('https://graph.microsoft.com/', req.query.code, function(token) {
		      if (token !== null) {
		        //cache the refresh token in a cookie and go back to index
		        res.cookie(authHelper.TOKEN_CACHE_KEY, token.refreshToken);
		        res.cookie(authHelper.TENANT_CAHCE_KEY, token.tenantId);
		        res.redirect('/');
		      }
		      else {
		        //TODO: ERROR
		      }
		    });
		  }
		  else {
		    res.render('login', { auth_url: authHelper.getAuthUrl('https://graph.microsoft.com/') });
		  }
		});
13.  All the app logic should be complete, now for some minor markup updates in the handlebars views. Start by updating the main app layout in **views/layout.hbs**. This should reference bootstrap/jquery that bower imported and have a handlebars placeholder for {{{body}}}.

		<!DOCTYPE html>
		<html>
		  <head>
		    <title>{{title}}</title>
		    <link rel='stylesheet' href='/libs/bootstrap/dist/css/bootstrap.min.css' />
		  </head>
		  <body>
		    <div class="container">
		    {{{body}}}
		    </div>
		    
		    <script src="/libs/jquery/dist/jquery.min.js"></script>
		    <script src="/libs/bootstrap/dist/js/bootstrap.min.js"></script>
		  </body>
		</html>

14.  Next, update the layout for **views/index.hbs**.

		<div class="row">
			<div class="col-xs-6">
				<h2>Manager: <a href="/{{data.manager.objectId}}">{{data.manager.displayName}}</a></h2>
				<h2>Employee: {{data.user.displayName}}</h2>
				<h2>Direct Reports: 
					{{#each data.directReports.value}}
					<span><a href="/{{objectId}}">{{displayName}}</a>{{#unless @last}}, {{/unless}}</span>
					{{/each}}
				</h2>
			</div>
			<div class="col-xs-6">
				<h2>{{data.user.displayName}}'s Files</h2>
				<ul>
					{{#each data.files.value}}
					<li>{{name}}</li>
					{{/each}}
				</ul>
			</div>
		</div>

15.  Lastly, create a **views/login.hbs** that will allow users to login with Office 365.

		<button onclick="login()" class="btn btn-primary btn-block">Login with Office 365</button>
		<script type="text/javascript">
			function login() {
				window.location = '{{auth_url}}'.replace(/&amp;/g, '&');;	
			}
		</script>
16. The app should be ready to run!

## Step 4: Testing the App ##
The app should be ready to test. Testing approach will vary based on development environment. The sample was built in Visual Studio Code, which provides a full debugging experience.