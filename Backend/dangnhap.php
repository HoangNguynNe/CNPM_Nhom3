<?php
$servername = "localhost";
$username = "tvppdznq_cnpm";
$password = "65mTe4MXEPsddYx6tgRN";
$dbname = "tvppdznq_cnpm";

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

// Kết nối cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    http_response_code(500); // Lỗi server
    echo json_encode([
        "status" => "error",
        "message" => "Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error
    ]);
    exit;
}

// Thiết lập mã hóa UTF-8
$conn->set_charset("utf8mb4");

// Lấy dữ liệu từ yêu cầu
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra dữ liệu đầu vào
if (isset($data['Email']) && isset($data['MatKhau'])) {
    $email = $conn->real_escape_string($data['Email']);
    $matKhau = $data['MatKhau']; // Mật khẩu sẽ được so sánh sau

    // Truy vấn kiểm tra thông tin đăng nhập
    $query = "SELECT MaTaiKhoan, Email, MatKhau FROM TaiKhoan WHERE Email = '$email'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Kiểm tra mật khẩu (giả sử bạn đã mã hóa bằng password_hash)
       if ($matKhau == $user['MatKhau']) {
    echo json_encode([
        "status" => "success",
        "message" => "Đăng nhập thành công",
        "data" => [
            "MaTaiKhoan" => $user['MaTaiKhoan'],
            "Email" => $user['Email']
        ]
    ]);
}else {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Mật khẩu không chính xác"
            ]);
        }
    } else {
        http_response_code(404); // Not Found
        echo json_encode([
            "status" => "error",
            "message" => "Email không tồn tại"
        ]);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error",
        "message" => "Vui lòng cung cấp Email và Mật khẩu"
    ]);
}

// Đóng kết nối
$conn->close();
?>
