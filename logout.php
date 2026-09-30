<?php
require 'config/db.php';
$_SESSION = [];
session_destroy();
redirect('index.php');
