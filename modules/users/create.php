<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$error = '';

if(isset($_POST['save'])){

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if($username=='' || $password=='' || $role==''){
        $error = "Semua field wajib diisi.";
    }else{

        $check = mysqli_query($conn,"SELECT id FROM users WHERE username='$username'");
        if(mysqli_num_rows($check)>0){
            $error = "Username sudah digunakan.";
        }else{

            $hash = password_hash($password, PASSWORD_DEFAULT);

            mysqli_query($conn,"
                INSERT INTO users (username,password,role)
                VALUES ('$username','$hash','$role')
            ");

            header("Location: index.php?module=users&action=index");
            exit;
        }
    }
}
?>

<style>
.card-form{
background:white;
padding:25px;
border-radius:12px;
box-shadow:0 8px 20px rgba(0,0,0,0.08);
max-width:500px;
}

.form-group{
margin-bottom:15px;
}

.form-group label{
display:block;
font-size:13px;
margin-bottom:5px;
font-weight:bold;
}

.form-group input,
.form-group select{
width:100%;
padding:8px 10px;
border-radius:6px;
border:1px solid #ddd;
}

.btn{
padding:8px 14px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:13px;
text-decoration:none;
}

.btn-primary{background:#16a34a;color:white;}
.btn-secondary{background:#64748b;color:white;}

.alert{
background:#fee2e2;
color:#b91c1c;
padding:10px;
border-radius:6px;
margin-bottom:15px;
font-size:13px;
}
</style>

<h2>Tambah User</h2>

<div class="card-form">

<?php if($error!=''){ ?>
<div class="alert"><?= $error ?></div>
<?php } ?>

<form method="POST">

<div class="form-group">
<label>Username</label>
<input type="text" name="username" required>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<div class="form-group">
<label>Role</label>
<select name="role" required>
<option value="">-- Pilih Role --</option>
<option value="ADMIN">ADMIN</option>
<option value="USER">USER</option>
</select>
</div>

<button type="submit" name="save" class="btn btn-primary">Simpan</button>
<a href="index.php?module=users&action=index" class="btn btn-secondary">Kembali</a>

</form>

</div>

</div></body></html>