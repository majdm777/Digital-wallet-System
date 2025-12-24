<?php

ini_set('display_errors', 0); 
header('Content-Type: application/json');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);





try{
    $db= new mysqli("localhost","root", "NewPassword123!","wallet_db");
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $data= json_decode(file_get_contents("php://input"),true);
        $action= $data['action']?? NULL;

if ($action === "getBalance") {

    // 1. Call procedure (stores value in @balance)
    $db->query("CALL globalBalance(@balance)");

    // 2. Read OUT parameter
    $result = $db->query("SELECT @balance AS balance");
    $row = $result->fetch_assoc();

    echo json_encode([
        "balance" => $row["balance"] ?? 0
    ]);
    exit;
}

if($action=== "getUsers"){ 

    $db->query("CALL totalUsers(@users)");


    $result = $db->query("SELECT @users AS users");
    $row = $result->fetch_assoc();

    echo json_encode([
        "users" => $row["users"] ?? 0
    ]);
    exit;

}

if($action=== "getWithdrawals"){ 

    $db->query("CALL totalWithdrawals(@withdrawals)");


    $result = $db->query("SELECT @withdrawals AS withdrawals");
    $row = $result->fetch_assoc();



    echo json_encode([
        "withdrawals" => $row["withdrawals"] ?? 0
    ]);
    exit;

}
}

if($action==="addManager"){
    $userName= $data["user"];
    $email= $data["email"];
    $password= $data["pass"];

    if(!str_ends_with($email,"@wallet.com")){
        echo json_encode(["comment"=>"invalid email"]);
        exit;
    }

    $password= password_hash($password, PASSWORD_DEFAULT);

    $query= "CALL addNewManager(?,?,?)";
    $stmt=$db->prepare($query);
    $stmt->bind_param("sss",$userName, $email, $password);
    $stmt->execute();
    $stmt->close();
    $db->next_result();

    echo json_encode([
        "comment" => "manager added successfully"
    ]);
    exit;
}

if($action==="getManagerRows"){

    $query= "CALL getManagers()";
    $stmt= $db->prepare($query);
    $stmt->execute();

    $results= $stmt->get_result();
    $managers= $results->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $db->next_result();

    echo json_encode($managers);
    exit;


}

if($action==="invokeManager"){
    $managerId= (int) $data["id"];
    $query= "CALL invokeManager(?)";
    $stmt= $db->prepare($query);
    $stmt->bind_param("i",$managerId);
    $stmt->execute();
    $stmt->close();
    $db->next_result();

    echo json_encode([
        "comment" => "manager invoked successfully"
    ]);
    exit;

}




}catch( Exception $e){
    echo json_encode(['error' => $e->getMessage()]);
}








?>