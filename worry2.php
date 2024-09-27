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

echo "<h2>あなたの悩みに関連する本:</h2>";

if (!empty($user_worry)) {
    // 全ての本を取得
    $sql = "SELECT * FROM gs_bm_table";
    $result = $conn->query($sql);

    $recommendations = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // worryとcomentの両方で類似度を計算
            $similarity_worry = similarity($user_worry, $row["worry"]);
            $similarity_coment = similarity($user_worry, $row["coment"]);
            $max_similarity = max($similarity_worry, $similarity_coment);
            
            // 類似度が20%以上の場合のみ推薦に追加
            if ($max_similarity >= 20) {
                $recommendations[] = [
                    'similarity' => $max_similarity,
                    'book' => $row["book"],
                    'worry' => $row["worry"],
                    'coment' => $row["coment"],
                    'url' => $row["url"]
                ];
            }
        }

        // 類似度でソート
        usort($recommendations, function($a, $b) {
            return $b['similarity'] - $a['similarity'];
        });

        if (!empty($recommendations)) {
            // 上位3件を表示
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
            echo "<p>申し訳ありません。あなたの悩みに直接関連する本が見つかりませんでした。</p>";
            echo "<p>別のキーワードで試すか、より一般的な表現で悩みを入力してみてください。</p>";
        }
    } else {
        echo "<p>データベースに本が登録されていません。</p>";
    }
} else {
    echo "<p>悩みを入力してください。関連する本をお探しします。</p>";
}

// データベース接続を閉じる
$conn->close();
?>