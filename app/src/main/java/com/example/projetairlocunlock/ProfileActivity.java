package com.example.projetairlocunlock;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;

public class ProfileActivity extends Activity {

    EditText editName, editEmail;
    ImageView profileImage, editNameIcon, editEmailIcon;
    Button saveButton, backButton;

    boolean isNameEditable = false;
    boolean isEmailEditable = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);

        editName = findViewById(R.id.editName);
        editEmail = findViewById(R.id.editEmail);
        editNameIcon = findViewById(R.id.editNameIcon);
        editEmailIcon = findViewById(R.id.editEmailIcon);
        profileImage = findViewById(R.id.profileImage);
        saveButton = findViewById(R.id.saveButton);
        backButton = findViewById(R.id.backButton);

        SharedPreferences prefs = getSharedPreferences("user_prefs", MODE_PRIVATE);
        String nom = prefs.getString("nom", "");
        String email = prefs.getString("email", "");

        editName.setText(nom);
        editEmail.setText(email);
        profileImage.setImageResource(R.drawable.profile_image);

        // Clic sur l'icône pour modifier le nom
        editNameIcon.setOnClickListener(v -> {
            isNameEditable = !isNameEditable;
            editName.setEnabled(isNameEditable);
            if (isNameEditable) editName.requestFocus();
        });

        // Clic sur l'icône pour modifier l'email
        editEmailIcon.setOnClickListener(v -> {
            isEmailEditable = !isEmailEditable;
            editEmail.setEnabled(isEmailEditable);
            if (isEmailEditable) editEmail.requestFocus();
        });

        // Sauvegarde des données
        saveButton.setOnClickListener(v -> {
            String newName = editName.getText().toString().trim();
            String newEmail = editEmail.getText().toString().trim();

            if (!newName.isEmpty() && !newEmail.isEmpty()) {
                SharedPreferences.Editor editor = prefs.edit();
                editor.putString("nom", newName);
                editor.putString("email", newEmail);
                editor.apply();

                Toast.makeText(this, "Profil mis à jour", Toast.LENGTH_SHORT).show();

                // Désactiver les champs après sauvegarde
                editName.setEnabled(false);
                editEmail.setEnabled(false);
                isNameEditable = false;
                isEmailEditable = false;
            } else {
                Toast.makeText(this, "Les champs ne peuvent pas être vides", Toast.LENGTH_SHORT).show();
            }
        });

        // Retour à la page d'accueil
        backButton.setOnClickListener(v -> {
            startActivity(new Intent(ProfileActivity.this, HomeActivity.class));
            finish();
        });
    }
}
