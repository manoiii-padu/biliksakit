<?php
require_once 'config.php';
checkRole(['biro']);

// Get all permohonan by this biro
$query = "SELECT p.*, pl.nama as nama_pelajar, pl.no_matrik, pl.kelas, pl.no_telefon, pl.alamat,
                 b.no_bilik, b.nama_bilik 
          FROM permohonan p 
          JOIN pelajar pl ON p.pelajar_id = pl.id 
          LEFT JOIN bilik b ON p.bilik_id = b.id
          WHERE p.biro_id = " . $_SESSION['user_id'] . " 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

$page_title = 'Senarai Permohonan';
include 'header.php';
?>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Senarai Permohonan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>No. Matrik</th>
                        <th>Nama Pelajar</th>
                        <th>Tarikh & Masa</th>
                        <th>Simptom</th>
                        <th>Bilik</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)): 
                        $status_class = 'badge-' . $row['status'];
                        $status_text = ucfirst($row['status']);
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['no_matrik']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_pelajar']); ?></td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($row['tarikh_sakit'])); ?><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($row['masa_sakit'])); ?></small>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars(substr($row['simptom'], 0, 50)); ?>...</small>
                            </td>
                            <td>
                                <?php if ($row['no_bilik']): ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-door-open me-1"></i><?php echo htmlspecialchars($row['no_bilik']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info btn-detail" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-no-matrik="<?php echo htmlspecialchars($row['no_matrik'], ENT_QUOTES); ?>"
                                        data-nama="<?php echo htmlspecialchars($row['nama_pelajar'], ENT_QUOTES); ?>"
                                        data-kelas="<?php echo htmlspecialchars($row['kelas'] ?? '', ENT_QUOTES); ?>"
                                        data-telefon="<?php echo htmlspecialchars($row['no_telefon'] ?? '', ENT_QUOTES); ?>"
                                        data-alamat="<?php echo htmlspecialchars($row['alamat'] ?? '', ENT_QUOTES); ?>"
                                        data-tarikh-sakit="<?php echo $row['tarikh_sakit']; ?>"
                                        data-masa-sakit="<?php echo $row['masa_sakit']; ?>"
                                        data-simptom="<?php echo htmlspecialchars($row['simptom'], ENT_QUOTES); ?>"
                                        data-catatan="<?php echo htmlspecialchars($row['catatan'] ?? '', ENT_QUOTES); ?>"
                                        data-no-bilik="<?php echo htmlspecialchars($row['no_bilik'] ?? '', ENT_QUOTES); ?>"
                                        data-nama-bilik="<?php echo htmlspecialchars($row['nama_bilik'] ?? '', ENT_QUOTES); ?>"
                                        data-status="<?php echo $row['status']; ?>"
                                        data-created="<?php echo $row['created_at']; ?>"
                                        data-approved="<?php echo $row['tarikh_diluluskan'] ?? ''; ?>">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                Tiada permohonan dijumpai
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Permohonan -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Butiran Permohonan
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
                        <strong><i class="fas fa-user me-2 text-primary"></i>Nama Pelajar:</strong>
                        <p id="detail_nama" class="mb-0"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-graduation-cap me-2 text-primary"></i>Kelas:</strong>
                        <p id="detail_kelas" class="mb-0"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-phone me-2 text-primary"></i>No. Telefon:</strong>
                        <p id="detail_telefon" class="mb-0"></p>
                    </div>
                    <div class="col-12 mb-3">
                        <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>Alamat:</strong>
                        <p id="detail_alamat" class="mb-0"></p>
                    </div>
                    <hr>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-calendar me-2 text-primary"></i>Tarikh Sakit:</strong>
                        <p id="detail_tarikh" class="mb-0"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-clock me-2 text-primary"></i>Masa Sakit:</strong>
                        <p id="detail_masa" class="mb-0"></p>
                    </div>
                    <div class="col-12 mb-3">
                        <strong><i class="fas fa-stethoscope me-2 text-primary"></i>Simptom:</strong>
                        <p id="detail_simptom" class="mb-0"></p>
                    </div>
                    <div class="col-12 mb-3">
                        <strong><i class="fas fa-notes-medical me-2 text-primary"></i>Catatan:</strong>
                        <p id="detail_catatan" class="mb-0 text-muted">-</p>
                    </div>
                    <hr>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-door-open me-2 text-primary"></i>Bilik:</strong>
                        <p id="detail_bilik" class="mb-0">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-info-circle me-2 text-primary"></i>Status:</strong>
                        <p id="detail_status" class="mb-0"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-calendar-plus me-2 text-primary"></i>Tarikh Dicipta:</strong>
                        <p id="detail_created" class="mb-0"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-check-circle me-2 text-primary"></i>Tarikh Diluluskan:</strong>
                        <p id="detail_approved" class="mb-0">-</p>
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

