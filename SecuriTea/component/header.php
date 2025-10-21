<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー)</title>
    <link rel="stylesheet" href="./component/css/header.css">
</head>

<body>
    <?php
    $loginState = $_SESSION['login_state'] ?? null;
    ?>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="./img/ロゴ2透過.png" alt="SecuriTea Logo">
                </a>
            </div>
            <nav class="menu">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-home"></i>
                            ホーム
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="productList.php" class="nav-link">
                            <i class="fas fa-bag-shopping"></i>
                            商品一覧
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-question-circle"></i>
                            Q&A
                        </a>
                    </li>
                </ul>
            </nav>
            <nav class="nav">
                <ul class="nav-list">
                    <?php if ($loginState === null): ?>
                        <li class="nav-item">
                            <a href="./login-input.php" class="nav-link">
                                <i class="fas fa-user"></i>
                                ログイン
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-user-plus"></i>
                                無料登録
                            </a>
                        </li>
                    <?php elseif ($loginState === 'user'): ?>
                        <li class="nav-item">
                            <a href="./mypage.php" class="nav-link">
                                <i class="fas fa-user"></i>
                                マイページ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="fas fa-right-from-bracket"></i>
                                ログアウト
                            </a>
                        </li>
                    <?php elseif ($loginState === 'admin'): ?>
                        <li class="nav-item">
                            <a href="./login.php" class="nav-link">
                                <i class="fas fa-user"></i>
                                管理者用ページ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="fas fa-right-from-bracket"></i>
                                ログアウト
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>