<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'] ?? 0;

$data = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT * FROM users WHERE id='$id'
"));

if(!$data){
    echo "User tidak ditemukan.";
    exit;
}

$error = '';

if(isset($_POST['update'])){

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if($username=='' || $role==''){
        $error = "Username dan role wajib diisi.";
    }else{

        $check = mysqli_query($conn,"
            SELECT id FROM users
            WHERE username='$username' AND id!='$id'
        ");

        if(mysqli_num_rows($check)>0){
            $error = "Username sudah digunakan.";
        }else{

            if($password!=''){
                $hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn,"
                    UPDATE users
                    SET username='$username',
                        password='$hash',
                        role='$role'
                    WHERE id='$id'
                ");
            }else{
                mysqli_query($conn,"
                    UPDATE users
                    SET username='$username',
                        role='$role'
                    WHERE id='$id'
                ");
            }

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

.btn-primary{background:#2563eb;color:white;}
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

<h2>Edit User</h2>

<div class="card-form">

<?php if($error!=''){ ?>
<div class="alert"><?= $error ?></div>
<?php } ?>

<form method="POST">

<div class="form-group">
<label>Username</label>
<input type="text" name="username"
value="<?= htmlspecialchars($data['username']); ?>" required>
</div>

<div class="form-group">
<label>Password (Kosongkan jika tidak diganti)</label>
<input type="password" name="password">
</div>

<div class="form-group">
<label>Role</label>
<select name="role" required>
<option value="ADMIN" <?= $data['role']=='ADMIN'?'selected':'' ?>>ADMIN</option>
<option value="USER" <?= $data['role']=='USER'?'selected':'' ?>>USER</option>
</select>
</div>

<button type="submit" name="update" class="btn btn-primary">Update</button>
<a href="index.php?module=users&action=index" class="btn btn-secondary">Kembali</a>

</form>

</div>

</div></body></html>