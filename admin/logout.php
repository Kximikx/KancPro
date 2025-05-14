<?php
require_once '../config/init.php';

session_unset();
session_destroy();

redirect('login.php');
