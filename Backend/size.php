<?php
$servername = "localhost";
$username = "tvppdznq_cnpm"; // Tên người dùng MySQL
$password = "65mTe4MXEPsddYx6tgRN"; // Mật khẩu MySQL
$dbname = "tvppdznq_cnpm"; // Tên cơ sở dữ liệu
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Thiết lập mã hóa UTF-8 cho kết nối MySQL
$conn->set_charset("utf8mb4");

// Lấy dữ liệu từ bảng "SanPham" và "Size" liên kết
$sql = "SELECT 
            SanPham.MaSanPham, 
            SanPham.TenSanPham, 
            SanPham.HinhAnh, 
            SanPham.Gia, 
            SanPham.SoLuong, 
            SanPham.MaDanhMuc, 
            DanhMuc.TenDanhMuc,
            GROUP_CONCAT(Size.Size) AS Sizes
        FROM SanPham
        JOIN DanhMuc ON SanPham.MaDanhMuc = DanhMuc.MaDanhMuc
        LEFT JOIN Size ON SanPham.MaSanPham = Size.MaSanPham
        GROUP BY SanPham.MaSanPham";

$result = $conn->query($sql);

// Mảng để chứa kết quả
$products = array();

// Kiểm tra nếu có kết quả
if ($result->num_rows > 0) {
    // Duyệt qua tất cả các sản phẩm
    while ($row = $result->fetch_assoc()) {
        // Thêm từng sản phẩm vào mảng
        $products[] = array(
            'MaSanPham' => $row['MaSanPham'],
            'TenSanPham' => $row['TenSanPham'],
            'HinhAnh' => $row['HinhAnh'],
            'Gia' => $row['Gia'],
            'SoLuong' => $row['SoLuong'],
            'TenDanhMuc' => $row['TenDanhMuc'],
            'Sizes' => $row['Sizes'] // Danh sách kích cỡ
        );
    }
} else {
    // Nếu không có sản phẩm nào
    $products = [];
}

// Đóng kết nối
$conn->close();

// Chuyển mảng $products thành chuỗi JSON và trả về kết quả
header('Content-Type: application/json; charset=utf-8');
echo json_encode($products, JSON_UNESCAPED_UNICODE);
?>
