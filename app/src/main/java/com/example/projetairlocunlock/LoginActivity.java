package com.example.projetairlocunlock;

import android.app.Activity;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class LoginActivity extends Activity {

    EditText emailEditText, passwordEditText;
    Button loginButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        emailEditText = findViewById(R.id.emailInput);
        passwordEditText = findViewById(R.id.passwordInput);
        loginButton = findViewById(R.id.loginButton);

        loginButton.setOnClickListener(v -> {
            String email = emailEditText.getText().toString();
            String password = passwordEditText.getText().toString();

            // Appel à la méthode pour effectuer la connexion via HTTP
            new LoginTask().execute(email, password);
        });
    }

    private class LoginTask extends AsyncTask<String, Void, String> {

        @Override
        protected String doInBackground(String... params) {
            String email = params[0];
            String password = params[1];
            String result = "";

            try {
                // URL de ton fichier PHP
                URL url = new URL("http://172.16.15.63:8080/AirlockUnlock/client/connexion.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setDoInput(true);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

                // Données envoyées dans la requête POST
                String postData = "email=" + URLEncoder.encode(email, "UTF-8") + "&mot_de_passe=" + URLEncoder.encode(password, "UTF-8");

                OutputStream os = conn.getOutputStream();
                os.write(postData.getBytes());
                os.flush();
                os.close();

                // Lire la réponse du serveur
                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String line;

                while ((line = in.readLine()) != null) {
                    response.append(line);
                }

                in.close();
                result = response.toString();

            } catch (Exception e) {
                result = "Erreur de connexion : " + e.getMessage();
                e.printStackTrace();
            }

            return result;
        }

        @Override
        protected void onPostExecute(String result) {
            try {
                // Parse la réponse JSON
                JSONObject jsonResponse = new JSONObject(result);
                String status = jsonResponse.getString("status");

                // Vérifie si la connexion est réussie
                if (status.equals("success")) {
                    // Récupère l'ID client depuis la réponse
                    int clientId = jsonResponse.getInt("client_id");
                    String clientName = jsonResponse.getString("nom");

                    // Affiche un message de bienvenue
                    Toast.makeText(LoginActivity.this, "Bienvenue, " + clientName, Toast.LENGTH_SHORT).show();

                    // Ouvrir l'activité suivante et transmettre l'ID client
                    Intent intent = new Intent(LoginActivity.this, HomeActivity.class);
                    intent.putExtra("client_id", clientId); // Envoi de l'ID client
                    startActivity(intent);
                    finish();
                } else {
                    // Afficher un message d'erreur
                    String message = jsonResponse.getString("message");
                    Toast.makeText(LoginActivity.this, message, Toast.LENGTH_LONG).show();
                }
            } catch (Exception e) {
                Toast.makeText(LoginActivity.this, "Erreur de parsing de la réponse : " + e.getMessage(), Toast.LENGTH_LONG).show();
            }
        }
    }
}
