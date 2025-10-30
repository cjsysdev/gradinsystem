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
                    <h1 class="mb-3" style="font-size:2.2rem;">Linked Lists</h1>
                    <hr>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Linked Lists</h3>
<p>A linked list consists of nodes with some sort of data, and a pointer, or link, to the next node.</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_linkedlists_singly.svg') ?>" alt="A singly linked list." class="img-fluid"></div>
<p>A big benefit with using linked lists is that nodes are stored wherever there is free space in memory, the nodes do not have to be stored contiguously right after each other like elements are stored in arrays. Another nice thing with linked lists is that when adding or removing nodes, the rest of the nodes in the list do not have to be shifted.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Linked Lists vs Arrays</h3>
<p>The easiest way to understand linked lists is perhaps by comparing linked lists with arrays.</p>
<p>Linked lists consist of nodes, and is a linear data structure we make ourselves, unlike arrays which is an existing data structure in the programming language that we can use.</p>
<p>Nodes in a linked list store links to other nodes, but array elements do not need to store links to other elements.</p>
<p>The table below compares linked lists with arrays to give a better understanding of what linked lists are.</p>
<div class="table-responsive"><table class="table table-bordered table-striped">
<tr><th></th><th>Arrays</th><th>Linked Lists</th></tr>
<tr><td>An existing data structure in the programming language</td><td>Yes</td><td>No</td></tr>
<tr><td>Fixed size in memory</td><td>Yes</td><td>No</td></tr>
<tr><td>Elements, or nodes, are stored right after each other in memory (contiguously)</td><td>Yes</td><td>No</td></tr>
<tr><td>Memory usage is low (each node only contains data, no links to other nodes)</td><td>Yes</td><td>No</td></tr>
<tr><td>Elements, or nodes, can be accessed directly (random access)</td><td>Yes</td><td>No</td></tr>
<tr><td>Elements, or nodes, can be inserted or deleted in constant time, no shifting operations in memory needed.</td><td>No</td><td>Yes</td></tr>
</table></div>
<p>To explain these differences in more detail, the next page will focus on how linked lists and arrays are stored in memory.</p>

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
