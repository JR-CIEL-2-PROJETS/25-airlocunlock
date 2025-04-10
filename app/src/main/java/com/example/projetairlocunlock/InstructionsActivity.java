package com.example.projetairlocunlock;

import android.app.Activity;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import java.net.HttpURLConnection;
import java.net.URL;

public class InstructionsActivity extends Activity {

    private TextView lockStatus;
    private ImageView lockImage;
    private Button openButton;
    private Button closeButton;

    private static final String ESP32_IP_ADDRESS = "http://192.168.137.68"; // Remplace par l'adresse IP de ton ESP32
    private static final String OPEN_URL = "/on";  // URL pour ouvrir
    private static final String CLOSE_URL = "/off"; // URL pour fermer

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_instructions);
        initUI();
    }

    private void initUI() {
        lockStatus = findViewById(R.id.lockStatus);
        lockImage = findViewById(R.id.lockImage);
        openButton = findViewById(R.id.openButton);
        closeButton = findViewById(R.id.closeButton);

        // Lors de l'appui sur le bouton "Ouvrir", envoyer la commande pour ouvrir la serrure
        openButton.setOnClickListener(v -> sendCommand(OPEN_URL));

        // Lors de l'appui sur le bouton "Fermer", envoyer la commande pour fermer la serrure
        closeButton.setOnClickListener(v -> sendCommand(CLOSE_URL));
    }

    private void sendCommand(String commandUrl) {
        new Thread(() -> {
            try {
                // Crée l'URL complète
                URL url = new URL(ESP32_IP_ADDRESS + commandUrl);
                HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("GET");

                // Connecte et envoie la requête
                connection.connect();

                // Vérifie le code de réponse HTTP
                int responseCode = connection.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    runOnUiThread(() -> {
                        if (commandUrl.equals(OPEN_URL)) {
                            lockStatus.setText("Ouvert");
                            lockImage.setImageResource(R.drawable.ouvert);  // Assure-toi que tu as l'image "ouvert" dans drawable
                            Toast.makeText(this, "Serrure ouverte", Toast.LENGTH_SHORT).show();
                        } else {
                            lockStatus.setText("Fermé");
                            lockImage.setImageResource(R.drawable.fermer);  // Assure-toi que tu as l'image "fermer" dans drawable
                            Toast.makeText(this, "Serrure fermée", Toast.LENGTH_SHORT).show();
                        }
                    });
                } else {
                    runOnUiThread(() -> {
                        Toast.makeText(this, "Erreur de communication avec l'ESP32", Toast.LENGTH_SHORT).show();
                    });
                }
            } catch (Exception e) {
                e.printStackTrace();
                runOnUiThread(() -> {
                    Toast.makeText(this, "Erreur de connexion", Toast.LENGTH_SHORT).show();
                });
            }
        }).start();
    }
}
