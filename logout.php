<?php
 require('function.php');

 $_SESSION = array();
 session_destroy();
 header("Location:login.php");
