<?php
    //SecuriTeaロゴ兼トップページリンク
    echo '<header class="header">',
            '<div class="container">',
                '<div class="logo">',
                    '<a href="top.php" class="logo">',
                        '<img src="images/ロゴ2透過.png" alt="Modern Securitea Logo">',
                    '</a>',
                '</div>',

                '<nav class="nav">',
                    '<ul class="nav-list">';
    //ログイン状態の確認
    //ログインしていればユーザー情報ページのリンク表示
    //ログインして無ければログインページのリンク表示
                if(isset($_SESSION['customer'])){
                    echo '<li class="nav-item">',
                            '<a href="account_plan_check.php" class="nav-link">',
                                '<i class="fas fa-question-circle"></i>アカウント情報',
                            '</a>',
                        '</li>';
                }
                else{
                    echo '<li class="nav-item">',
                            '<a href="login.php" class="nav-link">',
                                '<i class="fas fa-user"></i>ログイン',
                            '</a>',
                        '</li>';
                }

                    echo '<li class="nav-item">',
                            '<a href="product.php" class="nav-link">',
                                '<i class="fas fa-user"></i>商品一覧',
                            '</a>',
                        '</li>',

                        '<li class="nav-item">',
                            '<a href="inquiry.php" class="nav-link">',
                                '<i class="fas fa-question-circle"></i>お問い合わせフォーム',
                            '</a>',
                        '</li>',
                    '</ul>',
                '</nav>',
            '</div>',
        '</header>';
?>                                           
               
            
        