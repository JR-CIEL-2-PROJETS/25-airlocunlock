package com.example.projetairlocunlock;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.util.Patterns;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
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

        SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
        String token = prefs.getString("token", null);

        if (token != null) {
            String ip = Config.getIP(this);
            String port = Config.getPort(this);

            Log.d("CONFIG", "IP récupérée via Config : " + ip + ", Port : " + port);

            String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/profil.php";
            new Thread(() -> fetchUserProfile(urlString, token)).start();
        } else {
            Toast.makeText(this, "Token manquant. Veuillez vous reconnecter.", Toast.LENGTH_SHORT).show();
        }

        editNameIcon.setOnClickListener(v -> {
            isNameEditable = !isNameEditable;
            editName.setEnabled(isNameEditable);
            if (isNameEditable) editName.requestFocus();
        });

        editEmailIcon.setOnClickListener(v -> {
            isEmailEditable = !isEmailEditable;
            editEmail.setEnabled(isEmailEditable);
            if (isEmailEditable) editEmail.requestFocus();
        });

        saveButton.setOnClickListener(v -> {
            String newName = editName.getText().toString().trim();
            String newEmail = editEmail.getText().toString().trim();

            if (newName.isEmpty() || newEmail.isEmpty()) {
                Toast.makeText(this, "Les champs ne peuvent pas être vides", Toast.LENGTH_SHORT).show();
                return;
            }

            if (!Patterns.EMAIL_ADDRESS.matcher(newEmail).matches()) {
                Toast.makeText(this, "Email invalide", Toast.LENGTH_SHORT).show();
                return;
            }

            if (token != null) {
                new Thread(() -> updateUserProfile(newName, newEmail, token)).start();
            } else {
                Toast.makeText(this, "Token manquant. Veuillez vous reconnecter.", Toast.LENGTH_SHORT).show();
            }
        });

        backButton.setOnClickListener(v -> {
            startActivity(new Intent(ProfileActivity.this, HomeActivity.class));
            finish();
        });
    }

    private void fetchUserProfile(String urlString, String token) {
        try {
            URL url = new URL(urlString);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setRequestProperty("Authorization", "Bearer " + token);

            int responseCode = conn.getResponseCode();
            Log.d("RESPONSE_CODE", String.valueOf(responseCode));

            if (responseCode == HttpURLConnection.HTTP_OK) {
                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String inputLine;
                while ((inputLine = in.readLine()) != null) response.append(inputLine);
                in.close();

                Log.d("PROFILE_RESPONSE", response.toString());

                runOnUiThread(() -> {
                    try {
                        JSONObject jsonResponse = new JSONObject(response.toString());
                        String status = jsonResponse.getString("status");

                        if ("success".equals(status)) {
                            JSONObject user = jsonResponse.getJSONObject("user");
                            String nom = user.getString("nom");
                            String email = user.getString("email");

                            editName.setText(nom);
                            editEmail.setText(email);
                            profileImage.setImageResource(R.drawable.profile_image);
                        } else {
                            Toast.makeText(ProfileActivity.this, "Erreur de récupération du profil", Toast.LENGTH_SHORT).show();
                        }
                    } catch (Exception e) {
                        Toast.makeText(ProfileActivity.this, "Erreur lors de l'analyse du profil", Toast.LENGTH_SHORT).show();
                    }
                });
            } else {
                runOnUiThread(() -> Toast.makeText(ProfileActivity.this, "Erreur de connexion : " + responseCode, Toast.LENGTH_SHORT).show());
            }

        } catch (Exception e) {
            runOnUiThread(() -> Toast.makeText(ProfileActivity.this, "Erreur réseau : " + e.getMessage(), Toast.LENGTH_SHORT).show());
        }
    }

    private void updateUserProfile(String name, String email, String token) {
        try {
            String ip = Config.getIP(this);
            String port = Config.getPort(this);
            String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/profil.php";
            URL url = new URL(urlString);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();

            conn.setRequestMethod("POST");
            conn.setRequestProperty("Content-Type", "application/json; utf-8");
            conn.setRequestProperty("Authorization", "Bearer " + token);
            conn.setDoOutput(true);

            JSONObject jsonParam = new JSONObject();
            jsonParam.put("nom", name);
            jsonParam.put("email", email);

            try (OutputStream os = conn.getOutputStream()) {
                byte[] input = jsonParam.toString().getBytes("utf-8");
                os.write(input, 0, input.length);
            }

            int responseCode = conn.getResponseCode();
            Log.d("UPDATE_PROFILE", "Code réponse : " + responseCode);

            BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            StringBuilder response = new StringBuilder();
            String inputLine;
            while ((inputLine = in.readLine()) != null) response.append(inputLine);
            in.close();

            JSONObject responseJson = new JSONObject(response.toString());
            String status = responseJson.getString("status");

            runOnUiThread(() -> {
                if ("success".equals(status)) {
                    Toast.makeText(this, "Profil mis à jour avec succès", Toast.LENGTH_SHORT).show();

                    SharedPreferences.Editor editor = getSharedPreferences("MyAppPrefs", MODE_PRIVATE).edit();
                    editor.putString("nom", name);
                    editor.putString("email", email);
                    editor.apply();

                    editName.setEnabled(false);
                    editEmail.setEnabled(false);
                    isNameEditable = false;
                    isEmailEditable = false;
                } else {
                    Toast.makeText(this, "Erreur : " + responseJson.optString("message", "Inconnue"), Toast.LENGTH_SHORT).show();
                }
            });

        } catch (Exception e) {
            runOnUiThread(() -> Toast.makeText(this, "Erreur : " + e.getMessage(), Toast.LENGTH_SHORT).show());
        }
    }
}
