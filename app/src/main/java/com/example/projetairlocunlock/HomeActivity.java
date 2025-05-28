package com.example.projetairlocunlock;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.widget.*;
import android.util.Log;
import android.content.SharedPreferences;

import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

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

    LinearLayout reservationLayout;
    TextView noReservationText;
    ImageView menuIcon;
    SwipeRefreshLayout swipeRefreshLayout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        reservationLayout = findViewById(R.id.reservationLayout);
        noReservationText = findViewById(R.id.noReservationMessage);
        menuIcon = findViewById(R.id.menuIcon);
        swipeRefreshLayout = findViewById(R.id.swipeRefreshLayout);

        // Gestion du menu
        menuIcon.setOnClickListener(v -> {
            PopupMenu popupMenu = new PopupMenu(HomeActivity.this, menuIcon);
            popupMenu.getMenu().add(0, 0, 0, "Profil");
            popupMenu.getMenu().add(0, 1, 1, "Se déconnecter");

            popupMenu.setOnMenuItemClickListener(item -> {
                SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
                String token = prefs.getString("token", null);

                if (item.getTitle().equals("Profil") && token != null) {
                    Intent intent = new Intent(HomeActivity.this, ProfileActivity.class);
                    intent.putExtra("token", token);
                    startActivity(intent);
                } else if (item.getTitle().equals("Se déconnecter")) {
                    startActivity(new Intent(HomeActivity.this, LoginActivity.class));
                    finish();
                }
                return true;
            });
            popupMenu.show();
        });

        // Token récupéré automatiquement
        SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
        String token = prefs.getString("token", null);

        if (token != null && !token.isEmpty()) {
            loadReservations(token);
        } else {
            showError("Token introuvable. Veuillez vous reconnecter.");
        }

        // Rafraîchissement manuel
        swipeRefreshLayout.setOnRefreshListener(() -> {
            if (token != null && !token.isEmpty()) {
                loadReservations(token);
            } else {
                swipeRefreshLayout.setRefreshing(false);
                showError("Token introuvable.");
            }
        });
    }

    private void loadReservations(String token) {
        new FetchReservationsTask(token).execute();
    }

    private class FetchReservationsTask extends AsyncTask<Void, Void, String> {
        private final String token;

        FetchReservationsTask(String token) {
            this.token = token;
        }

        @Override
        protected String doInBackground(Void... voids) {
            try {
                SharedPreferences prefs = getSharedPreferences("config_prefs", MODE_PRIVATE);
                String ip = prefs.getString("server_ip", "172.16.15.63");
                String port = prefs.getString("server_port", "421");

                String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/reservations.php";
                URL url = new URL(urlString);

                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Authorization", "Bearer " + token);

                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = in.readLine()) != null) response.append(line);
                in.close();
                return response.toString();
            } catch (Exception e) {
                return "{\"status\":\"error\",\"message\":\"" + e.getMessage() + "\"}";
            }
        }

        @Override
        protected void onPostExecute(String result) {
            swipeRefreshLayout.setRefreshing(false);
            try {
                JSONObject json = new JSONObject(result);
                if (json.getString("status").equals("success")) {
                    JSONArray reservations = json.getJSONArray("reservations");

                    reservationLayout.removeAllViews();
                    if (reservations.length() == 0) {
                        noReservationText.setVisibility(TextView.VISIBLE);
                    } else {
                        noReservationText.setVisibility(TextView.GONE);
                        for (int i = 0; i < reservations.length(); i++) {
                            JSONObject res = reservations.getJSONObject(i);
                            String statut = res.optString("statut", "").toLowerCase(Locale.ROOT);
                            if (statut.equals("confirmée")) {
                                addReservationCard(res);
                            }
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

    private void addReservationCard(JSONObject res) {
        try {
            String title = res.getString("titre");
            String dateArriveeStr = res.getString("date_arrivee") + " 09:00";
            String dateDepartStr = res.getString("date_depart") + " 09:00";
            String dates = dateArriveeStr + " - " + dateDepartStr;
            String photoUrl = res.optString("photo_url", null);
            String numeroSerie = res.optString("numero_serie_tapkey", "");
            int idReservation = res.getInt("id_reservation");

            if (photoUrl != null) photoUrl = photoUrl.trim();
            boolean hasTapkey = !numeroSerie.trim().isEmpty() && !numeroSerie.equalsIgnoreCase("null");

            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault());
            Date now = new Date();
            Date dateArrivee = sdf.parse(dateArriveeStr);
            Date dateDepart = sdf.parse(dateDepartStr);


            boolean isWithinDate = now.after(dateArrivee) && now.before(dateDepart);
            boolean isPast = now.after(dateDepart);
            long dixJoursMillis = 10L * 24 * 60 * 60 * 1000; // 10 jours en millisecondes
            boolean peutAnnuler = now.getTime() < (dateArrivee.getTime() - dixJoursMillis);


            LinearLayout container = new LinearLayout(this);
            container.setOrientation(LinearLayout.VERTICAL);
            container.setPadding(16, 16, 16, 16);
            container.setBackgroundResource(R.drawable.bg_reservation_card);
            container.setLayoutParams(new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT));

            FrameLayout imageFrame = new FrameLayout(this);
            imageFrame.setLayoutParams(new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    500));

            ImageView imageView = new ImageView(this);
            FrameLayout.LayoutParams imageParams = new FrameLayout.LayoutParams(
                    FrameLayout.LayoutParams.MATCH_PARENT,
                    FrameLayout.LayoutParams.MATCH_PARENT);
            imageView.setLayoutParams(imageParams);
            imageView.setScaleType(ImageView.ScaleType.CENTER_CROP);

            if (photoUrl != null && !photoUrl.isEmpty()) {
                Glide.with(this)
                        .load(photoUrl)
                        .diskCacheStrategy(DiskCacheStrategy.NONE)
                        .skipMemoryCache(true)
                        .placeholder(R.drawable.placeholder)
                        .error(R.drawable.image_error)
                        .into(imageView);
            } else {
                imageView.setImageResource(R.drawable.placeholder);
            }

            imageFrame.addView(imageView);

            if (isPast) {
                ImageView deleteIcon = new ImageView(this);
                deleteIcon.setImageResource(R.drawable.ic_delete_red);
                int size = 80;
                FrameLayout.LayoutParams iconParams = new FrameLayout.LayoutParams(size, size);
                iconParams.setMargins(0, 16, 16, 0);
                iconParams.gravity = android.view.Gravity.TOP | android.view.Gravity.END;
                deleteIcon.setLayoutParams(iconParams);
                deleteIcon.setClickable(true);

                deleteIcon.setOnClickListener(v -> {
                    deleteReservation(idReservation, container);
                });

                imageFrame.addView(deleteIcon);
            }

            container.addView(imageFrame);

            LinearLayout itemLayout = new LinearLayout(this);
            itemLayout.setOrientation(LinearLayout.VERTICAL);
            itemLayout.setLayoutParams(new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT));

            // Ajout du bouton d’annulation en haut à droite de l'image
            if (peutAnnuler) {
                Button cancelButton = new Button(this);
                cancelButton.setText("ANNULER");
                cancelButton.setBackgroundColor(getResources().getColor(android.R.color.holo_red_dark));
                cancelButton.setTextColor(getResources().getColor(android.R.color.white));

                FrameLayout.LayoutParams cancelParams = new FrameLayout.LayoutParams(
                        FrameLayout.LayoutParams.WRAP_CONTENT,
                        FrameLayout.LayoutParams.WRAP_CONTENT);
                cancelParams.setMargins(0, 16, 16, 0);
                cancelParams.gravity = android.view.Gravity.TOP | android.view.Gravity.END;
                cancelButton.setLayoutParams(cancelParams);

                cancelButton.setOnClickListener(v -> {
                    new AlertDialog.Builder(HomeActivity.this)
                            .setTitle("Annuler la réservation")
                            .setMessage("Voulez-vous vraiment annuler cette réservation ?\nLe montant sera remboursé dans les 15 jours ouvrés.")
                            .setPositiveButton("Oui", (dialog, which) -> {
                                cancelReservation(idReservation, container);

                            })
                            .setNegativeButton("Non", null)
                            .show();
                });

                imageFrame.addView(cancelButton);
            }



            TextView titleView = new TextView(this);
            titleView.setText(title);
            titleView.setTextSize(18);
            titleView.setPadding(0, 16, 0, 8);

            TextView dateView = new TextView(this);
            dateView.setText("Dates : " + dates);
            dateView.setPadding(0, 0, 0, 8);

            TextView tapkeyView = new TextView(this);
            tapkeyView.setText("Serrure Tapkey : " + (hasTapkey ? "oui" : "non"));
            tapkeyView.setPadding(0, 0, 0, 8);

            Button instructionsButton = new Button(this);
            instructionsButton.setText("VOIR LES INSTRUCTIONS");
            instructionsButton.setBackgroundColor(getResources().getColor(R.color.blue_fond));
            instructionsButton.setTextColor(getResources().getColor(android.R.color.white));

            if (hasTapkey && isWithinDate) {
                instructionsButton.setEnabled(true);
                instructionsButton.setAlpha(1f);
                instructionsButton.setOnClickListener(v -> {
                    SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
                    String token = prefs.getString("token", null);

                    Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
                    intent.putExtra("reservationTitle", title);
                    intent.putExtra("reservationDate", dates);
                    intent.putExtra("tapkeySerial", numeroSerie);
                    intent.putExtra("token", token);
                    startActivity(intent);
                });
            } else {
                instructionsButton.setEnabled(false);
                instructionsButton.setAlpha(0.5f);
            }

            LinearLayout buttonsLayout = new LinearLayout(this);
            buttonsLayout.setOrientation(LinearLayout.HORIZONTAL);
            buttonsLayout.setLayoutParams(new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT));
            buttonsLayout.setPadding(0, 0, 0, 8);

            LinearLayout.LayoutParams instrParams = new LinearLayout.LayoutParams(
                    0,
                    LinearLayout.LayoutParams.WRAP_CONTENT, 1f);
            instructionsButton.setLayoutParams(instrParams);
            buttonsLayout.addView(instructionsButton);

            itemLayout.addView(titleView);
            itemLayout.addView(dateView);
            itemLayout.addView(tapkeyView);
            itemLayout.addView(buttonsLayout);

            container.addView(itemLayout);
            reservationLayout.addView(container);

        } catch (Exception e) {
            showError("Erreur d'affichage : " + e.getMessage());
        }
    }



    @SuppressLint("StaticFieldLeak")
    private void deleteReservation(int idReservation, LinearLayout container) {
        SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
        String token = prefs.getString("token", null);

        if (token == null) {
            showError("Token introuvable. Veuillez vous reconnecter.");
            return;
        }

        SharedPreferences configPrefs = getSharedPreferences("config_prefs", MODE_PRIVATE);
        String ip = configPrefs.getString("server_ip", "172.16.15.74");
        String port = configPrefs.getString("server_port", "421");

        new AsyncTask<Void, Void, String>() {
            @Override
            protected String doInBackground(Void... voids) {
                try {
                    String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/delete-reservation.php";
                    URL url = new URL(urlString);

                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                    conn.setRequestMethod("DELETE");
                    conn.setRequestProperty("Authorization", "Bearer " + token);
                    conn.setRequestProperty("Content-Type", "application/json");
                    conn.setDoOutput(true);

                    JSONObject jsonParam = new JSONObject();
                    jsonParam.put("id_reservation", idReservation);

                    byte[] postData = jsonParam.toString().getBytes("UTF-8");
                    conn.getOutputStream().write(postData);

                    int responseCode = conn.getResponseCode();
                    BufferedReader in = new BufferedReader(new InputStreamReader(
                            (responseCode >= 200 && responseCode < 300) ? conn.getInputStream() : conn.getErrorStream()));
                    StringBuilder response = new StringBuilder();
                    String line;
                    while ((line = in.readLine()) != null) response.append(line);
                    in.close();

                    return response.toString();

                } catch (Exception e) {
                    return "{\"status\":\"error\",\"message\":\"" + e.getMessage() + "\"}";
                }
            }

            @Override
            protected void onPostExecute(String result) {
                try {
                    JSONObject json = new JSONObject(result);
                    if ("success".equals(json.getString("status"))) {
                        reservationLayout.removeView(container);
                        Toast.makeText(HomeActivity.this, "Réservation supprimée avec succès.", Toast.LENGTH_SHORT).show();
                    } else {
                        showError("Erreur lors de la suppression : " + json.getString("message"));
                    }
                } catch (Exception e) {
                    showError("Erreur de réponse : " + e.getMessage());
                }
            }
        }.execute();
    }

    @SuppressLint("StaticFieldLeak")
    private void cancelReservation(int idReservation, LinearLayout container) {
        SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
        String token = prefs.getString("token", null);

        if (token == null) {
            showError("Token introuvable. Veuillez vous reconnecter.");
            return;
        }

        SharedPreferences configPrefs = getSharedPreferences("config_prefs", MODE_PRIVATE);
        String ip = configPrefs.getString("server_ip", "172.16.15.74");
        String port = configPrefs.getString("server_port", "421");

        new AsyncTask<Void, Void, String>() {
            @Override
            protected String doInBackground(Void... voids) {
                try {
                    String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/annuler-reservations.php";
                    URL url = new URL(urlString);

                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                    conn.setRequestMethod("POST");
                    conn.setRequestProperty("Authorization", "Bearer " + token);
                    conn.setRequestProperty("Content-Type", "application/json");
                    conn.setDoOutput(true);

                    JSONObject jsonParam = new JSONObject();
                    jsonParam.put("id_reservation", idReservation);

                    byte[] postData = jsonParam.toString().getBytes("UTF-8");
                    conn.getOutputStream().write(postData);

                    int responseCode = conn.getResponseCode();
                    BufferedReader in = new BufferedReader(new InputStreamReader(
                            (responseCode >= 200 && responseCode < 300) ? conn.getInputStream() : conn.getErrorStream()));
                    StringBuilder response = new StringBuilder();
                    String line;
                    while ((line = in.readLine()) != null) response.append(line);
                    in.close();

                    return response.toString();

                } catch (Exception e) {
                    return "{\"status\":\"error\",\"message\":\"" + e.getMessage() + "\"}";
                }
            }

            @Override
            protected void onPostExecute(String result) {
                try {
                    JSONObject json = new JSONObject(result);
                    if ("success".equals(json.getString("status"))) {
                        reservationLayout.removeView(container);
                        Toast.makeText(HomeActivity.this, "Votre réservation a été annulée avec succès.", Toast.LENGTH_SHORT).show();
                    } else {
                        showError("Erreur lors de l'annulation : " + json.getString("message"));
                    }
                } catch (Exception e) {
                    showError("Erreur de réponse : " + e.getMessage());
                }
            }
        }.execute();
    }



    private void showError(String message) {
        runOnUiThread(() -> {
            AlertDialog.Builder builder = new AlertDialog.Builder(HomeActivity.this);
            builder.setTitle("Erreur")
                    .setMessage(message)
                    .setPositiveButton("OK", null)
                    .show();
        });
    }
}
