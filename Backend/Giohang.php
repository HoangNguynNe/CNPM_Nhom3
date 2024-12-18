<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

// Cấu hình cơ sở dữ liệu
$servername = "localhost";
$username = "tvppdznq_cnpm";
$password = "65mTe4MXEPsddYx6tgRN";
$dbname = "tvppdznq_cnpm";

// Tạo kết nối
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    // Thiết lập mã hóa UTF-8
    $conn->set_charset("utf8mb4");

    // Chỉ xử lý với phương thức GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Truy vấn lấy dữ liệu
        $sql = "SELECT 
                    GioHang.MaGioHang, 
                    GioHang.MaSanPham, 
                    GioHang.SoLuong, 
                    GioHang.MaMauSac, 
                    GioHang.MaSize, 
                    SanPham.TenSanPham, 
                    SanPham.HinhAnh, 
                    SanPham.Gia, 
                    MauSac.MauSac, 
                    Size.Size
                FROM GioHang
                JOIN SanPham ON GioHang.MaSanPham = SanPham.MaSanPham
                LEFT JOIN MauSac ON GioHang.MaMauSac = MauSac.MaMauSac
                LEFT JOIN Size ON GioHang.MaSize = Size.MaSize";

        $result = $conn->query($sql);

        // Kiểm tra kết quả
        $cartItems = array();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = array(
                    'MaGioHang' => $row['MaGioHang'],
                    'MaSanPham' => $row['MaSanPham'],
                    'TenSanPham' => $row['TenSanPham'],
                    'HinhAnh' => $row['HinhAnh'],
                    'Gia' => $row['Gia'],
                    'SoLuong' => $row['SoLuong'],
                    'MaMauSac' => $row['MaMauSac'],
                    'TenMauSac' => $row['TenMauSac'],
                    'MaSize' => $row['MaSize'],
                    'TenSize' => $row['TenSize']
                );
            }
        } else {
            $cartItems = [];
        }

        // Trả về kết quả dưới dạng JSON
        echo json_encode($cartItems, JSON_UNESCAPED_UNICODE);
    } else {
        // Nếu phương thức không phải GET
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"), JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    // Xử lý lỗi
    http_response_code(500);
    echo json_encode(array("message" => "Đã xảy ra lỗi", "error" => $e->getMessage()), JSON_UNESCAPED_UNICODE);
} finally {
    // Đảm bảo đóng kết nối
    if ($conn) {
        $conn->close();
    }
}
?>
