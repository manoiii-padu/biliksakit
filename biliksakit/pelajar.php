<?php
require_once 'config.php';
checkRole(['biro']);

$success = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Create
        $no_matrik = mysqli_real_escape_string($conn, $_POST['no_matrik']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
        $no_telefon = mysqli_real_escape_string($conn, $_POST['no_telefon']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        
        // Check if no_matrik already exists
        $check_query = "SELECT * FROM pelajar WHERE no_matrik = '$no_matrik'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'No. Matrik sudah wujud!';
        } else {
            $query = "INSERT INTO pelajar (no_matrik, nama, kelas, no_telefon, alamat) 
                      VALUES ('$no_matrik', '$nama', '$kelas', '$no_telefon', '$alamat')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Pelajar berjaya ditambah!';
                header("Location: pelajar.php?success=1");
                exit();
            } else {
                $error = 'Ralat: ' . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['edit'])) {
        // Update
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $no_matrik = mysqli_real_escape_string($conn, $_POST['no_matrik']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
        $no_telefon = mysqli_real_escape_string($conn, $_POST['no_telefon']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        
        // Check if no_matrik already exists (except current)
        $check_query = "SELECT * FROM pelajar WHERE no_matrik = '$no_matrik' AND id != '$id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'No. Matrik sudah wujud!';
        } else {
            $query = "UPDATE pelajar SET no_matrik = '$no_matrik', nama = '$nama', kelas = '$kelas', 
                      no_telefon = '$no_telefon', alamat = '$alamat' WHERE id = '$id'";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Pelajar berjaya dikemaskini!';
                header("Location: pelajar.php?success=1");
                exit();
            } else {
                $error = 'Ralat: ' . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['delete'])) {
        // Delete
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        // Check if pelajar has permohonan
        $check_query = "SELECT * FROM permohonan WHERE pelajar_id = '$id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Pelajar mempunyai permohonan! Tidak boleh dipadam.';
        } else {
            $query = "DELETE FROM pelajar WHERE id = '$id'";
            if (mysqli_query($conn, $query)) {
                $success = 'Pelajar berjaya dipadam!';
                header("Location: pelajar.php?success=1");
                exit();
            } else {
                $error = 'Ralat: ' . mysqli_error($conn);
            }
        }
    }
}

// Get success message from URL
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Operasi berjaya dilakukan!';
}

// Get pelajar for edit
$edit_pelajar = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_query = "SELECT * FROM pelajar WHERE id = '$edit_id'";
    $edit_result = mysqli_query($conn, $edit_query);
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_pelajar = mysqli_fetch_assoc($edit_result);
    }
}

// Get all pelajar
$query = "SELECT * FROM pelajar ORDER BY nama";
$result = mysqli_query($conn, $query);

$page_title = 'Urus Pelajar';
include 'header.php';
?>

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

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Senarai Pelajar</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahPelajarModal">
            <i class="fas fa-user-plus me-2"></i>Tambah Pelajar
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>No. Matrik</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>No. Telefon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['no_matrik']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_telefon']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-info" 
                                            onclick='showDetail(<?php echo json_encode($row); ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary" 
                                            onclick="editPelajar(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['no_matrik'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['nama'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['kelas'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['no_telefon'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['alamat'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="deletePelajar(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                Tiada pelajar dijumpai
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pelajar -->
<div class="modal fade" id="tambahPelajarModal" tabindex="-1" aria-labelledby="tambahPelajarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h5 class="modal-title" id="tambahPelajarModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Tambah Pelajar Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="no_matrik" class="form-label">No. Matrik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="no_matrik" name="no_matrik" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kelas" class="form-label">Kelas</label>
                        <input type="text" class="form-control" id="kelas" name="kelas">
                    </div>
                    
                    <div class="mb-3">
                        <label for="no_telefon" class="form-label">No. Telefon</label>
                        <input type="text" class="form-control" id="no_telefon" name="no_telefon">
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" name="add" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pelajar -->
<div class="modal fade" id="editPelajarModal" tabindex="-1" aria-labelledby="editPelajarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h5 class="modal-title" id="editPelajarModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Pelajar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_no_matrik" class="form-label">No. Matrik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_no_matrik" name="no_matrik" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_kelas" class="form-label">Kelas</label>
                        <input type="text" class="form-control" id="edit_kelas" name="kelas">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_no_telefon" class="form-label">No. Telefon</label>
                        <input type="text" class="form-control" id="edit_no_telefon" name="no_telefon">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" name="edit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Kemaskini
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Show Detail Pelajar -->
<div class="modal fade" id="detailPelajarModal" tabindex="-1" aria-labelledby="detailPelajarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h5 class="modal-title" id="detailPelajarModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Butiran Pelajar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-id-card me-2 text-primary"></i>No. Matrik:</strong>
                        <p id="detail_no_matrik" class="mb-0"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user me-2 text-primary"></i>Nama:</strong>
                        <p id="detail_nama" class="mb-0"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-graduation-cap me-2 text-primary"></i>Kelas:</strong>
                        <p id="detail_kelas" class="mb-0">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-phone me-2 text-primary"></i>No. Telefon:</strong>
                        <p id="detail_telefon" class="mb-0">-</p>
                    </div>
                    <div class="col-12 mb-3">
                        <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>Alamat:</strong>
                        <p id="detail_alamat" class="mb-0">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div class="modal fade" id="deletePelajarModal" tabindex="-1" aria-labelledby="deletePelajarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePelajarModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Padam Pelajar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="delete_id" name="id">
                <div class="modal-body">
                    <p>Adakah anda pasti ingin memadam pelajar <strong id="delete_nama"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak boleh dibatalkan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" name="delete" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Padam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
// Reset form when modal is closed
document.getElementById('tambahPelajarModal').addEventListener('hidden.bs.modal', function () {
    document.querySelector('#tambahPelajarModal form').reset();
});

function editPelajar(id, no_matrik, nama, kelas, no_telefon, alamat) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_no_matrik').value = no_matrik;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_kelas').value = kelas || '';
    document.getElementById('edit_no_telefon').value = no_telefon || '';
    document.getElementById('edit_alamat').value = alamat || '';
    
    var editModal = new bootstrap.Modal(document.getElementById('editPelajarModal'));
    editModal.show();
}

function showDetail(data) {
    document.getElementById('detail_no_matrik').textContent = data.no_matrik || '-';
    document.getElementById('detail_nama').textContent = data.nama || '-';
    document.getElementById('detail_kelas').textContent = data.kelas || '-';
    document.getElementById('detail_telefon').textContent = data.no_telefon || '-';
    document.getElementById('detail_alamat').textContent = data.alamat || '-';
    
    var modal = new bootstrap.Modal(document.getElementById('detailPelajarModal'));
    modal.show();
}

function deletePelajar(id, nama) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_nama').textContent = nama;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deletePelajarModal'));
    deleteModal.show();
}
</script>
