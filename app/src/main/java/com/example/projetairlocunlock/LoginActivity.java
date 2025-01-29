package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class LoginActivity extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        EditText emailInput = findViewById(R.id.emailInput);
        EditText passwordInput = findViewById(R.id.passwordInput);
        Button loginButton = findViewById(R.id.loginButton);

        loginButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String email = emailInput.getText().toString().trim();
                String password = passwordInput.getText().toString().trim();

                // Vérification des credentials
                if (email.equals("toto@toto.fr") && password.equals("toto")) {
                    // URL de la requête
                    String urlString = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/login/client?email=" + email + "&password=" + password;

                    // Effectuer la requête réseau
                    new Thread(() -> {
                        try {
                            URL url = new URL(urlString);
                            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                            connection.setRequestMethod("GET");
                            connection.connect();

                            int responseCode = connection.getResponseCode();
                            if (responseCode == HttpURLConnection.HTTP_OK) {
                                BufferedReader in = new BufferedReader(new InputStreamReader(connection.getInputStream()));
                                StringBuilder response = new StringBuilder();
                                String inputLine;

                                while ((inputLine = in.readLine()) != null) {
                                    response.append(inputLine);
                                }
                                in.close();

                                // Affichage de la réponse dans une alerte
                                runOnUiThread(() -> showAlert("Réponse", response.toString(), () -> {
                                    // Redirection vers une autre activité après clic sur OK
                                    Intent intent = new Intent(LoginActivity.this, HomeActivity.class);
                                    startActivity(intent);
                                }));

                            } else {
                                runOnUiThread(() -> showAlert("Erreur", "Erreur serveur : " + responseCode, null));
                            }
                        } catch (Exception e) {
                            Log.e("LoginActivity", "Erreur de requête : " + e.getMessage());
                            e.printStackTrace(); // Ajout d'un stacktrace pour mieux débugger
                            runOnUiThread(() -> showAlert("Erreur", "Une erreur s'est produite : " + e.getMessage(), null));
                        }
                    }).start();

                } else {
                    // Alerte pour mot de passe incorrect
                    showAlert("Erreur", "Mot de passe incorrect", null);
                }
            }
        });
    }

    private void showAlert(String title, String message, Runnable onOkClicked) {
        new AlertDialog.Builder(this)
                .setTitle(title)
                .setMessage(message)
                .setPositiveButton("OK", (dialog, which) -> {
                    if (onOkClicked != null) {
                        onOkClicked.run();
                    }
                })
                .show();
    }
}
