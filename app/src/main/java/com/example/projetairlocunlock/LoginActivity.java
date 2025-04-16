package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.text.InputType;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;
import android.widget.TextView;
import android.net.Uri;
import android.view.View;


import org.json.JSONObject;

import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class LoginActivity extends Activity {

    TextView forgotPassword, inscription;
    EditText emailEditText, passwordEditText;
    Button loginButton;
    ImageView eyeIcon;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        emailEditText = findViewById(R.id.emailInput);
        passwordEditText = findViewById(R.id.passwordInput);
        loginButton = findViewById(R.id.loginButton);
        eyeIcon = findViewById(R.id.eyeIcon);
        forgotPassword = findViewById(R.id.forgotPassword);
        inscription = findViewById(R.id.inscription);

        eyeIcon.setOnClickListener(v -> togglePasswordVisibility());
        loginButton.setOnClickListener(v -> {
            String email = emailEditText.getText().toString();
            String password = passwordEditText.getText().toString();
            new LoginTask().execute(email, password);
        });

        forgotPassword.setOnClickListener(v -> {
            String url = "https://tonsiteweb.com/mot-de-passe-oublie"; // Remplace par ton lien
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
            startActivity(intent);
        });

        // Redirection vers lien d’inscription
        inscription.setOnClickListener(v -> {
            String url = "https://tonsiteweb.com/inscription"; // Remplace par ton lien
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
            startActivity(intent);
        });
    }

    private void togglePasswordVisibility() {
        int inputType = passwordEditText.getInputType();
        boolean isHidden = (inputType == (InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD));

        passwordEditText.setInputType(isHidden ?
                (InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_VISIBLE_PASSWORD) :
                (InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD));

        eyeIcon.setImageResource(isHidden ? R.drawable.ic_eye_open : R.drawable.ic_eye_closed);
        passwordEditText.setSelection(passwordEditText.getText().length());
    }

    private void showError(String message) {
        Toast.makeText(this, message, Toast.LENGTH_LONG).show();
        emailEditText.setText("");
        passwordEditText.setText("");
    }

    private class LoginTask extends AsyncTask<String, Void, String> {
        @Override
        protected String doInBackground(String... params) {
            try {
                URL url = new URL("https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/login/client");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

                String postData = "email=" + URLEncoder.encode(params[0], "UTF-8") +
                        "&mot_de_passe=" + URLEncoder.encode(params[1], "UTF-8");

                try (OutputStream os = conn.getOutputStream()) {
                    os.write(postData.getBytes());
                }

                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = in.readLine()) != null) response.append(line);
                in.close();
                return response.toString();

            } catch (Exception e) {
                return "{\"status\":\"error\",\"message\":\"Erreur de connexion : " + e.getMessage() + "\"}";
            }
        }

        @Override
        protected void onPostExecute(String result) {
            try {
                JSONObject json = new JSONObject(result);
                if (json.getString("status").equals("success")) {
                    String email = emailEditText.getText().toString();
                    String pass = passwordEditText.getText().toString();

                    if (email.equals("toto@client.com") && pass.equals("toto")) {
                        String nom = json.getString("nom");

                        new AlertDialog.Builder(LoginActivity.this)
                                .setTitle("Connexion réussie")
                                .setMessage("Bonjour, " + nom + " !")
                                .setPositiveButton("OK", (dialog, which) -> {
                                    Intent intent = new Intent(LoginActivity.this, HomeActivity.class);
                                    startActivity(intent);
                                    finish();
                                })
                                .setCancelable(false)
                                .show();
                    } else {
                        showError("Identifiants invalides");
                    }
                } else {
                    showError(json.getString("message"));
                }
            } catch (Exception e) {
                showError("Erreur de parsing : " + e.getMessage());
            }
        }
    }
}
