# Getting Started with the Office 365 Unified API (JavaScript) #
This folder contains a hands-on lab for getting started with the Office 365 Unified API using raw JavaScript (no libraries...not even JQuery). This solution is part of a broader getting started series across a number of platforms/languages, including AngularJS and Node.js.

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
12.	Locate the **permissions to other applications** section and click on the **Add application** button to launch the Permissions to other applications dialog.
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

## Step 2: Build the App ##
1. Create an index.html file, which will contain all markup and scripts for the project.
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
3. Modify **index.html** with the basic layout for the app into the **body**. It should have two columns...one for org structure and one for the selected users files.

	    <button onclick="DoLogon()">Login with Office 365</button>
	    <br/>
	    <div id="results">
	        <div style="width: 50%; float: left">
	            <h2 id="manager"></h2>
	            <h2 id="user"></h2>
	            <h2 id="reports"></h2>
	        </div>
	        <div style="width: 50%; float: right">
	            <h2 id="filesTitle"></h2>
	            <ul id="files"></ul>
	        </div>
	    </div>
4. Directly below the page markup (but still within the body), add a script block for the page logic.

	    <script type="text/javascript">
		</script>
5. Start by defining some constants specific to the app you registered in the previous section. This includes clientid and tenantid (the graphResource will be the same as below).

        var clientid = "0fe23ba5-f632-4a93-a898-b6b42adbfe2b";
        var tenantid = "dxdemos.onmicrosoft.com";
        var graphResource = "00000003-0000-0000-c000-000000000000";
        var state = "somestate";
        var nonce = "somenonce";
        var graphToken = "";
        var myUrl = window.location;
6. Next, define a utility function for parsing URL parameters.

        function parseQueryString(url) {
            var params = {}, queryString = url.substring(1),
                regex = /([^&=]+)=([^&]*)/g, m;
            while (m = regex.exec(queryString)) {
                params[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
            }
            return params;
        }
7. The first step in an implicit OAuth flow with Azure AD is to redirect the user to login and acquire token.

        //This is how we log on to AAD
        function DoLogon(p) {
            window.location = "https://login.windows.net/" + tenantid + "/oauth2/authorize" +
				"?response_type=id_token" + 
				"&client_id=" + clientid +
				"&redirect_uri=" + encodeURIComponent(myUrl) + 
				"&state=" + state + 
				"&nonce=" + nonce;
        }
8. The token returned from DoLogon is a AAD token, so you need another function to get a resource-specific token for the Unified API.

        //This is how we get an authorized access_token for the graph
        function requestTokenForGraph() {
            //We have a token for AAD, now we need a token for office graph
            window.location = "https://login.windows.net/" + tenantid + "/oauth2/authorize" + 
				"?response_type=token" + 
				"&client_id=" + clientid + 
				"&resource=" + graphResource +
				"&redirect_uri=" + encodeURIComponent(myUrl) + 
				"&state=" + state +
				"&prompt=none" + 
				"&nonce=undefined";
        }
9. Next, create a function to perform queries against the Unified API given a path parameter. The path will either be "me" or in the form "myorganization/users/{some user id}".

        function queryGraph(path) {
		}
10. Add script using XMLHttpRequest to get the user.

        //Get user's details
        var query = "https://graph.microsoft.com/beta/" + path;
        var req = new XMLHttpRequest();
        req.open("GET", query, false);
        req.setRequestHeader("Authorization", "Bearer " + graphToken);
        req.setRequestHeader("Accept", "application/json;odata.metadata=minimal;odata.streaming=true;IEEE754Compatible=false");
        req.send();
        var result = JSON.parse(req.responseText);
        user.innerHTML = "Employee: " + result.displayName;
        filesTitle.innerHTML = result.displayName + "'s files:";
11. Add script using XMLHttpRequest to get the users manager. The manager could be null, so check accordingly.

        //Get the manager's details
        req = new XMLHttpRequest();
        query = "https://graph.microsoft.com/beta/" + path + "/manager";
        req.open("GET", query, false);
        req.setRequestHeader("Authorization", "Bearer " + graphToken);
        req.setRequestHeader("Accept", "application/json;odata.metadata=minimal;odata.streaming=true;IEEE754Compatible=false");
        req.send();
        if (req.status === 200) {
            result = JSON.parse(req.responseText);
            manager.innerHTML = "Manager: <a href='javascript:queryGraph(\"myorganization/users/" + result.objectId + "\")'>" + result.displayName + "</a>";
        }
        else
            manager.innerHTML = "Manager: ";
12. Add script using XMLHttpRequest to get the users direct reports.

        //Get direct reports
        req = new XMLHttpRequest();
        query = "https://graph.microsoft.com/beta/" + path + "/directReports";
        req.open("GET", query, false);
        req.setRequestHeader("Authorization", "Bearer " + graphToken);
        req.setRequestHeader("Accept", "application/json;odata.metadata=minimal;odata.streaming=true;IEEE754Compatible=false");
        req.send();
        result = JSON.parse(req.responseText);
        reports.innerHTML = "Reports: ";
        result.value.forEach(function (item) {
            reports.innerHTML += "<a href='javascript:queryGraph(\"myorganization/users/" + item.objectId + "\")'>" + item.displayName + "</a>, ";
        });
        if (reports.innerHTML.length > 9)
            reports.innerHTML = reports.innerHTML.substring(0, reports.innerHTML.length - 2);
13. Add script using XMLHttpRequest to get the users files from OneDrive for Business. Notice you can use the same access token and how easy the Unified API makes discoverability of the API end-point for files.

        //Get files
        req = new XMLHttpRequest();
        query = "https://graph.microsoft.com/beta/" + path + "/files";
        req.open("GET", query, false);
        req.setRequestHeader("Authorization", "Bearer " + graphToken);
        req.setRequestHeader("Accept", "application/json;odata.metadata=minimal;odata.streaming=true;IEEE754Compatible=false");
        req.send();
        files.innerHTML = "";
        if (req.status === 200) {
            result = JSON.parse(req.responseText);
            result.value.forEach(function (item) {
                files.innerHTML += "<li>" + item.name + "</li>";
            });
        }
11. Finally, add some inline script at the bottom of the script block to handle page routing.

        //Read the current URL query string
        var params = parseQueryString(location.hash);

        if (params["id_token"] != null) {
            //If we have the id token, then we need to request the access token for Graph
            requestTokenForGraph();
        }
        else if (params['access_token'] != null) {
            graphToken = params['access_token'];
            queryGraph("me");
        }

## Step 4: Testing the App ##
The app should be ready to test. Testing approach will vary based on development environment. Visual Studio provides IIS Express. Other environment could use Node.js to host the application (which might be overkill). For this sample developed on a Mac and Visual Studio Code, we used Superstatic.