package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class HomeActivity extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        // Références aux éléments du layout
        TextView title = findViewById(R.id.title);
        ImageView icon = findViewById(R.id.icon);
        ImageView apartmentImage = findViewById(R.id.apartmentImage);
        TextView dateRange = findViewById(R.id.dateRange);
        TextView personCount = findViewById(R.id.personCount);
        TextView apartmentName = findViewById(R.id.apartmentName);
        Button instructionsButton = findViewById(R.id.instructionsButton);
        ImageView exitImage = findViewById(R.id.exitImage); // Référence à l'image "Exit"

        title.setText("Réserver");
        instructionsButton.setText("Voir les instructions");

        dateRange.setText("Du 16 au 18 Janvier 2025");
        personCount.setText("1 personne");
        apartmentName.setText("Appartement Cosy - Paris 16ème");

        instructionsButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                new Thread(new Runnable() {
                    @Override
                    public void run() {
                        try {
                            URL url = new URL("https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/bien1?bien_id=1&nom=appartementParis&reservation=1");
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

                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    // Afficher la réponse du serveur dans une alerte
                                    showAlert("Réponse du serveur", response.toString(), new Runnable() {
                                        @Override
                                        public void run() {
                                            // Passer à l'activité des instructions après avoir cliqué sur "OK"
                                            Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
                                            startActivity(intent);
                                        }
                                    });
                                }
                            });

                        } catch (Exception e) {
                            e.printStackTrace();
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    showAlert("Erreur", "Erreur de connexion", null);
                                }
                            });
                        }
                    }
                }).start();
            }
        });

        // Ajouter un écouteur d'événements pour l'image "Exit"
        exitImage.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Rediriger vers LoginActivity
                Intent intent = new Intent(HomeActivity.this, LoginActivity.class);
                startActivity(intent);
                finish(); // Termine l'activité actuelle pour éviter de revenir en arrière
            }
        });
    }

    // Méthode pour afficher une alerte avec un bouton OK
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