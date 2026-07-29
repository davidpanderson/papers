<?php

$x = shell_exec('diff paper_orig.php paper.php');
echo "
Note:
<p>
< means the line was deleted
<p>
> means the line was added
<hr>
";
echo "<pre>";
echo htmlspecialchars($x);
echo "</pre>";

?>
