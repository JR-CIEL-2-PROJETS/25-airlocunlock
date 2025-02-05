package com.example.projetairlocunlock;

import android.app.Activity;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.bluetooth.BluetoothSocket;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.LocationManager;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;
import android.Manifest;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import java.io.IOException;
import java.io.OutputStream;
import java.util.UUID;

public class InstructionsActivity extends Activity {

    private TextView lockStatus;
    private ImageView lockImage;
    private Button openButton;
    private Button closeButton;

    private static final String MAC_ADDRESS = "00:00:00:00:00:00"; // Adresse MAC de l'ESP32
    private static final UUID UUID_ESP32 = UUID.fromString("7821849C-6940-7821-449C-69403FFB2250");

    private BluetoothAdapter bluetoothAdapter;
    private BluetoothSocket socket;
    private OutputStream outputStream;
    private BluetoothDevice serrureBluetooth;

    private static final int REQUEST_BLUETOOTH_PERMISSIONS = 1;
    private static final int REQUEST_ENABLE_BT = 2;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_instructions);

        lockStatus = findViewById(R.id.lockStatus);
        lockImage = findViewById(R.id.lockImage);
        openButton = findViewById(R.id.openButton);
        closeButton = findViewById(R.id.closeButton);

        bluetoothAdapter = BluetoothAdapter.getDefaultAdapter();
        checkBluetoothPermissions();

        openButton.setOnClickListener(v -> {
            lockStatus.setText("Serrure status : Ouvert");
            lockImage.setImageResource(R.drawable.ouvert);
            sendBluetoothCommand("ON");
        });

        closeButton.setOnClickListener(v -> {
            lockStatus.setText("Serrure status : Fermé");
            lockImage.setImageResource(R.drawable.fermer);
            sendBluetoothCommand("OFF");
        });
    }

    // Vérifie si toutes les permissions Bluetooth sont accordées
    private boolean hasBluetoothPermission() {
        return ContextCompat.checkSelfPermission(this, Manifest.permission.BLUETOOTH_CONNECT)
                == PackageManager.PERMISSION_GRANTED;
    }

    // Vérifie si les permissions de localisation sont accordées
    private boolean hasLocationPermission() {
        return ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                == PackageManager.PERMISSION_GRANTED;
    }

    // Vérifie et demande les permissions Bluetooth et de localisation
    private void checkBluetoothPermissions() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.BLUETOOTH_CONNECT) != PackageManager.PERMISSION_GRANTED
                || ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.BLUETOOTH_CONNECT, Manifest.permission.ACCESS_FINE_LOCATION},
                    REQUEST_BLUETOOTH_PERMISSIONS);
        } else {
            enableBluetooth();
        }
    }

    // Active le Bluetooth si nécessaire
    private void enableBluetooth() {
        if (bluetoothAdapter == null) {
            Toast.makeText(this, "Bluetooth non supporté sur cet appareil", Toast.LENGTH_SHORT).show();
            return;
        }

        if (!bluetoothAdapter.isEnabled()) {
            // Demande d'activation du Bluetooth
            Intent enableBtIntent = new Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE);
            startActivityForResult(enableBtIntent, REQUEST_ENABLE_BT);
        } else {
            connectToLock();
        }
    }

    // Gère la réponse de la demande de permission
    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == REQUEST_BLUETOOTH_PERMISSIONS) {
            boolean bluetoothPermissionGranted = false;
            boolean locationPermissionGranted = false;

            for (int i = 0; i < permissions.length; i++) {
                if (permissions[i].equals(Manifest.permission.BLUETOOTH_CONNECT) && grantResults[i] == PackageManager.PERMISSION_GRANTED) {
                    bluetoothPermissionGranted = true;
                }
                if (permissions[i].equals(Manifest.permission.ACCESS_FINE_LOCATION) && grantResults[i] == PackageManager.PERMISSION_GRANTED) {
                    locationPermissionGranted = true;
                }
            }

            if (bluetoothPermissionGranted && locationPermissionGranted) {
                enableBluetooth();
            } else {
                Toast.makeText(this, "Permissions Bluetooth ou Localisation refusées", Toast.LENGTH_SHORT).show();
            }
        }
    }

    // Gère la réponse de l'activation du Bluetooth
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == REQUEST_ENABLE_BT) {
            if (resultCode == RESULT_OK) {
                connectToLock();
            } else {
                Toast.makeText(this, "Bluetooth doit être activé", Toast.LENGTH_SHORT).show();
            }
        }
    }

    // Connexion à l'ESP32 avec vérification des permissions
    private void connectToLock() {
        if (!hasBluetoothPermission()) {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.BLUETOOTH_CONNECT}, REQUEST_BLUETOOTH_PERMISSIONS);
            return;
        }

        try {
            if (bluetoothAdapter == null) {
                Toast.makeText(this, "Bluetooth non supporté sur cet appareil", Toast.LENGTH_SHORT).show();
                return;
            }

            serrureBluetooth = bluetoothAdapter.getRemoteDevice(MAC_ADDRESS);
            if (serrureBluetooth == null) {
                Toast.makeText(this, "Erreur : périphérique Bluetooth non trouvé", Toast.LENGTH_SHORT).show();
                return;
            }

            socket = serrureBluetooth.createRfcommSocketToServiceRecord(UUID_ESP32);
            socket.connect();
            outputStream = socket.getOutputStream();
            Toast.makeText(this, "Connexion Bluetooth réussie", Toast.LENGTH_SHORT).show();
        } catch (SecurityException e) {
            Toast.makeText(this, "Erreur de permission Bluetooth : " + e.getMessage(), Toast.LENGTH_SHORT).show();
        } catch (IOException e) {
            Toast.makeText(this, "Erreur de connexion Bluetooth : " + e.getMessage(), Toast.LENGTH_SHORT).show();
            e.printStackTrace();
            closeBluetoothConnection();
        }
    }

    // Envoie une commande Bluetooth à l'ESP32 avec vérification des permissions
    private void sendBluetoothCommand(String command) {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.BLUETOOTH_CONNECT) != PackageManager.PERMISSION_GRANTED) {
            Toast.makeText(this, "Permission Bluetooth manquante", Toast.LENGTH_SHORT).show();
            return;
        }

        try {
            if (outputStream != null) {
                outputStream.write(command.getBytes());
                outputStream.flush();
                Toast.makeText(this, "Commande envoyée : " + command, Toast.LENGTH_SHORT).show();
            } else {
                Toast.makeText(this, "Erreur : sortie Bluetooth non disponible", Toast.LENGTH_SHORT).show();
            }
        } catch (IOException e) {
            e.printStackTrace();
            Toast.makeText(this, "Erreur d'envoi Bluetooth", Toast.LENGTH_SHORT).show();
        }
    }

    // Ferme la connexion Bluetooth proprement
    private void closeBluetoothConnection() {
        try {
            if (outputStream != null) {
                outputStream.close();
            }
            if (socket != null) {
                socket.close();
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        closeBluetoothConnection();
    }
}
