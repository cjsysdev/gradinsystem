<script src="/assets/jquery-3.5.1.slim.min.js"></script>
<script src="/assets/popper.min.js"></script>
<script src="/assets/bootstrap.bundle.min.js"></script>

<script src="assets/highlights/11.7.0-highlight.min.js"></script>
<script>
    hljs.highlightAll();
</script>

<script>
    $(document).ready(function() {
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('collapsed');
            $('#content').toggleClass('collapsed');
        });
    });
</script>


</body>

</html>