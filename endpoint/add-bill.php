<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bill_date'], $_POST['bill_amount'])) {
        $billDate = $_POST['bill_date'];
        $billAmount = $_POST['bill_amount'];

        try {
            $stmt = $conn->prepare("INSERT INTO tbl_bill (bill_date, bill_amount) VALUES (:bill_date, :bill_amount)");
            
            $stmt->bindParam(":bill_date", $billDate, PDO::PARAM_STR); 
            $stmt->bindParam(":bill_amount", $billAmount, PDO::PARAM_STR);

            $stmt->execute();

            header("Location: http://localhost/electricity-comsumption-monitoring/");

            exit();
        } catch (PDOException $e) {
            echo "Error:" . $e->getMessage();
        }

    } else {
        echo "
            <script>
                alert('Please fill in all fields!');
                window.location.href = 'http://localhost/electricity-comsumption-monitoring/';
            </script>
        ";
    }
}
?>
