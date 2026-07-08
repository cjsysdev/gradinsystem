<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Natural Language Processing (NLP) with Orange Data Mining</title>

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
        <h1>Natural Language Processing (NLP) with Orange Data Mining</h1>
        <p>Turning text (reviews, comments, messages) into meaningful insights — no heavy coding required</p>
    </header>

    <div class="container">

        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Define NLP and explain why text needs preprocessing.</li>
                <li>Identify common NLP tasks: sentiment analysis, topic discovery, similarity, and classification.</li>
                <li>Build an end-to-end NLP workflow in <strong>Orange Data Mining</strong> using widgets.</li>
                <li>Interpret results using visualizations (Word Cloud, Topic Modeling, Confusion Matrix).</li>
                <li>Perform a mini-demonstration using a small dataset of customer reviews.</li>
            </ul>
        </div>

        <div class="section">
            <h2>What is NLP?</h2>
            <p>
                <strong>Natural Language Processing (NLP)</strong> is a field of AI that helps computers understand,
                analyze, and work with human language (text or speech). In real life, NLP powers:
            </p>
            <ul>
                <li><strong>Sentiment analysis</strong> (positive/negative reviews)</li>
                <li><strong>Spam detection</strong> (filtering unwanted messages)</li>
                <li><strong>Chatbots</strong> (customer support assistants)</li>
                <li><strong>Topic discovery</strong> (what people talk about most)</li>
                <li><strong>Text similarity</strong> (recommendations / matching)</li>
            </ul>
        </div>

        <div class="section">
            <h2>Why Text Needs Preprocessing</h2>
            <p>
                Computers don’t understand text the way humans do. They need text converted into numbers.
                But before that, we clean and standardize it so models can learn patterns.
            </p>

            <div class="row">
                <div class="col-md-6">
                    <h3>Common Problems in Raw Text</h3>
                    <ul>
                        <li>Different letter cases: <code>"Good"</code> vs <code>"good"</code></li>
                        <li>Punctuation/noise: <code>"good!!!"</code></li>
                        <li>Stopwords: <code>"the", "is", "and"</code></li>
                        <li>Word variants: <code>"running"</code>, <code>"runs"</code>, <code>"run"</code></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3>Common NLP Preprocessing Steps</h3>
                    <ul>
                        <li><strong>Tokenization</strong> — split text into words</li>
                        <li><strong>Lowercasing</strong> — standardize casing</li>
                        <li><strong>Stopword removal</strong> — remove common fillers</li>
                        <li><strong>Lemmatization/Stemming</strong> — reduce words to root form</li>
                        <li><strong>Vectorization</strong> — transform text into numeric features (e.g., TF-IDF)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Orange Data Mining (Overview)</h2>
            <p>
                <strong>Orange</strong> is a visual data mining tool where you build workflows by connecting
                widgets (blocks). For NLP, Orange provides a <strong>Text add-on</strong> with widgets for
                preprocessing, sentiment analysis, topic modeling, and classification.
            </p>

            <div class="alert alert-info">
                <strong>Tip:</strong> In Orange, NLP typically follows this pattern:<br>
                <strong>Data → Text Preprocess → Transform (TF-IDF) → Model / Visualization → Evaluation</strong>
            </div>
        </div>

        <div class="section">
            <h2>Demonstration Dataset (Sample Reviews)</h2>
            <p>
                Use this sample dataset (copy into a CSV file) for your classroom demo.
                It contains short customer reviews with sentiment labels.
            </p>

            <pre><code class="language-csv">id,review,sentiment
