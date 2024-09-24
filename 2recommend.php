<?php
// APIキーの設定
const API_URL = 'https://udify.app/workflow/wgyjDWDfrLNNCAmL';
const API_KEY = 'app-PuViyexKoaGmZ1FNFX55EdEu';

// フォームから送信された悩みの取得
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $worry = $_POST['worry'];

    // Dify APIに送信するデータを準備
    $data = array(
        'inputs' => array('user_input' => $worry),
        'query' => $worry,
        'user' => 'user-' . uniqid(),
        'response_mode' => 'blocking'
    );

    // cURLを使用してDify APIにPOSTリクエストを送信
    $ch = curl_init(API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . API_KEY
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    // エラーチェック
    if ($response === false) {
        $result = array('error' => 'cURLエラー: ' . curl_error($ch));
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = json_decode($response, true);

        if ($http_code != 200) {
            $result = array(
                'error' => 'APIリクエストに失敗しました。ステータスコード: ' . $http_code,
                'code' => $result['code'] ?? 'N/A',
                'message' => $result['message'] ?? 'N/A'
            );
        }
    }

    curl_close($ch);

    // 結果の処理
    if (isset($result['error'])) {
        echo "<h2>エラー</h2>";
        echo "<p>" . htmlspecialchars($result['error']) . "</p>";
        if (isset($result['code'])) {
            echo "<p>エラーコード: " . htmlspecialchars($result['code']) . "</p>";
        }
        if (isset($result['message'])) {
            echo "<p>エラーメッセージ: " . htmlspecialchars($result['message']) . "</p>";
        }
        echo "<h3>トラブルシューティング手順:</h3>";
        echo "<ol>
            <li>Dify ダッシュボードで、アプリケーションが有効になっていることを確認してください。</li>
            <li>アプリケーションの設定（特に API キーと権限）を確認してください。</li>
            <li>使用している API キーが正しいことを確認してください。</li>
            <li>アプリケーションに必要なすべての設定が完了していることを確認してください。</li>
            <li>問題が解決しない場合は、Dify のサポートに連絡してください。</li>
        </ol>";
    } else {
        echo "<h2>結果:</h2>";
        if (isset($result['answer'])) {
            echo "<p>回答: " . htmlspecialchars($result['answer']) . "</p>";
        } elseif (isset($result['data']['answer'])) {
            echo "<p>回答: " . htmlspecialchars($result['data']['answer']) . "</p>";
        } elseif (isset($result['message'])) {
            echo "<p>回答: " . htmlspecialchars($result['message']) . "</p>";
        } elseif (isset($result['text'])) {
            echo "<p>回答: " . htmlspecialchars($result['text']) . "</p>";
        } else {
            echo "<p>申し訳ありませんが、APIの応答から回答を抽出できませんでした。</p>";
            echo "<pre>";
            echo "API Response Structure:\n";
            print_r($result);
            echo "\n\nRaw API Response:\n";
            echo htmlspecialchars($response);
            echo "</pre>";
        }
    }
}
?>