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
    private String token;

    private static final String OPEN_URL = "/on";
    private static final String CLOSE_URL = "/off";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_instructions);
        initUI();

        reservationDate = getIntent().getStringExtra("reservationDate");
        token = getIntent().getStringExtra("token"); // Récupération du token dynamique
        checkReservationTime();
    }

    private void initUI() {
        lockStatus = findViewById(R.id.lockStatus);
        lockImage = findViewById(R.id.lockImage);
        openButton = findViewById(R.id.openButton);
        closeButton = findViewById(R.id.closeButton);
        backToHomeButton = findViewById(R.id.backToHomeButton);

        // Initialisation état des boutons : ouvrir actif, fermer inactif
        openButton.setEnabled(true);
        closeButton.setEnabled(false);

        openButton.setOnClickListener(v -> {
            // Désactiver ouvrir, activer fermer
            openButton.setEnabled(false);
            closeButton.setEnabled(true);
            sendCommand(OPEN_URL);
        });

        closeButton.setOnClickListener(v -> {
            // Désactiver fermer, activer ouvrir
            closeButton.setEnabled(false);
            openButton.setEnabled(true);
            sendCommand(CLOSE_URL);
        });

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

            String[] parts = reservationDate.split(" - ");
            String startString = parts[0].trim();
            String endString = parts[1].trim();

            SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault());
            Date reservationStartDate = dateFormat.parse(startString);
            Date reservationEndDate = dateFormat.parse(endString);
            Date currentDate = new Date();

            boolean isWithinReservation = currentDate.after(reservationStartDate) && currentDate.before(reservationEndDate);

            openButton.setEnabled(isWithinReservation);
            closeButton.setEnabled(isWithinReservation);

            if (!isWithinReservation) {
                Toast.makeText(this, "Hors période de réservation", Toast.LENGTH_SHORT).show();
            }

        } catch (Exception e) {
            e.printStackTrace();
            Toast.makeText(this, "Erreur de format de date : " + e.getMessage(), Toast.LENGTH_LONG).show();
        }
    }

    private void sendCommand(String commandUrl) {
        String espIp = Config.getEspIP(InstructionsActivity.this);
        String tapkeySerial = getIntent().getStringExtra("tapkeySerial");

        new Thread(() -> {
            try {
                String fullUrl = "http://" + espIp + commandUrl + "?serial=" + tapkeySerial;
                URL url = new URL(fullUrl);
                HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("GET");
                connection.setConnectTimeout(3000);

                // Envoi du token dynamique dans le header Authorization
                connection.setRequestProperty("Authorization", "Bearer " + token);

                connection.connect();
                int responseCode = connection.getResponseCode();

                runOnUiThread(() -> {
                    if (responseCode == HttpURLConnection.HTTP_OK) {
                        // Modifier le status et l'image seulement en cas de succès
                        if (commandUrl.equals(OPEN_URL)) {
                            lockStatus.setText("Ouvert");
                            lockImage.setImageResource(R.drawable.ouvert);
                        } else {
                            lockStatus.setText("Fermé");
                            lockImage.setImageResource(R.drawable.fermer);
                        }
                        Toast.makeText(this, "Commande envoyée avec succès", Toast.LENGTH_SHORT).show();
                    } else {
                        Toast.makeText(this, "Mauvaise Serrure", Toast.LENGTH_SHORT).show();
                        // Revenir à l'état précédent : bouton appuyé activé, l'autre désactivé
                        if (commandUrl.equals(OPEN_URL)) {
                            openButton.setEnabled(true);
                            closeButton.setEnabled(false);
                        } else {
                            closeButton.setEnabled(true);
                            openButton.setEnabled(false);
                        }
                    }
                });

            } catch (Exception e) {
                e.printStackTrace();
                runOnUiThread(() -> {
                    Toast.makeText(this, "Erreur de connexion : " + e.getMessage(), Toast.LENGTH_SHORT).show();
                    // Revenir à l'état précédent en cas d'erreur réseau
                    if (commandUrl.equals(OPEN_URL)) {
                        openButton.setEnabled(true);
                        closeButton.setEnabled(false);
                    } else {
                        closeButton.setEnabled(true);
                        openButton.setEnabled(false);
                    }
                });
            }
        }).start();
    }
}
