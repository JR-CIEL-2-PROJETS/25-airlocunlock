package com.example.projetairlocunlock;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

public class ProfileActivity extends Activity {

    TextView profileName, profileEmail;
    ImageView profileImage;
    Button backButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);

        profileName = findViewById(R.id.name);
        profileEmail = findViewById(R.id.email);
        profileImage = findViewById(R.id.profileImage);
        backButton = findViewById(R.id.backButton);

        // Récupérer les données utilisateur depuis SharedPreferences
        SharedPreferences prefs = getSharedPreferences("user_prefs", MODE_PRIVATE);
        String nom = prefs.getString("nom", "Nom inconnu");
        String email = prefs.getString("email", "Email inconnu");
        int clientId = prefs.getInt("id_client", -1); // Récupérer l'ID client

        profileName.setText("Nom : " + nom);
        profileEmail.setText("Email : " + email);
        profileImage.setImageResource(R.drawable.profile_image);

        backButton.setOnClickListener(v -> {
            Intent intent = new Intent(ProfileActivity.this, HomeActivity.class);
            startActivity(intent);
            finish();
        });
    }
}
