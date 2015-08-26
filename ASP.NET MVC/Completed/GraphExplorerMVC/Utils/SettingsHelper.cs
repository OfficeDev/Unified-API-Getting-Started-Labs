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
