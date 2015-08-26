using GraphExplorerMVC.Models;
using GraphExplorerMVC.Utils;
using Microsoft.IdentityModel.Clients.ActiveDirectory;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.Mvc;

namespace GraphExplorerMVC.Controllers
{
    public class UserController : Controller
    {
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
    }
}