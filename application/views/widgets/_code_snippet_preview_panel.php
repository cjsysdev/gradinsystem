<?php
// Empty sandboxed preview frame + console pane for widgets/code_snippet.php.
// Filled by the shared script at the bottom of that view. The frame gets
// allow-scripts but NEVER allow-same-origin: student code runs in a unique
// origin and cannot read the LMS cookies, session or DOM.
?>
<div class="cs-preview-wrap" data-cs-preview-wrap>
    <iframe class="cs-frame" data-cs-frame sandbox="allow-scripts" title="Preview of the code"></iframe>
    <div class="cs-console" data-cs-console></div>
</div>
