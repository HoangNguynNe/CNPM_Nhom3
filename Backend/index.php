<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$servername = "localhost";
$username = "tvppdznq_cnpm"; 
$password = "65mTe4MXEPsddYx6tgRN"; 
$dbname = "tvppdznq_cnpm"; 

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Thiết lập mã hóa UTF-8 cho kết nối MySQL
$conn->set_charset("utf8mb4");

// Lấy trang hiện tại từ tham số GET, mặc định là 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 8; // Số sản phẩm hiển thị mỗi trang
$offset = ($page - 1) * $items_per_page; // Vị trí bắt đầu của dữ liệu

// Lấy giá trị tìm kiếm từ tham số GET
$tendanhmuc = isset($_GET['tendanhmuc']) ? $conn->real_escape_string($_GET['tendanhmuc']) : '%';  // Lọc tất cả nếu không có giá trị
$tensanpham = isset($_GET['tensanpham']) ? $conn->real_escape_string($_GET['tensanpham']) : '%';  // Lọc tất cả nếu không có giá trị


$total_rows_sql = "SELECT COUNT(*) as total_rows FROM SanPham 
                    JOIN DanhMuc ON SanPham.MaDanhMuc = DanhMuc.MaDanhMuc 
                    WHERE DanhMuc.TenDanhMuc LIKE '%$tendanhmuc%' 
                    AND SanPham.TenSanPham LIKE '%$tensanpham%'";

$total_rows_result = $conn->query($total_rows_sql);
$total_rows = 0;

if ($total_rows_result) {
    $total_rows = $total_rows_result->fetch_assoc()['total_rows'];
}
$keywords = explode(' ', $tensanpham);

$search_query = implode('%', $keywords);

$sql = "SELECT SanPham.MaSanPham, SanPham.TenSanPham, SanPham.Gia, SanPham.HinhAnh, 
        SanPham.SoLuong, SanPham.MaDanhMuc, SanPham.MoTa, SanPham.DaBan, 
        SanPham.SoLuongConLai, DanhMuc.TenDanhMuc, GiamGia.PhanTramGiam, GiamGia.TenGiamGia,
        (SanPham.Gia - (SanPham.Gia * GiamGia.PhanTramGiam / 100)) AS GiaSauGiam
        FROM SanPham 
        JOIN DanhMuc ON SanPham.MaDanhMuc = DanhMuc.MaDanhMuc
        LEFT JOIN GiamGia ON SanPham.MaGiamGia = GiamGia.MaGiamGia
        WHERE DanhMuc.TenDanhMuc LIKE '%$tendanhmuc%' 
        AND SanPham.TenSanPham LIKE '%$search_query%'
        LIMIT $items_per_page OFFSET $offset";

$result = $conn->query($sql);

// Mảng để chứa kết quả
$products = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Truy vấn thêm các chi tiết hình ảnh, size, màu sắc của sản phẩm
        $product = array(
            'MaSanPham' => $row['MaSanPham'],
            'TenSanPham' => $row['TenSanPham'],
            'Gia' => $row['Gia'],
            'HinhAnh' => $row['HinhAnh'],
            'SoLuong' => $row['SoLuong'],
            'MaDanhMuc' => $row['MaDanhMuc'],
            'MoTa' => $row['MoTa'],
            'DaBan' => $row['DaBan'],
            'SoLuongConLai' => $row['SoLuongConLai'],
            'TenDanhMuc' => $row['TenDanhMuc'],  // Thêm tên danh mục
            'PhanTramGiam' => $row['PhanTramGiam'],  // Thêm phần trăm giảm giá
            'GiaSauGiam' => $row['GiaSauGiam'],  // Thêm giá sau khi giảm
            'TenGiamGia' => $row['TenGiamGia']  // Thêm tên mã giảm giá
        );

        // Truy vấn hình ảnh của sản phẩm
        $sql_images = "SELECT MaHinhAnh, DuongDan FROM HinhAnh WHERE MaSanPham = " . $row['MaSanPham'];
        $result_images = $conn->query($sql_images);

        $images = array();
        while ($image = $result_images->fetch_assoc()) {
            $images[] = array(
                'MaHinhAnh' => $image['MaHinhAnh'],
                'DuongDan' => $image['DuongDan']
            );
        }
        $product['HinhAnh'] = [
            'DuongDan' => $images[0]['DuongDan'] ?? '',
            'ChiTiet' => $images
        ];

        // Truy vấn size của sản phẩm
        $sql_size = "SELECT MaSize, Size FROM Size WHERE MaSanPham = " . $row['MaSanPham'];
        $result_size = $conn->query($sql_size);

        $sizes = array();
        while ($size = $result_size->fetch_assoc()) {
            $sizes[] = array(
                'MaSize' => $size['MaSize'],
                'Size' => $size['Size']
            );
        }
        $product['Size'] = $sizes;

        // Truy vấn màu sắc của sản phẩm
        $sql_color = "SELECT MaMauSac, MauSac FROM MauSac WHERE MaSanPham = " . $row['MaSanPham'];
        $result_color = $conn->query($sql_color);

        $colors = array();
        while ($color = $result_color->fetch_assoc()) {
        $colors[] = array(
        'MaMauSac' => $color['MaMauSac'],  
        'MauSac' => $color['MauSac']       
    );
}
    $product['MauSac'] = $colors;

        // Thêm sản phẩm vào mảng
        $products[] = $product;
    }
} else {
    $products = [];
}

// Tính tổng số trang
$total_pages = ceil($total_rows / $items_per_page);

// Kết quả trả về
$response = array(
    'products' => $products,
    'current_page' => $page,
    'total_rows' => $total_rows,
    'total_pages' => $total_pages
);

// Trả về JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);

// Đóng kết nối
$conn->close();
?>
