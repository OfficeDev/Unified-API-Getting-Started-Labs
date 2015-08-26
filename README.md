# Getting Started with the Office 365 Unified API #
This GitHub repository contains hands-on labs for getting started with the Office 365 Unified API. All the labs deliver the  exact same solution, but use different platforms/languages to get there. The purpose of the labs is to illustrate the strengths of the Office 365 Unified API, which improves upon past O365 APIs in a number of ways:

-  **Improved end-point discoverability**: legacy API end-points leveraged tenant or user-specific URIs, which added additional complexity in discovering API end-points. The Unified API uses a common unified URI (https://graph.microsoft.com) across ALL services, tenants, and users. Simple paths such as "me" and "myorganization" makes it easy to traverse user-specific data.
-  **Simplified token management**: with legacy end-points residing under their own API domain, calling them required resource/service specific tokens. Although this could be silent for end-users, it required extra development and  unnecessary round-trips with Azure Active Directory. A token for the Unified API is good for any services the Unified API front-ends (Exchange, SharePoint, Azure AD, OneNote, etc).
-  **Easier service traversal**: legacy API end-points were siloed and did not provide relationships across services. The Unified API is aware of relationships between services and allows for easy traversal between services. For example I can easily go from querying a file to pulling up the profile for the last user that modified it.

The lab project allows users to navigate through an organization structure and view shared files by user. It is helpful to have users in your directory with reporting structure. Reporting structure is driven by a manager field on the user object. If you are using this application in a development environment, it is recommended that you provision some users and create a reporting structure (tip: you can provision users in Azure AD without assigning them Office 365 licenses).

## Prerequisites ##
Office 365 applications are secured by Azure Active Directory, which comes as part of an Office 365 subscription. If you do not have an Office 365 Subscription or associated it with Azure AD, then you should follow the steps to [Set up your Office 365 development environment](https://msdn.microsoft.com/office/office365/HowTo/setup-development-environment "Set up your Office 365 development environment") from MSDN.

## Lab Platforms/Languages ##
Please select the platform/language of your choice for the lab:

- **Android (coming soon)**
- **[AngularJS](https://github.com/OfficeDev/Unified-API-Getting-Started-Labs/tree/master/AngularJS)**
- **[ASP.NET MVC](https://github.com/OfficeDev/Unified-API-Getting-Started-Labs/tree/master/ASP.NET%20MVC)**
- **iOS (coming soon)**
- **[JavaScript](https://github.com/OfficeDev/Unified-API-Getting-Started-Labs/tree/master/JavaScript) (raw...no libraries)**
- **Node.js (coming soon)**
- **[PHP](https://github.com/OfficeDev/Unified-API-Getting-Started-Labs/tree/master/PHP)**
- **Python (coming soon)**
- **Windows 10 (coming soon)**

Don't see your platform/language? Please let use know!