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

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            background: #ffffff;
            margin: auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2,
        h3 {
            color: #2c3e50;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .info input {
            width: 100%;
            padding: 6px;
        }

        /* table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table th,
        table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #e9ecef;
        } */

        textarea {
            width: 100%;
            min-height: 80px;
            padding: 8px;
            margin-top: 5px;
        }

        .section {
            margin-top: 35px;
        }

        .erd-box {
            border: 2px dashed #999;
            height: 250px;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
            color: #777;
        }

        pre {
            background-color: #f1f3f5;
            padding: 15px;
            overflow-x: auto;
        }

        .bonus {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 5px solid #0d6efd;
            margin-top: 20px;
        }
    </style>
</head>


<body>

    <div class="container">

        <h1>Database Design Worksheet</h1>
        <h3 style="text-align:center;">Normalization → ERD → SQL</h3>

        <!-- <div class="info">
            <div>
                <label>Name</label>
                <input type="text">
            </div>
            <div>
                <label>Course / Section</label>
                <input type="text">
            </div>
            <div>
                <label>Date</label>
                <input type="date">
            </div>
        </div> -->

        <hr>

        <!-- <div class="section">
            <h2>Scenario: Hospital Outpatient System</h2>

            <p>
                A hospital outpatient department stores all patient visit records in one spreadsheet.
                This has caused redundancy and data inconsistencies.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>VisitID</th>
                        <th>PatientID</th>
                        <th>PatientName</th>
                        <th>DoctorID</th>
                        <th>DoctorName</th>
                        <th>DoctorSpecialty</th>
                        <th>Clinic</th>
                        <th>VisitDate</th>
                        <th>Diagnosis</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>P001</td>
                        <td>Maria Santos</td>
                        <td>D01</td>
                        <td>Dr. Cruz</td>
                        <td>Cardiology</td>
                        <td>Heart Clinic</td>
                        <td>2025-01-10</td>
                        <td>Hypertension</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>P002</td>
                        <td>Juan Dela Rosa</td>
                        <td>D02</td>
                        <td>Dr. Lim</td>
                        <td>Pulmonology</td>
                        <td>Lung Clinic</td>
                        <td>2025-01-11</td>
                        <td>Asthma</td>
                    </tr>
                </table>
            </div>
        </div> -->

        <!-- <div class="section">
            <h2>Scenario: Public Library Borrowing System</h2>

            <p>
                A public library keeps all book borrowing transactions in a single table.
                This setup results in repeated book and member information.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>BorrowID</th>
                        <th>MemberID</th>
                        <th>MemberName</th>
                        <th>BookID</th>
                        <th>BookTitle</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>BorrowDate</th>
                        <th>ReturnDate</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>M001</td>
                        <td>Carla Diaz</td>
                        <td>B101</td>
                        <td>Clean Code</td>
                        <td>Robert Martin</td>
                        <td>Programming</td>
                        <td>2025-01-05</td>
                        <td>2025-01-12</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>M002</td>
                        <td>Leo Ramos</td>
                        <td>B102</td>
                        <td>Database Design</td>
                        <td>Elmasri</td>
                        <td>IT</td>
                        <td>2025-01-06</td>
                        <td>2025-01-13</td>
                    </tr>
                </table>
            </div>
        </div> -->

        <!-- <div class="section">
            <h2>Scenario: Retail Store Sales System</h2>

            <p>
                A small retail store records all sales transactions in one worksheet.
                Customer and product details are repeated, leading to data redundancy.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>SaleID</th>
                        <th>CustomerID</th>
                        <th>CustomerName</th>
                        <th>ProductID</th>
                        <th>ProductName</th>
                        <th>Category</th>
                        <th>UnitPrice</th>
                        <th>Quantity</th>
                        <th>SaleDate</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>C001</td>
                        <td>Paolo Reyes</td>
                        <td>P100</td>
                        <td>USB Flash Drive</td>
                        <td>Accessories</td>
                        <td>450</td>
                        <td>2</td>
                        <td>2025-01-08</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>C002</td>
                        <td>Nina Cruz</td>
                        <td>P101</td>
                        <td>Wireless Mouse</td>
                        <td>Accessories</td>
                        <td>850</td>
                        <td>1</td>
                        <td>2025-01-09</td>
                    </tr>
                </table>
            </div>
        </div> -->

        <!-- <div class="section">
            <h2>Scenario: Hotel Room Booking System</h2>

            <p>
                A small hotel records all room bookings in a single spreadsheet.
                Guest and room information are repeatedly stored, making updates
                and record management difficult.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>BookingID</th>
                        <th>GuestID</th>
                        <th>GuestName</th>
                        <th>RoomNumber</th>
                        <th>RoomType</th>
                        <th>RoomRate</th>
                        <th>CheckInDate</th>
                        <th>CheckOutDate</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>G001</td>
                        <td>Anna Reyes</td>
                        <td>101</td>
                        <td>Deluxe</td>
                        <td>3500</td>
                        <td>2025-02-01</td>
                        <td>2025-02-03</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>G002</td>
                        <td>Michael Tan</td>
                        <td>102</td>
                        <td>Standard</td>
                        <td>2500</td>
                        <td>2025-02-02</td>
                        <td>2025-02-05</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>Scenario: Online Food Delivery Order System</h2>

            <p>
                An online food delivery service stores all customer orders in one table.
                Customer, restaurant, and food item details are repeated for every order,
                causing data redundancy and update issues.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>OrderID</th>
                        <th>CustomerID</th>
                        <th>CustomerName</th>
                        <th>RestaurantID</th>
                        <th>RestaurantName</th>
                        <th>FoodItem</th>
                        <th>Price</th>
                        <th>OrderDate</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>C101</td>
                        <td>James Cruz</td>
                        <td>R01</td>
                        <td>Burger House</td>
                        <td>Cheeseburger</td>
                        <td>180</td>
                        <td>2025-02-10</td>
                        <td>Delivered</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>C102</td>
                        <td>Lisa Gomez</td>
                        <td>R02</td>
                        <td>Pasta Corner</td>
                        <td>Carbonara</td>
                        <td>220</td>
                        <td>2025-02-11</td>
                        <td>Preparing</td>
                    </tr>
                </table>
            </div>
        </div> -->

        <!-- <div class="section">
            <h2>Scenario: Retail Store Sales System</h2>

            <p>
                A small retail store sells computer accessories and records all sales
                transactions in a single worksheet. Each row represents one product sold
                to a customer during a transaction.
            </p>

            <p>
                However, the store owner noticed several problems:
            <ul>
                <li>Customer information is repeated every time the customer makes a purchase.</li>
                <li>Product details such as name, category, and unit price are duplicated.</li>
                <li>If a product price changes, old records may become inconsistent.</li>
                <li>It is difficult to generate accurate reports such as total sales per customer or product.</li>
            </ul>
            </p>

            <p>
                The store wants to redesign its system using a properly structured
                relational database to reduce redundancy and improve data consistency.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>SaleID</th>
                        <th>CustomerID</th>
                        <th>CustomerName</th>
                        <th>ProductID</th>
                        <th>ProductName</th>
                        <th>Category</th>
                        <th>UnitPrice</th>
                        <th>Quantity</th>
                        <th>SaleDate</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>C001</td>
                        <td>Paolo Reyes</td>
                        <td>P100</td>
                        <td>USB Flash Drive</td>
                        <td>Accessories</td>
                        <td>450</td>
                        <td>2</td>
                        <td>2025-01-08</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>C002</td>
                        <td>Nina Cruz</td>
                        <td>P101</td>
                        <td>Wireless Mouse</td>
                        <td>Accessories</td>
                        <td>850</td>
                        <td>1</td>
                        <td>2025-01-09</td>
                    </tr>
                </table>
            </div>
        </div> -->

        <div class="section">
            <h2>Scenario: School Library Borrowing System</h2>

            <p>
                A school library keeps track of books borrowed by students using a single spreadsheet.
                Each row records one borrowing transaction.
            </p>

            <p>
                The librarian noticed several problems:
            <ul>
                <li>Student information is repeated every time a student borrows a book.</li>
                <li>Book details such as title and author are duplicated.</li>
                <li>If a book title changes or is corrected, multiple rows must be updated.</li>
                <li>It is difficult to generate reports such as most borrowed books or student borrowing history.</li>
            </ul>
            </p>

            <p>
                The school wants to redesign the system using a properly structured
                relational database to reduce redundancy and improve data consistency.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>BorrowID</th>
                        <th>StudentID</th>
                        <th>StudentName</th>
                        <th>GradeLevel</th>
                        <th>BookID</th>
                        <th>BookTitle</th>
                        <th>Author</th>
                        <th>BorrowDate</th>
                        <th>ReturnDate</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>S001</td>
                        <td>Maria Santos</td>
                        <td>Grade 10</td>
                        <td>B100</td>
                        <td>Introduction to Programming</td>
                        <td>J. Cruz</td>
                        <td>2025-01-05</td>
                        <td>2025-01-12</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>S002</td>
                        <td>Kevin Lim</td>
                        <td>Grade 9</td>
                        <td>B101</td>
                        <td>Basic Algebra</td>
                        <td>L. Reyes</td>
                        <td>2025-01-07</td>
                        <td>2025-01-14</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h3>Activity Tasks</h3>
            <ol>
                <li>Identify the possible <strong>entities</strong> in the scenario.</li>
                <li>Determine the <strong>primary key</strong> for each entity.</li>
                <li>Assign appropriate <strong>attributes</strong> to each entity.</li>
                <li>Draw the <strong>ERD</strong> showing relationships between entities.</li>
                <li>Create the SQL database and tables with:
                    <ul>
                        <li>Primary Keys</li>
                        <li>Foreign Keys</li>
                        <li>Proper data types</li>
                    </ul>
                </li>
            </ol>
        </div>


        <!-- <div class="section">
            <h3>Activity Tasks</h3>
            <ol>
                <li>Identify the possible <strong>entities</strong> in the scenario.</li>
                <li>Determine the <strong>primary key</strong> for each entity.</li>
                <li>Identify the <strong>attributes</strong> that belong to each entity.</li>
                <li>Draw the <strong>ERD</strong> showing relationships between entities.</li>
                <li>Create the SQL database and tables using:
                    <ul>
                        <li>Primary Keys</li>
                        <li>Foreign Keys</li>
                        <li>Appropriate data types</li>
                    </ul>
                </li>
                <li>Insert at least 3 sample records per table.</li>
                <li>Create one query that shows the total sales per product.</li>
            </ol>
        </div> -->




        <!-- <div class="section">
            <h2>✏️ Part 1 – Identify the Problems</h2>

            <h3>A. Repeated Data</h3>
            <textarea placeholder="List repeated data here..."></textarea>

            <h3>B. Data Anomalies</h3>

            <p><strong>1. Update anomaly:</strong></p>
            <textarea></textarea>

            <p><strong>2. Insertion anomaly:</strong></p>
            <textarea></textarea>

            <p><strong>3. Deletion anomaly:</strong></p>
            <textarea></textarea>
        </div> -->

        <div class="section">
            <h2>Normalization</h2>

            <h3>A. Identify the Entities</h3>
            <textarea placeholder="List entities here..."></textarea>

            <!-- <h3>B. Normalized Tables</h3>

            <h4>PATIENT</h4>
            <textarea></textarea>

            <h4>DOCTOR</h4>
            <textarea></textarea>

            <h4>VISIT</h4>
            <textarea></textarea>

            <h3>C. Explanation</h3>
            <textarea placeholder="Why should doctor details not be stored in Visit table?"></textarea> -->
        </div>

        <div class="section">
            <h2>ERD Design</h2>

            <p>Draw your ERD below (use paper or diagram tool if required):</p>
            <div class="erd-box">
                ERD DRAWING AREA
            </div>

            <textarea placeholder="Describe relationships and cardinality here..."></textarea>
        </div>

        <div class="section">
            <h2>SQL CREATE TABLE</h2>

            <!-- <h4>PATIENT</h4>
            <pre>
CREATE TABLE Patient (
    ...
);
</pre>
            <textarea></textarea>

            <h4>DOCTOR</h4>
            <pre>
CREATE TABLE Doctor (
    ...
);
</pre>
            <textarea></textarea>

            <h4>VISIT</h4>
            <pre>
CREATE TABLE Visit (
    ...
);
</pre> -->
            <textarea></textarea>
        </div>

        <!-- <div class="section">
            <h2>Reflection</h2>
            <textarea placeholder="What did you learn from this activity?"></textarea>
        </div> -->

        <!-- <div class="bonus">
            <h3>⭐ Bonus Challenge</h3>
            <p>
                If a visit can have multiple doctors:
            </p>
            <textarea placeholder="What table is required? What are the foreign keys?"></textarea>
        </div> -->

    </div>

</body>

</html>