(function() {
	"use strict";
	
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
				if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
					deferred.resolve(user); 
			});
			//get the manager
			$http.get("https://graph.microsoft.com/beta/" + path + "/manager").then(function(r) { 
				user.manager = r.data;
				if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
					deferred.resolve(user); 
			}, function(er) {
				user.manager = {};
				if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
					deferred.resolve(user); 
			});
			//get the directReports
			$http.get("https://graph.microsoft.com/beta/" + path + "/directReports").then(function(r) { 
				user.directReports = r.data;
				if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
					deferred.resolve(user); 
			});
			//get the files
			$http.get("https://graph.microsoft.com/beta/" + path + "/files").then(function(r) { 
				user.files = r.data;
				if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
					deferred.resolve(user); 
			}, function(er) {
				user.files = {};
				if (user.user !== null && user.manager !== null && user.directReports !== null && user.files !== null)
					deferred.resolve(user); 
			});
				
			return deferred.promise;
		};
		
		return appService;
	}]);
	
	angular.module("app.controllers", [])
	.controller("loginCtrl", ["$scope", "$location", "adalAuthenticationService", "appService", function ($scope, $location, adalService, appService) {
		if (adalService.userInfo.isAuthenticated) {
			$location.path("/user");
		}
		
		$scope.login = function() {
			adalService.login();	
		};
	}])
	.controller("meCtrl", ["$scope", "appService", function ($scope, appService) {
		appService.getUser("me").then(function(d) { 
			$scope.data = d; 
		});
	}])
	.controller("userCtrl", ["$scope", "$routeParams", "appService", function ($scope, $routeParams, appService) {
		appService.getUser("myorganization/users/" + $routeParams.id).then(function(d) { 
			$scope.data = d; 
		});
	}]);
	
	angular.module("app", ["ngRoute", "app.services", "app.controllers", "AdalAngular"])
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
	
		adalProvider.init({
			instance: "https://login.microsoftonline.com/",
			tenant: "dxdemos.onmicrosoft.com",
			clientId: "0fe23ba5-f632-4a93-a898-b6b42adbfe2b",
			endpoints: {
				"https://graph.microsoft.com/": "https://graph.microsoft.com"
			}
		}, $httpProvider);
	}]);
})();