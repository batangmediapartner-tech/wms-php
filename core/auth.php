<?php

function checkLogin(){
    if(!isset($_SESSION['user_id'])){
        header("Location: index.php?module=auth&action=login");
        exit;
    }
}

function checkRole($allowed_roles = []){
    if(!isset($_SESSION['role'])){
        die("Akses ditolak.");
    }

    if(!in_array($_SESSION['role'], $allowed_roles)){
        die("Anda tidak memiliki hak akses.");
    }
}

function logout(){
    session_unset();
    session_destroy();
    header("Location: index.php?module=auth&action=login");
    exit;
}