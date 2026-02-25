<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Overview</title>

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
        <h1>Analytics Overview</h1>
        <p>Understanding Descriptive, Predictive, and Prescriptive Analytics</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Business Analytics</h2>

        <p class="discussion-intro">
            Analytics is the process of examining data to discover patterns, trends, and insights that
            support effective decision-making. Organizations use analytics to understand past
            performance, predict future outcomes, and determine the best actions to take.
        </p>

        <hr>

        <h4>What is Analytics?</h4>
        <p>
            Analytics transforms raw data into meaningful information that helps answer key business
            questions such as what happened, what is likely to happen, and what should be done next.
            As analytics maturity increases, so does its value to decision-makers.
        </p>

        <hr>

        <h4>Descriptive Analytics</h4>
        <p>
            <b>Descriptive analytics</b> focuses on summarizing historical data to understand past
            performance. It answers the question: <i>"What happened?"</i>
        </p>

        <ul>
            <li>Uses historical data</li>
            <li>Provides reports and dashboards</li>
            <li>Identifies trends and patterns</li>
        </ul>

        <p><b>Example:</b> Monthly enrollment reports showing the number of students per course.</p>

        <hr>

        <h4>Predictive Analytics</h4>
        <p>
            <b>Predictive analytics</b> uses historical and current data along with statistical models
            to forecast future outcomes. It answers the question: <i>"What is likely to happen?"</i>
        </p>

        <ul>
            <li>Uses trend analysis and forecasting</li>
            <li>Applies statistical and machine learning models</li>
            <li>Estimates probabilities of future events</li>
        </ul>

        <p><b>Example:</b> Predicting student dropout risk based on attendance and grades.</p>

        <hr>

        <h4>Prescriptive Analytics</h4>
        <p>
            <b>Prescriptive analytics</b> recommends actions by analyzing data and predicted outcomes.
            It answers the question: <i>"What should we do?"</i>
        </p>

        <ul>
            <li>Uses optimization and simulation</li>
            <li>Builds on descriptive and predictive analytics</li>
            <li>Provides decision recommendations</li>
        </ul>

        <p><b>Example:</b> Recommending additional course offerings to avoid enrollment decline.</p>

        <hr>

        <h4>Comparison of Analytics Types</h4>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Analytics Type</th>
                    <th>Main Question</th>
                    <th>Focus</th>
                    <th>Output</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Descriptive</td>
                    <td>What happened?</td>
                    <td>Past data</td>
                    <td>Reports and dashboards</td>
                </tr>
                <tr>
                    <td>Predictive</td>
                    <td>What will happen?</td>
                    <td>Future trends</td>
                    <td>Forecasts and probabilities</td>
                </tr>
                <tr>
                    <td>Prescriptive</td>
                    <td>What should we do?</td>
                    <td>Decision optimization</td>
                    <td>Recommended actions</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>Analytics in Practice</h4>
        <p>
            In real-world systems, descriptive analytics provides insights into past performance,
            predictive analytics forecasts future outcomes, and prescriptive analytics guides
            decision-making. Together, they enable data-driven strategies and smarter planning.
        </p>

        <a class="btn alert-primary btn-block mb-3" href="<?= base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/BI_book.pdf')) . '#page=65' ?>" target="_blank"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Business Intelligence, Analytics, Data Science, and AI A Managerial Perspective (5th Edition) </a>

    </div>

        <?php $this->load->view('web_to_image'); ?>


</body>

</html>