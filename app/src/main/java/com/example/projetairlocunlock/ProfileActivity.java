package com.example.projetairlocunlock;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

public class ProfileActivity extends Activity {

    // Déclaration des éléments de l'interface
    TextView profileName, profileEmail;
    ImageView profileImage;
    Button backButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile); // Lien avec le layout XML

        // Initialisation des éléments de l'interface
        profileName = findViewById(R.id.name);
        profileEmail = findViewById(R.id.email);
        profileImage = findViewById(R.id.profileImage);
        backButton = findViewById(R.id.backButton);

        // Mettre des données fictives ou récupérer des données depuis une source (par exemple une base de données ou une API)
        profileName.setText("Nom : Toto Toto");
        profileEmail.setText("Email : toto@client.com");

        // Mettre une image de profil
        profileImage.setImageResource(R.drawable.profile_image); // Remplacer "profile_image" par le nom réel de l'image

        // Action du bouton retour
        backButton.setOnClickListener(v -> {
            // Créer un Intent pour revenir à l'activité d'accueil
            Intent intent = new Intent(ProfileActivity.this, HomeActivity.class);
            startActivity(intent);
            finish(); // Terminer cette activité pour ne pas revenir en arrière
        });
    }
}