1,"Fast delivery and excellent packaging. Very satisfied!",positive
2,"Product arrived broken. Poor quality.",negative
3,"Customer service was helpful and fixed my issue quickly.",positive
4,"Not worth the price. Disappointed.",negative
5,"Great value for money! Will buy again.",positive
6,"Late delivery and the item is different from the photo.",negative
7,"Easy to use and works as expected.",positive
8,"Stopped working after two days. Waste of money.",negative
</code></pre>

            <div class="alert alert-warning">
                <strong>Note:</strong> You can also replace this with real-world data like Shopee/Lazada reviews,
                Facebook comments, or student feedback — as long as you have a <code>review</code> column.
            </div>
        </div>

        <div class="section">
            <h2>Hands-On Demo: NLP Workflow in Orange</h2>

            <h3>Step A — Install the Text Add-on</h3>
            <ol>
                <li>Open <strong>Orange</strong></li>
                <li>Go to <strong>Options → Add-ons</strong></li>
                <li>Search for <strong>Text</strong> and install <strong>Orange3-Text</strong></li>
                <li>Restart Orange</li>
            </ol>

            <hr>

            <h3>Step B — Load the Dataset</h3>
            <ol>
                <li>Create a CSV file: <code>reviews.csv</code> using the sample above.</li>
                <li>In Orange Canvas, add <strong>File</strong> widget.</li>
                <li>Open <code>reviews.csv</code>.</li>
                <li>Check that <code>review</code> is recognized as <strong>Text</strong> and <code>sentiment</code> as
                    <strong>Target</strong> (class label).</li>
            </ol>

            <hr>

            <h3>Step C — Build the Text Preprocessing Pipeline</h3>
            <p>Add and connect these widgets in order:</p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Widget</th>
                            <th>Purpose</th>
                            <th>Suggested Settings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>File</strong></td>
                            <td>Load dataset</td>
                            <td>Select CSV</td>
                        </tr>
                        <tr>
                            <td><strong>Corpus</strong> (Text)</td>
                            <td>Ensure text is treated as a corpus</td>
                            <td>Select <code>review</code> as text feature</td>
                        </tr>
                        <tr>
                            <td><strong>Preprocess Text</strong></td>
                            <td>Clean and normalize text</td>
                            <td>Lowercase, Remove stopwords (English), Tokenize, Lemmatize (optional)</td>
                        </tr>
                        <tr>
                            <td><strong>Bag of Words</strong> / <strong>TF-IDF</strong></td>
                            <td>Convert text into numeric features</td>
                            <td>Use <strong>TF-IDF</strong> for better weighting</td>
                        </tr>
                        <tr>
                            <td><strong>Word Cloud</strong></td>
                            <td>See frequent words visually</td>
                            <td>Compare by class: positive vs negative</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-success">
                <strong>Checkpoint:</strong> If your TF-IDF output has many features (words), your preprocessing is working.
            </div>
        </div>

        <!-- <div class="section">
            <h2>Demo 1: Sentiment Classification (Supervised)</h2>
            <p>
                Here, we train a model using the labeled column <code>sentiment</code>.
                We want the model to learn patterns that predict if a review is positive or negative.
            </p>

            <h3>Recommended Workflow</h3>
            <p class="mb-2"><strong>TF-IDF → Learners → Test & Score → Confusion Matrix</strong></p>

            <div class="row">
                <div class="col-md-6">
                    <h3>Add These Widgets</h3>
                    <ul>
                        <li><strong>Logistic Regression</strong></li>
                        <li><strong>Naive Bayes</strong> (common for text)</li>
                        <li><strong>SVM</strong> (optional)</li>
                        <li><strong>Test & Score</strong> (Cross-validation)</li>
                        <li><strong>Confusion Matrix</strong></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3>What to Observe</h3>
                    <ul>
                        <li><strong>Accuracy</strong>: overall correctness</li>
                        <li><strong>Precision/Recall</strong>: important when classes are imbalanced</li>
                        <li><strong>Confusion Matrix</strong>: which class is mistaken for which</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Teaching Tip:</strong> Ask students why Naive Bayes often performs surprisingly well on text data.
                (Answer: it works well with word-frequency features under independence assumptions.)
            </div>
        </div>

        <div class="section">
            <h2>Demo 2: Topic Modeling (Unsupervised)</h2>
            <p>
                Topic modeling helps discover hidden themes in a set of documents without labels.
                Example: "delivery issues", "quality problems", "value for money".
            </p>

            <h3>Recommended Workflow</h3>
            <p class="mb-2"><strong>Preprocess Text → Topic Modeling → Topic Visualization</strong></p>

            <ol>
                <li>Add <strong>Topic Modeling</strong> widget (e.g., LDA).</li>
                <li>Set number of topics (start with <strong>3</strong>).</li>
                <li>View top keywords per topic.</li>
                <li>Interpret and label topics in human terms.</li>
            </ol>

            <div class="alert alert-warning">
                <strong>Note:</strong> Topic modeling works better with more documents (50+). For class demo, it still shows the idea,
                but results become clearer with larger datasets.
            </div>
        </div>

        <div class="section">
            <h2>Demo 3: Document Similarity (Text Matching)</h2>
            <p>
                Text similarity finds which reviews are most alike. This is useful for:
                detecting duplicate complaints, clustering feedback, or recommending related items.
            </p>

            <h3>Recommended Workflow</h3>
            <p class="mb-2"><strong>TF-IDF → Distances → MDS / Hierarchical Clustering</strong></p>

            <ul>
                <li><strong>Distances</strong> widget: use cosine distance for text</li>
                <li><strong>MDS</strong>: visualize documents in 2D space</li>
                <li><strong>Hierarchical Clustering</strong>: group similar reviews</li>
            </ul>

            <div class="alert alert-success">
                <strong>Quick Insight:</strong> Negative reviews often cluster together because they share complaint keywords
                (e.g., "broken", "late", "waste").
            </div>
        </div>

        <div class="section">
            <h2>Mini Activity</h2>
            <p><strong>Task:</strong> Students will build an Orange NLP pipeline and answer analysis questions.</p>

            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="card-title">Student Instructions</h3>
                    <ol>
                        <li>Load the dataset in Orange using <strong>File</strong>.</li>
                        <li>Preprocess text using <strong>Preprocess Text</strong>.</li>
                        <li>Transform using <strong>TF-IDF</strong>.</li>
                        <li>Train at least <strong>two models</strong>: Naive Bayes and Logistic Regression.</li>
                        <li>Evaluate using <strong>Test & Score</strong> and interpret the <strong>Confusion Matrix</strong>.</li>
                        <li>Generate a <strong>Word Cloud</strong> and compare positive vs negative keywords.</li>
                    </ol>
                </div>
            </div>

            <h3>Guide Questions (Submit Answers)</h3>
            <ol>
                <li>Which model performed better? Provide the accuracy score.</li>
                <li>List the top 5 keywords that appear in positive reviews.</li>
                <li>List the top 5 keywords that appear in negative reviews.</li>
                <li>Give one example of a review your model misclassified (if any). Why do you think it happened?</li>
                <li>If you remove stopwords vs keep stopwords, does performance change?</li>
            </ol>
        </div> -->

        <div class="section">
            <h2>Common Issues and Quick Fixes</h2>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Issue</th>
                            <th>Cause</th>
                            <th>Fix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Text column not detected</td>
                            <td>Orange treats it as string/category</td>
                            <td>Use <strong>Corpus</strong> widget and select the text field</td>
                        </tr>
                        <tr>
                            <td>Low accuracy</td>
                            <td>Too few samples / noisy text</td>
                            <td>Add more documents and improve preprocessing</td>
                        </tr>
                        <tr>
                            <td>Topic modeling looks random</td>
                            <td>Dataset is too small</td>
                            <td>Use 50+ documents and reduce topics to 3–5</td>
                        </tr>
                        <tr>
                            <td>Word Cloud is empty</td>
                            <td>Stopwords removed too aggressively</td>
                            <td>Adjust stopword list and keep frequent terms</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>Wrap-Up</h2>
            <p>
                NLP is about converting human language into structured signals that models and analytics can use.
                With Orange Data Mining, you can rapidly prototype NLP workflows using visual blocks — great for learning
                and teaching core concepts like preprocessing, TF-IDF, classification, topics, and similarity.
            </p>

            <div class="alert alert-info">
                <strong>Next Step:</strong> Try using a larger dataset (100+ reviews) and compare results between
                Naive Bayes, Logistic Regression, and SVM.
            </div>
        </div>

        <!-- <div class="section">
            <h2>Optional Extension (Challenge)</h2>
            <p><strong>Challenge:</strong> Replace the dataset with real student feedback (anonymous) and:</p>
            <ul>
                <li>Discover top concerns using Topic Modeling</li>
                <li>Cluster feedback into groups</li>
                <li>Present recommendations based on the findings</li>
            </ul>
        </div> -->

        <footer class="mt-5">
            <p class="text-center text-muted mb-0">
                End of Discussion — NLP with Orange Data Mining
            </p>
        </footer>

    </div>

</body>

</html>
