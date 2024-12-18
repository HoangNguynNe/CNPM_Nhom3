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

// Lấy dữ liệu tên danh mục từ bảng "DanhMuc"
$sql = "SELECT MaDanhMuc, TenDanhMuc FROM DanhMuc";

// Thực thi truy vấn SQL
$result = $conn->query($sql);

// Mảng để chứa kết quả
$categories = array();

// Kiểm tra nếu có kết quả
if ($result->num_rows > 0) {
    // Duyệt qua tất cả các danh mục
    while ($row = $result->fetch_assoc()) {
        // Thêm từng danh mục vào mảng
        $categories[] = array(
            'MaDanhMuc' => $row['MaDanhMuc'],
            'TenDanhMuc' => $row['TenDanhMuc']
        );
    }
} else {
    // Nếu không có danh mục nào
    $categories = [];
}

// Đóng kết nối
$conn->close();

// Chuyển mảng $categories thành chuỗi JSON và trả về kết quả
header('Content-Type: application/json; charset=utf-8');
echo json_encode($categories, JSON_UNESCAPED_UNICODE);
?>
