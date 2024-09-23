<?php

session_start();
include "funcs.php";

// DB接続
$pdo = db_conn();

sschk();

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

// Tavily API key
$tavily_api_key = 'tvly-';

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <title>AI書籍検索</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

        <meta name="theme-color" content="#7952b3">
        <style>
            body {
                background-image: url('./img/haikei3.png');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                font-family: Arial, sans-serif;
                font-size: 16px;
            }

            .profile-img {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                object-fit: cover;
            }

            .navbar {
                background-color: #003775;
                padding: 15px 15px;
                color: #ffffff;
            }
            
            .navbar-brand {
                color: #ffffff !important;
                font-weight: 350;
                font-size: 1.2rem;
                margin-left: 10px; 
            }

            .navbar-brand:hover {
                text-decoration: underline;
            }

            .navbar-nav {
                margin-left: auto;
            }

            .container {
                max-width: 1200px;
            }
            textarea {
                width:100%;
                height:100px;
            }
            #outputText{
                width: 100%;
                height: 100%;
                background-color: #f8f9fa;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
                padding: 15px;
                margin-top: 15px;
                white-space: pre-wrap;
                word-wrap: break-word;
                font-size: 1rem;
                line-height: 1.5;
                text-align: left;
                display: none; /* 初期状態で非表示 */
            }

            .btn {
                border-radius: 25px;
                padding: 10px 20px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-primary {
                background-color: #6c5ce7;
                border-color: #6c5ce7;
            }

            .btn-primary:hover {
                background-color: #5b4cdb;
                border-color: #5b4cdb;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(108,92,231,.3);
            }

            .btn-secondary {
                background-color: #95a5a6;
                border-color: #95a5a6;
            }

            .btn-secondary:hover {
                background-color: #7f8c8d;
                border-color: #7f8c8d;
            }

            .genre-list {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
            }
            .genre-list label {
                display: flex;
                align-items: center;
            }
            .genre-list input[type="checkbox"] {
                width: 17px;
                height: 17px;
                margin-right: 5px;
            }
            .genre-list label span {
                font-size: 19px;
            }
            .genre-list input[type="checkbox"]:checked + span {
                font-weight: bold;
            }

            #recommendationResult {
                background-color: white;
                border-radius: 8px;
                padding: 20px;
                margin-top: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                display: none;
                text-align: left; /* 左寄せにする */
            }

            #generatingMessage {
                font-weight: bold;
            }

            .error-message {
                margin-top: 20px;
                font-weight: bold;
            }

            .white-bg-heading {
                background-color: white;
                padding: 10px 20px;
                border-radius: 8px;
                display: inline-block;
                margin-bottom: 15px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .white-bg-section {
                background-color: white;
                border-radius: 8px;
                padding: 15px;
                margin: 0 auto 20px; /* 上下のマージンは0と20px、左右は自動 */
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                max-width: 40%;  /* 最大幅を設定 */
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .genre-buttons {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .genre-btn {
                padding: 10px 20px;
                font-size: 20px;
                font-weight: bold;
                border-radius: 20px;
                transition: all 0.3s ease;
                color: black;
                background-color: #f0f0f0; /* 薄い灰色 */
                border: none;
            }

            .genre-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                background-color: #e0e0e0; /* ホバー時はさらに濃い灰色 */
            }

            .genre-btn.active {
                background-color: #007bff;
                color: white;
            }

            #generatingMessage {
                font-weight: bold;
                font-size: 24px; /* 文字サイズを大きくする */
                color: #007bff; /* 青色にする */
                margin-top: 20px; /* 上部に余白を追加 */
                margin-bottom: 20px; /* 下部に余白を追加 */
            }

            #tavily-results {
                background-color: white;
                border-radius: 8px;
                padding: 20px;
                margin-top: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: left;
                display: none; /* 初期状態で非表示 */
            }

            .result-item {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e0e0e0;
            }

            .result-item:last-child {
                border-bottom: none;
            }

            .result-item h5 {
                margin-bottom: 10px;
            }

            .result-item a {
                color: #007bff;
                text-decoration: none;
            }

            .result-item a:hover {
                text-decoration: underline;
            }

            .result-item p {
                margin-bottom: 0;
                color: #555;
            }

            #dify-chatbot {
                width: 90%;
                height: 400px;
                border: none;
                background: transparent;
            }

            #dify-chatbot::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: transparent;
                z-index: -1;
            }

            @media (max-width: 991px) {
                .navbar-toggler {
                    display: block;
                    background-color: transparent;
                    border: none;
                    color: #ffffff;
                }

                .navbar-collapse {
                    display: none;
                    position: absolute;
                    top: 100%;
                    right: 0;
                    background-color: #003775;
                    width: 200px;
                    padding: 10px;
                }

                .navbar-collapse.show {
                    display: block;
                }

                .navbar-nav {
                    flex-direction: column;
                }

                .navbar-brand {
                    margin-left: 0;
                    padding: 5px 0;
                }
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container">
                <a style="font-size: 18px;">
                    <img src="<?= $profile_image ?>" alt="Profile Image" class="profile-img">
                    &thinsp;
                    <?=$_SESSION["username"]?>さん
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="navbar-nav ml-auto">
                        <a class="navbar-brand" href="select.php"><i class="fa fa-table"></i>登録データ一覧</a>
                        <a class="navbar-brand" href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a>
                    </div>
                </div>
            </div>
        </nav>    
    
        <main>
            <section class="text-center container">
                <h3 class="mb-3 fw-medium white-bg-heading" style="font-size:24px"><b>あなたの悩みを教えて下さい</b></h3>
                
                <div>

                <iframe
                id="dify-chatbot"
                src="https://udify.app/chat"
                allow="microphone">
                </iframe>

                    <textarea id="concern" placeholder="あなたの悩みや困りごとを入力してください"></textarea>

                    <h3 class="mt-2 mb-2 white-bg-heading" style="font-size:24px"><b>本のジャンルを選んで下さい</b></h3>

                    <div class="genre-buttons mt-2" id="genreButtons">
                        <button class="btn genre-btn" data-genre="小説">小説</button>
                        <button class="btn genre-btn" data-genre="漫画">漫画</button>
                        <button class="btn genre-btn" data-genre="ビジネス書">ビジネス書</button>
                        <button class="btn genre-btn" data-genre="自己啓発">自己啓発</button>
                    </div>
                    
                    <div id="error" class="error-message"></div>

                    <div id="recommendation" class="mt-2"><strong id="generatingMessage"></strong></div>

                    <div id="recommendationResult" class="mt-3 mb-3"></div>
                    
                    <div id="outputText" class="mt-3 mb-3"></div>

                </div>

                <!-- Add a new section for displaying Tavily search results -->
                <div id="tavily-results" class="mt-4"></div>
                
            </section>
        </main>

        <script>
        const genres = ['小説', '漫画', 'ビジネス書', '自己啓発'];
        const genreButtons = document.getElementById('genreButtons');
        const concernInput = document.getElementById('concern');
        const errorDiv = document.getElementById('error');
        const recommendationDiv = document.getElementById('recommendation');
        const tavilyResultsDiv = document.getElementById('tavily-results');

        // ジャンルボタンのイベントリスナーを追加
        genreButtons.addEventListener('click', function(event) {
            if (event.target.classList.contains('genre-btn')) {
                const genre = event.target.dataset.genre;
                handleGenreSelection(genre);
            }
        });

        function handleGenreSelection(genre) {
            // 全てのボタンからactiveクラスを削除
            document.querySelectorAll('.genre-btn').forEach(btn => btn.classList.remove('active'));
            
            // 選択されたボタンにactiveクラスを追加
            event.target.classList.add('active');

            if (genre === 'ビジネス書') {
                const concern = concernInput.value.trim();
                if (concern === '') {
                    errorDiv.textContent = '悩みや困りごとを入力してください。';
                    return;
                }
                searchTavily(concern + ' ビジネス書 site:amazon.co.jp');
            } else {
                // 他のジャンルの処理（必要に応じて実装）
            }
        }

        function searchTavily(query) {
            errorDiv.textContent = '';
            const generatingMessage = document.getElementById('generatingMessage');
            generatingMessage.textContent = '検索中...';
            generatingMessage.style.display = 'block'; // 明示的に表示

            fetch('tavily_search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query: query }),
            })
            .then(response => response.json())
            .then(data => {
                displayTavilyResults(data);
            })

            .catch(error => {
                errorDiv.textContent = 'エラーが発生しました。もう一度お試しください。';
                console.error('Error:', error);
                tavilyResultsDiv.style.display = 'none'; // エラー時に結果表示領域を非表示にする
            })

            .finally(() => {
                generatingMessage.style.display = 'none'; // 検索完了後に非表示
            });
        }

        function displayTavilyResults(data) {
            document.getElementById('generatingMessage').style.display = 'none';
            tavilyResultsDiv.innerHTML = '<h4 style="margin-bottom: 20px;">おすすめのビジネス書:</h4>';
            if (data.results && Array.isArray(data.results)) {
                data.results.forEach(result => {
                    const resultElement = document.createElement('div');
                    resultElement.className = 'result-item';
                    resultElement.innerHTML = `
                        <h5><a href="${result.url}" target="_blank">${result.title}</a></h5>
                        <p><strong>概要:</strong> ${result.summary}</p>
                        <p><strong>おすすめの理由:</strong> ${result.recommendation_reason}</p>
                        <p>${result.description}</p>
                    `;
                    tavilyResultsDiv.appendChild(resultElement);
                });
                tavilyResultsDiv.style.display = 'block';
            } else {
                tavilyResultsDiv.innerHTML += '<p>検索結果がありません。</p>';
                tavilyResultsDiv.style.display = 'block';
            }
        }

        </script>        

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>
</html>