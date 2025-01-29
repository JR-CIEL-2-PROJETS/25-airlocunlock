package com.example.projetairlocunlock;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
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
        setContentView(R.layout.activity_instructions);

        lockStatus = findViewById(R.id.lockStatus);
        lockImage = findViewById(R.id.lockImage);
        openButton = findViewById(R.id.openButton);
        closeButton = findViewById(R.id.closeButton);

        openButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Logique pour ouvrir la serrure
                lockStatus.setText("Serrure status : Ouvert");
                lockImage.setImageResource(R.drawable.ouvert);

                sendRequest("on");
            }
        });

        closeButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                lockStatus.setText("Serrure status : Fermer");
                lockImage.setImageResource(R.drawable.fermer);

                sendRequest("off");
            }
        });
    }

    private void sendRequest(final String action) {
        new Thread(new Runnable() {
            @Override
            public void run() {
                try {
                    String urlString = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/serrure?reservation=1&serurre_1=" + action;
                    URL url = new URL(urlString);
                    HttpURLConnection urlConnection = (HttpURLConnection) url.openConnection();
                    urlConnection.setRequestMethod("GET");

                    int responseCode = urlConnection.getResponseCode();
                    if (responseCode == HttpURLConnection.HTTP_OK) {
                        BufferedReader in = new BufferedReader(new InputStreamReader(urlConnection.getInputStream()));
                        String inputLine;
                        StringBuffer response = new StringBuffer();

                        while ((inputLine = in.readLine()) != null) {
                            response.append(inputLine);
                        }
                        in.close();

                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                Toast.makeText(InstructionsActivity.this, response.toString(), Toast.LENGTH_SHORT).show();
                            }
                        });
                    } else {
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                Toast.makeText(InstructionsActivity.this, "Échec de la requête", Toast.LENGTH_SHORT).show();
                            }
                        });
                    }
                } catch (Exception e) {
                    e.printStackTrace();
                    runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            Toast.makeText(InstructionsActivity.this, "Erreur : " + e.getMessage(), Toast.LENGTH_SHORT).show();
                        }
                    });
                }
            }
        }).start();
    }
}
