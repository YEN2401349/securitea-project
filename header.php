<!DOCTYPE html>
<html>
    <html></html>
</html>
<?php
    //SecuriTeaロゴ兼トップページリンク
    echo '<header class="header">',
            '<div class="container">',
                '<div class="logo">',
                    '<a href="test.php" class="logo">',
                        '<img src="images/ロゴ2透過.png" alt="Modern Securitea Logo">',
                    '</a>',
                '</div>',

                '<nav class="nav">';
    //ログイン状態の確認
    //ログインしていればユーザー情報ページのリンク表示
    //ログインして無ければログインページのリンク表示
    if(isset($_SESSION['customer'])){
        echo '<ul class="nav-list">',
                '<li class="nav-item">',
                    '<a href="login.html" class="nav-link">'
    }
?>

        
            
                
                    
                
            
            
                
                    
                        
                            <i class="fas fa-user"></i>
                            ログイン
                        </a>
                    </li>
                     <li class="nav-item">
                        <a href="software.php" class="nav-link">
                            <i class="fas fa-user"></i>
                            商品一覧
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="account.html" class="nav-link">
                            <i class="fas fa-question-circle"></i>
                            アカウント情報
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="inquiry.html" class="nav-link">
                            <i class="fas fa-question-circle"></i>
                            お問い合わせフォーム
                        </a>
                    </li>
                </ul>
            </nav>
        </div>