<?php
// PHPコードブロックを開始
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $worry = $_POST['worry'];
    
    // recommend.phpをインクルード
    include 'recommend.php';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>悩み相談</title>
</head>
<body>
    <h1>悩みを入力してください</h1>
    <form method="post" action="">
        <label for="worry">悩み:</label><br>
        <textarea id="worry" name="worry" rows="4" cols="50"><?php echo isset($worry) ? htmlspecialchars($worry) : ''; ?></textarea><br><br>
        <input type="submit" value="送信">
    </form>

    <?php
    // 結果を表示（recommend.phpで設定された変数を使用）
    if (isset($result)) {
        echo "<h2>結果:</h2>";
        if (isset($result['answer'])) {
            echo "<p>回答: " . htmlspecialchars($result['answer']) . "</p>";
        } elseif (isset($result['message'])) {
            echo "<p>回答: " . htmlspecialchars($result['message']) . "</p>";
        } elseif (isset($result['text'])) {
            echo "<p>回答: " . htmlspecialchars($result['text']) . "</p>";
        } else {
            echo "<p>申し訳ありませんが、APIの応答から回答を抽出できませんでした。</p>";
        }
    }
    ?>
</body>
</html>