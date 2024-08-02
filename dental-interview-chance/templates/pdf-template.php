<!-- templates/pdf-template.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluation Report</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            height: 100%;
        }
        .container {
            padding: 20px;
            background: linear-gradient(to bottom, #FF9635, #007991); /* Gradient background */
            margin-top: 100px;
        }
        h1 {
            text-align: center;
            color: #000;
            font-weight: 700;
            margin-top: 100px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            border-top: 2px solid #4CAF50;
            border-bottom: 2px solid #4CAF50;
            background-color: #f2f2f2;
            font-weight: 700;
        }
        td {
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }
        td:nth-child(2) {
            text-align: center; /* Center the "Interview Chance" column */
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .footer-left {
            float: left;
        }
        .footer-right {
            float: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Evaluation Report</h1>
        <table>
            <thead>
                <tr>
                    <th>School</th>
                    <th>Interview Chance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['school']); ?></td>
                        <td><?php echo htmlspecialchars($item['chance']); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="footer">
            <div class="footer-left">Copyright © <?php echo date('Y'); ?> DentStats.com | All Rights Reserved</div>
            <div class="footer-right">Powered by PremiumVortex.com</div>
        </div>
    </div>
</body>
</html>