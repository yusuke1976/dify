<?php
// データベース接続情報
$servername = "";
$username = "";
$password = "";
$dbname = "";

// データベースに接続
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続エラーの確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// テキストの類似度を計算する関数
function similarity($str1, $str2) {
    $str1 = mb_strtolower($str1);
    $str2 = mb_strtolower($str2);
    similar_text($str1, $str2, $percent);
    return $percent;
}

// ユーザーの入力を受け取る
$user_worry = isset($_POST['worry']) ? $_POST['worry'] : '';

if (!empty($user_worry)) {
    // 全ての本を取得
    $sql = "SELECT * FROM gs_bm_table";
    $result = $conn->query($sql);

    $recommendations = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // 各本の悩みとユーザーの悩みの類似度を計算
            $similarity = similarity($user_worry, $row["worry"]);
            $recommendations[] = [
                'similarity' => $similarity,
                'book' => $row["book"],
                'worry' => $row["worry"],
                'coment' => $row["coment"],
                'url' => $row["url"]
            ];
        }

        // 類似度でソート
        usort($recommendations, function($a, $b) {
            return $b['similarity'] - $a['similarity'];
        });

        // 上位3件を表示
        echo "<h2>あなたの悩みに関連する本:</h2>";
        for ($i = 0; $i < min(3, count($recommendations)); $i++) {
            $book = $recommendations[$i];
            echo "<h3>" . htmlspecialchars($book["book"]) . "</h3>";
            echo "<p>悩み: " . htmlspecialchars($book["worry"]) . "</p>";
            echo "<p>コメント: " . htmlspecialchars($book["coment"]) . "</p>";
            echo "<p>URL: <a href='" . htmlspecialchars($book["url"]) . "' target='_blank'>" . htmlspecialchars($book["url"]) . "</a></p>";
            echo "<p>類似度: " . number_format($book["similarity"], 2) . "%</p>";
            echo "<hr>";
        }
    } else {
        echo "データベースに本が登録されていません。";
    }
} else {
    echo "悩みを入力してください。";
}

// データベース接続を閉じる
$conn->close();
?>