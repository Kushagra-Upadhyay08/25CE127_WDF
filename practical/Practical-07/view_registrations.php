<?php
/**
 * StudentHub — View Registrations (Practical 7)
 * 
 * Displays stored registration records from CSV and JSON files.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations — StudentHub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; color: #222; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #1a1a2e; margin-bottom: 4px; }
        h2 { color: #16213e; border-bottom: 2px solid #00bcd4; display: inline-block; padding-bottom: 4px; margin-top:
 32px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 0.9rem; background: #fff; box-
shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; }
        thead { background: #1a1a2e; color: #fff; }
        th { padding: 12px 14px; text-align: left; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.
4px; }
        td { padding: 10px 14px; border-bottom: 1px solid #eee; }

        tbody tr:hover { background: #f5f8fa; }
        .empty { text-align: center; padding: 30px; color: #888; background: #fff; border-radius: 8px; }
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #00bcd4; color: #fff; 
text-decoration: none; border-radius: 6px; font-weight: 600; }
        .btn-back:hover { background: #0097a7; }
        .section-label { font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-top: 
16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>■ Registered Students</h1>
        <p>Records stored from the registration form via PHP processing.</p>
        <!-- CSV Records -->
        <h2>CSV Records</h2>
        <p class="section-label">Source: data/registrations.csv</p>
        <?php
        $csvFile = __DIR__ . '/data/registrations.csv';
        if (file_exists($csvFile)):
            $fp = fopen($csvFile, 'r');
            $header = fgetcsv($fp);
        ?>
        <table>
            <thead>
                <tr>
                    <?php foreach ($header as $col): ?>
                        <th><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $col))) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php while (($row = fgetcsv($fp)) !== false): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?= htmlspecialchars($cell) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php
            fclose($fp);
        else:
        ?>
            <div class="empty">No CSV records found. Submit the registration form first.</div>
        <?php endif; ?>
        <!-- JSON Records -->
        <h2>JSON Records</h2>
        <p class="section-label">Source: data/registrations.json</p>
        <?php
        $jsonFile = __DIR__ . '/data/registrations.json';
        if (file_exists($jsonFile)):
            $records = json_decode(file_get_contents($jsonFile), true);
            if (!empty($records)):
        ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <?php foreach (array_keys($records[0]) as $key): ?>
                        <th><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $i => $record): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <?php foreach ($record as $val): ?>
                        <td><?= htmlspecialchars($val) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
            <?php else: ?>
                <div class="empty">JSON file exists but contains no records.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty">No JSON records found. Submit the registration form first.</div>
        <?php endif; ?>
        <a href="../Practical-02/StudentHub/index.html" class="btn-back">← Back to Home</a>
    </div>
</body>
</html>

Practical 08
