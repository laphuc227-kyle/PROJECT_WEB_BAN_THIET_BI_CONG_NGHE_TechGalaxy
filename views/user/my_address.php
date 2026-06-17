<?php
// File: views/user/my_address.php
if (empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
$pageTitle = 'Sổ địa chỉ nhận hàng - TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
$currentPage = 'addresses';
?>
<div class="container-xl py-5">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-12 col-md-4 col-lg-3">
      <?php require_once __DIR__ . '/../../includes/account_sidebar.php'; ?>
    </div>
    <!-- Main Content -->
    <div class="col-12 col-md-8 col-lg-9">
      <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius)">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="fw-bold mb-1 text-dark">Địa chỉ nhận hàng</h4>
            <p class="text-muted small mb-0">Quản lý các địa chỉ giao hàng của bạn để thanh toán nhanh hơn</p>
          </div>
          <button type="button" class="btn btn-primary btn-sm px-3 py-2 fw-semibold" 
                  data-bs-toggle="modal" data-bs-target="#addAddressModal" style="border-radius: var(--radius)">
            <i class="fa-solid fa-plus me-1"></i> Thêm địa chỉ mới
          </button>
        </div>
        <?php if (empty($addresses)): ?>
          <div class="text-center py-5 bg-light rounded-3" style="border-radius: var(--radius)">
            <i class="fa-regular fa-address-book text-muted display-4 mb-3"></i>
            <h5 class="fw-semibold text-secondary">Chưa có địa chỉ nào</h5>
            <p class="text-muted small">Hãy thêm địa chỉ giao hàng đầu tiên của bạn để mua hàng.</p>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($addresses as $addr): ?>
              <div class="col-12 col-lg-6">
                <div class="card border-light h-100 p-3 shadow-xs position-relative <?= $addr['is_default'] ? 'border-primary bg-light-subtle' : '' ?>" 
                     style="border-radius: var(--radius); border-width: 1.5px;">
                  
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <span class="fw-bold text-dark me-2"><?= htmlspecialchars($addr['name']) ?></span>
                      <?php if ($addr['is_default']): ?>
                        <span class="badge bg-primary-subtle text-primary small px-2 py-1" style="border-radius: 4px;">Mặc định</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="text-muted small mb-3">
                    <p class="mb-1"><i class="fa-solid fa-phone me-2 text-secondary"></i><?= htmlspecialchars($addr['phone']) ?></p>
                    <p class="mb-0"><i class="fa-solid fa-location-dot me-2 text-secondary"></i>
                      <?= htmlspecialchars($addr['detail']) ?>, 
                      <?= htmlspecialchars($addr['ward']) ?>, 
                      <?= htmlspecialchars($addr['district']) ?>, 
                      <?= htmlspecialchars($addr['province']) ?>
                    </p>
                  </div>
                  <div class="d-flex gap-2 border-top border-light pt-2 mt-auto">
                    <!-- Edit Button -->
                    <button type="button" class="btn btn-link text-primary text-decoration-none small p-0 fw-medium edit-address-btn"
                            data-bs-toggle="modal" data-bs-target="#editAddressModal"
                            data-id="<?= $addr['id'] ?>"
                            data-name="<?= htmlspecialchars($addr['name']) ?>"
                            data-phone="<?= htmlspecialchars($addr['phone']) ?>"
                            data-province="<?= htmlspecialchars($addr['province']) ?>"
                            data-district="<?= htmlspecialchars($addr['district']) ?>"
                            data-ward="<?= htmlspecialchars($addr['ward']) ?>"
                            data-detail="<?= htmlspecialchars($addr['detail']) ?>"
                            data-default="<?= $addr['is_default'] ?>">
                      <i class="fa-regular fa-pen-to-square me-1"></i>Sửa
                    </button>
                    <!-- Delete Form -->
                    <form action="<?= BASE_URL ?>/account/addresses/delete" method="POST" class="d-inline ms-2" onsubmit="event.preventDefault(); confirmDeleteAddress(this)">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                      <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                      <button type="submit" class="btn btn-link text-danger text-decoration-none small p-0 fw-medium">
                        <i class="fa-regular fa-trash-can me-1"></i>Xóa
                      </button>
                    </form>
                    <!-- Default Form -->
                    <?php if (!$addr['is_default']): ?>
                      <form action="<?= BASE_URL ?>/account/addresses/default" method="POST" class="d-inline ms-auto">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                        <button type="submit" class="btn btn-outline-secondary btn-xs py-1 px-2 fw-semibold" style="font-size: 11px; border-radius: 6px;">
                          Đặt mặc định
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- Modal Thêm địa chỉ mới -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow" style="border-radius: var(--radius)">
      <div class="modal-header border-bottom border-light">
        <h5 class="modal-title fw-bold" id="addAddressModalLabel">Thêm địa chỉ giao hàng mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL ?>/account/addresses/create" method="POST">
        <div class="modal-body p-4">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          
          <div class="mb-3">
            <label for="addName" class="form-label small fw-medium text-secondary">Họ và tên người nhận <span class="text-danger">*</span></label>
            <input type="text" class="form-control border-light" id="addName" name="name" required style="border-radius: 8px;">
          </div>
          <div class="mb-3">
            <label for="addPhone" class="form-label small fw-medium text-secondary">Số điện thoại <span class="text-danger">*</span></label>
            <input type="tel" class="form-control border-light" id="addPhone" name="phone" required style="border-radius: 8px;">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-4">
              <label for="addProvince" class="form-label small fw-medium text-secondary">Tỉnh / Thành <span class="text-danger">*</span></label>
              <input type="text" class="form-control border-light" id="addProvince" name="province" required style="border-radius: 8px;">
            </div>
            <div class="col-4">
              <label for="addDistrict" class="form-label small fw-medium text-secondary">Quận / Huyện <span class="text-danger">*</span></label>
              <input type="text" class="form-control border-light" id="addDistrict" name="district" required style="border-radius: 8px;">
            </div>
            <div class="col-4">
              <label for="addWard" class="form-label small fw-medium text-secondary">Phường / Xã <span class="text-danger">*</span></label>
              <input type="text" class="form-control border-light" id="addWard" name="ward" required style="border-radius: 8px;">
            </div>
          </div>
          <div class="mb-3">
            <label for="addDetail" class="form-label small fw-medium text-secondary">Địa chỉ chi tiết (Số nhà, Tên đường) <span class="text-danger">*</span></label>
            <input type="text" class="form-control border-light" id="addDetail" name="detail" required style="border-radius: 8px;">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="addDefault" name="is_default" value="1">
            <label class="form-check-label small" for="addDefault">Đặt địa chỉ này làm mặc định</label>
          </div>
        </div>
        <div class="modal-footer border-top border-light">
          <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px;">Hủy</button>
          <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px;">Lưu</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Modal Sửa địa chỉ -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow" style="border-radius: var(--radius)">
      <div class="modal-header border-bottom border-light">
        <h5 class="modal-title fw-bold" id="editAddressModalLabel">Chỉnh sửa địa chỉ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL ?>/account/addresses/update" method="POST">
        <div class="modal-body p-4">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="address_id" id="editAddressId">
          <div class="mb-3">
            <label for="editName" class="form-label small fw-medium text-secondary">Họ và tên người nhận <span class="text-danger">*</span></label>
            <input type="text" class="form-control border-light" id="editName" name="name" required style="border-radius: 8px;">
          </div>
          <div class="mb-3">
            <label for="editPhone" class="form-label small fw-medium text-secondary">Số điện thoại <span class="text-danger">*</span></label>
            <input type="tel" class="form-control border-light" id="editPhone" name="phone" required style="border-radius: 8px;">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-4">
              <label for="editProvince" class="form-label small fw-medium text-secondary">Tỉnh / Thành <span class="text-danger">*</span></label>
              <input type="text" class="form-control border-light" id="editProvince" name="province" required style="border-radius: 8px;">
            </div>
            <div class="col-4">
              <label for="editDistrict" class="form-label small fw-medium text-secondary">Quận / Huyện <span class="text-danger">*</span></label>
              <input type="text" class="form-control border-light" id="editDistrict" name="district" required style="border-radius: 8px;">
            </div>
            <div class="col-4">
              <label for="editWard" class="form-label small fw-medium text-secondary">Phường / Xã <span class="text-danger">*</span></label>
              <input type="text" class="form-control border-light" id="editWard" name="ward" required style="border-radius: 8px;">
            </div>
          </div>
          <div class="mb-3">
            <label for="editDetail" class="form-label small fw-medium text-secondary">Địa chỉ chi tiết (Số nhà, Tên đường) <span class="text-danger">*</span></label>
            <input type="text" class="form-control border-light" id="editDetail" name="detail" required style="border-radius: 8px;">
          </div>
          <div class="form-check" id="editDefaultContainer">
            <input class="form-check-input" type="checkbox" id="editDefault" name="is_default" value="1">
            <label class="form-check-label small" for="editDefault">Đặt địa chỉ này làm mặc định</label>
          </div>
        </div>
        <div class="modal-footer border-top border-light">
          <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px;">Hủy</button>
          <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px;">Cập nhật</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php 
