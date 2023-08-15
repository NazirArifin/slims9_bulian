<?php
/**
 * Plugin Name: Bebas Pustaka
 * Plugin URI: 
 * Description: Generate Bebas Pustaka for member
 * Version: 0.0.1
 * Author: Mohammad Nazir Arifin
 * Author URI: github.com/NazirArifin
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();
$plugin->registerMenu('membership', 'Bebas Pustaka', __DIR__ . '/index.php');