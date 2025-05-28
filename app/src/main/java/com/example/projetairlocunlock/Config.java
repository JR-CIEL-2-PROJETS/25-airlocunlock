package com.example.projetairlocunlock;

import android.content.Context;
import android.content.SharedPreferences;

public class Config {

    private static final String PREFS_NAME = "config_prefs";
    private static final String KEY_IP = "server_ip";
    private static final String KEY_PORT = "server_port";
    private static final String KEY_ESP_IP = "esp_ip";

    // Valeurs par défaut
    private static final String DEFAULT_IP = "172.16.15.74";
    private static final String DEFAULT_PORT = "421";
    private static final String DEFAULT_ESP_IP = "192.168.138.1";

    public static String getIP(Context context) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        return prefs.getString(KEY_IP, DEFAULT_IP);
    }

    public static String getPort(Context context) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        return prefs.getString(KEY_PORT, DEFAULT_PORT);
    }

    public static String getEspIP(Context context) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        return prefs.getString(KEY_ESP_IP, DEFAULT_ESP_IP);
    }

    public static void setIP(Context context, String ip) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        prefs.edit().putString(KEY_IP, ip).apply();
    }

    public static void setPort(Context context, String port) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        prefs.edit().putString(KEY_PORT, port).apply();
    }

    public static void setEspIP(Context context, String ip) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        prefs.edit().putString(KEY_ESP_IP, ip).apply();
    }
}
