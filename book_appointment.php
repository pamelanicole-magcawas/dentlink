<?php
session_start();
include 'config/db_connect.php';

// Redirect to login if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in user's info
$user_stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$full_name = $user['first_name'] . ' ' . $user['last_name'];
$email = $user['email'];

// Handle booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'book') {
    $date = $_POST['date'];
    $location = $_POST['location'];
    $time = $_POST['time'];
    $service = $_POST['service'];

    // Check conflict with approved appointments
    $conflict_sql = "SELECT * FROM appointments 
                     WHERE status = 'approved' 
                     AND date = ? 
                     AND start_time = ? 
                     AND location = ?";
    $stmt = $conn->prepare($conflict_sql);
    $stmt->bind_param("sss", $date, $time, $location);
    $stmt->execute();
    $conflict_result = $stmt->get_result();

    if ($conflict_result->num_rows > 0) {
        echo "<p style='color: red; font-weight: bold;'>⚠️ Sorry, this time slot is already booked at $location. Please choose another time.</p>";
    } else {
        // Insert appointment linked to logged-in user
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, name, email, date, location, start_time, description, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issssss", $user_id, $full_name, $email, $date, $location, $time, $service);

        if ($stmt->execute()) {
            echo "<p style='color: green; font-weight: bold;'>✅ Appointment request sent for $location! Please wait for admin approval.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>⚠️ Failed to submit appointment. Please try again.</p>";
        }
    }
}

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    
      // Ensure user owns the appointment and it’s not approved
      $verify_sql = "SELECT * FROM appointments WHERE id = ? AND user_id = ? AND status != 'approved'";
      $stmt = $conn->prepare($verify_sql);
      $stmt->bind_param("ii", $id, $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
  
      if ($result->num_rows > 0) {
          $delete_sql = "DELETE FROM appointments WHERE id = ?";
          $stmt = $conn->prepare($delete_sql);
          $stmt->bind_param("i", $id);
          if ($stmt->execute()) {
              echo "<p style='color: green; font-weight: bold;'>🗑 Appointment deleted successfully!</p>";
          } else {
              echo "<p style='color: red; font-weight: bold;'>⚠️ Failed to delete appointment.</p>";
          }
      } else {
          echo "<p style='color: red; font-weight: bold;'>⚠️ You cannot delete this appointment.</p>";
      }
  }
  
  // Fetch logged-in user's pending and denied appointments
  $pending = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id AND status = 'pending' ORDER BY date DESC");
  $denied = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id AND status = 'denied' ORDER BY date DESC");
  ?>
  
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <title>Book Your Dental Appointment</title>
      <style>
          body {
              font-family: Arial, sans-serif;
              margin: 40px;
              background: #f9f9f9;
              display: flex;
              flex-direction: column;
              align-items: center;
          }
          iframe {
              border: 1px solid #ccc;
              border-radius: 8px;
              width: 100%;
              height: 500px;
              margin-bottom: 30px;
          }
          .container {
              display: flex;
              width: 100%;
              max-width: 1000px;
              gap: 20px;
          }
          form {
              background: #fff;
              padding: 25px;
              border-radius: 8px;
              box-shadow: 0 0 10px rgba(0,0,0,0.1);
              flex: 2;
          }
          .sidebar {
              flex: 1;
              background: #fff;
              border-radius: 8px;
              padding: 20px;
              box-shadow: 0 0 10px rgba(0,0,0,0.1);
              overflow-y: auto;
              max-height: 600px;
          }
          .sidebar h3 { text-align: center; margin-bottom: 10px; }
          .appointment-item {
              border-bottom: 1px solid #ddd;
              padding: 8px 0;
              font-size: 14px;
              position: relative;
          }
          .appointment-item:last-child { border-bottom: none; }
          .delete-btn {
              background-color: #dc3545;
              color: white;
              border: none;
              padding: 4px 8px;
              border-radius: 4px;
              cursor: pointer;
              font-size: 12px;
              margin-top: 5px;
          }
          .delete-btn:hover { background-color: #c82333; }
          label { font-weight: bold; }
          input, textarea, select, button {
              width: 100%;
              margin: 8px 0;
              padding: 8px;
              border-radius: 4px;
              border: 1px solid #ccc;
          }
          button {
              background-color: #4CAF50;
              color: white;
              border: none;
              cursor: pointer;
          }
          button:hover { background-color: #45a049; }
      </style>
  </head>
  <body>
  
  <h1>🦷 Book Your Dental Appointment</h1>
  <p><strong>Welcome, <?= htmlspecialchars($full_name) ?></strong> (<?= htmlspecialchars($email) ?>)</p>
  
  <!-- Google Calendar Embed -->
  <iframe
      src="https://calendar.google.com/calendar/embed?src=allimagcawas%40gmail.com&ctz=Asia%2FManila"
      frameborder="0" scrolling="no"></iframe>
  
  <div class="container">
      <!-- Booking Form -->
      <form method="POST">
          <input type="hidden" name="action" value="book">
  
          <label>Date:</label>
          <input type="date" name="date" required>
  
          <label>Location:</label>
          <select name="location" required>
              <option value="">--Select Location--</option>
              <option value="Dental Clinic, Lipa City">Lipa</option>
              <option value="Dental Clinic, San Pablo City">San Pablo</option>
          </select>
  
          <label>Time:</label>
          <input type="time" name="time" required>  

        <label>Service:</label>
        <select name="service" id="serviceSelect" required>
            <option value="">--Select Service--</option>
            <option value="All Ceramic Veneers with E-Max">All Ceramic Veneers with E-Max</option>
            <option value="All Ceramic Fixed Bridge with Zirconia">All Ceramic Fixed Bridge with Zirconia</option>
            <option value="Cleaning">Cleaning</option>
            <option value="Consultation">Consultation</option>
            <option value="Cosmetic Restoration Crown Build-Up">Cosmetic Restoration Crown Build-Up</option>
            <option value="Dental Braces">Dental Braces</option>
            <option value="Dental Bridges">Dental Bridges</option>
            <option value="Dental Crowns">Dental Crowns</option>
            <option value="Dental Filling">Dental Filling</option>
            <option value="Dental Veneers">Dental Veneers</option>
            <option value="Dentures">Dentures</option>
            <option value="Digital Panoramic X-Ray">Digital Panoramic X-Ray</option>
            <option value="Digital Periapical X-Ray & Intra-Oral Camera">Digital Periapical X-Ray & Intra-Oral Camera</option>
            <option value="Extraction of Mandibular First Molar">Extraction of Mandibular First Molar</option>
            <option value="Fixed Bridge">Fixed Bridge</option>
            <option value="Flexible Dentures">Flexible Dentures</option>
            <option value="Fluoride Treatment">Fluoride Treatment</option>
            <option value="General Checkup">General Checkup</option>
            <option value="Gingivectomy">Gingivectomy</option>
            <option value="Metallic Ortho Braces">Metallic Ortho Braces</option>
            <option value="Odontectomy (Impacted Tooth Removal)">Odontectomy (Impacted Tooth Removal)</option>
            <option value="Oral Prophylaxis">Oral Prophylaxis</option>
            <option value="Orthodontic Treatment">Orthodontic Treatment</option>
            <option value="Panoramic X-Ray">Panoramic X-Ray</option>
            <option value="Periapical X-Ray">Periapical X-Ray</option>
            <option value="Porcelain Fused to Metal Fixed Bridge">Porcelain Fused to Metal Fixed Bridge</option>
            <option value="Removable Partial Dentures">Removable Partial Dentures</option>
            <option value="Retainers and Other Ortho Appliances">Retainers and Other Ortho Appliances</option>
            <option value="Root Canal Treatment">Root Canal Treatment</option>
            <option value="Self-Ligating Braces">Self-Ligating Braces</option>
            <option value="Teeth Whitening">Teeth Whitening</option>
            <option value="Tooth Extraction">Tooth Extraction</option>
            <option value="Tooth Restoration">Tooth Restoration</option>
            <option value="Wisdom / 3rd Molar Extraction">Wisdom / 3rd Molar Extraction</option>
        </select>

        <div id="serviceDescription" style="display: none; background: #e7f3ff; padding: 12px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #2196F3;">
            <strong style="color: #1976D2;">Service Details:</strong>
            <p id="descriptionText" style="margin: 8px 0 0 0; color: #555; font-size: 14px;"></p>
        </div>

        <button type="submit">Submit Booking</button>
    </form>

    <!-- Sidebar with pending and denied -->
    <div class="sidebar">
        <h3>Pending Requests</h3>
        <?php if ($pending->num_rows > 0) { ?>
            <?php while ($p = $pending->fetch_assoc()) { ?>
                <div class="appointment-item">
                    <strong><?= htmlspecialchars($p['description']) ?></strong><br>
                    <?= htmlspecialchars($p['date']) ?> @ <?= htmlspecialchars($p['start_time']) ?><br>
                    <small><b>Location:</b> <?= htmlspecialchars($p['location']) ?></small><br>
                    <button class="delete-btn" onclick="showDeleteModal(<?= $p['id'] ?>)">Delete Request</button>
                </div>
            <?php } ?>
        <?php } else { echo "<p>No pending requests.</p>"; } ?>

        <h3 style="margin-top: 20px;">Denied Requests</h3>
        <?php if ($denied->num_rows > 0) { ?>
            <?php while ($d = $denied->fetch_assoc()) { ?>
                <div class="appointment-item" style="color: #b30000;">
                    <strong><?= htmlspecialchars($d['description']) ?></strong><br>
                    <?= htmlspecialchars($d['date']) ?> @ <?= htmlspecialchars($d['start_time']) ?><br>
                    <small><b>Location:</b> <?= htmlspecialchars($d['location']) ?></small><br>
                    <button class="delete-btn" onclick="showDeleteModal(<?= $d['id'] ?>)">Delete Request</button>
                </div>
            <?php } ?>
        <?php } else { echo "<p>No denied requests.</p>"; } ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Delete Appointment</h3>
        <p>Are you sure you want to delete this appointment?</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="delete-btn">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
// Service descriptions
const serviceDescriptions = {
  "All Ceramic Veneers with E-Max": "Tooth-colored, durable ceramic veneers made from E-Max material, used to enhance the appearance, shape, and color of front teeth for a natural, aesthetic smile.",
  "All Ceramic Fixed Bridge with Zirconia": "Strong, tooth-colored bridge made from zirconia, used to replace missing teeth while providing a natural appearance and durable long-term function.",
  "Cleaning": "Professional removal of plaque, tartar, and stains, followed by polishing and often fluoride treatment to maintain oral health.",
  "Consultation": "Initial appointment to discuss dental concerns, evaluate oral health, and create a personalized treatment plan. Includes examination and professional recommendations.",
  "Cosmetic Restoration Crown Build-Up": "Procedure that rebuilds and strengthens damaged teeth using aesthetic materials to restore natural shape, function, and appearance before crown placement.",
  "Dental Braces": "Correct problems with crooked teeth, crowding and out of alignment.",
  "Dental Bridges": "Dental restoration to replace one or more missing teeth.",
  "Dental Crowns": "Protect, cover and restore the shape of your broken, weak or worn-down teeth.",
  "Dental Filling": "Procedure to restore a tooth damaged by decay. The decayed portion is removed and filled with composite resin, amalgam, or other suitable materials.",
  "Dental Veneers": "Cosmetic dental treatment to conceal cracks, chips, stains and other imperfections.",
  "Dentures": "Removable replacement for missing teeth and surrounding tissues.",
  "Digital Panoramic X-Ray": "Wide-view dental imaging technique that captures the entire mouth, including teeth, jaws, and surrounding structures, in a single detailed image for comprehensive diagnosis and treatment planning.",
  "Digital Periapical X-Ray & Intra-Oral Camera": "Advanced imaging tools that provide detailed tooth and gum visuals for accurate diagnosis, treatment planning, and improved patient understanding.",
  "Extraction of Mandibular First Molar": "Surgical removal of the lower first molar tooth due to decay, infection, or damage, performed to relieve pain and preserve overall oral health.",
  "Fixed Bridge": "Permanent restoration used to replace one or more missing teeth by joining artificial teeth to adjacent crowns.",
  "Flexible Dentures": "Lightweight and flexible partial dentures that offer improved comfort and aesthetics compared to traditional acrylic dentures.",
  "Fluoride Treatment": "Application of fluoride gel, varnish, or foam to strengthen tooth enamel and prevent cavities.",
  "General Checkup": "Comprehensive oral examination to assess overall dental health, detect cavities, gum disease, and other issues. May include visual inspection and X-rays.",
  "Gingivectomy": "Surgical removal of diseased gum tissue to treat gum disease or improve the aesthetics of the gum line.",
  "Metallic Ortho Braces": "Traditional orthodontic braces made of high-quality stainless steel, used to straighten teeth and correct bite problems.",
  "Odontectomy (Impacted Tooth Removal)": "Surgical extraction of an impacted tooth, often required for teeth that fail to erupt properly (such as wisdom teeth).",
  "Oral Prophylaxis": "Professional cleaning that removes plaque, tartar, and stains from teeth, including areas below the gumline.",
  "Orthodontic Treatment": "Dental procedure that aligns and straightens teeth using braces or clear aligners to improve bite function, oral health, and overall smile aesthetics.",
  "Panoramic X-Ray": "Comprehensive dental imaging that captures the entire mouth, including teeth, jaws, and surrounding structures, in a single image.",
  "Periapical X-Ray": "Detailed X-ray focused on one or a few teeth to detect problems below the gumline or around the tooth root.",
  "Porcelain Fused to Metal Fixed Bridge": "Durable restoration that replaces missing teeth using a metal base covered with tooth-colored porcelain for strength and a natural appearance.",
  "Removable Partial Dentures": "Prosthesis that replaces some missing teeth and can be easily removed for cleaning and maintenance.",
  "Retainers and Other Ortho Appliances": "Devices used after orthodontic treatment to maintain tooth alignment or assist with specific orthodontic corrections.",
  "Root Canal Treatment": "Endodontic procedure to remove infected or damaged pulp tissue, clean and seal the canal, preserving the natural tooth.",
  "Self-Ligating Braces": "Advanced braces system that uses clips instead of elastic bands to hold wires, reducing friction and often shortening treatment time.",
  "Teeth Whitening": "Cosmetic procedure that brightens and whitens teeth using professional-grade bleaching agents to remove stains and discoloration.",
  "Tooth Extraction": "Removal of a tooth that is damaged, decayed, or beyond repair.",
  "Tooth Restoration": "Repair of tooth decay or structural damage using fillings, inlays, or other restorative materials to restore function and aesthetics.",
  "Wisdom / 3rd Molar Extraction": "Removal of third molars (wisdom teeth) that are impacted, misaligned, or causing pain or infection."
};

// Show service description when service is selected
document.getElementById('serviceSelect').addEventListener('change', function() {
    const selectedService = this.value;
    const descriptionBox = document.getElementById('serviceDescription');
    const descriptionText = document.getElementById('descriptionText');
    
    if (selectedService && serviceDescriptions[selectedService]) {
        descriptionText.textContent = serviceDescriptions[selectedService];
        descriptionBox.style.display = 'block';
    } else {
        descriptionBox.style.display = 'none';
    }
});

function showDeleteModal(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').style.display = 'block';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
</script>

</body>
</html>