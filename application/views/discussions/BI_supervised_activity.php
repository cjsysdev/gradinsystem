<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Supervised Learning: Learning Patterns from Music Notes</title>

    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css'); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-light.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        hljs.highlightAll();
    </script>

    <style>
        .note-symbol {
            font-size: 60px;
            text-align: center;
            margin: 15px 0;
        }

        .note-card {
            border: 2px dashed #ccc;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
        }

        .answer-box {
            display: none;
            margin-top: 10px;
        }
    </style>

</head>

<body>

    <header class="bg-light py-4 border-bottom">
        <div class="container">
            <h1>Supervised Learning Using Music Notes</h1>
            <p class="text-muted">Understanding Machine Learning Through Pattern Recognition</p>
        </div>
    </header>

    <div class="content mt-4 mb-5">
        <div class="container">

            <!-- Learning Objectives -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Learning Objectives</h2>

                <ul>
                    <li>Understand the concept of <strong>Supervised Learning</strong></li>
                    <li>Recognize how machines learn from <strong>labeled data</strong></li>
                    <li>Identify patterns using musical notes</li>
                    <li>Predict labels for new examples</li>
                </ul>

            </div>

            <!-- Introduction -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Introduction</h2>

                <p>
                    Supervised Learning is a type of machine learning where a model learns from
                    <strong>examples that already have correct answers</strong>.
                </p>

                <p>
                    These examples are called <strong>training data</strong>.
                    After learning patterns from the training data,
                    the model predicts answers for <strong>new unseen data</strong>.
                </p>

                <p>
                    In this activity, we will simulate this process using
                    <strong>musical notes and their durations</strong>.
                </p>

            </div>

            <!-- Training Data -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Training Data (Learning Phase)</h2>

                <p>Observe the labeled musical notes below.</p>

                <div class="row">

                    <div class="col-md-4">
                        <div class="note-card">
                            <div class="note-symbol">𝅝</div>
                            <p><strong>Whole Note</strong></p>
                            <p>Duration: 4 beats</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="note-card">
                            <div class="note-symbol">𝅗𝅥</div>
                            <p><strong>Half Note</strong></p>
                            <p>Duration: 2 beats</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="note-card">
                            <div class="note-symbol">♩</div>
                            <p><strong>Quarter Note</strong></p>
                            <p>Duration: 1 beat</p>
                        </div>
                    </div>

                </div>

                <div class="alert alert-info">
                    Think like an AI model:
                    What pattern do you notice between the note symbol and its duration?
                </div>

            </div>

            <!-- Activity 1 -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Interactive Activity 1</h2>

                <p>Predict the type of note.</p>

                <div class="note-symbol">𝅗𝅥</div>

                <p><strong>Question:</strong> What type of note is this?</p>

                <button class="btn btn-primary" onclick="showAnswer1()">Show Answer</button>

                <div class="answer-box alert alert-success" id="answer1">
                    Correct Answer: <strong>Half Note (2 beats)</strong>
                </div>

            </div>

            <!-- Activity 2 -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Interactive Activity 2</h2>

                <p>Predict the duration.</p>

                <div class="note-symbol">♩</div>

                <p><strong>Question:</strong> How many beats does this note have?</p>

                <button class="btn btn-primary" onclick="showAnswer2()">Show Answer</button>

                <div class="answer-box alert alert-success" id="answer2">
                    Correct Answer: <strong>1 Beat (Quarter Note)</strong>
                </div>

            </div>

            <!-- Activity 3 -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Interactive Activity 3</h2>

                <p>Classify the note type.</p>

                <div class="note-symbol">𝅝</div>

                <p><strong>Question:</strong> What type of note is this?</p>

                <button class="btn btn-primary" onclick="showAnswer3()">Show Answer</button>

                <div class="answer-box alert alert-success" id="answer3">
                    Correct Answer: <strong>Whole Note (4 beats)</strong>
                </div>

            </div>

            

            <!-- Explanation -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>How This Demonstrates Supervised Learning</h2>

                <ul>
                    <li>The labeled notes represent the <strong>training dataset</strong></li>
                    <li>The note symbols act as <strong>features</strong></li>
                    <li>The note names and durations are the <strong>labels</strong></li>
                    <li>You used patterns to predict new answers</li>
                </ul>

                <pre><code>
Training Data → Model Learns Pattern → Predict New Data
</code></pre>

            </div>

            <!-- Reflection -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Reflection Questions</h2>

                <ol>
                    <li>How did you identify the correct note?</li>
                    <li>What visual patterns helped you?</li>
                    <li>What would happen if some training labels were incorrect?</li>
                    <li>How might computers learn patterns like this?</li>
                </ol>

            </div>

            <!-- Real World -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Real-World Applications</h2>

                <ul>
                    <li>Music transcription software</li>
                    <li>Speech recognition</li>
                    <li>Image recognition</li>
                    <li>Email spam filtering</li>
                    <li>Recommendation systems</li>
                </ul>

            </div>

            <!-- Conclusion -->
            <div class="section card shadow-sm p-4 mb-4">
                <h2>Conclusion</h2>

                <p>
                    Supervised learning works by studying labeled examples and learning patterns.
                    Once the pattern is learned, the system can classify or predict new data.
                </p>

                <p>
                    In this activity, you acted like a machine learning model by recognizing
                    patterns in musical notes.
                </p>

            </div>

        </div>
    </div>

    <script>
        function showAnswer1() {
            document.getElementById("answer1").style.display = "block";
        }

        function showAnswer2() {
            document.getElementById("answer2").style.display = "block";
        }

        function showAnswer3() {
            document.getElementById("answer3").style.display = "block";
        }
    </script>

</body>

</html>