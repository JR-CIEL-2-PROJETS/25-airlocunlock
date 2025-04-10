package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.LinearLayout;
import android.graphics.Color;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;

public class HomeActivity extends Activity {

    TextView apartmentName, dateRange, personCount;
    LinearLayout reservationLayout; // Ajout d'un LinearLayout pour appliquer le style
    int clientId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        clientId = getIntent().getIntExtra("client_id", -1); // ✅ Récupère l’ID transmis

        apartmentName = findViewById(R.id.apartmentName);
        dateRange = findViewById(R.id.dateRange);
        personCount = findViewById(R.id.personCount);
        reservationLayout = findViewById(R.id.reservationLayout); // Initialiser le LinearLayout

        Button instructionsButton = findViewById(R.id.instructionsButton);
        ImageView exitImage = findViewById(R.id.exitImage);

        loadReservationsFromServer();

        instructionsButton.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
            startActivity(intent);
        });

        exitImage.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, LoginActivity.class);
            startActivity(intent);
            finish();
        });
    }

    private void loadReservationsFromServer() {
        new Thread(() -> {
            try {
                URL url = new URL("http://172.16.15.63:8080/AirlockUnlock/client/reservations.php?client_id=" + clientId);
                HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("GET");
                connection.setConnectTimeout(15000);
                connection.setReadTimeout(15000);

                InputStreamReader reader = new InputStreamReader(connection.getInputStream());
                StringBuilder response = new StringBuilder();
                int read;
                while ((read = reader.read()) != -1) {
                    response.append((char) read);
                }

                reader.close();
                connection.disconnect();

                JSONObject jsonResponse = new JSONObject(response.toString());
                if (jsonResponse.getString("status").equals("success")) {
                    JSONArray reservations = jsonResponse.getJSONArray("reservations");
                    if (reservations.length() > 0) {
                        JSONObject res = reservations.getJSONObject(0);

                        String dateArrivee = res.getString("date_arrivee");
                        String dateDepart = res.getString("date_depart");
                        String nbPersonnes = res.getString("nombre_personnes");
                        String titre = res.getString("titre");

                        // Conversion des dates
                        DateTimeFormatter formatter = DateTimeFormatter.ofPattern("yyyy-MM-dd");
                        LocalDate currentDate = LocalDate.now(); // Date actuelle
                        LocalDate arrivalDate = LocalDate.parse(dateArrivee, formatter);
                        LocalDate departureDate = LocalDate.parse(dateDepart, formatter);

                        runOnUiThread(() -> {
                            apartmentName.setText(titre);
                            dateRange.setText("Du " + dateArrivee + " au " + dateDepart);
                            personCount.setText(nbPersonnes + " personne(s)");

                            // Comparer les dates
                            if (currentDate.isBefore(arrivalDate) || currentDate.isAfter(departureDate)) {
                                // Griser la case si la date ne correspond pas
                                reservationLayout.setBackgroundColor(Color.GRAY); // Applique une couleur grise
                                apartmentName.setTextColor(Color.DKGRAY); // Change la couleur du texte si souhaité
                                dateRange.setTextColor(Color.DKGRAY);
                                personCount.setTextColor(Color.DKGRAY);
                            } else {
                                // Rétablir les couleurs normales si la réservation est active
                                reservationLayout.setBackgroundColor(Color.WHITE);
                                apartmentName.setTextColor(Color.BLACK);
                                dateRange.setTextColor(Color.BLACK);
                                personCount.setTextColor(Color.BLACK);
                            }
                        });
                    }
                } else {
                    showAlert("Erreur", jsonResponse.getString("message"));
                }

            } catch (Exception e) {
                e.printStackTrace();
                runOnUiThread(() -> showAlert("Erreur", "Impossible de charger les réservations."));
            }
        }).start();
    }

    private void showAlert(String title, String message) {
        runOnUiThread(() -> new AlertDialog.Builder(HomeActivity.this)
                .setTitle(title)
                .setMessage(message)
                .setPositiveButton("OK", null)
                .show());
    }
}
