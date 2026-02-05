<?php
// CC105 - Overview of Databases and RDBMS
// Web-based discussion format similar to 105a_data.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CC105 | Overview of Databases and RDBMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">


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
        <h1>Overview of Databases and RDBMS</h1>
        <p>Understand the fundamentals of databases and relational database management systems.</p>
    </header>

    <div class="content my-5">

        <!-- Section 1 -->
        <div class="discussion-card">
            <h3 class="section-title">Scenario</h3>
            <p>
                
A public library wants to manage its collection and membership more efficiently. The system should track books, authors, and genres. Each book can have multiple authors, and each author can write multiple books. Members can borrow multiple books, but each book can only be borrowed by one member at a time. The library also needs to track borrowing dates, due dates, and fines for late returns. Design a database that supports book inventory, member records, borrowing history, and fine calculation.

            </p>
        </div>

    </div>

</body>

</html>