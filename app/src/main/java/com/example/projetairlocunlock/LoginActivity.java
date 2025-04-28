package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.text.InputType;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class LoginActivity extends Activity {

    private EditText emailEditText, passwordEditText;
    private Button loginButton;
    private ImageView eyeIcon, logo;
    private int logoClickCount = 0;
    private long lastClickTime = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        emailEditText = findViewById(R.id.emailInput);
        passwordEditText = findViewById(R.id.passwordInput);
        loginButton = findViewById(R.id.loginButton);
        eyeIcon = findViewById(R.id.eyeIcon);
        logo = findViewById(R.id.logo); // Assure-toi d'avoir un logo avec id="@+id/logo" dans ton layout

        eyeIcon.setOnClickListener(v -> togglePasswordVisibility());
        loginButton.setOnClickListener(v -> new LoginTask().execute(
                emailEditText.getText().toString(), passwordEditText.getText().toString()));

        logo.setOnClickListener(v -> {
            long currentTime = System.currentTimeMillis();
            if (currentTime - lastClickTime < 600) {
                logoClickCount++;
                if (logoClickCount == 3) {
                    logoClickCount = 0;
                    showConfigDialog();
                }
            } else {
                logoClickCount = 1;
            }
            lastClickTime = currentTime;
        });
    }

    private void togglePasswordVisibility() {
        boolean isHidden = (passwordEditText.getInputType() == (InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD));
        passwordEditText.setInputType(isHidden ? InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_VISIBLE_PASSWORD : InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
        eyeIcon.setImageResource(isHidden ? R.drawable.ic_eye_open : R.drawable.ic_eye_closed);
        passwordEditText.setSelection(passwordEditText.getText().length());
    }

    private void showError(String message) {
        Toast.makeText(this, message, Toast.LENGTH_LONG).show();
        emailEditText.setText("");
        passwordEditText.setText("");
    }

    private void showConfigDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Configuration IP / Port");

        LinearLayout layout = new LinearLayout(this);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(50, 40, 50, 10);

        final EditText ipInput = new EditText(this);
        ipInput.setHint("Adresse IP");
        ipInput.setText(Config.getIP(this));
        layout.addView(ipInput);

        final EditText portInput = new EditText(this);
        portInput.setHint("Port");
        portInput.setInputType(InputType.TYPE_CLASS_NUMBER);
        portInput.setText(Config.getPort(this));
        layout.addView(portInput);

        builder.setView(layout);

        builder.setPositiveButton("Enregistrer", (dialog, which) -> {
            Config.setIP(this, ipInput.getText().toString().trim());
            Config.setPort(this, portInput.getText().toString().trim());
            Toast.makeText(this, "Configuration enregistrée", Toast.LENGTH_SHORT).show();
        });

        builder.setNegativeButton("Annuler", (dialog, which) -> dialog.cancel());

        builder.show();
    }

    private class LoginTask extends AsyncTask<String, Void, String> {

        @Override
        protected String doInBackground(String... params) {
            try {
                String ip = Config.getIP(LoginActivity.this);
                String port = Config.getPort(LoginActivity.this);
                String urlString = "https://" + ip + ":" + port + "/AirlockUnlock/client/connexion.php";

                URL url = new URL(urlString);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

                String postData = "email=" + URLEncoder.encode(params[0], "UTF-8") +
                        "&mot_de_passe=" + URLEncoder.encode(params[1], "UTF-8");

                try (OutputStream os = conn.getOutputStream()) {
                    os.write(postData.getBytes());
                }

                if (conn.getResponseCode() != HttpURLConnection.HTTP_OK) {
                    return "{\"status\":\"error\",\"message\":\"Erreur serveur\"}";
                }

                try (BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()))) {
                    StringBuilder response = new StringBuilder();
                    String line;
                    while ((line = in.readLine()) != null) response.append(line);
                    return response.toString();
                }

            } catch (IOException e) {
                return "{\"status\":\"error\",\"message\":\"Erreur de connexion : " + e.getMessage() + "\"}";
            }
        }

        @Override
        protected void onPostExecute(String result) {
            try {
                android.util.Log.d("LOGIN_RESPONSE", result);
                JSONObject json = new JSONObject(result);
                if ("success".equals(json.getString("status"))) {
                    SharedPreferences prefs = getSharedPreferences("user_prefs", MODE_PRIVATE);
                    SharedPreferences.Editor editor = prefs.edit();

                    int clientId = json.getInt("client_id");
                    String nom = json.getString("nom");
                    String email = json.getString("email");

                    editor.putInt("id_client", clientId);
                    editor.putString("nom", nom);
                    editor.putString("email", email);
                    editor.apply();

                    Intent intent = new Intent(LoginActivity.this, HomeActivity.class);
                    intent.putExtra("id_client", clientId);
                    startActivity(intent);
                    finish();
                } else {
                    showError(json.getString("message"));
                }
            } catch (Exception e) {
                showError("Erreur de parsing : " + e.getMessage());
            }
        }
    }
}
