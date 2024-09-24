<?php
// APIキーの設定
const API_KEY = 'app-PuViyexKoaGmZ1FNFX55EdEu';

// 新しいAPIエンドポイント
const API_URL = 'https://api.dify.ai/v1/workflows/run';

// フォームの表示
echo '<form method="POST">';
echo '<textarea name="worry" placeholder="あなたの悩みを入力してください"></textarea>';
echo '<input type="submit" value="送信">';
echo '</form>';

// フォームから送信された悩みの取得と処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $worry = $_POST['worry'];

    // Dify APIに送信するデータを準備
    $data = array(
        'inputs' => array('user_input' => $worry),  // 'inputs'をキーと値のペアを含む配列として設定
        'response_mode' => 'blocking',
        'user' => 'user-' . uniqid() // ユニークなユーザーIDを生成
    );

    // デバッグ情報: 送信データの表示
    echo '<h2>送信データ:</h2>';
    echo '<pre>' . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>';

    // APIリクエストを送信
    $response = sendApiRequest(API_URL, $data);

    // デバッグ情報: APIレスポンスの表示
    echo '<h2>APIレスポンス:</h2>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';

    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo '<h2>回答:</h2>';
        echo '<p>' . nl2br(htmlspecialchars($result['output'])) . '</p>';
    } elseif (isset($result['error'])) {
        echo '<h2>エラー:</h2>';
        echo '<p>APIエラー: ' . htmlspecialchars($result['error']['message']) . '</p>';
    } else {
        echo '<h2>エラー:</h2>';
        echo '<p>APIからの応答に問題がありました。</p>';
        echo '<p>レスポンス内容: ' . htmlspecialchars($response) . '</p>';
    }
}

// API リクエスト送信関数
function sendApiRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . API_KEY
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    
    // cURLエラーをチェック
    if ($response === false) {
        return json_encode(array('error' => array('message' => curl_error($ch))));
    }
    
    curl_close($ch);

    return $response;
}
?>