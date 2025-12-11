<?php
require_once 'config.php';
checkRole(['biro']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pelajar_id = mysqli_real_escape_string($conn, $_POST['pelajar_id']);
    $tarikh_sakit = mysqli_real_escape_string($conn, $_POST['tarikh_sakit']);
    $masa_sakit = mysqli_real_escape_string($conn, $_POST['masa_sakit']);
    $simptom = mysqli_real_escape_string($conn, $_POST['simptom']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    // Handle file upload
    $filename = null;
    $camera_image = null;
    
    // Process file upload if exists
    if (isset($_FILES['dokumen_sokongan']) && $_FILES['dokumen_sokongan']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['dokumen_sokongan']['type'], $allowed_types)) {
            if ($_FILES['dokumen_sokongan']['size'] <= $max_size) {
                $file_ext = pathinfo($_FILES['dokumen_sokongan']['name'], PATHINFO_EXTENSION);
                $filename = 'dokumen_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = 'uploads/dokumen/' . $filename;
                
                if (!is_dir('uploads/dokumen')) {
                    mkdir('uploads/dokumen', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['dokumen_sokongan']['tmp_name'], $upload_path)) {
                    $filename = mysqli_real_escape_string($conn, $filename);
                } else {
                    $error = 'Ralat semasa memuat naik fail.';
                }
            } else {
                $error = 'Fail terlalu besar. Maksimum 5MB dibenarkan.';
            }
        } else {
            $error = 'Hanya format JPEG, PNG, JPG dan PDF dibenarkan.';
        }
    }
    
    // Process camera image if exists
    if (isset($_POST['camera_image']) && !empty($_POST['camera_image'])) {
        $camera_data = $_POST['camera_image'];
        
        // Check if it's base64 data
        if (strpos($camera_data, 'data:image') === 0) {
            list($type, $camera_data) = explode(';', $camera_data);
            list(, $camera_data) = explode(',', $camera_data);
            $camera_data = base64_decode($camera_data);
            
            $camera_filename = 'camera_' . time() . '_' . uniqid() . '.jpg';
            $camera_path = 'uploads/camera/' . $camera_filename;
            
            if (!is_dir('uploads/camera')) {
                mkdir('uploads/camera', 0777, true);
            }
            
            if (file_put_contents($camera_path, $camera_data)) {
                $camera_image = mysqli_real_escape_string($conn, $camera_filename);
            }
        }
    }
    
    // Insert into database if no error
    if (empty($error)) {
        $query = "INSERT INTO permohonan (pelajar_id, biro_id, tarikh_sakit, masa_sakit, simptom, catatan, dokumen_sokongan, gambar_camera, status) 
                  VALUES ('$pelajar_id', '" . $_SESSION['user_id'] . "', '$tarikh_sakit', '$masa_sakit', '$simptom', '$catatan', 
                  " . ($filename ? "'$filename'" : "NULL") . ", 
                  " . ($camera_image ? "'$camera_image'" : "NULL") . ", 
                  'menunggu')";
        
        if (mysqli_query($conn, $query)) {
            $permohonan_id = mysqli_insert_id($conn);
            
            // Simpan data gambar dalam session sementara untuk paparan
            $_SESSION['last_uploaded_files'] = [
                'dokumen' => $filename,
                'camera' => $camera_image
            ];
            
            // Redirect ke halaman kelulusan dengan ID permohonan
            header("Location: kelulusan_permohonan.php?id=" . $permohonan_id);
            exit();
        } else {
            $error = 'Ralat: ' . mysqli_error($conn);
        }
    }
}

// Get success message from URL
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Permohonan berjaya dihantar!';
}

// Get list of pelajar
$pelajar_query = "SELECT * FROM pelajar ORDER BY nama";
$pelajar_result = mysqli_query($conn, $pelajar_query);

$page_title = 'Permohonan Baru';
include 'header.php';
?>

