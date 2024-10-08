<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Consumption Monitoring Tool</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

        * {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-image: linear-gradient(-225deg, #A445B2 0%, #D41872 52%, #FF0066 100%);
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .electricity-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            background-color: rgb(255, 255, 255);
            border-radius: 10px;
            height: 90vh;
            width: 90vw;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
        }
        
        .header {
            display: flex;
            width: 100%;
            justify-content: space-between;
            border-bottom: 1px solid rgb(200, 200, 200);
            padding-bottom: 10px;
        }

        .table-graph-container {
            display: flex;
            width: 100%;
            height: 100%;
            padding: 20px
        }

        .table-container {
            width: 500px;
            padding-right: 10px;
            border-right: 1px solid rgb(200, 200, 200);
            height: 100%;
        }

        .graph-container > canvas {
            margin-left: 10px;
            width: 800px;
            height: 100% !important;
        }

        .btn-primary {
            background-color: #D41872 !important;
            border: none !important;
            outline: none !important;
        }
    </style>
</head>
<body>
    <div class="main">
        <div class="electricity-container">
            <div class="header">
                <h3>Electricity Consumption Monitoring Tool</h3>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addBillModal">+ Add Electricity Bill</button>

                <!-- Modal -->
                <div class="modal fade" id="addBillModal" tabindex="-1" aria-labelledby="addBill" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addBill">Add Bill</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="./endpoint/add-bill.php" method="POST">
                                    <div class="form-group">
                                        <label for="billDate">Bill Date:</label>
                                        <input type="date" class="form-control" id="billDate" name="bill_date">
                                    </div>
                                    <div class="form-group">
                                        <label for="billAmount">Bill Amount:</label>
                                        <input type="number" class="form-control" id="billAmount" name="bill_amount">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Add</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-graph-container">
                <div class="table-container">
                    <table class="table text-center table-sm">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Bill</th>
                                <th scope="col">Consumed</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="billTableBody">
                            <?php
                                include('./conn/conn.php');

                                $stmt = $conn->prepare("SELECT * FROM tbl_bill ORDER BY bill_date");
                                $stmt->execute();
                                $result = $stmt->fetchAll();

                                foreach ($result as $row) {
                                    $billId = $row['tbl_bill_id'];
                                    $billDate = $row['bill_date'];
                                    $billAmount = $row['bill_amount'];

                                    // Fetch previous bill amount
                                    $prevBillStmt = $conn->prepare("SELECT bill_amount FROM tbl_bill WHERE tbl_bill_id = ?");
                                    $prevBillStmt->execute([$billId - 1]);
                                    $prevBill = $prevBillStmt->fetchColumn();

                                    // Calculate the difference between current and previous bill amounts
                                    $billDifference = $billAmount - $prevBill;

                                    // Output the table row
                                    echo '<tr class="billList">';
                                    echo '<th hidden>' . $billId . '</th>';
                                    echo '<td>' . $billDate . '</td>';
                                    echo '<td>' . $billAmount . '</td>';
                                    echo '<td>' . $billDifference . '</td>';
                                    echo '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeBill(' . $billId . ')">X</button></td>';
                                    echo '</tr>';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="graph-container">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        </div>
    </div>   
        
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <?php
        include('./conn/conn.php');

        $stmt = $conn->prepare("SELECT * FROM tbl_bill ORDER BY bill_date");
        $stmt->execute();
        $result = $stmt->fetchAll();

        $labels = [];
        $consumptions = [];

        $prevBill = null;
        foreach ($result as $row) {
            $billDate = $row['bill_date'];
            $billAmount = $row['bill_amount'];

            // Calculate bill consumption
            $consumption = $prevBill !== null ? $billAmount - $prevBill : 0;

            // Store data for chart
            $labels[] = $billDate;
            $consumptions[] = $consumption;

            // Update previous bill for next iteration
            $prevBill = $billAmount;
        }
    ?>

    
    <script>
        function removeBill(id) {
            if (confirm("Do you want to delete this bill?")) {
                window.location = "./endpoint/delete-bill.php?bill=" + id;
            }
        }

        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Electricity Consumed Each Month',
                    data: <?php echo json_encode($consumptions); ?>,
                    borderColor: '#D41872',
                    backgroundColor: '#D41872',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>