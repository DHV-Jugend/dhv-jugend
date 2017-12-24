<?php
/**
 * Plugin Name: DHV-Jugend
 * Plugin URI: https://github.com/DHV-Jugend/dhv-jugend
 * Description: WP site package (plugin) for www.dhv-jugend.de
 * Version: 0.2.0
 * Text Domain: dhv-jugend
 * Domain Path: /languages
 * Author: Christoph Bessei
 * Author URI: https://www.dhv-jugend.de
 * License: GPLv2
 */
require_once(__DIR__ . '/vendor/autoload.php');
(new \BIT\DhvJugend\Boostrap())->run();