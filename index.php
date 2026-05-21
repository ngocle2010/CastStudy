<?php 
require_once 'includes/db_config.php'; 
require_once 'includes/header.php'; 
?>

<section class="hero-section">
    <div class="container text-center">
        <h1 class="text-white fw-bold display-4">
            Tìm phòng trọ lý tưởng <br>
            <span class="text-warning">Quanh ĐH Vinh</span>
        </h1>
    </div>
</section>

<div class="container mb-5">
    <div class="card search-card shadow-lg">
        <form action="search.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold text-secondary small">
                    <i class="fa-solid fa-location-dot me-1"></i> Khu vực
                </label>
                <select name="district" class="form-select border-0 bg-light p-3">
                    <option value="">Tất cả địa điểm...</option>
                    <?php
                    $res_dist = mysqli_query($conn, "SELECT * FROM districts");
                    while($row_dist = mysqli_fetch_assoc($res_dist)) {
                        echo "<option value='".$row_dist['ID']."'>".htmlspecialchars($row_dist['Name'])."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold text-secondary small">
                    <i class="fa-solid fa-house-flag me-1"></i> Loại phòng
                </label>
                <select name="category" class="form-select border-0 bg-light p-3">
                    <option value="">Tất cả loại phòng...</option>
                    <?php
                    $res_cat = mysqli_query($conn, "SELECT * FROM categories");
                    while($row_cat = mysqli_fetch_assoc($res_cat)) {
                        echo "<option value='".$row_cat['ID']."'>".htmlspecialchars($row_cat['Name'])."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold text-secondary small">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> Khoảng giá
                </label>
                <select name="price" class="form-select border-0 bg-light p-3">
                    <option value="">Tất cả mức giá</option>
                    <option value="1">Dưới 1.5 triệu</option>
                    <option value="2">1.5 - 3 triệu</option>
                    <option value="3">Trên 3 triệu</option>
                </select>
            </div>
<div class="col-md-3">
    <label class="form-label fw-bold text-secondary small">
        <i class="fa-solid fa-wifi me-1"></i> Tiện ích
    </label>
 
    <select name="utility" class="form-select border-0 bg-light p-3">
        <option value="">Tất cả tiện ích</option>
        <option value="wifi">Wifi</option>
        <option value="máy lạnh">Máy lạnh</option>
        <option value="tủ lạnh">Tủ lạnh</option>
        <option value="máy giặt">Máy giặt</option>
        <option value="nóng lạnh">Nóng lạnh</option>
    </select>
</div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
                </button>
            </div>

            <div class="col-12 mt-3">
                <a href="search.php?near_vinh=1" class="btn btn-success rounded-pill px-4 py-2 shadow-sm">
                    <i class="fa-solid fa-school me-2"></i> Tìm phòng gần Đại học Vinh
                </a>
            </div>
        </form>
    </div>

    <div class="row mt-5 pt-4">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0 border-start border-5 border-primary ps-3">
                    Phòng trọ mới nhất
                </h3>
                <a href="all_room.php" class="text-primary text-decoration-none fw-semibold">
                    Xem thêm <i class="fa-solid fa-arrow-right-long ms-1"></i>
                </a>
            </div>
            
            <div class="row g-4">
                <?php
                $sql_new = "SELECT motel.*, categories.Name as category_name 
                            FROM motel 
                            JOIN categories ON motel.category_id = categories.ID 
                            WHERE motel.approve = 1 
                            ORDER BY created_at DESC LIMIT 6";
                $res_new = mysqli_query($conn, $sql_new);

                if(mysqli_num_rows($res_new) > 0) {
                    while($room = mysqli_fetch_assoc($res_new)) {

                        $raw_imgs = explode(',', $room['images']); 
                        $room_imgs = array_filter(array_map('trim', $raw_imgs));
                        $first_img = !empty($room_imgs) ? reset($room_imgs) : 'default-room.jpg';
                        $img_data = json_encode(array_values($room_imgs));
                ?>
        
                <div class="col-md-6">
                    <div class="card room-card shadow-sm h-100">
                        <div class="room-img-container position-relative" style="height: 200px; overflow: hidden;">
                            <img 
                                src="uploads/rooms/<?php echo htmlspecialchars($first_img); ?>" 
                                class="w-100 h-100 object-fit-cover room-cycle-img" 
                                data-images='<?php echo htmlspecialchars($img_data, ENT_QUOTES); ?>' 
                                data-original="uploads/rooms/<?php echo htmlspecialchars($first_img); ?>"
                                alt="Phòng trọ"
                            >
            
                            <span class="badge-custom shadow-sm position-absolute top-0 start-0 m-3 bg-white px-2 py-1 rounded small">
                                <i class="fa-solid fa-tag text-primary me-1"></i>
                                <?php echo htmlspecialchars($room['category_name']); ?>
                            </span>

                            <?php if ((int)($room['is_rented'] ?? 0) === 1): ?>
                                <span class="position-absolute bottom-0 start-0 m-3 bg-secondary text-white px-2 py-1 rounded small shadow-sm">Đã thuê</span>
                            <?php else: ?>
                                <span class="position-absolute bottom-0 start-0 m-3 bg-success text-white px-2 py-1 rounded small shadow-sm">Còn trống</span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-2 text-truncate">
                                <?php echo htmlspecialchars($room['title']); ?>
                            </h5>

                            <p class="text-muted small mb-3 text-truncate">
                                <i class="fa-solid fa-location-dot me-1"></i> 
                                <?php echo htmlspecialchars($room['address']); ?>
                            </p>

                            <div class="d-flex gap-2 mb-4">
                                <span class="badge bg-blue-subtle text-primary border border-primary-subtle rounded-pill">
                                    <?php echo htmlspecialchars($room['area']); ?>m²
                                </span>
                                <span class="badge bg-green-subtle text-success border border-success-subtle rounded-pill">
                                    Sạch sẽ
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="price-tag fw-bold text-danger fs-5">
                                    <?php echo number_format($room['price'], 0, ',', '.'); ?>đ
                                </span>

                                <a href="detail.php?id=<?php echo $room['ID']; ?>" class="btn btn-dark rounded-pill px-4 shadow-sm">
                                    Xem ngay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                    }
                }
                ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sidebar-card shadow-sm p-4 bg-white mb-4 border-0 rounded-4">
                <h5 class="fw-bold mb-4">
                    <i class="fa-solid fa-list-ul text-primary me-2"></i>Danh mục loại phòng
                </h5>

                <div class="list-group list-group-flush">
                    <?php
                    $res_cat_list = mysqli_query($conn, "SELECT * FROM categories");
                    while($cat_item = mysqli_fetch_assoc($res_cat_list)) {
                    ?>
                    <a href="search.php?category=<?php echo $cat_item['ID']; ?>" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 px-0">
                        <span>
                            <i class="fa-solid fa-chevron-right small me-2 text-muted"></i> 
                            <?php echo htmlspecialchars($cat_item['Name']); ?>
                        </span>
                        <span class="badge bg-light text-muted rounded-pill">Xem</span>
                    </a>
                    <?php } ?>
                </div>
            </div>

            <div class="card sidebar-card shadow-sm p-4 bg-white mb-4 border-0 rounded-4">
                <h5 class="fw-bold mb-4">
                    <i class="fa-solid fa-fire text-danger me-2"></i>xem nhiều nhất
                </h5>

                <div class="list-group list-group-flush">
                    <?php
                    $sql_hot = "SELECT * FROM motel WHERE approve = 1 ORDER BY count_view DESC LIMIT 5";
                    $res_hot = mysqli_query($conn, $sql_hot);

                    while($hot = mysqli_fetch_assoc($res_hot)) {
                    ?>
                    <a href="detail.php?id=<?php echo $hot['ID']; ?>" 
                       class="list-group-item sidebar-item p-3 border-0 bg-transparent">
                        <div class="fw-bold text-dark text-truncate">
                            <?php echo htmlspecialchars($hot['title']); ?>
                        </div>
                        <div class="text-danger fw-bold small">
                            <?php echo number_format($hot['price'], 0, ',', '.'); ?>đ / tháng
                        </div>
                    </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
