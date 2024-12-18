<?php
header("Access-Control-Allow-Origin: https://fe-cnpm-three.vercel.app");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Access-Control-Allow-Credentials: true');

$servername = "localhost";
$username = "tvppdznq_cnpm";
$password = "65mTe4MXEPsddYx6tgRN";
$dbname = "tvppdznq_cnpm";
// Xử lý OPTIONS request (pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}
// Kết nối cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(array("message" => "Kết nối thất bại: " . $conn->connect_error), JSON_UNESCAPED_UNICODE);
    exit();
}

// Lấy dữ liệu từ client
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra dữ liệu đầu vào
if (!isset($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Dữ liệu không hợp lệ."), JSON_UNESCAPED_UNICODE);
    exit();
}

// Duyệt qua từng sản phẩm để kiểm tra số lượng
$items = $data['items'];
$totalAmount = 0;

foreach ($items as $item) {
    $maSanPham = $conn->real_escape_string($item['MaSanPham']);
    $soLuong = intval($item['SoLuong']);

    // Kiểm tra sản phẩm có tồn tại không
    $sqlCheckProduct = "SELECT Gia, SoLuongConLai, MaGiamGia FROM SanPham WHERE MaSanPham = '$maSanPham'";
    $result = $conn->query($sqlCheckProduct);

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(array("message" => "Sản phẩm $maSanPham không tồn tại."), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $product = $result->fetch_assoc();
    $giaSanPham = floatval($product['Gia']);
    $soLuongTon = intval($product['SoLuongConLai']);
    $maGiamGia = intval($product['MaGiamGia']);

    // Kiểm tra số lượng có hợp lệ không
    if ($soLuong > $soLuongTon) {
        http_response_code(400);
        echo json_encode(array("message" => "Số lượng của sản phẩm $maSanPham không đủ. Hiện chỉ còn $soLuongTon."), JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Nếu có mã giảm giá, lấy thông tin giảm giá từ bảng `GiamGia`
    if ($maGiamGia) {
        $sqlDiscount = "SELECT PhanTramGiam FROM GiamGia WHERE MaGiamGia = $maGiamGia";
        $discountResult = $conn->query($sqlDiscount);

        if ($discountResult->num_rows > 0) {
            $discountData = $discountResult->fetch_assoc();
            $phanTramGiam = floatval($discountData['PhanTramGiam']);
        } else {
            $phanTramGiam = 0; // Nếu không có giảm giá, mặc định là 0%
        }
    } else {
        $phanTramGiam = 0; // Nếu không có mã giảm giá, mặc định là 0%
    }

    // Tính tổng tiền với giảm giá (nếu có)
    $giaSauGiam = $giaSanPham * (1 - $phanTramGiam / 100);
    $totalAmount += $giaSauGiam * $soLuong;
}

// Tạo bản ghi cho bảng `Order`
$sqlOrder = "INSERT INTO DatHang (NgayDat, TongTien) VALUES (CURRENT_DATE(), '$totalAmount')";
if ($conn->query($sqlOrder) === TRUE) {
    $orderId = $conn->insert_id; // Lấy ID đơn hàng vừa tạo
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi khi tạo đơn hàng: " . $conn->error), JSON_UNESCAPED_UNICODE);
    exit();
}

// Thêm sản phẩm vào chi tiết đơn hàng và cập nhật số lượng trong kho
foreach ($items as $item) {
    $maSanPham = $conn->real_escape_string($item['MaSanPham']);
    $soLuong = intval($item['SoLuong']);
    $maMauSac = $conn->real_escape_string($item['MaMauSac']);
    $maSize = $conn->real_escape_string($item['MaSize']);

    // Thêm chi tiết đơn hàng
    $sqlOrderDetail = "INSERT INTO ChiTietDonHang (MaDonHang, MaSanPham, SoLuong, MaMauSac, MaSize) 
                       VALUES ('$orderId', '$maSanPham', '$soLuong', '$maMauSac', '$maSize')";
    if ($conn->query($sqlOrderDetail) !== TRUE) {
        http_response_code(500);
        echo json_encode(array("message" => "Lỗi khi thêm sản phẩm $maSanPham vào đơn hàng: " . $conn->error), JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Cập nhật số lượng sản phẩm trong kho
    $sqlUpdateStock = "UPDATE SanPham SET DaBan = DaBan + $soLuong WHERE MaSanPham = '$maSanPham'";
    if ($conn->query($sqlUpdateStock) !== TRUE) {
        http_response_code(500);
        echo json_encode(array("message" => "Lỗi khi cập nhật số lượng sản phẩm $maSanPham: " . $conn->error), JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// Trả về kết quả thành công
http_response_code(200);
echo json_encode(array("message" => "Đặt hàng thành công.", "OrderID" => $orderId), JSON_UNESCAPED_UNICODE);

// Đóng kết nối
$conn->close();
?>
