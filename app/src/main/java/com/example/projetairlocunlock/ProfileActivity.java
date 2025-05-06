package com.example.projetairlocunlock;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class ProfileActivity extends Activity {

    EditText editName, editEmail;
    ImageView profileImage, editNameIcon, editEmailIcon;
    Button saveButton, backButton;

    boolean isNameEditable = false;
    boolean isEmailEditable = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);

        editName = findViewById(R.id.editName);
        editEmail = findViewById(R.id.editEmail);
        editNameIcon = findViewById(R.id.editNameIcon);
        editEmailIcon = findViewById(R.id.editEmailIcon);
        profileImage = findViewById(R.id.profileImage);
        saveButton = findViewById(R.id.saveButton);
        backButton = findViewById(R.id.backButton);

        Intent intent = getIntent();
        int clientId = intent.getIntExtra("id_client", -1); // Récupère l'ID client
        String token = intent.getStringExtra("token");     // Récupère le token


        if (clientId != -1 && token != null) {
            // Utilisation des SharedPreferences pour récupérer l'IP et le port
            SharedPreferences prefs = getSharedPreferences("config_prefs", MODE_PRIVATE);
            String ip = prefs.getString("server_ip", "172.16.15.63");
            String port = prefs.getString("server_port", "421");

            Log.d("CONFIG_PREFS", "IP récupérée : " + ip + ", Port récupéré : " + port);

            // Construire l'URL avec l'IP et le port récupérés
            String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/profil.php?id_client=" + clientId;
            new Thread(() -> fetchUserProfile(urlString, token)).start();
        } else {
            Toast.makeText(this, "Aucun profil trouvé.", Toast.LENGTH_SHORT).show();
        }

        // Clic sur l'icône pour modifier le nom
        editNameIcon.setOnClickListener(v -> {
            isNameEditable = !isNameEditable;
            editName.setEnabled(isNameEditable);
            if (isNameEditable) editName.requestFocus();
        });

        // Clic sur l'icône pour modifier l'email
        editEmailIcon.setOnClickListener(v -> {
            isEmailEditable = !isEmailEditable;
            editEmail.setEnabled(isEmailEditable);
            if (isEmailEditable) editEmail.requestFocus();
        });

        // Sauvegarde des données
        saveButton.setOnClickListener(v -> {
            String newName = editName.getText().toString().trim();
            String newEmail = editEmail.getText().toString().trim();

            if (!newName.isEmpty() && !newEmail.isEmpty()) {
                // Sauvegarde des nouvelles informations dans les SharedPreferences
                SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
                SharedPreferences.Editor editor = prefs.edit();
                editor.putString("nom", newName);
                editor.putString("email", newEmail);
                editor.apply();

                Toast.makeText(this, "Profil mis à jour", Toast.LENGTH_SHORT).show();

                // Désactiver les champs après sauvegarde
                editName.setEnabled(false);
                editEmail.setEnabled(false);
                isNameEditable = false;
                isEmailEditable = false;
            } else {
                Toast.makeText(this, "Les champs ne peuvent pas être vides", Toast.LENGTH_SHORT).show();
            }
        });

        // Retour à la page d'accueil
        backButton.setOnClickListener(v -> {
            startActivity(new Intent(ProfileActivity.this, HomeActivity.class));
            finish();
        });
    }

    private void fetchUserProfile(String urlString, String token) {
        try {
            // Créer une URL avec l'IP et le port récupérés
            URL url = new URL(urlString);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setRequestProperty("Authorization", "Bearer " + token); // En-tête avec le token

            int responseCode = conn.getResponseCode();
            Log.d("RESPONSE_CODE", String.valueOf(responseCode)); // Log du code de réponse

            if (responseCode == HttpURLConnection.HTTP_OK) { // Vérifie que la réponse est OK
                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                String inputLine;
                StringBuilder response = new StringBuilder();

                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();

                // Afficher la réponse brute pour déboguer
                Log.d("PROFILE_RESPONSE", response.toString());

                // Traitement de la réponse JSON
                runOnUiThread(() -> {
                    try {
                        JSONObject jsonResponse = new JSONObject(response.toString());
                        String status = jsonResponse.getString("status");

                        if ("success".equals(status)) {
                            JSONObject user = jsonResponse.getJSONObject("user");
                            String nom = user.getString("nom");
                            String email = user.getString("email");

                            // Remplir les champs avec les informations de l'utilisateur
                            editName.setText(nom);
                            editEmail.setText(email);
                            profileImage.setImageResource(R.drawable.profile_image); // Image de profil par défaut
                        } else {
                            Toast.makeText(ProfileActivity.this, "Erreur de récupération du profil", Toast.LENGTH_SHORT).show();
                        }
                    } catch (Exception e) {
                        Toast.makeText(ProfileActivity.this, "Erreur lors de la réponse du serveur", Toast.LENGTH_SHORT).show();
                    }
                });
            } else {
                // En cas d'échec de la requête
                runOnUiThread(() -> Toast.makeText(ProfileActivity.this, "Erreur de connexion : " + responseCode, Toast.LENGTH_SHORT).show());
            }

        } catch (Exception e) {
            // En cas d'exception
            runOnUiThread(() -> Toast.makeText(ProfileActivity.this, "Erreur réseau : " + e.getMessage(), Toast.LENGTH_SHORT).show());
        }
    }

}
