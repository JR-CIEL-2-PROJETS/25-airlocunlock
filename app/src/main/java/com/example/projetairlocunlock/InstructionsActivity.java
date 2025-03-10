package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class InstructionsActivity extends Activity {

    private TextView lockStatus;
    private ImageView lockImage;
    private Button openButton;
    private Button closeButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Vérification au démarrage avant d'afficher la page
        new Thread(() -> {
            String verificationResponse = verifyLockStatus();
            runOnUiThread(() -> showVerificationDialog(verificationResponse));
        }).start();
    }

    private void showVerificationDialog(String message) {
        new AlertDialog.Builder(this)
                .setTitle("Vérification")
                .setMessage(message)
                .setCancelable(false)
                .setPositiveButton("OK", (dialog, which) -> {
                    setContentView(R.layout.activity_instructions);
                    initUI();
                })
                .show();
    }

    private void initUI() {
        lockStatus = findViewById(R.id.lockStatus);
        lockImage = findViewById(R.id.lockImage);
        openButton = findViewById(R.id.openButton);
        closeButton = findViewById(R.id.closeButton);

        openButton.setOnClickListener(v -> sendRequest("on"));
        closeButton.setOnClickListener(v -> sendRequest("off"));
    }

    private void sendRequest(final String action) {
        new Thread(() -> {
            try {
                String urlString = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/serrure?reservation=1&serurre_1=" + action;
                URL url = new URL(urlString);
                HttpURLConnection urlConnection = (HttpURLConnection) url.openConnection();
                urlConnection.setRequestMethod("GET");

                int responseCode = urlConnection.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    BufferedReader in = new BufferedReader(new InputStreamReader(urlConnection.getInputStream()));
                    StringBuilder response = new StringBuilder();
                    String inputLine;

                    while ((inputLine = in.readLine()) != null) {
                        response.append(inputLine);
                    }
                    in.close();

                    String serverResponse = response.toString();
                    runOnUiThread(() -> {
                        // Met à jour l'interface avec le texte et l'image corrects
                        if (action.equals("on")) {
                            lockStatus.setText("Ouvert");
                            lockImage.setImageResource(R.drawable.ouvert);
                        } else {
                            lockStatus.setText("Fermé");
                            lockImage.setImageResource(R.drawable.fermer);
                        }

                        showToast(serverResponse); // Affiche la réponse du serveur
                    });
                } else {
                    runOnUiThread(() -> showToast("Échec de la requête"));
                }
            } catch (Exception e) {
                e.printStackTrace();
                runOnUiThread(() -> showToast("Erreur : " + e.getMessage()));
            }
        }).start();
    }


    private void showToast(String message) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show(); // Affichage rapide et temporaire
    }


    private String verifyLockStatus() {
        try {
            String urlString = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/serrure_1?serrure_1=op&date=op";
            URL url = new URL(urlString);
            HttpURLConnection urlConnection = (HttpURLConnection) url.openConnection();
            urlConnection.setRequestMethod("GET");

            int responseCode = urlConnection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) {
                BufferedReader in = new BufferedReader(new InputStreamReader(urlConnection.getInputStream()));
                StringBuilder response = new StringBuilder();
                String inputLine;

                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();

                return response.toString();
            } else {
                return "Échec de la vérification";
            }
        } catch (Exception e) {
            e.printStackTrace();
            return "Erreur de vérification : " + e.getMessage();
        }
    }
}