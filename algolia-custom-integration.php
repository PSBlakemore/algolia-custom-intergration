<?php

/**
 * Plugin Name:     Algolia Custom Integration
 * Description:     Adds Algolia Search features
 * Text Domain:     algolia-custom-integration
 * Version:         1.0.0
 * Author:          Phil Blakemore
 * Author URI:      https://philblakemore.com/
 *
 * @package         Algolia_Custom_Integration
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/wp-cli.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

global $algolia;

$admin_api_key = $_ENV['ADMIN_API_KEY'];
$algolia = \Algolia\AlgoliaSearch\SearchClient::create("CZ0AAFMPIA", "$admin_api_key");
