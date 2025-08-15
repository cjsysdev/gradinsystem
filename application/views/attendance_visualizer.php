<!DOCTYPE html>
<html>

<head>
    <title>Attendance Visualizer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .attendance-table th {
            background: #f2f2f2;
        }

        .present {
            background: #d4edda;
        }

        .absent {
            background: #f8d7da;
        }
    </style>
</head>

<body>
    <h2>Attendance Visualizer</h2>
    <p><b>Date:</b> <?= date('Y-m-d') ?></p>
    <p><b>Section:</b> <?= htmlspecialchars($class['section'] ?? '') ?></p>
    <p><b>Schedule:</b> <?= htmlspecialchars($class['time_start'] ?? '') ?> - <?= htmlspecialchars($class['time_end'] ?? '') ?> (<?= htmlspecialchars($class['time_start'] ?? '') ?>)</p>

    <?php if (!isset($class) || !$class): ?>
        <form method="post">
            <label for="schedule_id"><b>Select Class Schedule:</b></label>
            <select name="schedule_id" id="schedule_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($class_schedules as $sched): ?>
                    <option value="<?= $sched['schedule_id'] ?>">
                        <?= htmlspecialchars($sched['section']) ?> | <?= htmlspecialchars($sched['time_start']) ?> <?= htmlspecialchars($sched['day']) ?> <?= htmlspecialchars($sched['time_start']) ?>-<?= htmlspecialchars($sched['time_end']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View Attendance</button>
        </form>
        <?php return; ?>
    <?php endif; ?>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>ID No.</th>
                <th>Student Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($record)): ?>
                <?php foreach ($record as $student): ?>
                    <tr class="<?= $student['status'] === 'present' ? 'present' : 'absent' ?>">
                        <td><?= htmlspecialchars($student['student_id']) ?></td>
                        <td><?= htmlspecialchars($student['lastname']) ?>, <?= htmlspecialchars($student['firstname']) ?></td>
                        <td><?= ucfirst($student['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center;">No attendance records found for today.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>