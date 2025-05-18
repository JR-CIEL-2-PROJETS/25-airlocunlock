package com.example.projetairlocunlock;

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

            LinearLayout itemLayout = new LinearLayout(this);
            itemLayout.setOrientation(LinearLayout.VERTICAL);
            itemLayout.setPadding(16, 16, 16, 16);
            itemLayout.setBackgroundResource(R.drawable.bg_reservation_card);

            ImageView imageView = new ImageView(this);
            imageView.setLayoutParams(new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT, 500));
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

            TextView titleView = new TextView(this);
            titleView.setText(title);
            titleView.setTextSize(18);
            titleView.setPadding(0, 16, 0, 8);

            TextView dateView = new TextView(this);
            dateView.setText("Dates : " + dates);
            dateView.setPadding(0, 0, 0, 8);

            Button instructionsButton = new Button(this);
            instructionsButton.setText("VOIR LES INSTRUCTIONS");
            instructionsButton.setBackgroundColor(getResources().getColor(R.color.blue_fond));
            instructionsButton.setTextColor(getResources().getColor(android.R.color.white));

            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault());
            Date now = new Date();
            Date dateArrivee = sdf.parse(dateArriveeStr);
            Date dateDepart = sdf.parse(dateDepartStr);

            if (now.after(dateArrivee) && now.before(dateDepart)) {
                instructionsButton.setEnabled(true);
                instructionsButton.setAlpha(1f);
                instructionsButton.setOnClickListener(v -> {
                    Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
                    intent.putExtra("reservationTitle", title);
                    intent.putExtra("reservationDate", dates);
                    startActivity(intent);
                });
            } else {
                instructionsButton.setEnabled(false);
                instructionsButton.setAlpha(0.5f);
            }

            itemLayout.addView(imageView);
            itemLayout.addView(titleView);
            itemLayout.addView(dateView);
            itemLayout.addView(instructionsButton);

            reservationLayout.addView(itemLayout);

        } catch (Exception e) {
            showError("Erreur d'affichage : " + e.getMessage());
        }
    }

    private void showError(String message) {
        new AlertDialog.Builder(this)
                .setTitle("Erreur")
                .setMessage(message)
                .setCancelable(false)
                .setPositiveButton("OK", (dialog, which) -> {
                    if (message.toLowerCase().contains("token") || message.toLowerCase().contains("expired")) {
                        // Supprimer le token
                        SharedPreferences prefs = getSharedPreferences("MyAppPrefs", MODE_PRIVATE);
                        SharedPreferences.Editor editor = prefs.edit();
                        editor.remove("token");
                        editor.apply();

                        // Redirection vers la page de login
                        Intent intent = new Intent(HomeActivity.this, LoginActivity.class);
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                        startActivity(intent);
                        finish();
                    }
                })
                .show();
    }

}
