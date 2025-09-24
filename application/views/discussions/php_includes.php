<?php $this->load->view('header'); ?>

<!-- Highlight.js CSS -->
<link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
<!-- Highlight.js JS -->
<script src="<?= base_url("assets/highlights/11.7.0-highlight.min.js") ?> "></script>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="mb-3" style="font-size:2.2rem;">Include Files</h1>
                    <hr>
                    <p>The include (or 
require) statement takes all the text/code/markup that exists in the specified file and copies it into
the file that uses the include statement.</p>
<p>Including files is very useful when you want to include the same PHP,
HTML, or text on multiple pages of a website.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">PHP include and require Statements</h3>
<p>It is possible to insert the content of one PHP file into another PHP file (before the 
server executes it), with the include or require statement.</p>
<p>The include and require statements are identical, except upon failure:</p>
<ul>
<li>require will produce a fatal error (E_COMPILE_ERROR) and stop the script</li>
<li>include will only produce a warning (E_WARNING) and the script will continue</li>
</ul>
<p>So, if you want the execution to go on and show users the output, even if the 
include file is missing, use the include statement. Otherwise, in case of FrameWork, CMS, or a 
complex PHP application coding, always use the require statement to include a key file to the 
flow of execution. This will help avoid compromising your application's security 
and integrity, just in-case one key file is accidentally missing.</p>
<p>Including files saves a lot of work. This means that 
you can create a standard header, footer, or menu file for all your web pages. 
Then, when the header needs to be updated, you can only 
update the header include file.</p>
<pre><code class="language-text">'include '
filename
';
or
require '
filename
';');
</code></pre>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">PHP include Examples</h3>
<p>Assume we have a standard footer file called "footer.php", that looks like this:</p>
<pre><code class="language-php">'&lt;?php
echo "<p>Copyright &copy; 1999-" . date("Y") . " W3Schools.com</p>";
&gt;
</code></pre>
<p>To include the footer file in a page, use the 
include statement:</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-php">'<html>
<body>
<h1>Welcome to my home page!</h1>
<p>Some text.</p>
<p>Some more text.</p>
&lt;?php include 'footer.php';&gt;
</body>
</html>
</code></pre>
<!-- Try it Yourself example -->
<p>Assume we have a standard menu file called "menu.php":</p>
<pre><code class="language-php">'&lt;?php
echo '<a href="/default.asp">Home</a> -
<a href="/html/default.asp">HTML Tutorial</a> -
<a href="/css/default.asp">CSS Tutorial</a> -
<a href="/js/default.asp">JavaScript Tutorial</a> -
<a href="default.asp">PHP Tutorial</a>';
&gt;');
</code></pre>
<p>All pages in the Web site should use this menu file. Here is how it can be done 
(we are using a <div> element so that the menu easily can be styled with CSS later):</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-php">'<html>
<body>
<div class="menu">
&lt;?php include 'menu.php';&gt;
</div>
<h1>Welcome to my home page!</h1>
<p>Some text.</p>
<p>Some more text.</p>
</body>
</html>');
</code></pre>
<!-- Try it Yourself example -->
<p>Assume we have a file called "vars.php", with some variables defined:</p>
<pre><code class="language-php">'&lt;?php
$color='red';
$car='BMW';
&gt;');
</code></pre>
<p>Then, if we include the "vars.php" file, the variables can be used in the calling file:</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-php">'<html>
<body>
<h1>Welcome to my home page!</h1>
&lt;?php include 'vars.php';
echo "I have a $color $car.";
&gt;
</body>
</html>');
</code></pre>
<!-- Try it Yourself example -->
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">PHP include vs. require</h3>
<p>The require statement is also used to include a file into the PHP code.</p>
<p>However, there is one big difference between include and require; when a 
file is included with the 
include statement and PHP cannot find it, the script 
will continue to execute:</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-php">'<html>
<body>
<h1>Welcome to my home page!</h1>
&lt;?php include 'noFileExists.php';
echo "I have a $color $car.";
&gt;
</body>
</html>');
</code></pre>
<!-- Try it Yourself example -->
<p>If we do the same example using the 
require statement, the 
echo statement will not be executed because the script execution dies after the 
require statement returned a fatal error:</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-php">'<html>
<body>
<h1>Welcome to my home page!</h1>
&lt;?php require 'noFileExists.php';
echo "I have a $color $car.";
&gt;
</body>
</html>');
</code></pre>
<!-- Try it Yourself example -->

                    <div class="mt-4">
                        <a href="<?= base_url('discussion') ?>" class="btn btn-outline-primary">Back to Topics</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Highlight.js JS -->
<script>
    hljs.highlightAll();
</script>

<?php $this->load->view('footer'); ?>
