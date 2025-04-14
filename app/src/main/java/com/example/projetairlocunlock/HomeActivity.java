package com.example.projetairlocunlock;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.PopupMenu;
import android.widget.TextView;
import android.widget.LinearLayout;
import android.graphics.Color;

public class HomeActivity extends Activity {

    TextView apartmentName, dateRange, personCount;
    LinearLayout reservationLayout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        apartmentName = findViewById(R.id.apartmentName);
        dateRange = findViewById(R.id.dateRange);
        personCount = findViewById(R.id.personCount);
        reservationLayout = findViewById(R.id.reservationLayout);

        Button instructionsButton = findViewById(R.id.instructionsButton);
        Button instructionsButton2 = findViewById(R.id.instructionsButton2);
        Button instructionsButton3 = findViewById(R.id.instructionsButton3);
        ImageView menuIcon = findViewById(R.id.menuIcon);

        instructionsButton.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
            startActivity(intent);
        });
        instructionsButton2.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
            startActivity(intent);
        });
        instructionsButton3.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, InstructionsActivity.class);
            startActivity(intent);
        });

        menuIcon.setOnClickListener(v -> {
            PopupMenu popupMenu = new PopupMenu(HomeActivity.this, menuIcon);
            popupMenu.getMenu().add(0, R.id.action_profile, 0, "Profil");
            popupMenu.getMenu().add(0, R.id.action_logout, 0, "Se déconnecter");

            popupMenu.setOnMenuItemClickListener(item -> {
                if (item.getTitle().equals("Profil")) {
                    Intent intent = new Intent(HomeActivity.this, ProfileActivity.class);
                    startActivity(intent);
                    return true;
                } else if (item.getTitle().equals("Se déconnecter")) {
                    Intent intent = new Intent(HomeActivity.this, LoginActivity.class);
                    startActivity(intent);
                    finish();
                    return true;
                }
                return false;
            });

            popupMenu.show();
        });

    }

    // Tu peux aussi supprimer complètement cette fonction si plus utilisée
    private void showAlert(String title, String message) {
        // Vérifie si l'activité est en train de se terminer ou déjà détruite
        if (!isFinishing() && !isDestroyed()) {
            runOnUiThread(() -> new AlertDialog.Builder(HomeActivity.this)
                    .setTitle(title)
                    .setMessage(message)
                    .setPositiveButton("OK", null)
                    .show());
        }
    }

}