// Inject script dynamic to handle modal binding
$extraJS = '
<script>
document.addEventListener("DOMContentLoaded", function() {
  // Điền dữ liệu vào Edit Address Modal khi bấm nút Sửa
  const editBtns = document.querySelectorAll(".edit-address-btn");
  editBtns.forEach(btn => {
    btn.addEventListener("click", function() {
      document.getElementById("editAddressId").value = this.dataset.id;
      document.getElementById("editName").value = this.dataset.name;
      document.getElementById("editPhone").value = this.dataset.phone;
      document.getElementById("editProvince").value = this.dataset.province;
      document.getElementById("editDistrict").value = this.dataset.district;
      document.getElementById("editWard").value = this.dataset.ward;
      document.getElementById("editDetail").value = this.dataset.detail;
      
      const isDefault = parseInt(this.dataset.default) === 1;
      const checkbox = document.getElementById("editDefault");
      const container = document.getElementById("editDefaultContainer");
      
      if (isDefault) {
        checkbox.checked = true;
        container.style.display = "none"; // Nếu đang là mặc định sẵn, không cho tắt
      } else {
        checkbox.checked = false;
        container.style.display = "block";
      }
    });
  });
});
async function confirmDeleteAddress(form) {
  if (await confirmDelete("Xóa địa chỉ?", "Bạn có chắc chắn muốn xóa địa chỉ này khỏi danh sách nhận hàng?")) {
    form.submit();
  }
}
</script>
';
require_once __DIR__ . '/../../includes/footer.php'; 
?>