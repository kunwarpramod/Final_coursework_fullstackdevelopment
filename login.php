<?php
session_start();
include 'db.php';
include 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!verify_csrf_token($csrf)) {
    $error = "Invalid session token. Please reload the page and try again.";
  }


$USE_JSON_VALIDATION = file_exists('voters.json');

  if (!isset($error)) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $input_district = trim($_POST['district'] ?? '');
  $input_cit = trim($_POST['citizenship_no'] ?? '');
  $input_bio = trim($_POST['biometric_id'] ?? '');

  $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($row = $res->fetch_assoc()) {
    if (!password_verify($password, $row['password'])) {
      $error = "Invalid password.";
    } else {
      if ($USE_JSON_VALIDATION && $row['role'] === 'voter') {
        $data = json_decode(file_get_contents("voters.json"), true);
        $match = false;
        foreach ($data['voters'] ?? [] as $v) {
          if ($v['citizenship_no'] === $input_cit &&
              $v['district'] === $input_district &&
              ($input_bio === '' || $v['biometric_id'] === $input_bio)) {
            $match = true; break;
          }
        }
        if (!$match) {
          $error = "Your details do not match the authorized voter records.";
        }
      }

      if (!isset($error)) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['name'] = $row['name'];

        if ($row['role'] === 'admin') {
          header("Location: admin/admin_dashboard.php");
        } else {
          header("Location: voter/voter_dashboard.php");
        }
        exit;
      }
    }
  } else {
    $error = "User not found.";
  }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Online Voting System</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .banner-overlay .quote {
      background: transparent;
      border: none;
      box-shadow: none;
      color: #ffffff;
      font-style: italic;
      font-size: 1.1rem;
      margin-top: 10px;
      padding: 0;
      text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
      font-weight: 300;
    }
    .header-banner { height: 220px; }
  </style>
</head>
<body>

  <div class="header-banner">
    <img src="https://static.vecteezy.com/system/resources/previews/020/896/284/original/nepal-flag-with-mountains-and-sunset-in-the-background-vector.jpg" alt="Nepal Voting Portal">
    <div class="banner-overlay">
      <h1>Online Voting System</h1>
      <p class="quote">"Your Vote, Your Voice, Our Nation's Future"</p>
    </div>
  </div>

  <h2>Sign In</h2>

  <form method="POST">
    <?php echo csrf_input_field(); ?>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Email Address</label>
    <input type="email" name="email" placeholder="Enter your email" required>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Password</label>
    <input type="password" name="password" placeholder="Enter your password" required>



    <label style="font-size: 13px; font-weight: 600; color: #475569;">District</label>
    <select name="district">
      <option value="" disabled selected>Select District</option>
      <optgroup label="Koshi Province">
          <option value="Bhojpur">Bhojpur</option>
          <option value="Dhankuta">Dhankuta</option>
          <option value="Ilam">Ilam</option>
          <option value="Jhapa">Jhapa</option>
          <option value="Khotang">Khotang</option>
          <option value="Morang">Morang</option>
          <option value="Okhaldhunga">Okhaldhunga</option>
          <option value="Panchthar">Panchthar</option>
          <option value="Sankhuwasabha">Sankhuwasabha</option>
          <option value="Solukhumbu">Solukhumbu</option>
          <option value="Sunsari">Sunsari</option>
          <option value="Taplejung">Taplejung</option>
          <option value="Terhathum">Terhathum</option>
          <option value="Udayapur">Udayapur</option>
      </optgroup>
      <optgroup label="Madhesh Province">
          <option value="Bara">Bara</option>
          <option value="Dhanusha">Dhanusha</option>
          <option value="Mahottari">Mahottari</option>
          <option value="Parsa">Parsa</option>
          <option value="Rautahat">Rautahat</option>
          <option value="Saptari">Saptari</option>
          <option value="Sarlahi">Sarlahi</option>
          <option value="Siraha">Siraha</option>
      </optgroup>
      <optgroup label="Bagmati Province">
          <option value="Bhaktapur">Bhaktapur</option>
          <option value="Chitwan">Chitwan</option>
          <option value="Dhading">Dhading</option>
          <option value="Dolakha">Dolakha</option>
          <option value="Kathmandu">Kathmandu</option>
          <option value="Kavrepalanchok">Kavrepalanchok</option>
          <option value="Lalitpur">Lalitpur</option>
          <option value="Makwanpur">Makwanpur</option>
          <option value="Nuwakot">Nuwakot</option>
          <option value="Ramechhap">Ramechhap</option>
          <option value="Rasuwa">Rasuwa</option>
          <option value="Sindhuli">Sindhuli</option>
          <option value="Sindhupalchok">Sindhupalchok</option>
      </optgroup>
      <optgroup label="Gandaki Province">
          <option value="Baglung">Baglung</option>
          <option value="Gorkha">Gorkha</option>
          <option value="Kaski">Kaski</option>
          <option value="Lamjung">Lamjung</option>
          <option value="Manang">Manang</option>
          <option value="Mustang">Mustang</option>
          <option value="Myagdi">Myagdi</option>
          <option value="Nawalpur">Nawalpur</option>
          <option value="Parbat">Parbat</option>
          <option value="Syangja">Syangja</option>
          <option value="Tanahun">Tanahun</option>
      </optgroup>
      <optgroup label="Lumbini Province">
          <option value="Arghakhanchi">Arghakhanchi</option>
          <option value="Banke">Banke</option>
          <option value="Bardiya">Bardiya</option>
          <option value="Dang">Dang</option>
          <option value="Gulmi">Gulmi</option>
          <option value="Kapilvastu">Kapilvastu</option>
          <option value="Parasi">Parasi</option>
          <option value="Palpa">Palpa</option>
          <option value="Pyuthan">Pyuthan</option>
          <option value="Rolpa">Rolpa</option>
          <option value="Rukum East">Rukum East</option>
          <option value="Rupandehi">Rupandehi</option>
      </optgroup>
      <optgroup label="Karnali Province">
          <option value="Dailekh">Dailekh</option>
          <option value="Dolpa">Dolpa</option>
          <option value="Humla">Humla</option>
          <option value="Jajarkot">Jajarkot</option>
          <option value="Jumla">Jumla</option>
          <option value="Kalikot">Kalikot</option>
          <option value="Mugu">Mugu</option>
          <option value="Rukum West">Rukum West</option>
          <option value="Salyan">Salyan</option>
          <option value="Surkhet">Surkhet</option>
      </optgroup>
      <optgroup label="Sudurpashchim Province">
          <option value="Achham">Achham</option>
          <option value="Baitadi">Baitadi</option>
          <option value="Bajhang">Bajhang</option>
          <option value="Bajura">Bajura</option>
          <option value="Dadeldhura">Dadeldhura</option>
          <option value="Darchula">Darchula</option>
          <option value="Doti">Doti</option>
          <option value="Kailali">Kailali</option>
          <option value="Kanchanpur">Kanchanpur</option>
      </optgroup>
    </select>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Citizenship Number</label>
    <input type="text" name="citizenship_no" placeholder="Format: 75-xx-xx-xxxxx">

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Biometric ID</label>
    <input type="text" name="biometric_id" placeholder="BIOHASHxxxxx">

    <button type="submit" style="width: 100%; margin: 15px 0 0 0;">Login</button>
  </form>

  <p style="text-align: center;">
    Don't have an account? <a href="register.php" style="background: none; color: #1877f2; padding: 0; box-shadow: none;">Register here</a>
  </p>

</body>
</html>