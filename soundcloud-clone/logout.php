<?php
require_once 'includes/functions.php';

$auth = new Auth();
$result = $auth->logout();

redirect('index.php');
?>