<?php
function exitWhenNotLoggedIn()
{ 
  if (!isset($_SESSION['loggedIn'])) {
    header("Location: ../index.php");
    exit();  
  }
}