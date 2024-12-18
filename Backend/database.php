<?php
// Kết nối cơ sở dữ liệu
$conn = mysqli_connect("localhost", "tvppdznq_cnpm", "65mTe4MXEPsddYx6tgRN", "tvppdznq_cnpm");
mysqli_set_charset($conn, "utf8");

// Truy vấn tất cả kích thước từ bảng Size
$query = "SELECT MaSize, Size, MaSanPham FROM Size";
$result = mysqli_query($conn, $query);

// Mảng chứa dữ liệu
$sizes = array();

// Nếu có dữ liệu từ truy vấn
if (mysqli_num_rows($result) > 0) {
    // Duyệt qua tất cả các dòng và thêm vào mảng
    while ($row = mysqli_fetch_assoc($result)) {
        $sizes[] = $row;
    }

    // Tạo phản hồi JSON
    $response = array(
        'status' => 'success',
        'data' => $sizes
    );

    // Thiết lập header cho JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    // Nếu không có dữ liệu
    echo json_encode(array('status' => 'error', 'message' => 'Không có dữ liệu kích thước'), JSON_UNESCAPED_UNICODE);
}

// Đóng kết nối
mysqli_close($conn);
?>
