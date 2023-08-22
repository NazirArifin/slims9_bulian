<?php
/**
 * Plugin Name: Skripsi
 * Plugin URI:
 * Description: Upload skripsi/ta/tesis for member
 * Version: 0.0.1
 * Author: Mohammad Nazir Arifin
 * Author URI: github.com/NazirArifin
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();
$plugin->registerMenu('membership', 'Skripsi/Tesis', __DIR__ . '/index.php');