<?php
include ('../conn/conn.php');

if (isset($_GET['bill'])) {
    $bill = $_GET['bill'];

    try {

        $query = "DELETE FROM tbl_bill WHERE tbl_bill_id = '$bill'";

        $stmt = $conn->prepare($query);

        $query_execute = $stmt->execute();

        if ($query_execute) {
            header("Location: http://localhost/electricity-comsumption-monitoring/");
        } else {
            header("Location: http://localhost/electricity-comsumption-monitoring/");
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>