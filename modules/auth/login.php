<?php
global $conn;

if(isset($_SESSION['user_id'])){
    header("Location: index.php?module=dashboard&action=index");
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn,"
        SELECT * FROM users
        WHERE username='$username'
        LIMIT 1
    ");

    if(mysqli_num_rows($query) > 0){

        $user = mysqli_fetch_assoc($query);

        if(password_verify($password, $user['password'])){

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            logActivity(
                'Authentication',
                'LOGIN',
                'User '.$user['name'].' login'
            );

            header("Location: index.php?module=dashboard&action=index");
            exit;

        } else {
            $error = "Password salah.";
        }

    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>WMS KBR - Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(-45deg,#1e3c72,#2a5298,#1e90ff,#0f2027);
background-size:400% 400%;
animation:gradientBG 12s ease infinite;
}

@keyframes gradientBG{
0%{background-position:0% 50%;}
50%{background-position:100% 50%;}
100%{background-position:0% 50%;}
}

.login-wrapper{
width:100%;
max-width:420px;
padding:20px;
}

.login-card{
background:rgba(255,255,255,0.1);
backdrop-filter:blur(15px);
-webkit-backdrop-filter:blur(15px);
border-radius:20px;
padding:40px;
box-shadow:0 25px 50px rgba(0,0,0,0.3);
color:white;
}

.logo{
text-align:center;
margin-bottom:25px;
font-size:26px;
font-weight:bold;
letter-spacing:1px;
}

.logo span{
color:#00e0ff;
}

.input-group{
position:relative;
margin-bottom:25px;
}

.input-group input{
width:100%;
padding:12px 12px;
background:transparent;
border:none;
border-bottom:2px solid rgba(255,255,255,0.5);
color:white;
font-size:14px;
outline:none;
}

.input-group label{
position:absolute;
top:12px;
left:0;
font-size:14px;
color:rgba(255,255,255,0.7);
transition:0.3s;
}

.input-group input:focus ~ label,
.input-group input:valid ~ label{
top:-10px;
font-size:12px;
color:#00e0ff;
}

.toggle-password{
position:absolute;
right:0;
top:12px;
cursor:pointer;
font-size:12px;
color:#00e0ff;
}

.btn-login{
width:100%;
padding:12px;
background:#00e0ff;
border:none;
border-radius:30px;
font-weight:bold;
cursor:pointer;
transition:0.3s;
color:#0f2027;
}

.btn-login:hover{
background:white;
color:#1e3c72;
transform:translateY(-2px);
}

.alert{
background:rgba(255,0,0,0.2);
border:1px solid rgba(255,0,0,0.4);
padding:10px;
border-radius:10px;
margin-bottom:20px;
text-align:center;
font-size:13px;
}

.footer{
text-align:center;
margin-top:20px;
font-size:12px;
color:rgba(255,255,255,0.7);
}

@media(max-width:480px){
.login-card{
padding:30px;
}
}

</style>
</head>

<body>

<div class="login-wrapper">
<div class="login-card">

<div class="logo">
WMS <span>KBR</span>
</div>

<?php if($error!=''){ ?>
<div class="alert"><?= $error ?></div>
<?php } ?>

<form method="POST">

<div class="input-group">
<input type="text" name="username" required>
<label>Username</label>
</div>

<div class="input-group">
<input type="password" name="password" id="password" required>
<label>Password</label>
<span class="toggle-password" onclick="togglePassword()">Show</span>
</div>

<button class="btn-login">LOGIN</button>

</form>

<div class="footer">
© <?= date('Y') ?> WMS by : Wahyu P 
</div>

</div>
</div>

<script>
function togglePassword(){
let input = document.getElementById("password");
let toggle = document.querySelector(".toggle-password");

if(input.type==="password"){
    input.type="text";
    toggle.innerText="Hide";
}else{
    input.type="password";
    toggle.innerText="Show";
}
}
</script>

</body>
</html>