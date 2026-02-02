<?php
include '../auth_check.php';
require_role('admin');
include '../db.php';
include '../csrf.php';


$upload_dir = realpath(__DIR__ . "/../assets/images");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) { die('Invalid CSRF token.'); }

    $name = trim($_POST['name']);
    $organization = trim($_POST['organization']);
    $district = trim($_POST['district']);
    $election_id = (int)$_POST['election_id'];
    $image_path = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fileSize = $_FILES['image']['size'] ?? 0;
        $fileError = $_FILES['image']['error'] ?? 0;

        // Check upload error code first for clearer diagnostics
        if ($fileError !== UPLOAD_ERR_OK) {
            switch ($fileError) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errMsg = 'File is too large'; break;
                case UPLOAD_ERR_PARTIAL:
                    $errMsg = 'File was only partially uploaded'; break;
                case UPLOAD_ERR_NO_FILE:
                    $errMsg = 'No file uploaded'; break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errMsg = 'Missing temporary folder on server'; break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errMsg = 'Failed to write file to disk'; break;
                case UPLOAD_ERR_EXTENSION:
                    $errMsg = 'A PHP extension stopped the file upload'; break;
                default:
                    $errMsg = 'Unknown upload error';
            }
            error_log('Candidate image upload error: ' . $errMsg . ' (' . $fileError . ') for file: ' . ($_FILES['image']['name'] ?? 'unknown'));
            echo "<script>alert('Upload error: " . htmlspecialchars($errMsg) . "');</script>";

        } elseif (!in_array($ext, $allowed) || $fileSize > 2*1024*1024) {
            echo "<script>alert('Invalid file type or size. Allowed: jpg,jpeg,png,gif up to 2MB.');</script>";
        } else {
            // Use a robust path for upload dir and ensure it exists & is writable
            $upload_dir = __DIR__ . "/../assets/images";
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    error_log('Failed to create upload dir: ' . $upload_dir);
                    echo "<script>alert('Server error: cannot create upload directory. Check permissions.');</script>";
                }
            }

            if (!is_writable($upload_dir)) {
                @chmod($upload_dir, 0755);
                if (!is_writable($upload_dir)) {
                    error_log('Upload dir not writable: ' . $upload_dir);
                    echo "<script>alert('Server error: upload directory is not writable. Check permissions/owner.');</script>";
                }
            }

            $fname = time() . "_" . preg_replace("/[^A-Za-z0-9\.]/", "", basename($_FILES['image']['name']));
            $target_file = $upload_dir . "/" . $fname;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                @chmod($target_file, 0644);
                $image_path = "assets/images/" . $fname;
            } else {
                error_log('move_uploaded_file failed. tmp_name: ' . ($_FILES['image']['tmp_name'] ?? 'none') . ' target: ' . $target_file);
                echo "<script>alert('Server Error: Could not move uploaded file. Check folder permissions and upload_tmp_dir setting.');</script>";
            }
        }
    }
    $stmt = $conn->prepare("INSERT INTO candidates (name, image, organization, district, election_id) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssssi", $name, $image_path, $organization, $district, $election_id);
    $stmt->execute();
    header("Location: manage_candidates.php"); exit;


}

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $csrf = $_GET['csrf'] ?? '';
    if (!verify_csrf_token($csrf)) { die('Invalid CSRF token.'); }
    $stmt = $conn->prepare("SELECT image FROM candidates WHERE candidate_id=?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $img = $stmt->get_result()->fetch_assoc()['image'];
    if ($img && file_exists(__DIR__ . "/../" . $img)) unlink(__DIR__ . "/../" . $img);
    
    $stmt = $conn->prepare("DELETE FROM candidates WHERE candidate_id=?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    header("Location: manage_candidates.php"); exit;
}

$elections = $conn->query("SELECT election_id, title FROM elections ORDER BY start_date DESC");

// Search handling for candidates (by name, organization, district, election title)
$search_q = trim($_GET['q'] ?? '');
if ($search_q !== '') {
    $like = "%" . $search_q . "%";
    $stmt = $conn->prepare("SELECT c.*, e.title AS election_title FROM candidates c LEFT JOIN elections e ON c.election_id=e.election_id WHERE c.name LIKE ? OR c.organization LIKE ? OR c.district LIKE ? OR e.title LIKE ? ORDER BY candidate_id DESC");
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $candidates = $stmt->get_result();
} else {
    $candidates = $conn->query("SELECT c.*, e.title AS election_title FROM candidates c LEFT JOIN elections e ON c.election_id=e.election_id ORDER BY candidate_id DESC");
}
?>
<!DOCTYPE html> 
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Candidates</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Manage Candidates</h1>

    <h3>Add New Candidate</h3>
    <form method="POST" enctype="multipart/form-data">        <?php echo csrf_input_field(); ?>        <input type="text" name="name" placeholder="Candidate Name" required>
        <input type="text" name="organization" placeholder="Organization/Party" required>
      
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

        <select name="election_id" required>
            <option value="">Select Election</option>
            <?php while ($e = $elections->fetch_assoc()): ?>
                <option value="<?php echo $e['election_id']; ?>"><?php echo htmlspecialchars($e['title']); ?></option>
            <?php endwhile; ?>
        </select>
        <p style="border:none; box-shadow:none; margin:0; padding:0; background:none;">Candidate Image:</p>
        <input type="file" name="image" accept="image/*">
        <button type="submit" name="create">Save Candidate</button>
    </form>

    <h3>Candidate List</h3>

    <form method="GET" style="margin-bottom:10px;">
        <input type="text" name="q" placeholder="Search by name, organization, district, election..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
        <button type="submit">Search</button>
        <?php if (!empty($_GET['q'])): ?><a href="manage_candidates.php" style="margin-left:10px;">Clear</a><?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>photo</th>
                <th>Name</th>
                <th>District</th>
                <th>Election</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($c = $candidates->fetch_assoc()): ?> 
            <tr>
                <td>
                    <?php if ($c['image']): ?>
                        <img src="../<?php echo htmlspecialchars($c['image']); ?>" width="60" style="border-radius:4px;">
                    <?php else: ?>
                        <small>No Image</small>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($c['name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($c['organization']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($c['district']); ?></td>
                <td><?php echo htmlspecialchars($c['election_title']); ?></td>
                <td>
                    <a href="?delete=<?php echo $c['candidate_id']; ?>&csrf=<?php echo urlencode(generate_csrf_token()); ?>" class="glass-btn" onclick="return confirm('Delete candidate?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p style="text-align:center;"><a href="admin_dashboard.php">Back to Dashboard</a></p>

  
</body>
</html>