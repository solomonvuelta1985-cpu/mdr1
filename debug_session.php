<?php
session_start();
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "\nSession File: " . session_save_path() . "/" . session_name() . session_id() . "\n";
echo "\nCookies:\n";
print_r($_COOKIE);
echo "</pre>";