<?php include 'footer.php'; ?>

<script>
// Format tanggal
function formatDate(dateString) {
    if (!dateString || dateString === '') return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return day + '/' + month + '/' + year;
}

function formatDateTime(dateString) {
    if (!dateString || dateString === '') return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
}

// Event listener untuk tombol detail
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-detail').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const data = {
                no_matrik: this.getAttribute('data-no-matrik') || '-',
                nama_pelajar: this.getAttribute('data-nama') || '-',
                kelas: this.getAttribute('data-kelas') || '-',
                no_telefon: this.getAttribute('data-telefon') || '-',
                alamat: this.getAttribute('data-alamat') || '-',
                tarikh_sakit: this.getAttribute('data-tarikh-sakit') || '',
                masa_sakit: this.getAttribute('data-masa-sakit') || '-',
                simptom: this.getAttribute('data-simptom') || '-',
                catatan: this.getAttribute('data-catatan') || '',
                no_bilik: this.getAttribute('data-no-bilik') || '',
                nama_bilik: this.getAttribute('data-nama-bilik') || '',
                status: this.getAttribute('data-status') || '',
                created_at: this.getAttribute('data-created') || '',
                tarikh_diluluskan: this.getAttribute('data-approved') || ''
            };
            
            showDetail(data);
        });
    });
});

function showDetail(data) {
    // Set data
    document.getElementById('detail_no_matrik').textContent = data.no_matrik || '-';
    document.getElementById('detail_nama').textContent = data.nama_pelajar || '-';
    document.getElementById('detail_kelas').textContent = data.kelas || '-';
    document.getElementById('detail_telefon').textContent = data.no_telefon || '-';
    document.getElementById('detail_alamat').textContent = data.alamat || '-';
    document.getElementById('detail_tarikh').textContent = formatDate(data.tarikh_sakit);
    document.getElementById('detail_masa').textContent = data.masa_sakit || '-';
    document.getElementById('detail_simptom').textContent = data.simptom || '-';
    
    // Catatan
    const catatanEl = document.getElementById('detail_catatan');
    if (data.catatan && data.catatan.trim() !== '') {
        catatanEl.textContent = data.catatan;
        catatanEl.classList.remove('text-muted');
    } else {
        catatanEl.textContent = '-';
        catatanEl.classList.add('text-muted');
    }
    
    // Bilik
    if (data.no_bilik && data.no_bilik !== '') {
        const bilikText = data.nama_bilik && data.nama_bilik !== '' 
            ? data.no_bilik + ' (' + data.nama_bilik + ')' 
            : data.no_bilik;
        document.getElementById('detail_bilik').textContent = bilikText;
    } else {
        document.getElementById('detail_bilik').textContent = '-';
    }
    
    // Status
    const statusBadge = {
        'menunggu': '<span class="badge bg-warning text-dark">Menunggu</span>',
        'diluluskan': '<span class="badge bg-success">Diluluskan</span>',
        'ditolak': '<span class="badge bg-danger">Ditolak</span>'
    };
    document.getElementById('detail_status').innerHTML = statusBadge[data.status] || '-';
    
    // Tarikh
    document.getElementById('detail_created').textContent = formatDateTime(data.created_at);
    
    // Tarikh diluluskan
    const approvedEl = document.getElementById('detail_approved');
    if (approvedEl) {
        approvedEl.textContent = formatDateTime(data.tarikh_diluluskan);
    }
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
}
</script>