<!-- Camera Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="cameraModalLabel"><i class="fas fa-camera me-2"></i>Ambil Gambar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="camera-container mb-3">
                            <video id="cameraPreview" autoplay playsinline class="img-fluid rounded border"></video>
                            <canvas id="cameraCanvas" class="d-none"></canvas>
                            <!-- Button Ambil Gambar -->
                            <button type="button" class="btn btn-primary btn-lg" id="capturePhotoBtn" 
                                    style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1000;">
                                <i class="fas fa-camera me-2"></i>Ambil Gambar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="capturedImageContainer" class="mb-3">
                            <h6 class="text-muted mb-2">Gambar Diambil:</h6>
                            <img id="capturedImage" src="" class="img-fluid rounded border d-none">
                            <div id="noImageMessage" class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">Tiada gambar diambil</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="retakePhoto" style="display: none;">
                    <i class="fas fa-redo me-1"></i>Ambil Semula
                </button>
                <button type="button" class="btn btn-primary" id="usePhoto" style="display: none;">
                    <i class="fas fa-check me-1"></i>Guna Gambar Ini
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Permohonan Baru</h5>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pelajar_id" class="form-label fw-bold">
                                <i class="fas fa-user-graduate me-2 text-primary"></i>Pelajar <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="pelajar_id" name="pelajar_id" required>
                                <option value="">-- Pilih Pelajar --</option>
                                <?php while ($pelajar = mysqli_fetch_assoc($pelajar_result)): ?>
                                    <option value="<?php echo $pelajar['id']; ?>">
                                        <?php echo htmlspecialchars($pelajar['no_matrik'] . ' - ' . $pelajar['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="tarikh_sakit" class="form-label fw-bold">
                                <i class="fas fa-calendar me-2 text-primary"></i>Tarikh Sakit <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" id="tarikh_sakit" name="tarikh_sakit" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="masa_sakit" class="form-label fw-bold">
                                <i class="fas fa-clock me-2 text-primary"></i>Masa Sakit <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control form-control-lg" id="masa_sakit" name="masa_sakit" 
                                   value="<?php echo date('H:i'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="simptom" class="form-label fw-bold">
                            <i class="fas fa-stethoscope me-2 text-primary"></i>Simptom <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="simptom" name="simptom" rows="5" 
                                  placeholder="Sila nyatakan simptom yang dialami pelajar..." required></textarea>
                        <small class="text-muted">Contoh: Demam, batuk, sakit kepala, dll.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="catatan" class="form-label fw-bold">
                            <i class="fas fa-notes-medical me-2 text-primary"></i>Catatan Tambahan
                        </label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                  placeholder="Catatan tambahan (jika ada)..."></textarea>
                    </div>
                    
                    <!-- File Upload Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-file-upload me-2 text-primary"></i>Dokumen Sokongan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="dokumen_sokongan" class="form-label">
                                            Muat Naik Fail <small class="text-muted">(JPEG, PNG, PDF - Maks: 5MB)</small>
                                        </label>
                                        <input type="file" class="form-control" id="dokumen_sokongan" name="dokumen_sokongan" 
                                               accept=".jpg,.jpeg,.png,.pdf">
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                Contoh: Surat pengesahan doktor, bukti lain yang berkaitan
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Preview for uploaded file -->
                                    <div id="filePreview" class="mt-3 d-none">
                                        <h6 class="text-muted mb-2">Pratonton Fail:</h6>
                                        <div class="border rounded p-2 bg-light">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <span id="fileName"></span>
                                            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="clearFile()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-camera me-2 text-primary"></i>Gambar (Camera)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" id="camera_image" name="camera_image">
                                    
                                    <div class="mb-3 text-center">
                                        <button type="button" class="btn btn-outline-primary btn-lg w-100" id="openCameraBtn">
                                            <i class="fas fa-camera me-2"></i>Buka Kamera
                                        </button>
                                    </div>
                                    
                                    <!-- Camera image preview -->
                                    <div id="cameraPreviewContainer" class="d-none">
                                        <h6 class="text-muted mb-2">Gambar Diambil:</h6>
                                        <div class="position-relative">
                                            <img id="cameraPreviewImage" src="" class="img-fluid rounded border" style="max-height: 200px;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                                    onclick="clearCameraImage()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="noCameraImage" class="text-center p-3 border rounded bg-light">
                                        <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                                        <p class="text-muted small mb-0">Tiada gambar diambil dari kamera</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="dashboard.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Hantar Permohonan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Camera functionality
let cameraStream = null;
let capturedPhoto = null;

// Open camera modal
document.getElementById('openCameraBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
    modal.show();
    
    // Start camera after modal is shown
    setTimeout(startCamera, 500);
});

// Start camera
function startCamera() {
    const video = document.getElementById('cameraPreview');
    
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        })
        .then(function(stream) {
            cameraStream = stream;
            video.srcObject = stream;
        })
        .catch(function(err) {
            console.error("Camera error: ", err);
            alert('Tidak dapat mengakses kamera. Sila pastikan anda memberikan kebenaran kamera.');
        });
    } else {
        alert('Kamera tidak disokong oleh pelayar anda.');
    }
}

// Stop camera
function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
}

