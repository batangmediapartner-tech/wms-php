<?php
checkLogin();

logActivity(
    'Authentication',
    'LOGOUT',
    'User '.$_SESSION['name'].' logout dari sistem'
);

logout();