<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLTP vs OLAP</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>

    <header>
        <h1>OLTP vs OLAP</h1>
        <p>Understanding Operational and Analytical Database Systems</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Database Systems</h2>

        <p class="discussion-intro">
            Database systems are designed to serve different purposes. Some focus on handling daily
            transactions efficiently, while others focus on analyzing large volumes of data to
            support decision-making. These two approaches are known as OLTP and OLAP.
        </p>

        <hr>

        <h4>What is OLTP?</h4>
        <p>
            <b>Online Transaction Processing (OLTP)</b> systems are designed to manage day-to-day
            operations of an organization. They handle a large number of short, fast transactions
            such as inserting, updating, or deleting records.
        </p>

        <ul>
            <li>Supports many concurrent users</li>
            <li>Processes frequent transactions</li>
            <li>Works with current, highly detailed data</li>
            <li>Emphasizes speed and accuracy</li>
        </ul>

        <p><b>Example systems:</b></p>
        <ul>
            <li>Banking systems (ATM withdrawals, deposits)</li>
            <li>Online shopping systems (orders, payments)</li>
            <li>Library management systems (borrowing and returning books)</li>
            <li>Student information systems (enrollment, grades)</li>
        </ul>

        <pre><code class="sql">
UPDATE accounts
SET balance = balance - 1000
WHERE account_id = 123;
        </code></pre>

        <hr>

        <h4>What is OLAP?</h4>
        <p>
            <b>Online Analytical Processing (OLAP)</b> systems are designed for data analysis and
            reporting. They help organizations analyze historical data to discover patterns,
            trends, and insights for better decision-making.
        </p>

        <ul>
            <li>Used by managers and analysts</li>
            <li>Handles large volumes of historical data</li>
            <li>Executes complex queries</li>
            <li>Primarily read-only operations</li>
        </ul>

        <p><b>Example systems:</b></p>
        <ul>
            <li>Sales data warehouses</li>
            <li>Business Intelligence (BI) dashboards</li>
            <li>Financial reporting systems</li>
            <li>Academic performance analytics systems</li>
        </ul>

        <pre><code class="sql">
SELECT year, SUM(total_sales)
FROM sales
GROUP BY year;
        </code></pre>

        <hr>

        <h4>Key Differences Between OLTP and OLAP</h4>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Feature</th>
                    <th>OLTP</th>
                    <th>OLAP</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Main Purpose</td>
                    <td>Run daily operations</td>
                    <td>Analyze data</td>
                </tr>
                <tr>
                    <td>Data Type</td>
                    <td>Current, detailed data</td>
                    <td>Historical, summarized data</td>
                </tr>
                <tr>
                    <td>Operations</td>
                    <td>INSERT, UPDATE, DELETE</td>
                    <td>SELECT, AGGREGATE</td>
                </tr>
                <tr>
                    <td>Query Complexity</td>
                    <td>Simple and short</td>
                    <td>Complex and long</td>
                </tr>
                <tr>
                    <td>Users</td>
                    <td>Clerks, customers, staff</td>
                    <td>Managers, analysts</td>
                </tr>
                <tr>
                    <td>Performance Focus</td>
                    <td>Transaction speed</td>
                    <td>Query depth and insight</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>OLTP and OLAP in Practice</h4>
        <p>
            In real-world systems, OLTP databases handle daily business transactions, while OLAP
            databases analyze accumulated data from these transactions. Together, they support both
            operational efficiency and strategic decision-making.
        </p>

        <a class="btn alert-primary btn-block mb-3" href="<?= base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/BI_book.pdf')) . '#page=62' ?>" target="_blank"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Business Intelligence, Analytics, Data Science, and AI A Managerial Perspective (5th Edition) </a>
    </div>

</body>

</html>