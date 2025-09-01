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
                    <h1 class="mb-3" style="font-size:2.2rem;">C Structures (structs)</h1>
                    <hr>
                    <p>
                        <b>Structures</b> (structs) in C are user-defined data types that allow grouping variables of different types under a single name. They are useful for organizing complex data in a program.
                    </p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Syntax</h3>
                    <pre><code class="language-c">
struct StructName {
    type member1;
    type member2;
    // ...
};
</code></pre>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
#include &lt;stdio.h&gt;

struct Point {
    int x;
    int y;
};

int main() {
    struct Point p1 = {10, 20};
    printf("x = %d, y = %d", p1.x, p1.y);
    return 0;
}
</code></pre>
                    <div class="alert alert-info mt-3">
                        <strong>Output:</strong><br>
                        x = 10, y = 20
                    </div>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Accessing Members</h3>
                    <p>
                        You can access struct members using the dot (<code>.</code>) operator:
                    </p>
                    <pre><code class="language-c">
p1.x // Accesses x member
p1.y // Accesses y member
</code></pre>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Why Use Structs?</h3>
                    <ul>
                        <li>Group related variables together</li>
                        <li>Organize complex data</li>
                        <li>Improve code readability</li>
                    </ul>
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