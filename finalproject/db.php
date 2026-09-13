<?php  
    $db_host = 'localhost'; 
    $db_user = 'root'; 
    $db_pass = '';
    $db_database = 'otakomater';



    //  // just good practice to have the errors displayed in dev/qa (and disabled in prod)
    //  ini_set('display_errors', 1); 


    //  // connection parameters should always be in just one place! 
    //  include('db.php'); 
 
 
     
    //  // create the connection 
    //  $con = new mysqli($db_host, 
    //                    $db_user, 
    //                    $db_pass, 
    //                    $db_database);
 
 
    //  if ($con -> connect_errno) {
    //      echo "Failed to connect to MySQL: " . $con -> connect_error;
    //      exit();
    //  } 
 
 
 
    //  // Perform query 
 
    //  if (isset($_POST['email'])) { 
 
 
    //      $email = $_POST['email']; 
    //      $pass = $_POST['password']; 
 
    //      $query = "SELECT email FROM users WHERE email = ? AND password = sha1(?)";
    //      if ($stmt = $con->prepare($query)) {
    //          $stmt->bind_param('ss', $email, $pass);
    //          $stmt->execute(); 
    //          $result = $stmt->get_result();
    //          $num_rows = $result->num_rows; 
             
    //          echo "Number of rows returned: $num_rows <br />"; 
 
    //          while ($row = $result -> fetch_row()) {
    //              // over here 
    //              $logged_in = true;
    //              $retrieved_email = $row[0];
    //              echo $retrieved_email; 
    //          } 
    //      }
    //  } 
 
 
 
    //  $con->close();