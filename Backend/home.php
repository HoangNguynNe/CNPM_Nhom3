<?php
$servername = "localhost";
$username = "tvppdznq_cnpm";
$password = "65mTe4MXEPsddYx6tgRN";
$dbname = "tvppdznq_cnpm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 8;
$offset = ($page - 1) * $items_per_page;

// Tổng số sản phẩm còn hàng
$sql_total = "SELECT COUNT(*) as total FROM SanPham WHERE SoLuong > 0";
$result_total = $conn->query($sql_total);
$total_rows = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Lấy sản phẩm còn hàng
$sql = "SELECT SanPham.MaSanPham, SanPham.TenSanPham, SanPham.HinhAnh, SanPham.Gia, 
        SanPham.SoLuong, DanhMuc.TenDanhMuc, 
        GiamGia.PhanTramGiam, GiamGia.TenGiamGia,
        (SanPham.Gia - (SanPham.Gia * GiamGia.PhanTramGiam / 100)) AS GiaSauGiam
        FROM SanPham 
        JOIN DanhMuc ON SanPham.MaDanhMuc = DanhMuc.MaDanhMuc
        LEFT JOIN GiamGia ON SanPham.MaGiamGia = GiamGia.MaGiamGia
        WHERE SanPham.SoLuong > 0
        LIMIT $offset, $items_per_page";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Web Bán Hàng</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .product-images img {
            width: 100px;
            height: 100px;
            margin: 5px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <header>
        <h1>Danh Sách Sản Phẩm</h1>
    </header>

    <section class="products">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo $row['HinhAnh']; ?>" alt="<?php echo $row['TenSanPham']; ?>" class="product-image">
                    <h2 class="product-name"><?php echo $row['TenSanPham']; ?></h2>
                    <p class="product-category"><?php echo $row['TenDanhMuc']; ?></p>
                    <p class="product-price">
                        <?php echo number_format($row['GiaSauGiam'], 0, ',', '.'); ?> VND
                    </p>
                    <p class="product-original-price">
                        Giá gốc: <?php echo number_format($row['Gia'], 0, ',', '.'); ?> VND
                    </p>
                    <p class="product-stock">Còn lại: <?php echo $row['SoLuong']; ?> sản phẩm</p>
                    <div class="product-images">
                        <h3>Ảnh chi tiết:</h3>
                        <?php
                        $sql_images = "SELECT DuongDan FROM HinhAnh WHERE MaSanPham = '" . $row['MaSanPham'] . "'";
                        $result_images = $conn->query($sql_images);
                        if ($result_images->num_rows > 0) {
                            while ($image = $result_images->fetch_assoc()) {
                                echo '<img src="' . $image['DuongDan'] . '" alt="Chi tiết">';
                            }
                        } else {
                            echo '<p>Không có ảnh chi tiết.</p>';
                        }
                        ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Không có sản phẩm nào còn hàng.</p>
        <?php endif; ?>
    </section>

    <!-- Phân trang -->
    <section class="pagination">
        <ul>
            <?php if ($page > 1): ?>
                <li><a href="?page=1">Đầu</a></li>
                <li><a href="?page=<?php echo $page - 1; ?>">Trước</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <li><span class="current"><?php echo $i; ?></span></li>
                <?php else: ?>
                    <li><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li><a href="?page=<?php echo $page + 1; ?>">Tiếp</a></li>
                <li><a href="?page=<?php echo $total_pages; ?>">Cuối</a></li>
            <?php endif; ?>
        </ul>
    </section>
</body>
</html>

<?php $conn->close(); ?>
