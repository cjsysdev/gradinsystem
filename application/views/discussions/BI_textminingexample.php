<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Intelligence Overview</title>

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

    <div class="content my-5">
        <div class="discussion-container">

            <h1 class="text-center mb-4">
                Real-World Applications of Text Analytics, Text Mining, and NLP
            </h1>

            <p>
                Text analytics, text mining, and natural language processing (NLP) are widely used in
                various industries to extract meaningful insights from unstructured text data.
                Below are common real-world applications that demonstrate how these technologies
                support decision-making and automation.
            </p>

            <!-- CUSTOMER REVIEWS -->
            <h3 class="section-title">1. Customer Reviews and Online Ratings</h3>
            <p>
                E-commerce platforms analyze customer reviews to understand user satisfaction
                and product performance.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP interprets customer-written text</li>
                    <li>Text analytics classifies reviews as positive, negative, or neutral</li>
                    <li>Insights are summarized for business decisions</li>
                </ul>
                <strong>Outcome:</strong> Faster understanding of customer feedback.
            </div>

            <!-- SOCIAL MEDIA -->
            <h3 class="section-title">2. Social Media Sentiment Analysis</h3>
            <p>
                Organizations monitor social media platforms to track public opinion about
                brands, products, or events.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP processes informal language, hashtags, and emojis</li>
                    <li>Text mining identifies trending topics</li>
                    <li>Text analytics summarizes overall sentiment</li>
                </ul>
                <strong>Outcome:</strong> Early detection of issues and trends.
            </div>

            <!-- CHATBOTS -->
            <h3 class="section-title">3. Chatbots and Virtual Assistants</h3>
            <p>
                Chatbots use NLP to communicate with users in natural language.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP identifies user intent</li>
                    <li>System generates relevant responses</li>
                </ul>
                <strong>Outcome:</strong> 24/7 automated customer support.
            </div>

            <!-- SPAM -->
            <h3 class="section-title">4. Spam Email Detection</h3>
            <p>
                Email providers automatically detect and filter spam messages.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>Text mining finds common spam patterns</li>
                    <li>NLP analyzes message structure and keywords</li>
                </ul>
                <strong>Outcome:</strong> Cleaner and safer inboxes.
            </div>

            <!-- DOCUMENT MANAGEMENT -->
            <h3 class="section-title">5. Document Search and Classification</h3>
            <p>
                Large organizations manage thousands of documents and reports using text-based systems.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP extracts keywords and meanings</li>
                    <li>Text mining groups documents by topic</li>
                </ul>
                <strong>Outcome:</strong> Faster document retrieval and organization.
            </div>

            <!-- HEALTHCARE -->
            <h3 class="section-title">6. Healthcare Text Analysis</h3>
            <p>
                Hospitals analyze doctors’ notes and medical records.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP recognizes medical terms and symptoms</li>
                    <li>Text mining identifies patterns across patient records</li>
                </ul>
                <strong>Outcome:</strong> Improved diagnosis and patient care.
            </div>

            <!-- FRAUD -->
            <h3 class="section-title">7. Fraud Detection in Banking</h3>
            <p>
                Financial institutions analyze transaction descriptions and customer reports.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>Text mining detects unusual activity patterns</li>
                    <li>Text analytics highlights suspicious behavior</li>
                </ul>
                <strong>Outcome:</strong> Reduced fraud and financial risk.
            </div>

            <!-- RECRUITMENT -->
            <h3 class="section-title">8. Resume Screening and Recruitment</h3>
            <p>
                Companies use automated systems to evaluate job applications.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP extracts skills and qualifications</li>
                    <li>Text analytics ranks candidates</li>
                </ul>
                <strong>Outcome:</strong> Faster and more efficient hiring.
            </div>

            <!-- SUMMARY -->
            <h3 class="section-title">9. Summary</h3>
            <p>
                Text analytics, text mining, and NLP are essential tools for transforming
                unstructured text into valuable insights. These technologies are widely applied
                across industries to automate processes, improve decisions, and enhance user experiences.
            </p>


            <h3 class="section-title">Student Activity: Real-World Text Analysis</h3>

            <p>
                In this activity, you will explore how <strong>Natural Language Processing (NLP)</strong>,
                <strong>Text Mining</strong>, and <strong>Text Analytics</strong> are used to analyze real-world text data.
                You will analyze customer reviews and generate insights based on your findings.
            </p>

            <h4>Instructions</h4>
            <ul>
                <li>Form a group of <strong>3–4 students</strong></li>
                <li>Read the sample customer reviews carefully</li>
                <li>Complete all tasks below</li>
                <li>Write your answers clearly and briefly</li>
            </ul>

            <hr>

            <h4>Scenario</h4>
            <p>
                You are helping an online store analyze customer feedback to improve product quality
                and customer service.
            </p>

            <div class="example-box">
                <strong>Customer Reviews:</strong>
                <ul>
                    <li>“The product quality is excellent and delivery was fast.”</li>
                    <li>“Customer service is very slow and unhelpful.”</li>
                    <li>“Affordable price but the packaging was damaged.”</li>
                    <li>“I love the design, but the battery life is poor.”</li>
                </ul>
            </div>

            <hr>

            <h4>Task 1: NLP – Understanding the Text</h4>
            <p>
                For each review, identify the <strong>important keywords</strong> and determine the
                <strong>overall sentiment</strong>.
            </p>

            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Review</th>
                        <th>Keywords</th>
                        <th>Sentiment<br>(Positive / Negative / Neutral)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Review 1</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Review 2</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Review 3</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Review 4</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <h4>Task 2: Text Mining – Finding Patterns</h4>
            <p>
                Analyze all reviews together and answer the following:
            </p>
            <ul>
                <li>What problems appear more than once?</li>
                <li>What positive features are mentioned?</li>
            </ul>

            <p><strong>Your Answers:</strong></p>
            <ul>
                <li>Repeated Problems: ______________________________</li>
                <li>Positive Features: ______________________________</li>
            </ul>

            <hr>

            <h4>Task 3: Text Analytics – Generating Insights</h4>
            <p>
                Based on your analysis, answer the questions below:
            </p>

            <ul>
                <li>What is the main customer complaint?</li>
                <li>What should the company improve?</li>
                <li>What should the company continue doing well?</li>
            </ul>

            <hr>

            <h4>Task 4: Identify the Concept Used</h4>
            <p>
                Match each activity to the correct concept.
            </p>

            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Activity</th>
                        <th>Concept</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Identifying keywords and sentiment</td>
                        <td>NLP</td>
                    </tr>
                    <tr>
                        <td>Finding repeated issues in reviews</td>
                        <td>Text Mining</td>
                    </tr>
                    <tr>
                        <td>Making business recommendations</td>
                        <td>Text Analytics</td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <h4>Reflection</h4>
            <ul>
                <li>Why is understanding text important before making decisions?</li>
                <li>How can text analytics help businesses improve?</li>
            </ul>

            <h4>What You Should Learn</h4>
            <ul>
                <li>How NLP helps computers understand human language</li>
                <li>How text mining discovers patterns in text data</li>
                <li>How text analytics supports decision-making</li>
            </ul>

        </div>
    </div>

    <?php $this->load->view('web_to_image'); ?>


</body>

</html>