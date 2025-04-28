package com.example.projetairlocunlock;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import java.net.HttpURLConnection;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class InstructionsActivity extends Activity {

    private TextView lockStatus;
    private ImageView lockImage;
    private Button openButton;
    private Button closeButton;
    private Button backToHomeButton;
    private String reservationDate;

    private static final String ESP32_IP_ADDRESS = "http://192.168.137.194";
    private static final String OPEN_URL = "/on";
    private static final String CLOSE_URL = "/off";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_instructions);
        initUI();

        reservationDate = getIntent().getStringExtra("reservationDate");
        checkReservationTime();
    }

    private void initUI() {
        lockStatus = findViewById(R.id.lockStatus);
        lockImage = findViewById(R.id.lockImage);
        openButton = findViewById(R.id.openButton);
        closeButton = findViewById(R.id.closeButton);
        backToHomeButton = findViewById(R.id.backToHomeButton);

        openButton.setOnClickListener(v -> sendCommand(OPEN_URL));
        closeButton.setOnClickListener(v -> sendCommand(CLOSE_URL));
        backToHomeButton.setOnClickListener(v -> {
            Intent intent = new Intent(InstructionsActivity.this, HomeActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
            startActivity(intent);
            finish();
        });
    }

    private void checkReservationTime() {
        try {
            if (reservationDate == null || !reservationDate.contains(" - ")) {
                Toast.makeText(this, "Date invalide ou non reçue", Toast.LENGTH_SHORT).show();
                return;
            }

            Toast.makeText(this, "Date reçue : " + reservationDate, Toast.LENGTH_LONG).show();

            String[] parts = reservationDate.split(" - ");
            String startString = parts[0].trim() + " 09:00";
            String endString = parts[1].trim() + " 09:00";

            SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault());
            Date reservationStartDate = dateFormat.parse(startString);
            Date reservationEndDate = dateFormat.parse(endString);
            Date currentDate = new Date();

            if (currentDate.after(reservationStartDate) && currentDate.before(reservationEndDate)) {
                openButton.setEnabled(true);
            } else {
                openButton.setEnabled(false);
                Toast.makeText(this, "Il n'est pas encore l'heure d'ouverture", Toast.LENGTH_SHORT).show();
            }

        } catch (Exception e) {
            e.printStackTrace();
            Toast.makeText(this, "Erreur de format de date : " + e.getMessage(), Toast.LENGTH_LONG).show();
        }
    }

    private void sendCommand(String commandUrl) {
        // Mise à jour immédiate de l'UI
        runOnUiThread(() -> {
            if (commandUrl.equals(OPEN_URL)) {
                lockStatus.setText("Ouverture en cours...");
                lockImage.setImageResource(R.drawable.ouvert);
            } else {
                lockStatus.setText("Fermeture en cours...");
                lockImage.setImageResource(R.drawable.fermer);
            }
        });

        // Lancement de la requête réseau en arrière-plan
        new Thread(() -> {
            try {
                URL url = new URL(ESP32_IP_ADDRESS + commandUrl);
                HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("GET");
                connection.setConnectTimeout(3000); // Timeout rapide
                connection.connect();

                int responseCode = connection.getResponseCode();
                runOnUiThread(() -> {
                    if (responseCode == HttpURLConnection.HTTP_OK) {
                        Toast.makeText(this, "Commande envoyée avec succès", Toast.LENGTH_SHORT).show();
                    } else {
                        Toast.makeText(this, "Erreur de communication avec l'ESP32 (code : " + responseCode + ")", Toast.LENGTH_SHORT).show();
                    }
                });

            } catch (Exception e) {
                e.printStackTrace();
                runOnUiThread(() ->
                        Toast.makeText(this, "Erreur de connexion : " + e.getMessage(), Toast.LENGTH_SHORT).show()
                );
            }
        }).start();
    }
}
