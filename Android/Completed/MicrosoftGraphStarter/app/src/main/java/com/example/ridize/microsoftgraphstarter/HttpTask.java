package com.example.ridize.microsoftgraphstarter;

import android.os.AsyncTask;

import com.microsoft.aad.adal.AuthenticationResult;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.URL;

import javax.net.ssl.HttpsURLConnection;

/**
 * Created by ridize on 11/30/2015.
 */
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
