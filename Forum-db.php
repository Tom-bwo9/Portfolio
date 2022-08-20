<?php

    // DB接続設定
    $dsn = '****';
    $user = '****';
    $password = '****';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    //テーブルの作成
    $sql = "CREATE TABLE IF NOT EXISTS Forum"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "pass TEXT,"
    . "time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    .");";
    $stmt = $pdo->query($sql);
    
    //テーブルの表示
     $sql ='SHOW TABLES';
    $result = $pdo -> query($sql);
    foreach ($result as $row){
        echo $row[0];
        echo '<br>';
    }
    echo "<hr>";
    
    //構成内容の確認
     $sql ='SHOW CREATE TABLE Forum';
    $result = $pdo -> query($sql);
    foreach ($result as $row){
        echo $row[1];
    }
    echo "<hr>";
?>

<?php
//編集フォームに投稿があったとき
    if(!empty($_POST["edit"]) && !empty($_POST["pass_edit"])){
        $id=$_POST["edit"];
        $sql = 'SELECT * FROM Forum WHERE id=:id ';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                             // ←SQLを実行する。
        $results = $stmt->fetchAll(); 
        foreach ($results as $row){
            $pass_edit=$row['pass'];
            //パスワードが合っていたとき
            if($pass_edit==$_POST["pass_edit"]){
                $postnum_value=$row['id'];
                $name_value=$row['name'];
                $comment_value=$row['comment'];
                $pass_value=$row['pass'];
            //パスワードが間違っていたとき
            }elseif($pass_edit!=$_POST["pass_edit"]){
                $postnum_value="";
                $name_value="";
                $comment_value="";
                $pass_value="";
                echo "パスワードが間違っています。".'<br>';
            }
        }
    }else
    {$postnum_value="";
    $name_value="";
    $comment_value="";
    $pass_value="";
    }
    
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Forum</title>
</head>
<body>
    　
    <form action="" method="post">
         <input type="hidden" name="postnum" value=<?php echo $postnum_value; ?>>
         <input type="text" name="name" placeholder="名前" value=<?php echo $name_value; ?>>
        <input type="text" name="comment" placeholder="コメント" value=<?php echo $comment_value; ?>>
        <input type="password" name="pass" placeholder="パスワード" value=<?php echo $pass_value; ?>>
        <input type="submit" name="submit"><br>
        
         <input type="number" name="delete" placeholder="削除対象番号">
         <input type="password" name="pass_delete" placeholder="パスワード" >
        <input type="submit" name="submit" value="削除"><br>
        
        <input type="number" name="edit" placeholder="編集対象番号">
        <input type="password" name="pass_edit" placeholder="パスワード" >
        <input type="submit" name="submit" value="編集">
    </form>
    
<?php
    //データの登録
    if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["pass"])){
       //新規投稿
        if(empty($_POST["postnum"])){
         $sql = $pdo -> prepare("INSERT INTO Forum (name, comment, pass) VALUES (:name, :comment, :pass)");
         $sql -> bindParam(':name', $_POST["name"], PDO::PARAM_STR);
         $sql -> bindParam(':comment', $_POST["comment"], PDO::PARAM_STR);
         $sql -> bindParam(':pass', $_POST["pass"], PDO::PARAM_STR);
         $sql -> execute();
         echo "データを登録しました。".'<br>';
       
        //編集による上書き
        }elseif(!empty($_POST["postnum"])){
            $id=$_POST["postnum"];
            $sql = 'SELECT * FROM Forum WHERE id=:id ';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                $postnum=$row['id'];
                if($postnum==$_POST["postnum"]){
                    $id = $_POST["postnum"]; //変更する投稿番号
                    $name = $_POST["name"];
                    $comment = $_POST["comment"];
                    $pass = $_POST["pass"];
                    $sql = 'UPDATE Forum SET name=:name,comment=:comment, pass=:pass WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    echo "投稿を編集しました。".'<br>';
                }else{
                    echo"全てのデータを入力してください。".'<br>';
                }
            }
        }
    
    //データの削除
    }elseif(!empty($_POST["delete"]) && !empty($_POST["pass_delete"])){
        $id=$_POST["delete"];
        $sql = 'SELECT * FROM Forum WHERE id=:id ';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                             // ←SQLを実行する。
        $results = $stmt->fetchAll(); 
        foreach ($results as $row){
            $pass_delete=$row['pass'];
            if($pass_delete==$_POST["pass_delete"]){
                $id=$_POST["delete"];
                $sql = 'delete from Forum where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                echo "投稿を削除しました。".'<br>';
            }else{
                echo "パスワードが間違っています。".'<br>';
            }
        }
    }
    
    
?>

<?php
    //入力されたデータの表示
    $sql = 'SELECT * FROM Forum';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['time'].'<br>';
    }
?>

</body>
</html>