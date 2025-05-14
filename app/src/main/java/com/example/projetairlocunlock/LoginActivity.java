package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;
import android.text.InputType;
import android.util.Log;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

import java.security.SecureRandom;
import java.security.cert.X509Certificate;
import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

public class LoginActivity extends Activity {

    private EditText emailEditText, passwordEditText;
    private Button loginButton;
    private ImageView eyeIcon, logo;
    private int logoClickCount = 0;
    private long lastClickTime = 0;
    private static final String TAG = "LoginActivity";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        disableSSLCertificateChecking();

        emailEditText = findViewById(R.id.emailInput);
        passwordEditText = findViewById(R.id.passwordInput);
        loginButton = findViewById(R.id.loginButton);
        eyeIcon = findViewById(R.id.eyeIcon);
        logo = findViewById(R.id.logo);

        Log.d(TAG, "Initialisation des vues");

        eyeIcon.setOnClickListener(v -> togglePasswordVisibility());
        loginButton.setOnClickListener(v -> validateAndLogin());

        logo.setOnClickListener(v -> {
            long currentTime = System.currentTimeMillis();
            if (currentTime - lastClickTime < 600) {
                logoClickCount++;
                if (logoClickCount == 3) {
                    logoClickCount = 0;
                    showConfigDialog();
                }
            } else {
                logoClickCount = 1;
            }
            lastClickTime = currentTime;
        });
    }

    private void validateAndLogin() {
        String email = emailEditText.getText().toString().trim();
        String password = passwordEditText.getText().toString();

        Log.d(TAG, "email=" + email + ", password=" + password);

        boolean isValid = true;

        if (email.isEmpty()) {
            emailEditText.setBackgroundTintList(getColorStateList(android.R.color.holo_red_light));
            isValid = false;
        } else {
            emailEditText.setBackgroundTintList(getColorStateList(android.R.color.darker_gray));
        }

        if (password.isEmpty()) {
            passwordEditText.setBackgroundTintList(getColorStateList(android.R.color.holo_red_light));
            isValid = false;
        } else {
            passwordEditText.setBackgroundTintList(getColorStateList(android.R.color.darker_gray));
        }

        if (isValid) {
            Log.d(TAG, "Champs valides, lancement de LoginTask");
            new LoginTask().execute(email, password);
        } else {
            Toast.makeText(this, "Veuillez remplir tous les champs", Toast.LENGTH_SHORT).show();
        }
    }

    private void togglePasswordVisibility() {
        boolean isHidden = (passwordEditText.getInputType() == (InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD));
        passwordEditText.setInputType(isHidden ? InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_VISIBLE_PASSWORD : InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
        eyeIcon.setImageResource(isHidden ? R.drawable.ic_eye_open : R.drawable.ic_eye_closed);
        passwordEditText.setSelection(passwordEditText.getText().length());
    }

    private void showError(String message) {
        Log.e(TAG, "Error: " + message);
        Toast.makeText(this, message, Toast.LENGTH_LONG).show();
        emailEditText.setBackgroundTintList(getColorStateList(android.R.color.holo_red_light));
        passwordEditText.setBackgroundTintList(getColorStateList(android.R.color.holo_red_light));
    }

    private void showSuccess() {
        Log.d(TAG, "Connexion réussie");
        emailEditText.setBackgroundTintList(getColorStateList(android.R.color.holo_green_light));
        passwordEditText.setBackgroundTintList(getColorStateList(android.R.color.holo_green_light));
    }

    private void showConfigDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Configuration Réseau");

        LinearLayout layout = new LinearLayout(this);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(50, 40, 50, 10);

        final EditText ipInput = new EditText(this);
        ipInput.setHint("Adresse IP serveur");
        ipInput.setText(Config.getIP(this));
        layout.addView(ipInput);

        final EditText portInput = new EditText(this);
        portInput.setHint("Port");
        portInput.setInputType(InputType.TYPE_CLASS_NUMBER);
        portInput.setText(Config.getPort(this));
        layout.addView(portInput);

        final EditText espIpInput = new EditText(this);
        espIpInput.setHint("Adresse IP ESP32");
        espIpInput.setText(Config.getEspIP(this)); // <-- Nouvelle méthode à ajouter
        layout.addView(espIpInput);

        builder.setView(layout);

        builder.setPositiveButton("Enregistrer", (dialog, which) -> {
            String ip = ipInput.getText().toString().trim();
            String port = portInput.getText().toString().trim();
            String espIp = espIpInput.getText().toString().trim();

            Config.setIP(this, ip);
            Config.setPort(this, port);
            Config.setEspIP(this, espIp); // <-- Nouvelle méthode à ajouter

            Log.d(TAG, "Configuration enregistrée : IP=" + ip + ", Port=" + port + ", ESP=" + espIp);
            Toast.makeText(this, "Configuration enregistrée", Toast.LENGTH_SHORT).show();
        });

        builder.setNegativeButton("Annuler", (dialog, which) -> dialog.cancel());
        builder.show();
    }


    private class LoginTask extends AsyncTask<String, Void, String> {
        @Override
        protected String doInBackground(String... params) {
            try {
                String ip = Config.getIP(LoginActivity.this);
                String port = Config.getPort(LoginActivity.this);
                String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/connexion.php";

                Log.d(TAG, "URL=" + urlString);

                URL url = new URL(urlString);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

                String postData = "email=" + URLEncoder.encode(params[0], "UTF-8") +
                        "&mot_de_passe=" + URLEncoder.encode(params[1], "UTF-8");

                Log.d(TAG, "postData=" + postData);

                try (OutputStream os = conn.getOutputStream()) {
                    os.write(postData.getBytes());
                }

                int responseCode = conn.getResponseCode();
                Log.d(TAG, "responseCode =" + responseCode);

                if (responseCode != HttpURLConnection.HTTP_OK) {
                    return "{\"status\":\"error\",\"message\":\"Erreur serveur\"}";
                }

                try (BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()))) {
                    StringBuilder response = new StringBuilder();
                    String line;
                    while ((line = in.readLine()) != null) response.append(line);
                    Log.d(TAG, "response=" + response.toString());
                    return response.toString();
                }

            } catch (IOException e) {
                Log.e(TAG, "Erreur de connexion", e);
                return "{\"status\":\"error\",\"message\":\"Erreur de connexion : " + e.getMessage() + "\"}";
            }
        }

        @Override
        protected void onPostExecute(String result) {
            Log.d(TAG, "onPostExecute: result=" + result);
            try {
                JSONObject json = new JSONObject(result);
                if ("success".equals(json.getString("status"))) {
                    String token = json.getString("token");

                    SharedPreferences sharedPreferences = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
                    SharedPreferences.Editor editor = sharedPreferences.edit();
                    editor.putString("token", token); // Enregistre uniquement le token
                    editor.apply(); // Sauvegarde du token

                    Log.d(TAG, "token =" + token);

                    showSuccess();
                    Intent intent = new Intent(LoginActivity.this, HomeActivity.class);
                    startActivity(intent);
                    finish();
                } else {
                    showError(json.getString("message"));
                }
            } catch (Exception e) {
                Log.e(TAG, "Erreur de parsing JSON", e);
                showError("Erreur de parsing : " + e.getMessage());
            }
        }
    }

    private void disableSSLCertificateChecking() {
        try {
            TrustManager[] trustAllCerts = new TrustManager[] {
                    new X509TrustManager() {
                        public void checkClientTrusted(X509Certificate[] chain, String authType) {}
                        public void checkServerTrusted(X509Certificate[] chain, String authType) {}
                        public X509Certificate[] getAcceptedIssuers() { return new X509Certificate[0]; }
                    }
            };

            SSLContext sc = SSLContext.getInstance("SSL");
            sc.init(null, trustAllCerts, new SecureRandom());
            HttpsURLConnection.setDefaultSSLSocketFactory(sc.getSocketFactory());
            HttpsURLConnection.setDefaultHostnameVerifier((hostname, session) -> true);
            Log.d(TAG, "SSL désactivé avec succès");
        } catch (Exception e) {
            Log.e(TAG, "Erreur", e);
        }
    }
}
