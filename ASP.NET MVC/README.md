# Getting Started with the Office 365 Unified API (ASP.NET MVC) #
This folder contains a hands-on lab for getting started with the Office 365 Unified API using ASP.NET MVC. This solution is part of a broader getting started series across a number of platforms/languages.

## Step 1: Register the Application ##
1.	Create a new **ASP.NET Application** app in Visual Studio 2015.
![New Project](http://i.imgur.com/Y12LmfJ.png)
2.	In the creation wizard, select the **MVC** template.
![MVC Template](http://i.imgur.com/712MZIx.png)
3.	Click on the **Change Authentication** button to launch the Change Authentication dialog.
4.	Select **Work And School Accounts**, provide the tenant **Domain**, and check the **Read directory data** checkbox under **Directory Access Permissions**.
![Change Authentication](http://i.imgur.com/ANn9f2D.png)
4.	Click Ok in the  (you may be prompted to sign into your tenant) and the Change Authentication and then Ok on the project wizard dialog to create your new ASP.NET MVC project.
5.	Login to the Azure Management Portal at [https://manage.azurewebsites.net](https://manage.azurewebsites.net "https://manage.azurewebsites.net") using an account that has access to the O365 Organization’s Azure Active Directory 
6.	Click on the **ACTIVE DIRECTORY** option towards the bottom of the left side menu and select the appropriate directory in the directory listing (you may only have one directory).
![Azure Active Directory](http://i.imgur.com/GbW9j2R.jpg)
7.	Next, click on the **APPLICATIONS** link in the top tab menu to display a list of applications registered in the directory.
![AAD Apps](http://i.imgur.com/EMLupXo.jpg)
8.	Locate and select the new application you just finished provisioning in Visual Studio.
9.	click on the **CONFIGURE** link in the top tab menu.
![Configure tab](http://i.imgur.com/szJEaAb.jpg)
10.	Locate the **permissions to other applications** section and click on the **Add application** button to launch the Permissions to other applications dialog.
![Perms to other applications](http://i.imgur.com/r8kv0vh.jpg)
11.	Locate and add **Office 365 unified API (preview)** before clicking the check button to close the dialog.
![Permissions to other apps dialog](http://i.imgur.com/c9wFK5g.jpg)
12.	Add Delegated Permissions for **Access directory as signed-in user** and **Read signed-in user's files**.
![Unified API Permissions](http://i.imgur.com/izWrLrk.jpg)
13.	Click the **SAVE** button in the footer to save the updated application permissions.

## Step 2: Build the App ##
1. Start by creating a **Utils** folder in the root of the web project and then create a **SettingsHelper.cs** file to store app-specific settings.

		using System;
		using System.Collections.Generic;
		using System.Configuration;
		using System.Linq;
		using System.Text;
		using System.Threading.Tasks;
		
		namespace GraphExplorerMVC.Utils
		{
		    public class SettingsHelper
		    {
		        public static string UserTokenCacheKey
		        {
		            get { return "USER_TOKEN"; }
		        }
		
		        public static string ClientId
		        {
		            get { return ConfigurationManager.AppSettings["ida:ClientID"]; }
		        }
		
		        public static string ClientSecret
		        {
		            get { return ConfigurationManager.AppSettings["ida:ClientSecret"]; }
		        }
		
		        public static string AzureAdTenant
		        {
		            get { return ConfigurationManager.AppSettings["ida:Domain"]; }
		        }
		
		        public static string AzureAdTenantId
		        {
		            get { return ConfigurationManager.AppSettings["ida:TenantId"]; }
		        }
		
		        public static string UnifiedApiResource
		        {
		            get { return "https://graph.microsoft.com/"; }
		        }
		
		        public static string AzureADAuthority
		        {
		            get { return string.Format("https://login.microsoftonline.com/{0}/", AzureAdTenantId); }
		        }
		
		        public static string ClaimTypeObjectIdentifier
		        {
		            get { return "http://schemas.microsoft.com/identity/claims/objectidentifier"; }
		        }
		    }
		}

2. Next, create entity models in the **Models** folder for **UserModel.cs**, **FileModel.cs**, and **UserDetailModel.cs**. These files should have the following properties defined.

		//UserModel.cs
	    public class UserModel
	    {
	        public string displayName { get; set; }
	        public Guid objectId { get; set; }
	    }

		//FileModel.cs
	    public class FileModel
	    {
	        public Guid objectId { get; set; }
	        public string name { get; set; }
	    }

		//UserDetailModel.cs
	    public class UserDetailModel
	    {
	        public UserModel User { get; set; }
	        public UserModel Manager { get; set; }
	        public List<UserModel> DirectReports { get; set; }
	        public List<FileModel> Files { get; set; }
		}

3. Add additional functionality to **UserDetailModel.cs** to retrieve values for a specific user.

        public async static Task<UserDetailModel> GetUserDetail(string path, string token)
        {
            UserDetailModel data = new UserDetailModel();

            //get the user
            var json = await GetJson(String.Format("https://graph.microsoft.com/beta/{0}", path), token);
            data.User = JsonConvert.DeserializeObject<UserModel>(json);

            //get the manager...might not exist
            json = await GetJson(String.Format("https://graph.microsoft.com/beta/{0}/manager", path), token);
            if (json == null)
                data.Manager = new UserModel();
            else
                data.Manager = JsonConvert.DeserializeObject<UserModel>(json);

            //get the direct reports
            json = await GetJson(String.Format("https://graph.microsoft.com/beta/{0}/directReports", path), token);
            data.DirectReports = JObject.Parse(json).SelectToken("value").ToObject<List<UserModel>>();

            //get the files
            json = await GetJson(String.Format("https://graph.microsoft.com/beta/{0}/files", path), token);
            if (json == null)
                data.Files = new List<FileModel>();
            else
                data.Files = JObject.Parse(json).SelectToken("value").ToObject<List<FileModel>>();

            return data;
        }

        private async static Task<string> GetJson(string endpoint, string accessToken)
        {
            HttpClient client = new HttpClient();
            client.DefaultRequestHeaders.Add("Authorization", "Bearer " + accessToken);
            client.DefaultRequestHeaders.Add("Accept", "application/json");
            using (HttpResponseMessage response = await client.GetAsync(new Uri(endpoint)))
            {
                if (response.IsSuccessStatusCode)
                {
                    return await response.Content.ReadAsStringAsync();
                }
                else
                    return null;
            }
        }
4. Open the **Startup.Auth.cs** file in the **App_Start** folder and make the following update to **line 59** (right before the return).

		//cache the token in session state
		HttpContext.Current.Session[SettingsHelper.UserTokenCacheKey] = result;

		return Task.FromResult(0);
5. Next, create a empty Controller in the **Controllers** folder with the name **UserController**. Then add actions for **Index**, **Detail** and a utility method for **GetAccessToken**.

        [Authorize]
        public async Task<ActionResult> Index()
        {
            var token = await GetAccessToken();
            var user = await UserDetailModel.GetUserDetail("me", token.AccessToken);
            return View(user);
        }

		[Authorize]
        public async Task<ActionResult> Detail(Guid id)
        {
            var token = await GetAccessToken();
            var user = await UserDetailModel.GetUserDetail(String.Format("{0}/users/{1}", SettingsHelper.AzureAdTenant, id.ToString()), token.AccessToken);
            return View(user);
        }

        private async Task<AuthenticationResult> GetAccessToken()
        {
            AuthenticationContext context = new AuthenticationContext(SettingsHelper.AzureADAuthority);
            var clientCredential = new ClientCredential(SettingsHelper.ClientId, SettingsHelper.ClientSecret);
            AuthenticationResult result = (AuthenticationResult)this.Session[SettingsHelper.UserTokenCacheKey];
            return await context.AcquireTokenByRefreshTokenAsync(result.RefreshToken, clientCredential, SettingsHelper.UnifiedApiResource);
        }
6. Create two views under **Views\User** for **Detail.cshtml** and **Index.cshtml** each with the following markup.


		@{
		    ViewBag.Title = "Org Explorer";
		    Layout = "~/Views/Shared/_Layout.cshtml";
		}
		
		@model GraphExplorerMVC.Models.UserDetailModel
		
		@{ ViewBag.Title = "Org Explorer"; }
		<div class="row">
		    <div class="col-xs-6">
		        <h2>Manager: <a href="/User/Detail?id=@Model.Manager.objectId">@Model.Manager.displayName</a></h2>
		        <h2>User: @Model.User.displayName</h2>
		        <h2>
		            Direct Reports:
		            @for (var i = 0; i < Model.DirectReports.Count; i++)
		            {
		                <span><a href="/User/Detail?id=@Model.DirectReports[i].objectId">@Model.DirectReports[i].displayName</a></span>if (i < Model.DirectReports.Count - 1)
		                {<span>, </span>}
		            }
		        </h2>
		    </div>
		    <div class="col-xs-6">
		        <h2>@Model.User.displayName's files</h2>
		        <ul>
		            @foreach (var file in Model.Files)
		            {
		                <li>@file.name</li>
		            }
		        </ul>
		    </div>
		</div>

7. Finally, open the **Views\Shared\_Layout.cshtml** and add the following after **line 25**.

		<li>@Html.ActionLink("Org Explorer", "Index", "User")</li>
## Step 4: Testing the App ##
The app should be test. Just start the debugger and login with Office 365 when prompted.