// Capture photo
document.getElementById('capturePhotoBtn').addEventListener('click', function() {
    const canvas = document.getElementById('cameraCanvas');
    const video = document.getElementById('cameraPreview');
    const capturedImage = document.getElementById('capturedImage');
    const noImageMessage = document.getElementById('noImageMessage');
    const retakeBtn = document.getElementById('retakePhoto');
    const usePhotoBtn = document.getElementById('usePhoto');
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
        capturedImage.src = capturedPhoto;
        capturedImage.classList.remove('d-none');
        noImageMessage.classList.add('d-none');
        
        // Show retake and use buttons
        retakeBtn.style.display = 'block';
        usePhotoBtn.style.display = 'block';
        
        // Hide capture button temporarily
        this.style.display = 'none';
    } else {
        alert('Kamera belum bersedia. Sila tunggu sebentar.');
    }
});

// Retake photo
document.getElementById('retakePhoto').addEventListener('click', function() {
    const capturedImage = document.getElementById('capturedImage');
    const noImageMessage = document.getElementById('noImageMessage');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const usePhotoBtn = document.getElementById('usePhoto');
    
    capturedImage.src = '';
    capturedImage.classList.add('d-none');
    noImageMessage.classList.remove('d-none');
    capturedPhoto = null;
    
    // Show capture button again
    captureBtn.style.display = 'block';
    
    // Hide retake and use buttons
    this.style.display = 'none';
    usePhotoBtn.style.display = 'none';
});

// Use photo
document.getElementById('usePhoto').addEventListener('click', function() {
    if (capturedPhoto) {
        document.getElementById('camera_image').value = capturedPhoto;
        document.getElementById('cameraPreviewImage').src = capturedPhoto;
        document.getElementById('cameraPreviewContainer').classList.remove('d-none');
        document.getElementById('noCameraImage').classList.add('d-none');
        
        stopCamera();
        bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();
    }
});

// Close camera when modal is closed
document.getElementById('cameraModal').addEventListener('hidden.bs.modal', function() {
    stopCamera();
    const video = document.getElementById('cameraPreview');
    video.srcObject = null;
    
    // Reset modal state
    const capturedImage = document.getElementById('capturedImage');
    const noImageMessage = document.getElementById('noImageMessage');
    const retakeBtn = document.getElementById('retakePhoto');
    const usePhotoBtn = document.getElementById('usePhoto');
    const captureBtn = document.getElementById('capturePhotoBtn');
    
    capturedImage.src = '';
    capturedImage.classList.add('d-none');
    noImageMessage.classList.remove('d-none');
    retakeBtn.style.display = 'none';
    usePhotoBtn.style.display = 'none';
    captureBtn.style.display = 'block';
    capturedPhoto = null;
});

// File upload preview
document.getElementById('dokumen_sokongan').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        
        fileName.textContent = file.name;
        filePreview.classList.remove('d-none');
        
        // Show appropriate icon
        const icon = fileName.previousElementSibling;
        if (file.type === 'application/pdf') {
            icon.className = 'fas fa-file-pdf text-danger me-2';
        } else if (file.type.includes('image')) {
            icon.className = 'fas fa-file-image text-success me-2';
        } else {
            icon.className = 'fas fa-file text-secondary me-2';
        }
    }
});

// Clear file input
function clearFile() {
    document.getElementById('dokumen_sokongan').value = '';
    document.getElementById('filePreview').classList.add('d-none');
}

// Clear camera image
function clearCameraImage() {
    document.getElementById('camera_image').value = '';
    document.getElementById('cameraPreviewContainer').classList.add('d-none');
    document.getElementById('noCameraImage').classList.remove('d-none');
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('dokumen_sokongan');
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        
        // Check file size
        if (file.size > maxSize) {
            e.preventDefault();
            alert('Fail terlalu besar. Maksimum 5MB dibenarkan.');
            return false;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            e.preventDefault();
            alert('Hanya format JPEG, PNG, JPG dan PDF dibenarkan.');
            return false;
        }
    }
    
    // Check if at least one attachment is provided
    const hasFile = fileInput.files.length > 0;
    const hasCameraImage = document.getElementById('camera_image').value !== '';
    
    if (!hasFile && !hasCameraImage) {
        if (!confirm('Tiada dokumen sokongan atau gambar dilampirkan. Adakah anda pasti ingin hantar?')) {
            e.preventDefault();
            return false;
        }
    }
});
</script>

<style>
.camera-container {
    position: relative;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    min-height: 400px;
}

.camera-container video {
    width: 100%;
    height: auto;
    max-height: 400px;
}

#capturedImageContainer {
    max-height: 400px;
    overflow: hidden;
}

#capturedImage {
    width: 100%;
    height: auto;
    max-height: 350px;
    object-fit: contain;
}

#cameraPreviewImage {
    width: 100%;
    height: auto;
    object-fit: cover;
}

@media (max-width: 768px) {
    .camera-container {
        min-height: 250px;
    }
    
    .camera-container video,
    #capturedImage {
        max-height: 250px;
    }
}
</style>

<?php include 'footer.php'; ?>