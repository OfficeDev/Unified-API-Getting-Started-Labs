# Getting Started with the Microsoft Graph (Android) #
This folder contains a hands-on lab for getting started with the Microsoft Graph in an Android Studio project. This solution is part of a broader getting started series across a number of platforms/languages.

## Step 1: Register the Application ##
1.	Navigate to the app registration tool on dev.office.com using the link [https://dev.office.com/app-registration](https://dev.office.com/app-registration")
2.	The app registration tool may ask you to sign-in. If so, use an account that is an administrator in Office 365 (ex: developer tenant owner).
![App registration - sign in](http://i.imgur.com/ErIGTnp.png)
3.	On the app registration form, give your application an **App name**, select **Native App** for the **App type**, and check the **Users.Read.All** permission in the **Users** permission grouping.
![App registration - registration form](http://i.imgur.com/MH8NIV9.png)
4.	Review all the app settings and if they look correct, click the **Register App** button.
5.	When the app registration is complete, the screen should display the new application's Client ID below the registration form. Capture this Client ID and the Redirect URI.
![App registration - confirmation and client id](http://i.imgur.com/l5pUeRF.png)


## Step 2: Build the App ##
The steps outlined in this section are specific to Android Studio 1.5. You can likely follow then with other IDEs or versions of Android Studio, but the steps will like vary slightly.

1. Open **Android Studio** and select **New Project**.
2. Give your project an **Application Name** (in this case *MicrosoftGraphStarter*) and click the **Next** button.
![New project wizard 1](http://i.imgur.com/A9NwX66.png)
3. Accept the defaults in the **Select the form factors your app will run on** and click the **Next** button.
![New project wizard 2](http://i.imgur.com/IBSSc8T.png)
4. Add an **Empty Activity** on the add activity dialog and click the **Next** button.
![New project wizard 3](http://i.imgur.com/3wTUNsU.png)
5. Give the **Empty Activity** a name (in this case *MainActivity*) and click the **Finish** button.
![New project wizard 4](http://i.imgur.com/lRxCKTW.png)
6. Once the project has finished provisioning, you need to update the manifest to have the Internet permission as seen below.

		<?xml version="1.0" encoding="utf-8"?>
		<manifest xmlns:android="http://schemas.android.com/apk/res/android"
	    package="com.example.ridize.microsoftgraphstarter">
	
		    <uses-permission android:name="android.permission.INTERNET"/>
		    <application
		        android:allowBackup="true"
		        android:icon="@mipmap/ic_launcher"
		        android:label="Microsoft Graph Starter"
		        android:supportsRtl="true"
		        android:theme="@style/AppTheme">
		        <activity android:name=".MainActivity">
		            <intent-filter>
		                <action android:name="android.intent.action.MAIN" />
		
		                <category android:name="android.intent.category.LAUNCHER" />
		            </intent-filter>
		        </activity>
		    </application>
	
		</manifest>
7. Next, open the **build.gradle** for the app and add a dependency to **com.microsoft.aad:adal:1.1.3**. This is the **Azure AD Authentication Library (ADAL)** will handle almost all of the sign-in/OAuth flow for the mobile application. The final **build.gradle** should should have the following dependencies:

		dependencies {
		    compile fileTree(dir: 'libs', include: ['*.jar'])
		    testCompile 'junit:junit:4.12'
		    compile 'com.android.support:appcompat-v7:22.2.1'
		    compile('com.microsoft.aad:adal:1.1.3') {
		        exclude group: 'com.android.support'
		        // exclude group: 'com.google.code.gson'
		    }
		}
8. Next, add a **ServiceConsts** class to the project (*ServiceConsts.java*) to store application constants such as the Authority (used for sign-in), the resource ID for the Microsoft Graph (used for getting tokens), and the Client ID and Redirect  URI from the application registration. 

		public class ServiceConsts {
		    public static final String AUTHORITY_URL = "https://login.microsoftonline.com/common";
		    public static final String RESOURCE_ID = "https://graph.microsoft.com";
		    public static final String REDIRECT_URL = "http://localhost:8000";
		    public static final String CLIENT_ID = "0b678d8e-6163-4aa4-883c-7b96ecc1a53e";
		}
9. Next, add a **HttpTask** class to the project (*HttpTask.java*). This will serve as an asynchronous utility for performing Http GET requests against Microsoft Graph REST endpoints. You don't need to worry about all the details, but note that the constructor requires an **AuthenticationResult** object and that object is used to set a **Bearer** token on the **Authorization** header of all the REST calls.

		public class HttpTask extends AsyncTask<String, Void, JSONObject> {
		    private JSONObject result = null;
		    private AuthenticationResult auth;
		
		    public HttpTask(AuthenticationResult authResult) {
		        //AuthenticationResult is part of constructor
		        auth = authResult;
		    }
		
		    @Override
		    protected JSONObject doInBackground(String... args) {
		        try {
		            //perform the REST query
		            URL url = new URL(args[0]);
		            HttpsURLConnection conn = (HttpsURLConnection) url.openConnection();
		            conn.setRequestProperty("Authorization", "Bearer " + auth.getAccessToken());
		            conn.setRequestProperty("Accept", "application/json");
		            conn.setRequestMethod("GET");
		            int httpStatus = conn.getResponseCode();
		
		            //check for successful status
		            if (httpStatus == 200) {
		                StringBuffer json = new StringBuffer("");
		                InputStream inputStream = conn.getInputStream();
		                BufferedReader rd = new BufferedReader(new InputStreamReader(inputStream));
		                String line = "";
		                while ((line = rd.readLine()) != null) {
		                    json.append(line);
		                }
		                result = new JSONObject(json.toString());
		                return result;
		            } else
		                return null;
		        } catch (Exception ex) {
		            return null;
		        }
		    }
		
		    @Override
		    protected void onPostExecute(JSONObject json) {
		        //ensure taskHandler has been set
		        if (this.taskHandler != null) {
		            //check for result
		            if (result != null) {
		                taskHandler.taskSuccessful(json);
		            } else {
		                taskHandler.taskFailed();
		            }
		        }
		    }
		
		    public static interface HttpTaskHandler {
		        void taskSuccessful(JSONObject json);
		
		        void taskFailed();
		    }
		
		    HttpTaskHandler taskHandler;
		
		    public void setTaskHandler(HttpTaskHandler taskHandler) {
		        this.taskHandler = taskHandler;
		    }
		}
10. Open the layout for the MainActivity (activity_main.xml) and controls in the RelativeLayout as seen below. The updates include a ProgressBar to display while waiting for data to load and and update to the existing TextView control so we can reference it in code.

		<?xml version="1.0" encoding="utf-8"?>
		<RelativeLayout xmlns:android="http://schemas.android.com/apk/res/android"
		    xmlns:tools="http://schemas.android.com/tools"
		    android:layout_width="match_parent"
		    android:layout_height="match_parent"
		    android:paddingBottom="@dimen/activity_vertical_margin"
		    android:paddingLeft="@dimen/activity_horizontal_margin"
		    android:paddingRight="@dimen/activity_horizontal_margin"
		    android:paddingTop="@dimen/activity_vertical_margin"
		    tools:context="com.example.ridize.microsoftgraphstarter.MainActivity">
		
		    <TextView
		        android:id="@+id/txtMessage"
		        android:layout_width="wrap_content"
		        android:layout_height="wrap_content"
		        android:text="Loading..." />
		    <ProgressBar
		        android:layout_height="wrap_content"
		        android:layout_width="wrap_content"
		        android:id="@+id/spinner"
		        android:indeterminateOnly="true"
		        android:keepScreenOn="true"
		        android:layout_centerVertical="true"
		        android:layout_centerHorizontal="true" />
		</RelativeLayout>
11. Next, open the **MainActivity.java** file and add the following field references below the class definition.

		public class MainActivity extends AppCompatActivity {	    
			AuthenticationContext mAuthContext;
	    	TextView txtMessage;
	    	AlertDialog.Builder dialog;
	    	ProgressBar spinner;
12. Override the **onActivityResult** method for **MainActivity** and pass the parameters on to the **onActivityResult** method of the **mAuthContext** object, but only if mAuthContext has been initialized.

		@Override
	    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
	        super.onActivityResult(requestCode, resultCode, data);
	        if (mAuthContext != null)
	            mAuthContext.onActivityResult(requestCode, resultCode, data);
	    }
13. Inside the **OnCreate** method, initialize the activity's controls using findViewById (**txtMessage** and **spinner**) and initialize the **AlertDialog** as seen below.

        //get controls
        txtMessage = (TextView)findViewById(R.id.txtMessage);
        spinner = (ProgressBar)findViewById(R.id.spinner);

        //setup dialog
        dialog = new AlertDialog.Builder(MainActivity.this);
        dialog.setTitle("Error");
        dialog.setCancelable(false);
        dialog.setPositiveButton("Ok", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
            }
        });
        dialog.create();
14. Next, you will initialize the **AuthenticationContext** for the application. This will initiate an OAuth flow that is handled by the **Azure AD Authentication Library (ADAL)** that you imported via gradle in step 7. The constructor for the AuthenticationContext takes the activity, the authority (where users sign-in to Office 365), and a boolean indicating if the authority should be validated. This must be initialized in a try/catch block.

		try {
            mAuthContext = new AuthenticationContext(MainActivity.this, 
				ServiceConsts.AUTHORITY_URL, false);
		} catch (Throwable t) { }
15. Directly below the AuthenticationContext initialization, use the **mAuthContext** object to get an access token using the **acquireToken** method. This method takes a parameters for the activity, the graph resource ID, the application's client ID, the application's redirect URI, a login hint (which we will pass an empty string), and an AuthenticationCallback object).

        try {
            mAuthContext = new AuthenticationContext(MainActivity.this, 
                    ServiceConsts.AUTHORITY_URL, false);
            mAuthContext.acquireToken(MainActivity.this,
                    ServiceConsts.RESOURCE_ID,
                    ServiceConsts.CLIENT_ID,
                    ServiceConsts.REDIRECT_URL, "",
                    new AuthenticationCallback<AuthenticationResult>() {
                        @Override
                        public void onSuccess(AuthenticationResult result) {
                           
                        }

                        @Override
                        public void onError(Exception exc) {
                            
                        }
                    });
        } catch (Throwable t) { }
16. **onSuccess** of the **AuthenticationCallback** returns an **AuthenticationResult** containing an access token. Access tokens as the key to getting data from the Microsoft Graph. Notice that we use it to initialize the HttpTask utility that will perform REST GETs against the Microsoft Graph.

        //initiate login
        try {
            mAuthContext = new AuthenticationContext(MainActivity.this,
                    ServiceConsts.AUTHORITY_URL, false);
            mAuthContext.acquireToken(MainActivity.this,
                    ServiceConsts.RESOURCE_ID,
                    ServiceConsts.CLIENT_ID,
                    ServiceConsts.REDIRECT_URL, "",
                    new AuthenticationCallback<AuthenticationResult>() {
                        @Override
                        public void onSuccess(AuthenticationResult result) {
                            //make a call to the Microsoft Graph
                            HttpTask http = new HttpTask(result);
                            http.setTaskHandler(new HttpTask.HttpTaskHandler() {
                                @Override
                                public void taskSuccessful(JSONObject json) {
                                    //bind the txtMessage control
                                    try {
                                        String name = json.getString("displayName");
                                        txtMessage.setText("Hello " + name);

                                        //hide spinner
                                        spinner.setVisibility(View.INVISIBLE);
                                    }
                                    catch (Exception ex) {

                                    }
                                }

                                @Override
                                public void taskFailed() {
                                    dialog.setMessage("REST call failed");
                                    dialog.show();
                                }
                            });
                            http.execute("https://graph.microsoft.com/v1.0/me");
                        }

                        @Override
                        public void onError(Exception exc) {
                            dialog.setMessage(exc.getMessage());
                            dialog.show();
                        }
                    });
        }
        catch (Throwable t) {
            dialog.setMessage(t.getMessage());
            dialog.show();
        }
## Step 3: Testing the App ##
The app should be ready to test. Testing approach will vary based on development environment. You can use Android emulators or physical devices. The application should prompt you to sign-in with Office 365 credentials and then display your name.