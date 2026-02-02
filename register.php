<?php
session_start();
include 'db.php';
include 'csrf.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!verify_csrf_token($csrf)) {
    $errors[] = "Invalid session token. Please reload the form and try again.";
  }
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $citizenship_no = trim($_POST['citizenship_no'] ?? '');
  $district = trim($_POST['district'] ?? '');
  $biometric_id = trim($_POST['biometric_id'] ?? '');

  if ($name === '' || $email === '' || $password === '' || $citizenship_no === '' || $district === '') {
    $errors[] = "All required fields must be filled.";
  }

  $role = 'voter';

  if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, citizenship_no, district, biometric_id) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssss", $name, $email, $hash, $role, $citizenship_no, $district, $biometric_id);
    if ($stmt->execute()) {
      $success = "Registration successful! You can now log in.";
    } else {
      $errors[] = "Registration failed. This email or citizenship number might already be registered.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Voter Registration | Online Voting System</title>
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

  <h2>Voter Registration</h2>
  
  <form method="POST">
    <?php echo csrf_input_field(); ?>
    <?php foreach ($errors as $e): ?>
        <div class="error"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Full Name *</label>
    <input type="text" name="name" placeholder="Enter your full name" required>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Email Address *</label>
    <input type="email" name="email" placeholder="example@mail.com" required>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Password *</label>
    <input type="password" name="password" placeholder="Create a strong password" required>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Citizenship Number *</label>
    <input type="text" name="citizenship_no" placeholder="e.g. 75-01-72-xxxxx" required>

    <label style="font-size: 13px; font-weight: 600; color: #475569;">District *</label>
    <select name="district" required>
        <option value="" disabled selected>Select your district</option>
        
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

    <label style="font-size: 13px; font-weight: 600; color: #475569;">Biometric ID (Optional)</label>
    <input type="text" name="biometric_id" placeholder="BIOHASHxxxxx">

    <div style="margin-top: 10px;">
        <button type="submit" style="width: 100%; margin: 0;">Register Now</button>
    </div>
  </form>

  <p style="text-align: center;">
    Already have an account? <a href="login.php" style="background: none; color: #1877f2; padding: 0; box-shadow: none;">Login here</a>
  </p>

</body>
</html>