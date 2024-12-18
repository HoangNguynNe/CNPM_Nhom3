<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$servername = "localhost";
$username = "tvppdznq_cnpm"; // Tên người dùng MySQL
$password = "65mTe4MXEPsddYx6tgRN"; // Mật khẩu MySQL
$dbname = "tvppdznq_cnpm"; // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Thiết lập mã hóa UTF-8 cho kết nối MySQL
$conn->set_charset("utf8mb4");

// Lấy dữ liệu từ bảng "GiamGia" và bảng "SanPham"
$sql = "SELECT GiamGia.MaGiamGia, GiamGia.TenGiamGia, GiamGia.PhanTramGiam, GiamGia.NgayBatDau, GiamGia.NgayKetThuc, 
        SanPham.MaSanPham, SanPham.TenSanPham, SanPham.HinhAnh, SanPham.Gia
        FROM GiamGia 
        JOIN SanPham ON GiamGia.MaSanPham = SanPham.MaSanPham";
$result = $conn->query($sql);

// Mảng để chứa kết quả
$discounts = array();

// Kiểm tra nếu có kết quả
if ($result->num_rows > 0) {
    // Duyệt qua tất cả các chương trình giảm giá
    while ($row = $result->fetch_assoc()) {
        // Thêm từng sản phẩm vào mảng
        $discounts[] = array(
            'MaGiamGia' => $row['MaGiamGia'],
            'TenGiamGia' => $row['TenGiamGia'],
            'PhanTramGiam' => $row['PhanTramGiam'],
            'NgayBatDau' => $row['NgayBatDau'],
            'NgayKetThuc' => $row['NgayKetThuc'],
            'MaSanPham' => $row['MaSanPham'],
            'TenSanPham' => $row['TenSanPham'],
            'HinhAnh' => $row['HinhAnh'],
            'Gia' => $row['Gia']
        );
    }
} else {
    // Nếu không có chương trình giảm giá nào
    $discounts = [];
}

// Đóng kết nối
$conn->close();

// Chuyển mảng $discounts thành chuỗi JSON và trả về kết quả
header('Content-Type: application/json; charset=utf-8');
echo json_encode($discounts, JSON_UNESCAPED_UNICODE);
?>
