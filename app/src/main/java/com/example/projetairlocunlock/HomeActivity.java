package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.PopupMenu;
import android.widget.TextView;
import android.widget.Toast;

import com.bumptech.glide.Glide;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class HomeActivity extends Activity {

    TextView apartmentName, dateRange, personCount;
    LinearLayout reservationLayout;
    TextView noReservationText;

    // Déclarez les boutons pour les instructions
    Button instructionsButton, instructionsButton2, instructionsButton3;
    ImageView menuIcon;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        // Associez les vues à partir du XML
        apartmentName = findViewById(R.id.apartmentName);
        dateRange = findViewById(R.id.dateRange);
        reservationLayout = findViewById(R.id.reservationLayout);
        noReservationText = findViewById(R.id.noReservationMessage);

        instructionsButton = findViewById(R.id.instructionsButton);
        menuIcon = findViewById(R.id.menuIcon);

        // Menu icon handler
        menuIcon.setOnClickListener(v -> {
            PopupMenu popupMenu = new PopupMenu(HomeActivity.this, menuIcon);
            popupMenu.getMenu().add(0, R.id.action_profile, 0, "Profil");
            popupMenu.getMenu().add(0, R.id.action_logout, 0, "Se déconnecter");

            popupMenu.setOnMenuItemClickListener(item -> {
                if (item.getTitle().equals("Profil")) {
                    Intent intent = new Intent(this, ProfileActivity.class);
                    startActivity(intent);
                    return true;
                } else if (item.getTitle().equals("Se déconnecter")) {
                    startActivity(new Intent(this, LoginActivity.class));
                    finish();
                    return true;
                }
                return false;
            });

            popupMenu.show();
        });

        // Récupérer l'ID client depuis SharedPreferences
        SharedPreferences prefs = getSharedPreferences("user_prefs", MODE_PRIVATE);
        int clientId = prefs.getInt("id_client", -1); // Si aucun ID n'est trouvé, retourne -1

        if (clientId != -1) {
            new FetchReservationsTask().execute(clientId);
        } else {
            showError("ID client introuvable.");
        }
    }

    private void showAlert(String title, String message) {
        runOnUiThread(() -> new AlertDialog.Builder(this)
                .setTitle(title)
                .setMessage(message)
                .setPositiveButton("OK", null)
                .show());
    }

    private void showError(String message) {
        runOnUiThread(() -> Toast.makeText(this, message, Toast.LENGTH_LONG).show());
    }

    private class FetchReservationsTask extends AsyncTask<Integer, Void, String> {
        @Override
        protected String doInBackground(Integer... params) {
            try {
                int clientId = params[0];
                URL url = new URL("http://192.168.1.160:8080/AirlockUnlock/client/reservations.php?client_id=" + clientId);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");

                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = in.readLine()) != null) response.append(line);
                in.close();
                return response.toString();
            } catch (Exception e) {
                return "{\"status\":\"error\",\"message\":\"Erreur de récupération : " + e.getMessage() + "\"}";
            }
        }

        @Override
        protected void onPostExecute(String result) {
            try {
                JSONObject json = new JSONObject(result);

                if (json.getString("status").equals("success")) {
                    JSONArray reservations = json.getJSONArray("reservations");

                    if (reservations.length() == 0) {
                        noReservationText.setVisibility(TextView.VISIBLE);
                        reservationLayout.setVisibility(LinearLayout.GONE);
                    } else {
                        noReservationText.setVisibility(TextView.GONE);
                        reservationLayout.removeAllViews();

                        for (int i = 0; i < reservations.length(); i++) {
                            JSONObject res = reservations.getJSONObject(i);
                            String title = res.getString("titre");

                            String dateArriveeStr = res.getString("date_arrivee") + " 09:00";
                            String dateDepartStr = res.getString("date_depart") + " 09:00";
                            String dates = dateArriveeStr + " - " + dateDepartStr;
                            String photoFileName = res.getString("photos").trim();
                            String photoUrl = "http://192.168.1.160:8080/AirlockUnlock/bien/photos/" + photoFileName;

                            LinearLayout itemLayout = new LinearLayout(HomeActivity.this);
                            itemLayout.setOrientation(LinearLayout.VERTICAL);
                            itemLayout.setPadding(16, 16, 16, 16);
                            itemLayout.setBackgroundResource(R.drawable.card_background);

                            ImageView imageView = new ImageView(HomeActivity.this);
                            LinearLayout.LayoutParams imageParams = new LinearLayout.LayoutParams(
                                    LinearLayout.LayoutParams.MATCH_PARENT, 500);
                            imageView.setLayoutParams(imageParams);
                            imageView.setScaleType(ImageView.ScaleType.CENTER_CROP);

                            Glide.with(HomeActivity.this)
                                    .load(photoUrl)
                                    .placeholder(R.drawable.placeholder)
                                    .error(R.drawable.image_error)
                                    .into(imageView);

                            TextView nameView = new TextView(HomeActivity.this);
                            nameView.setText(title);
                            nameView.setTextSize(18);
                            nameView.setPadding(0, 16, 0, 8);

                            TextView dateView = new TextView(HomeActivity.this);
                            dateView.setText("Dates : " + dates);
                            dateView.setPadding(0, 0, 0, 8);

                            Button button = new Button(HomeActivity.this);
                            button.setText("VOIR LES INSTRUCTIONS");
                            button.setBackgroundColor(getResources().getColor(R.color.blue_fond));
                            button.setTextColor(getResources().getColor(android.R.color.white));

                            // Vérifie si la date actuelle >= date_arrivee
                            try {
                                SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault());
                                Date now = new Date();
                                Date dateArrivee = sdf.parse(dateArriveeStr);

                                if (now.before(dateArrivee)) {
                                    button.setEnabled(false);
                                    button.setAlpha(0.5f); // effet visuel "grisé"
                                } else {
                                    button.setOnClickListener(v -> {
                                        Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
                                        intent.putExtra("reservationTitle", title);
                                        intent.putExtra("reservationDate", dates);
                                        startActivity(intent);
                                    });
                                }
                            } catch (Exception e) {
                                showError("Erreur de date : " + e.getMessage());
                            }

                            itemLayout.addView(imageView);
                            itemLayout.addView(nameView);
                            itemLayout.addView(dateView);
                            itemLayout.addView(button);

                            reservationLayout.addView(itemLayout);
                        }
                    }
                } else {
                    showError(json.getString("message"));
                }
            } catch (Exception e) {
                showError("Erreur de parsing : " + e.getMessage());
            }
        }
    }
}
